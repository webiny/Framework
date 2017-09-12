<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Tests\Bridge\Smarty\Mocks;

use Webiny\Component\TemplateEngine\Drivers\Smarty\AbstractSmartyExtension;
use Webiny\Component\TemplateEngine\Drivers\Smarty\SmartySimplePlugin;

class DemoComponentExtension extends AbstractSmartyExtension
{
    public static function myCallback($val)
    {
        return strtolower($val);
    }

    public function getModifiers()
    {
        return [
            new SmartySimplePlugin('demoComponentPlugin', 'modifier', DemoComponentExtension::class . '::myCallback')
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