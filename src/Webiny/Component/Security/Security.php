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
     * List of encoder instances.
     * @var array
     */
    private $_encoders = [];

    /**
     * List of user provider instances.
     * @var array
     */
    private $_userProviders = [];


    /**
     * Initialize security.
     */
    protected function _init()
    {
        $this->_initEncoders();
        $this->_initUserProviders();
    }

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

            $fw = new Firewall($firewallKey, $firewall, $this->_getFirewallUserProviders($firewallKey
            ), $this->_getFirewallEncoder($firewallKey)
            );

            $this->_firewalls[$firewallKey] = $fw;
        }

        return $fw;
    }

    /**
     * Initialize user providers defined for this firewall.
     *
     * @throws SecurityException
     */
    private function _initUserProviders()
    {
        $providers = $this->getConfig()->get('UserProviders', []);

        if (count($providers) < 1) {
            throw new SecurityException('There are no user providers defined. Please define at last one provider.');
        }

        foreach ($providers as $pk => $provider) {
            if (is_object($provider)) {
                if (isset($provider->Driver)) {
                    try {
                        $params = $provider->get('Params', null, true);
                        $this->_userProviders[$pk] = $this->factory($provider->Driver,
                                                                    '\Webiny\Component\Security\User\UserProviderInterface',
                                                                    is_null($params) ? null : [$params]
                        );
                    } catch (\Exception $e) {
                        throw new SecurityException($e->getMessage());
                    }
                } else {
                    $this->_userProviders[$pk] = new MemoryProvider($provider->toArray());
                }
            } else {
                throw new SecurityException('Unable to read user provider "' . $pk . '".');
            }
        }
    }

    /**
     * Create the encoder instance.
     * If encoder is not defined, we create an instance of Null encoder.
     */
    private function _initEncoders()
    {
        $encoders = $this->getConfig()->get('Encoders', []);

        if (count($encoders) > 0) {
            foreach ($encoders as $ek => $encoder) {
                // encoder params
                $driver = $encoder->get('Driver', false);
                if (!$driver) {
                    throw new SecurityException('Encoder "Driver" param must be present.');
                }
                $salt = $encoder->get('Salt', '');
                $params = $encoder->get('Params', null);
                if ($params) {
                    $params = $params->toArray();
                }

                // encoder instance
                $this->_encoders[$ek] = new Encoder($driver, $salt, $params);
            }
        }

        if (!isset($this->_encoders['_null'])) {
            $encoder = '\Webiny\Component\Security\Encoder\Drivers\Null';
            $salt = '';
            $params = null;
            $this->_encoders['_null'] = new Encoder($encoder, $salt, $params);
        }
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

        // get the provider name
        $providers = $this->_getFirewallConfig($firewallKey)->get('UserProviders', false);
        if (!$providers) {
            throw new SecurityException('User providers for firewall "' . $firewallKey . '" are not defined.');
        }

        foreach ($providers as $p) {
            if (!isset($this->_userProviders[$p])) {
                throw new SecurityException('User provider "' . $p . '" missing for firewall "' . $firewallKey . '".');
            }

            $userProviders[] = $this->_userProviders[$p];
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
        $encoder = $this->_getFirewallConfig($firewallKey)->get('Encoder', '_null');
        if (!isset($this->_encoders[$encoder])) {
            if ($encoder != '') {
                throw new SecurityException('Encoder "' . $encoder . '" is not defined in your Security.Encoders config.'
                );
            } else {
                return $this->_encoders['_null'];
            }
        }

        return $this->_encoders[$encoder];
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
