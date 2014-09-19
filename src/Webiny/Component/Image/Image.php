<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image;

use Webiny\Component\StdLib\ComponentTrait;

/**
 * Image component.
 * This class provides methods for setting and returning the component configuration.
 *
 * @package         Webiny\Component\Image
 */
class Image
{
    use ComponentTrait;

    /**
     * @var array Default configuration params.
     */
    private static $_defaultConfig = [
        'Library' => 'gd',
        'Quality' => 90
    ];
}