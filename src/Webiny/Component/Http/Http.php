<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http;

use Webiny\Component\StdLib\ComponentTrait;

/**
 * Http component.
 * This class is only used to register the component configuration.
 * To actually use the component use HttpTrait.
 *
 * @package         Webiny\Component\Http
 */
class Http
{
    use ComponentTrait;

    /**
     * Default component configuration
     * @var array
     */
    private static $defaultConfig = [
        'Session'        => [
            'Storage' => [
                'Driver'     => '\Webiny\Component\Http\Session\Storage\NativeStorage',
                'Prefix'     => '',
                'ExpireTime' => 86400
            ]
        ],
        //'TrustedProxies' => ['127.0.0.1'],
        'TrustedHeaders' => [
            //'client_ip'    => 'X_FORWARDED_FOR', # needs to be explicitly enabled because this can be a security issue
            'client_host'  => 'X_FORWARDED_HOST',
            'client_proto' => 'X_FORWARDED_PROTO',
            'client_port'  => 'X_FORWARDED_PORT'
        ],
        'Cookie'         => [
            'Storage'    => [
                'Driver' => '\Webiny\Component\Http\Cookie\Storage\NativeStorage'
            ],
            'Prefix'     => '',
            'HttpOnly'   => 'true',
            'ExpireTime' => 86400
        ]
    ];
}