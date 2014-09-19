<?php /* Smarty version Smarty-3.1.14, created on 2014-08-27 12:26:01
         compiled from "/var/www/projects/webiny/Vendors/Webiny/Component/TemplateEngine/Tests/Bridge/Smarty/Templates/DemoComponent.tpl" */
?>
<?php /*%%SmartyHeaderCode:210661677853a11afd454eb2-80953279%%*/
if (!defined('SMARTY_DIR')) {
    exit('no direct access allowed');
}
$_valid = $_smarty_tpl->decodeProperties(array(
                                             'file_dependency'  => array(
                                                 'ed6253ac90214345a01e33dca99b5df458c5d2ad' => array(
                                                     0 => '/var/www/projects/webiny/Vendors/Webiny/Component/TemplateEngine/Tests/Bridge/Smarty/Templates/DemoComponent.tpl',
                                                     1 => 1407024275,
                                                     2 => 'file',
                                                 ),
                                             ),
                                             'nocache_hash'     => '210661677853a11afd454eb2-80953279',
                                             'function'         => array(),
                                             'version'          => 'Smarty-3.1.14',
                                             'unifunc'          => 'content_53a11afd4908c8_86472663',
                                             'variables'        => array(
                                                 'name' => 0,
                                             ),
                                             'has_nocache_code' => false,
                                         ), false
); /*/%%SmartyHeaderCode%%*/
?>
<?php if ($_valid && !is_callable('content_53a11afd4908c8_86472663')) {
    function content_53a11afd4908c8_86472663($_smarty_tpl)
    {
        ?>Hello <?php echo \Webiny\Component\TemplateEngine\Tests\Bridge\Smarty\Mocks\DemoComponentExtension::myCallback($_smarty_tpl->tpl_vars['name']->value
    ); ?>
    <?php
    }
} ?>