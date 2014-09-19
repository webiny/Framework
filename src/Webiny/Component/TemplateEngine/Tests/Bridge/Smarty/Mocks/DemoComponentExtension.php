<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Tests\Bridge\Smarty\Mocks;

use Webiny\Component\TemplateEngine\Drivers\Smarty\SmartyExtensionAbstract;
use Webiny\Component\TemplateEngine\Drivers\Smarty\SmartySimplePlugin;

class DemoComponentExtension extends SmartyExtensionAbstract
{
    public static function myCallback($val)
    {
        return strtolower($val);
    }

    public function getModifiers()
    {
        return [
            new SmartySimplePlugin('demoComponentPlugin', 'modifier',
                                   '\Webiny\Component\TemplateEngine\Tests\Bridge\Smarty\Mocks\DemoComponentExtension::myCallback'
            )
        ];
    }

    /**
     * Returns the name of the plugin.
     *
     * @return string
     */
    public function getName()
    {
        return 'DemoComponentExtension';
    }
}