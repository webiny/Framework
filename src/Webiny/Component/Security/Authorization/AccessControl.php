<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Authorization;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Authorization\Voters\AuthenticationVoter;
use Webiny\Component\Security\Authorization\Voters\RoleVoter;
use Webiny\Component\Security\Authorization\Voters\VoterInterface;
use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\User\UserAbstract;
use Webiny\Component\ServiceManager\ServiceManagerTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Access control class talks to the voters and accumulates the vote scoring. Then, based on the selected decision
 * strategy, makes a ruling either to allow the access for the current user, or to deny it.
 *
 * @package         Webiny\Component\Security\Authorization
 */
class AccessControl
{
    use StdLibTrait, HttpTrait, ServiceManagerTrait;

    // 1 single voter denies access
    const VOTER_STR_UNANIMOUS = 'unanimous';

    // 1 single vote grants access
    const VOTER_STR_AFFIRMATIVE = 'affirmative';

    // Majority wins
    const VOTER_STR_CONSENSUS = 'consensus';

    /**
     * Access control configuration
     * @var \Webiny\Component\Config\ConfigObject
     */
    private $config;

    /**
     * Selected decision strategy.
     * @var string
     */
    private $strategy;

    /**
     * Current path - based on current request.
     * @var \Webiny\Component\StdLib\StdObject\StringObject\StringObject
     */
    protected $currentPath;


    /**
     * Base constructor.
     *
     * @param UserAbstract $user   Instance of current user.
     * @param ConfigObject $config Access control configuration.
     */
    public function __construct(UserAbstract $user, ConfigObject $config)
    {
        $this->config = $config;
        $this->user = $user;
        $this->setDecisionStrategy();
    }

    /**
     * Checks if current user is allowed access.
     *
     * @return bool
     */
    public function isUserAllowedAccess()
    {
        $requestedRoles = $this->getRequestedRoles();

        // we allow access if there are no requested roles that the user must have
        if (count($requestedRoles) < 1) {
            return true;
        }

        return $this->getAccessDecision($requestedRoles);
    }

    /**
     * Creates an array of registered Voters.
     *
     * @return array Array of registered voters.
     */
    private function getVoters()
    {
        // we have 2 built in voters
        $voters = $this->servicesByTag('Security.Voter',
                                       '\Webiny\Component\Security\Authorization\Voters\RoleVoterInterface'
        );

        $voters[] = new AuthenticationVoter();
        $voters[] = new RoleVoter();

        return $voters;
    }

    /**
     * Returns an array of roles required by the access rule.
     *
     * @return array
     */
    private function getRequestedRoles()
    {
        $rules = $this->config->get('Rules', false);
        if (!$rules) {
            return [];
        }

        // see which of the rules matches the path and extract the requested roles for access
        $returnRoles = [];
        foreach ($rules as $r) {
            $path = $r->get('Path', false);
            if ($path && $this->testPath($path)) {
                $roles = $r->get('Roles', []);
                if ($this->isString($roles)) {
                    $roles = (array)$roles;
                } else {
                    $roles = $roles->toArray();
                }

                // covert the role names to Role instances
                foreach ($roles as $role) {
                    $returnRoles[] = new Role($role);
                }

                return $returnRoles;
            }
        }

        return [];
    }

    /**
     * Tests the given $path if it's within the current request path.
     *
     * @param string $path Path against to whom to test.
     *
     * @return bool True if path is within the current request, otherwise false.
     */
    private function testPath($path)
    {
        return $this->getCurrentPath()->match($path);
    }

    /**
     * Sets the decision strategy based on the application configuration.
     *
     * @throws AccessControlException
     */
    private function setDecisionStrategy()
    {
        $strategy = $this->config->get('DecisionStrategy', 'unanimous');
        if ($strategy != self::VOTER_STR_AFFIRMATIVE && $strategy != self::VOTER_STR_CONSENSUS && $strategy != self::VOTER_STR_UNANIMOUS
        ) {

            throw new AccessControlException('Invalid access control decision strategy "' . $strategy . '"');
        }

        $this->strategy = $strategy;
    }

    /**
     * This method get the votes from all the voters and sends them to the ruling.
     * The result of ruling is then returned.
     *
     * @param array $requestedRoles An array of requested roles for the current access map.
     *
     * @return bool True if access is allowed to the current user, otherwise false.
     */
    private function getAccessDecision(array $requestedRoles)
    {
        $voters = $this->getVoters();
        $userClassName = get_class($this->user);

        $voteScore = 0;
        $maxScore = 0;
        foreach ($voters as $v) {
            /**
             * @var $v VoterInterface
             */
            if ($v->supportsUserClass($userClassName)) {
                $vote = $v->vote($this->user, $requestedRoles);
                if ($this->strategy == self::VOTER_STR_AFFIRMATIVE) {
                    if ($vote > 0) {
                        $maxScore++;
                        $voteScore += $vote;
                    }
                } else {
                    if ($this->strategy == self::VOTER_STR_CONSENSUS) {
                        if ($vote > 0) {
                            $voteScore += $vote;
                        }
                        $maxScore++;
                    } else {
                        if ($vote <> 0) {
                            $maxScore++;
                            $voteScore += $vote;
                        }
                    }
                }
            }
        }

        return $this->whatsTheRuling($voteScore, $maxScore);
    }

    /**
     * Method that decides if access is allowed or not based on the results of votes and the defined decision strategy.
     *
     * @param int $votes    The voting score.
     * @param int $maxVotes Max possible number of votes.
     *
     * @return bool True if access is allowed, otherwise false.
     */
    private function whatsTheRuling($votes, $maxVotes)
    {
        switch ($this->strategy) {
            case self::VOTER_STR_UNANIMOUS:
                return ($votes == $maxVotes);
                break;

            case self::VOTER_STR_CONSENSUS:
                return ($votes > ($maxVotes - $votes));
                break;

            case self::VOTER_STR_AFFIRMATIVE:
                return ($votes > 0);
                break;
        }

        return false;
    }

    /**
     * Returns current path (url) as StringObject instance.
     *
     * @return \Webiny\Component\StdLib\StdObject\StringObject\StringObject
     */
    private function getCurrentPath()
    {
        if (is_null($this->currentPath)) {
            $this->currentPath = $this->str($this->httpRequest()->getCurrentUrl(true)->getPath());
        }

        return $this->currentPath;
    }
}