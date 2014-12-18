<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap\ApplicationClasses;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Router\Router;
use Webiny\Component\TemplateEngine\Bridge\TemplateEngine;

/**
 * Application class holds all the application data.
 *
 * @package         Webiny\Component\Bootstrap\ApplicationClasses
 */
class Application
{
    private $_appConfig;
    private $_componentConfig;
    private $_environment;
    private $_absolutePath;

    private $_view;

    public function __construct(ConfigObject $appConfig, ConfigObject $componentConfig, $environment)
    {
        $this->_appConfig = $appConfig;
        $this->_componentConfig = $componentConfig;
        $this->_environment = $environment;

        $this->_view = new View();
    }

    public function getNamespace()
    {
        return $this->_appConfig->get('Application.Namespace');
    }

    public function setAbsolutePath($absolutePath)
    {
        $this->_absolutePath = $absolutePath;
    }

    public function getAbsolutePath()
    {
        return $this->_absolutePath;
    }

    public function getWebPath()
    {
        return $this->_appConfig->get('Application.Environments' . $this->_environment . '.Domain');
    }

    public function getEnvironment()
    {
        return $this->_environment;
    }

    public function getApplicationConfig($query = '', $default = null)
    {
        if ($query == '') {
            return $this->_appConfig->get('Application', $default);
        } else {
            return $this->_appConfig->get('Application.' . $query, $default);
        }
    }

    public function getEnvironmentConfig($query = '', $default = null)
    {
        if ($query == '') {
            return $this->_appConfig->get('Application.Environments.' . $this->_environment, $default);
        } else {
            return $this->_appConfig->get('Application.Environments.' . $this->_environment . '.' . $query, $default);
        }
    }

    public function getComponentConfig($component, $query = '', $default = null)
    {
        if ($query == '') {
            return $this->_componentConfig->get($component, $default);
        } else {
            return $this->_componentConfig->get($component . '.' . $query, $default);
        }
    }

    /**
     * @return View
     */
    public function view()
    {
        return $this->_view;
    }

    public function htmlResponse()
    {
        // get view data
        $viewData = $this->view()->getAssignedData();

        // assign application data to view
        $viewData['App'] = [
            'Config'       => $this->getEnvironmentConfig(),
            'Components'   => $this->_componentConfig->toArray(),
            'Environment'  => $this->getEnvironment(),
            'AbsolutePath' => $this->getAbsolutePath(),
            'WebPath'      => $this->getWebPath()
        ];

        // initialize the template engine
        $teConfig = $this->_componentConfig->get('TemplateEngine.Engines')->toArray();
        reset($teConfig);
        $teName = key($teConfig);
        $teInstance = TemplateEngine::getInstance($teName,
                                                  $this->_componentConfig->get('TemplateEngine.Engines.' . $teName)
        );

        // render the view and assign it to the response object
        return new \Webiny\Component\Http\Response($teInstance->fetch($this->view()->getTemplate(), $viewData));
    }
}