<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap\ApplicationTraits;

use Webiny\Component\Bootstrap\ApplicationClasses\Application;

/**
 * Description
 *
 * @package         Webiny\Component\
 */
trait AppTrait
{
    private $_app;

    /**
     * @return Application
     */
    public function app()
    {
        return $this->_app;
    }

    /**
     * @param Application $app
     */
    public function setAppInstance(Application $app = null)
    {
        $this->_app = $app;
    }

    public function setUp(){}
}