<?php /* Smarty version Smarty-3.1.14, created on 2014-08-27 12:26:01
         compiled from "/var/www/projects/webiny/Vendors/Webiny/Component/TemplateEngine/Tests/Bridge/Smarty/Templates/TestWithParams.tpl" */
?>
<?php /*%%SmartyHeaderCode:35851252253a105ac137b33-14812532%%*/
if (!defined('SMARTY_DIR')) {
    exit('no direct access allowed');
}
$_valid = $_smarty_tpl->decodeProperties(array(
                                             'file_dependency'  => array(
                                                 '168be072c3c3281f13f6dbfb825f2bfc22f05f1c' => array(
                                                     0 => '/var/www/projects/webiny/Vendors/Webiny/Component/TemplateEngine/Tests/Bridge/Smarty/Templates/TestWithParams.tpl',
                                                     1 => 1408677011,
                                                     2 => 'file',
                                                 ),
                                             ),
                                             'nocache_hash'     => '35851252253a105ac137b33-14812532',
                                             'function'         => array(),
                                             'version'          => 'Smarty-3.1.14',
                                             'unifunc'          => 'content_53a105ac1412e3_08703453',
                                             'variables'        => array(
                                                 'name'      => 0,
                                                 'otherName' => 0,
                                             ),
                                             'has_nocache_code' => false,
                                         ), false
); /*/%%SmartyHeaderCode%%*/
?>
<?php if ($_valid && !is_callable('content_53a105ac1412e3_08703453')) {
    function content_53a105ac1412e3_08703453($_smarty_tpl)
    {
        ?>Hello <?php echo $_smarty_tpl->tpl_vars['name']->value; ?>
        . My name is <?php echo $_smarty_tpl->tpl_vars['otherName']->value; ?>
        .<?php
    }
} ?>