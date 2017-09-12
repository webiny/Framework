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
    private $smarty;


    /**
     * Base constructor.
     *
     * @param ConfigObject $config Configuration for the template engine.
     *
     * @throws SmartyException
     */
    public function __construct(ConfigObject $config)
    {
        $this->smarty = new \Smarty();

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
        if ($templateDir) {
            $this->setTemplateDir($templateDir);
        }

        // force compile
        $this->setForceCompile($config->get('ForceCompile', false));

        // merge compiled includes
        if (!$config->get('MergeCompiledIncludes', true)) {
            $this->setMergeCompiledIncludes(false);
        }

        // mute expected errors
        if ($config->get('MuteExpectedErrors', false)) {
            $this->smarty->muteExpectedErrors();
        } else {
            $this->smarty->unmuteExpectedErrors();
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

        $this->smarty->setCompileDir($compileDir);
    }

    /**
     * Returns the current compile dir.
     *
     * @return string Absolute path to compile dir.
     */
    public function getCompileDir()
    {
        return realpath($this->smarty->getCompileDir());
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

        $this->smarty->setCacheDir($cacheDir);
    }

    /**
     * Returns the current cache dir.
     *
     * @return string Absolute path to cache dir.
     */
    public function getCacheDir()
    {
        return realpath($this->smarty->getCacheDir());
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
        $this->smarty->setTemplateDir($templateDir);
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
        return realpath($this->smarty->getTemplateDir()[0]);
    }

    /**
     * Force to re-compile the templates on every refresh.
     *
     * @param bool $forceCompile
     */
    public function setForceCompile($forceCompile)
    {
        $this->smarty->force_compile = $forceCompile;
    }

    /**
     * Returns the current value of force_compile flag.
     *
     * @return bool
     */
    public function getForceCompile()
    {
        return $this->smarty->force_compile;
    }

    /**
     * Force to re-compile the templates on every refresh.
     *
     * @param bool $mergeCompiledIncludes
     */
    public function setMergeCompiledIncludes($mergeCompiledIncludes)
    {
        $this->smarty->inheritance_merge_compiled_includes = $mergeCompiledIncludes;
    }

    /**
     * Returns the current value of inheritance_merge_compiled_includes flag.
     *
     * @return bool
     */
    public function getMergeCompiledIncludes()
    {
        return $this->smarty->inheritance_merge_compiled_includes;
    }

    /**
     * Fetch the template from the given location, parse it and return the output.
     *
     * @param string $template Path to the template.
     * @param array  $parameters A list of parameters to pass to the template.
     *
     * @throws SmartyException
     * @return string Parsed template.
     */
    public function fetch($template, $parameters = [])
    {
        try {
            if (count($parameters) > 0) {
                $this->smarty->assign($parameters);
            }

            return $this->smarty->fetch($template);
        } catch (\Exception $e) {
            throw new SmartyException($e->getMessage());
        }
    }

    /**
     * Fetch the template from the given location, parse it and output the result to the browser.
     *
     * @param string $template Path to the template.
     * @param array  $parameters A list of parameters to pass to the template.
     *
     * @return void
     */
    public function render($template, $parameters = [])
    {
        if (count($parameters) < 1) {
            $parameters = null;
        }
        echo $this->smarty->fetch($template, $parameters);
    }

    /**
     * Assign a variable and its value into the template engine.
     *
     * @param string $var Variable name.
     * @param mixed  $value Variable value.
     *
     * @return void
     */
    public function assign($var, $value)
    {
        $this->smarty->assign($var, $value);
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
            $this->smarty->registerPlugin($plugin->getType(), $plugin->getName(), $plugin->getCallbackFunction(),
                $plugin->getAttribute('Cachable', true), $plugin->getAttribute('CacheAttr', null));
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
        $extensions = $this->servicesByTag('Smarty.Extension', SmartyExtensionInterface::class);

        /**
         * @var $e SmartyExtensionInterface
         */
        if (count($extensions) > 0) {
            $methods = get_class_methods(SmartyExtensionInterface::class);
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