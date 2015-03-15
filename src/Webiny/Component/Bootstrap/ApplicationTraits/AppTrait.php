<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap\ApplicationTraits;

use Webiny\Component\Bootstrap\ApplicationClasses\Application;

/**
 * Application trait is a helper for quicker access to the Application instance.
 *
 * @package         Webiny\Component\Bootstrap\ApplicationTraits
 */
trait AppTrait
{
    /**
     * @var Application Application instance.
     */
    private $app;


    /**
     * Get current application instance.
     *
     * @return Application
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * Set the application instance.
     *
     * @param Application $app Application instance.
     */
    public function setAppInstance(Application $app = null)
    {
        $this->app = $app;
    }

    /**
     * Overwrite this method in order to issue some operations, before the action method is called.
     */
    public function setUp(){}
}