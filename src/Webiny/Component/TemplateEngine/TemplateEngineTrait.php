<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine;

/**
 * TemplateEngineTrait provides easier access to template engine methods.
 *
 * @package         Webiny\Component\TemplateEngine
 */

trait TemplateEngineTrait
{

    /**
     * Get template engine instance.
     *
     * @param string $driver Name of the driver. Default driver is 'Smarty'.
     *
     * @return \Webiny\Component\TemplateEngine\Bridge\TemplateEngineInterface
     */
    function templateEngine($driver = 'Smarty')
    {
        return TemplateEngineLoader::getInstance($driver);
    }

}