<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap\ApplicationClasses;

use Webiny\Component\Bootstrap\Bootstrap;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Http\Response;
use Webiny\Component\TemplateEngine\Bridge\TemplateEngine;

/**
 * Application class holds all the application data and sends the response back to the browser.
 *
 * @package         Webiny\Component\Bootstrap\ApplicationClasses
 */
class Application
{
    use HttpTrait;

    /**
     * @var View View instance.
     */
    private $_view;


    /**
     * Base constructor.
     */
    public function __construct()
    {
        $this->_view = new View();
    }

    /**
     * Get application namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return Bootstrap::getInstance()->getEnvironment()->getApplicationConfig()->get('Application.Namespace');
    }

    /**
     * Get application root absolute path.
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return Bootstrap::getInstance()->getEnvironment()->getApplicationAbsolutePath();
    }

    /**
     * Get application web path.
     *
     * @return string
     */
    public function getWebPath()
    {
        $webPath = Bootstrap::getInstance()->getEnvironment()->getCurrentEnvironmentConfig()->get('Domain', false);
        if(!$webPath){
            $webPath = $this->httpRequest()->getCurrentUrl(true)->getDomain().'/';
        }

        return $webPath;
    }

    /**
     * Get the name of current environment.
     *
     * @return string
     * @throws \Webiny\Component\Bootstrap\BootstrapException
     */
    public function getEnvironment()
    {
        return Bootstrap::getInstance()->getEnvironment()->getCurrentEnvironmentName();
    }

    /**
     * Get the application configuration (config from App.yaml file).
     *
     * @param string $query   Query inside the application config.
     * @param null   $default Default value which should be returned if query has no matches.
     *
     * @return mixed|ConfigObject
     */
    public function getApplicationConfig($query = '', $default = null)
    {
        if ($query == '') {
            return Bootstrap::getInstance()->getEnvironment()->getApplicationConfig()->get('Application', $default);
        } else {
            return Bootstrap::getInstance()
                            ->getEnvironment()
                            ->getApplicationConfig()
                            ->get('Application.' . $query, $default);
        }
    }

    /**
     * Returns the current environment configuration.
     *
     * @param string $query   Query inside the environment configuration.
     * @param null   $default Default value which should be returned if query has no matches.
     *
     * @return mixed|ConfigObject
     */
    public function getEnvironmentConfig($query = '', $default = null)
    {
        if ($query == '') {
            return Bootstrap::getInstance()->getEnvironment()->getCurrentEnvironmentConfig();
        } else {
            return Bootstrap::getInstance()->getEnvironment()->getCurrentEnvironmentConfig()->get($query, $default);
        }
    }

    /**
     * Get a component configuration (configurations from within the environment folder).
     *
     * @param string $component Component name.
     * @param string $query     Query inside the component config.
     * @param null   $default   Default value which should be returned if query has no matches.
     *
     * @return mixed|ConfigObject
     */
    public function getComponentConfig($component, $query = '', $default = null)
    {
        if ($query == '') {
            return Bootstrap::getInstance()->getEnvironment()->getComponentConfigs()[$component];
        } else {
            return Bootstrap::getInstance()->getEnvironment()->getComponentConfigs()[$component]->get($query, $default);
        }
    }

    /**
     * Get the view instance.
     *
     * @return View
     */
    public function view()
    {
        return $this->_view;
    }

    /**
     * Send the html response to the browser.
     * This method is automatically called.
     *
     * @return Response
     * @throws \Exception
     * @throws \Webiny\Component\TemplateEngine\Bridge\TemplateEngineException
     */
    public function htmlResponse()
    {
        // get view data
        $viewData = $this->view()->getAssignedData();

        // assign application data to view
        $viewData['App'] = [
            'Config'       => $this->getEnvironmentConfig(),
            'Components'   => Bootstrap::getInstance()->getEnvironment()->getComponentConfigs()->toArray(),
            'Environment'  => $this->getEnvironment(),
            'AbsolutePath' => $this->getAbsolutePath(),
            'WebPath'      => $this->getWebPath()
        ];

        // initialize the template engine
        $teConfig = $this->getComponentConfig('TemplateEngine', 'Engines')->toArray();
        reset($teConfig);
        $teName = key($teConfig);
        $teInstance = TemplateEngine::getInstance($teName,
                                                  $this->getComponentConfig('TemplateEngine', 'Engines.' . $teName)
        );

        // render the view and assign it to the response object
        return new Response($teInstance->fetch($this->view()->getTemplate(), $viewData));
    }
}