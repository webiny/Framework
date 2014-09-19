<?php /* Smarty version Smarty-3.1.14, created on 2014-08-27 12:26:01
         compiled from "/var/www/projects/webiny/Vendors/Webiny/Component/TemplateEngine/Tests/Bridge/Smarty/Templates/TestPlugin.tpl" */
?>
<?php /*%%SmartyHeaderCode:177460397453a105ac14d8b9-72087054%%*/
if (!defined('SMARTY_DIR')) {
    exit('no direct access allowed');
}
$_valid = $_smarty_tpl->decodeProperties(array(
                                             'file_dependency'  => array(
                                                 '0a0e402634353572856a39b5e05a8f956f47c294' => array(
                                                     0 => '/var/www/projects/webiny/Vendors/Webiny/Component/TemplateEngine/Tests/Bridge/Smarty/Templates/TestPlugin.tpl',
                                                     1 => 1408677011,
                                                     2 => 'file',
                                                 ),
                                             ),
                                             'nocache_hash'     => '177460397453a105ac14d8b9-72087054',
                                             'function'         => array(),
                                             'version'          => 'Smarty-3.1.14',
                                             'unifunc'          => 'content_53a105ac153320_39924162',
                                             'variables'        => array(
                                                 'name' => 0,
                                             ),
                                             'has_nocache_code' => false,
                                         ), false
); /*/%%SmartyHeaderCode%%*/
?>
<?php if ($_valid && !is_callable('content_53a105ac153320_39924162')) {
    function content_53a105ac153320_39924162($_smarty_tpl)
    {
        ?>Hello <?php echo \Webiny\Component\TemplateEngine\Tests\Bridge\Smarty\Mocks\PluginMock::myCallback($_smarty_tpl->tpl_vars['name']->value
    ); ?>
    <?php
    }
} ?>