<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Drivers\Smarty;

/**
 * This is the interface for creating plugins for Smarty template engine.
 * The best way to implement this interface is by extending SmartyExtension and overwriting the desired methods.
 *
 * @package         Webiny\Component\TemplateEngine\Bridge\Drivers\Smarty
 */

interface SmartyExtensionInterface
{
    /**
     * Register template functions.
     *
     * @link http://www.smarty.net/docs/en/plugins.functions.tpl
     *
     * @return array of SmartySimplePlugin
     */
    public function getFunctions();

    /**
     * Register modifiers.
     * Modifiers are little functions that are applied to a variable in the template before it is displayed
     * or used in some other context. Modifiers can be chained together.
     *
     * @link http://www.smarty.net/docs/en/plugins.modifiers.tpl
     *
     * @return array of SmartySimplePlugin
     */
    public function getModifiers();

    /**
     * Register block functions.
     * Block functions are functions of the form: {func} .. {/func}.
     * In other words, they enclose a template block and operate on the contents of this block.
     *
     * @link http://www.smarty.net/docs/en/plugins.block.functions.tpl
     *
     * @return array of SmartySimplePlugin
     */
    public function getBlockFunctions();

    /**
     * Register compiler functions.
     * Compiler functions are called only during compilation of the template.
     * They are useful for injecting PHP code or time-sensitive static content into the template.
     *
     * @link http://www.smarty.net/docs/en/plugins.compiler.functions.tpl
     *
     * @return array of SmartySimplePlugin
     */
    public function getCompilerFunctions();

    /**
     * Register pre filters.
     * Prefilters are used to process the source of the template immediately before compilation.
     *
     * @link http://www.smarty.net/docs/en/plugins.prefilters.postfilters.tpl
     *
     * @return array of SmartySimplePlugin
     */
    public function getPreFilters();

    /**
     * Register post filters.
     * Postfilters are used to process the compiled output of the template (the PHP code) immediately
     * after the compilation is done but before the compiled template is saved to the filesystem.
     *
     * @link http://www.smarty.net/docs/en/plugins.prefilters.postfilters.tpl
     *
     * @return array of SmartySimplePlugin
     */
    public function getPostFilters();

    /**
     * Register output filters.
     * Output filter plugins operate on a template's output, after the template is loaded and executed,
     * but before the output is displayed.
     *
     * @link http://www.smarty.net/docs/en/plugins.outputfilters.tpl
     *
     * @return array of SmartySimplePlugin
     */
    public function getOutputFilters();

    /**
     * Register resources.
     * Resource plugins are meant as a generic way of providing template sources or PHP script components to Smarty.
     * Some examples of resources: databases, LDAP, shared memory, sockets, and so on.
     *
     * @link http://www.smarty.net/docs/en/plugins.resources.tpl
     *
     * @return array of SmartySimplePlugin
     */
    public function getResources();

    /**
     * Register insets.
     * Insert plugins are used to implement functions that are invoked by {insert} tags in the template.
     *
     * @link http://www.smarty.net/docs/en/plugins.inserts.tpl
     *
     * @return array of SmartySimplePlugin
     */
    public function getInserts();

    /**
     * Returns the name of the plugin.
     *
     * @return string
     */
    public function getName();
}