<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\EventManager\EventManagerTrait;
use Webiny\Component\Security\Authentication\Firewall;
use Webiny\Component\Security\Encoder\Encoder;
use Webiny\Component\Security\User\Providers\Memory\MemoryProvider;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * The security class initializes the whole security layer that consists of firewall and access controls.
 * The class checks if we are inside the firewall, and what is the state of the current user, is he authenticated or not.
 * Once we have the user, the security class check with the authorization layer (UAC) what roles are required to access
 * the current part of the site, and check is current user has the necessary role to enter this area.
 *
 * @package         Webiny\Component\Security
 */
class Security
{
    use SingletonTrait, StdLibTrait, FactoryLoaderTrait, EventManagerTrait, ComponentTrait;

    /**
     * Current firewall that took over the request.
     * @var Firewall
     */
    private $_firewalls;

    /**
     * @var array A list of currently built-in user providers. The keys are used so you don't need to write
     *            the fully qualified class names in the yaml config.
     */
    private static $_userProviders = [
        'Entity'       => '\Webiny\Component\Security\User\Providers\Entity\Entity',
        'Memory'       => '\Webiny\Component\Security\User\Providers\Memory\Memory',
        'OAuth2'       => '\Webiny\Component\Security\User\Providers\OAuth2\OAuth2',
        'TwitterOAuth' => '\Webiny\Component\Security\User\Providers\TwitterOAuth\TwitterOAuth'
    ];

    /**
     * @var array A list of currently built-in encoders. The keys are used so you don't need to write
     *            the fully qualified class names in the yaml config.
     */
    private static $_encoders = [
        'Crypt' => '\Webiny\Component\Security\Encoder\Drivers\Crypt',
        'Null'  => '\Webiny\Component\Security\Encoder\Drivers\Null'
    ];


    /**
     * Initializes the security layer for a specific firewall.
     *
     * @param string $firewallKey Name of the firewall you wish to return.
     *                            If you don't pass the name param, the first firewall from your configuration
     *                            will be used.
     *
     * @throws SecurityException
     * @return Firewall
     */
    public function firewall($firewallKey = '')
    {
        // initialize firewall
        if (isset($this->_firewalls[$firewallKey])) {
            $fw = $this->_firewalls[$firewallKey];
        } else {
            if ($firewallKey == '') {
                $firewall = $this->getConfig()->Firewalls[0];
                if (empty($firewall)) {
                    throw new SecurityException("There are no firewalls defined inside your configuration.");
                }
            } else {
                $firewall = $this->getConfig()->Firewalls->get($firewallKey, false);


                if (!$firewall) {
                    throw new SecurityException("Firewall '" . $firewallKey . "' is not defined under Security.Firewalls."
                    );
                }
            }

            $fw = new Firewall($firewallKey, $firewall, $this->_getFirewallUserProviders($firewallKey),
                               $this->_getFirewallEncoder($firewallKey)
            );

            $this->_firewalls[$firewallKey] = $fw;
        }

        return $fw;
    }

    /**
     * Returns an array of instances of user providers for the given firewall.
     * NOTE: this function also checks for chain providers.
     *
     * @param string $firewallKey Firewall name.
     *
     * @return array Array of user provider instances for the given firewall.
     * @throws SecurityException
     */
    private function _getFirewallUserProviders($firewallKey)
    {
        $userProviders = [];

        $firewallProviders = $this->_getFirewallConfig($firewallKey)->get('UserProviders', false);
        if (!$firewallProviders || count($firewallProviders) < 1) {
            throw new SecurityException('User providers for firewall "' . $firewallKey . '" are not defined.');
        }

        $providers = $this->getConfig()->get('UserProviders', []);

        foreach ($firewallProviders as $pk) {

            // get global config
            $gConfig = $providers->get($pk, false);

            // get firewall driver and params
            if (isset($gConfig->Driver)) {
                $driver = $gConfig->Driver;
                if (isset(self::$_userProviders[$driver])) { // short-hand driver name in the global config
                    $driver = self::$_userProviders[$driver];
                }
                $params = $gConfig->get('Params', null, true);
            } else if (isset(self::$_userProviders[$pk])) {
                $driver = self::$_userProviders[$pk];
                $params = null;
            } else {
                throw new SecurityException('User providers "' . $pk . '" is missing a Driver.');
            }

            // In case of memory user provider, we don't have the parameters, but we have the user accounts
            // these need to be passed to the constructor.
            // Not the best way to do it, but keeps the yaml config simpler
            if ($driver == self::$_userProviders['Memory'] && $params == null) {
                $params = $gConfig->toArray();
            }

            try {
                $userProviders[$pk] = $this->factory($driver, '\Webiny\Component\Security\User\UserProviderInterface',
                                                     is_null($params) ? null : [$params]
                );
            } catch (\Exception $e) {
                throw new SecurityException($e->getMessage());
            }
        }

        if (count($userProviders) < 1) {
            throw new SecurityException('Unable to detect the user providers for "' . $firewallKey . '" firewall.');
        }

        return $userProviders;
    }

    /**
     * Returns the encoder instance for the given firewall.
     *
     * @param string $firewallKey Firewall name.
     *
     * @return Encoder
     * @throws SecurityException
     */
    private function _getFirewallEncoder($firewallKey)
    {
        // get the encoder name
        $encoderName = $this->_getFirewallConfig($firewallKey)->get('Encoder', 'Crypt');
        if (!$encoderName) {
            $encoderName = 'Null';
        }

        // check if the encoder is defined in the global config
        $encoderConfig = $this->getConfig()->get('Encoders.' . $encoderName, false);

        // get the driver & params
        $driver = false;
        $params = null;
        if ($encoderConfig) {
            $driver = $encoderConfig->get('Driver', false);
            $params = $encoderConfig->get('Params', null, true);
        }

        // get the driver class name
        if (!$driver && isset(self::$_encoders[$encoderName])) { // use built-in driver
            $driver = self::$_encoders[$encoderName];
        } else if (isset(self::$_encoders[$driver])) { // driver defined as short-name built-in driver
            $driver = self::$_encoders[$driver];
        } else if (!$driver) {
            throw new SecurityException('Invalid "Driver" param for "' . $encoderName . '" encoder.');
        }

        // create encoder instance
        return new Encoder($driver, $params);
    }

    /**
     * @param $firewallKey
     *
     * @return ConfigObject
     */
    private function _getFirewallConfig($firewallKey)
    {
        return $this->getConfig()->Firewalls->{$firewallKey};
    }
}
