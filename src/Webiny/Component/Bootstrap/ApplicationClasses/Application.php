<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap\ApplicationClasses;

use Webiny\Component\Bootstrap\Environment;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Http;
use Webiny\Component\Http\Request;
use Webiny\Component\Http\Response;
use Webiny\Component\TemplateEngine\TemplateEngine;
use Webiny\Component\TemplateEngine\TemplateEngineLoader;

/**
 * Application class holds all the application data and sends the response back to the browser.
 *
 * @package         Webiny\Component\Bootstrap\ApplicationClasses
 */
class Application
{
    /**
     * @var View View instance.
     */
    private $_view;


    private $_environment;

    /**
     * Base constructor.
     *
     * @param Environment $environment Current application environment.
     *
     */
    public function __construct(Environment $environment)
    {
        $this->_view = new View();
        $this->_environment = $environment;
    }

    /**
     * Get application namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_environment->getApplicationConfig()->get('Namespace');
    }

    /**
     * Get application root absolute path.
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->_environment->getApplicationAbsolutePath();
    }

    /**
     * Get application web path.
     *
     * @return string
     */
    public function getWebPath()
    {
        $webPath = $this->_environment->getCurrentEnvironmentConfig()->get('Domain', false);
        if (!$webPath) {
            $webPath = Request::getInstance()->getCurrentUrl(true)->getDomain() . '/';
        }

        return $webPath;
    }

    /**
     * Get the name of current environment.
     *
     * @return string
     * @throws \Webiny\Component\Bootstrap\BootstrapException
     */
    public function getEnvironmentName()
    {
        return $this->_environment->getCurrentEnvironmentName();
    }

    /**
     * Checks if current environment is Production.
     *
     * @return bool
     */
    public function isProductionEnvironment()
    {
        return ($this->getEnvironmentName() == 'Production');
    }

    /**
     * Based on the current environment configuration, should system errors be shown or not.
     *
     * @return bool
     */
    public function showErrors()
    {
        $reporting = $this->getEnvironmentConfig('ErrorReporting', false);
        if ($reporting && strtolower($reporting) == 'on') {
            return true;
        }

        return false;
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
            return $this->_environment->getApplicationConfig();
        } else {
            return $this->_environment->getApplicationConfig()->get($query, $default);
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
            return $this->_environment->getCurrentEnvironmentConfig();
        } else {
            return $this->_environment->getCurrentEnvironmentConfig()->get($query, $default);
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
        $componentConfig = $this->_environment->getComponentConfigs()->get($component, false);

        if (!$componentConfig) {
            return $default;
        }

        if($query==''){
            return $componentConfig;
        }

        return $componentConfig->get($query, $default);
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
     * Send the http response to the browser.
     * This method is called automatically by the Dispatcher.
     *
     * @return Response|bool
     * @throws \Exception
     * @throws \Webiny\Component\TemplateEngine\Bridge\TemplateEngineException
     */
    public function httpResponse()
    {
        // get the template
        $template = $this->view()->getTemplate();

        // if there is no template, then we return false
        if(empty($template)) {
            return false;
        }

        // get view data
        $viewData = $this->view()->getAssignedData();

        // assign application data to view
        $viewData['App'] = [
            'Config'       => $this->getEnvironmentConfig(),
            'Components'   => $this->_environment->getComponentConfigs()->toArray(),
            'Environment'  => $this->getEnvironmentName(),
            'AbsolutePath' => $this->getAbsolutePath(),
            'WebPath'      => $this->getWebPath()
        ];

        // render the view and assign it to the response object
        return new Response($this->_getTemplateEngineInstance()->fetch($this->view()->getTemplate(), $viewData));
    }

    /**
     * Returns template engine instance, based on current configuration.
     * If a template engine is not defined, a default template engine instance will be created.
     *
     * @return \Webiny\Component\TemplateEngine\Bridge\TemplateEngineInterface
     * @throws \Exception
     * @throws \Webiny\Component\StdLib\Exception\Exception
     * @throws \Webiny\Component\TemplateEngine\TemplateEngineException
     */
    private function _getTemplateEngineInstance()
    {
        $teConfig = $this->getComponentConfig('TemplateEngine', 'Engines', false);

        // fallback to default template engine
        if (!$teConfig) {
            $defaultTemplateEngineConfig = [
                'Engines' => [
                    'Smarty' => [
                        'ForceCompile'     => false,
                        'CacheDir'         => $this->getAbsolutePath() . 'App/Cache/Smarty/Cache',
                        'CompileDir'       => $this->getAbsolutePath() . 'App/Cache/Smarty/Compile',
                        'TemplateDir'      => $this->getAbsolutePath() . 'App/Layouts',
                        'AutoEscapeOutput' => false,
                    ]
                ]
            ];

            TemplateEngine::setConfig(new ConfigObject($defaultTemplateEngineConfig));

            return TemplateEngineLoader::getInstance('Smarty');
        }

        $teConfig = $this->getComponentConfig('TemplateEngine', 'Engines')->toArray();
        reset($teConfig);
        $teName = key($teConfig);

        return TemplateEngineLoader::getInstance($teName);
    }
}