<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Tests\Bridge\Smarty\Mocks;

use Webiny\Component\TemplateEngine\Plugin;

class PluginMock extends Plugin
{

    public function __construct()
    {
        parent::__construct('myCustomUpper', 'modifier', PluginMock::class . '::myCallback');
    }

    public static function myCallback($val)
    {
        return strtoupper($val);
    }
}