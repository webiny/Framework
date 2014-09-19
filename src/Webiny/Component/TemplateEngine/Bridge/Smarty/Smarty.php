<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Bridge\Smarty;

use Webiny\Component\TemplateEngine\Bridge\TemplateEngineInterface;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\ServiceManager\ServiceManagerTrait;
use Webiny\Component\TemplateEngine\Drivers\Smarty\SmartyExtensionInterface;
use Webiny\Component\TemplateEngine\Plugin;

/**
 * Template engine bridge for Smarty library.
 *
 * @package         Webiny\Component\TemplateEngine\Bridge\Smarty
 */
class Smarty implements TemplateEngineInterface
{
    use ServiceManagerTrait;

    /**
     * @var \Smarty Holds smarty instance.
     */
    private $_smarty;


    /**
     * Base constructor.
     *
     * @param ConfigObject $config Configuration for the template engine.
     *
     * @throws SmartyException
     */
    public function __construct(ConfigObject $config)
    {
        $this->_smarty = new \Smarty();

        // compile dir
        $compileDir = $config->get('CompileDir', false);
        if (!$compileDir) {
            throw new SmartyException('Configuration error, "CompileDir" is missing.');
        }
        $this->setCompileDir($compileDir);

        // cache dir
        $cacheDir = $config->get('CacheDir', false);
        if (!$cacheDir) {
            throw new SmartyException('Configuration error, "CacheDir" is missing.');
        }
        $this->setCacheDir($cacheDir);

        // template dir
        $templateDir = $config->get('TemplateDir', false);
        if (!$templateDir) {
            throw new SmartyException('Configuration error, "TemplateDir" is missing.');
        }
        $this->setTemplateDir($templateDir);

        // force compile
        if ($config->get('ForceCompile', false)) {
            $this->setForceCompile(false);
        }

        // register extensions
        $this->registerExtensions();
    }

    /**
     * Set Smarty compile dir.
     *
     * @param string $compileDir Absolute path where to store compiled files.
     */
    public function setCompileDir($compileDir)
    {
        if (!file_exists($compileDir)) {
            mkdir($compileDir, 0755, true);
        }

        $this->_smarty->setCompileDir($compileDir);
    }

    /**
     * Returns the current compile dir.
     *
     * @return string Absolute path to compile dir.
     */
    public function getCompileDir()
    {
        return realpath($this->_smarty->getCompileDir());
    }

    /**
     * Set Smarty cache dir.
     *
     * @param string $cacheDir Absolute path where to store cache files.
     */
    public function setCacheDir($cacheDir)
    {
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $this->_smarty->setCacheDir($cacheDir);
    }

    /**
     * Returns the current cache dir.
     *
     * @return string Absolute path to cache dir.
     */
    public function getCacheDir()
    {
        return realpath($this->_smarty->getCacheDir());
    }

    /**
     * Root path where the templates are stored.
     *
     * @param string $templateDir
     *
     * @throws SmartyException
     * @internal param string $path Absolute path to the directory that holds the templates.
     *
     * @return void
     */
    public function setTemplateDir($templateDir)
    {
        $this->_smarty->setTemplateDir($templateDir);
        if (!$this->getTemplateDir()) {
            throw new SmartyException("The template dir '" . $templateDir . "' does not exist.");
        }
    }

    /**
     * Returns the current template dir.
     *
     * @return string Absolute path to template dir.
     */
    public function getTemplateDir()
    {
        return realpath($this->_smarty->getTemplateDir()[0]);
    }

    /**
     * Force to re-compile the templates on every refresh.
     *
     * @param bool $forceCompile
     */
    public function setForceCompile($forceCompile)
    {
        $this->_smarty->force_compile = $forceCompile;
    }

    /**
     * Returns the current value of force_compile flag.
     *
     * @return bool
     */
    public function getForceCompile()
    {
        return $this->_smarty->force_compile;
    }

    /**
     * Fetch the template from the given location, parse it and return the output.
     *
     * @param string $template   Path to the template.
     * @param array  $parameters A list of parameters to pass to the template.
     *
     * @throws SmartyException
     * @return string Parsed template.
     */
    public function fetch($template, $parameters = [])
    {
        try {
            $this->_smarty->assign($parameters);

            return $this->_smarty->fetch($template);
        } catch (\Exception $e) {
            throw new SmartyException($e->getMessage());
        }
    }

    /**
     * Fetch the template from the given location, parse it and output the result to the browser.
     *
     * @param string $template   Path to the template.
     * @param array  $parameters A list of parameters to pass to the template.
     *
     * @return void
     */
    public function render($template, $parameters = [])
    {
        echo $this->_smarty->fetch($template, $parameters);
    }

    /**
     * Assign a variable and its value into the template engine.
     *
     * @param string $var   Variable name.
     * @param mixed  $value Variable value.
     *
     * @return void
     */
    public function assign($var, $value)
    {
        $this->_smarty->assign($var, $value);
    }

    /**
     * Register a plugin for the template engine.
     *
     * @param Plugin $plugin
     *
     * @throws \Exception|SmartyException
     * @return void
     */
    public function registerPlugin(Plugin $plugin)
    {
        try {
            $this->_smarty->registerPlugin($plugin->getType(), $plugin->getName(), $plugin->getCallbackFunction(),
                                           $plugin->getAttribute('Cachable', true),
                                           $plugin->getAttribute('CacheAttr', null)
            );
        } catch (\SmartyException $e) {
            throw new SmartyException($e);
        }
    }

    /**
     * Gets the registered Smarty extensions that have been registered over the ServiceManager and
     * assigns them to current Smarty instance.
     * NOTE: This function is automatically called by the class constructor.
     */
    public function registerExtensions()
    {
        // register extensions
        $extensions = $this->servicesByTag('Smarty.Extension',
                                           '\Webiny\Component\TemplateEngine\Drivers\Smarty\SmartyExtensionInterface'
        );

        /**
         * @var $e SmartyExtensionInterface
         */
        if (count($extensions) > 0) {
            $methods = get_class_methods('\Webiny\Component\TemplateEngine\Drivers\Smarty\SmartyExtensionInterface');
            foreach ($extensions as $e) {
                foreach ($methods as $m) {
                    if ($m != 'getName') {
                        $plugins = $e->{$m}();
                        foreach ($plugins as $p) {
                            $this->registerPlugin($p);
                        }
                    }
                }
            }
        }
    }
}