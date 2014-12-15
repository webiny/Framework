<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Tests;

/**
 * Class RouterCallback used to test Router callback execution
 * @package Webiny\Component\Router\Tests
 */
class RouterCallback
{

    public function handle($tag){
        return 'instance-'.$tag;
    }

    public static function handleStatic($tag, $id){
        return 'static-'.$tag.'-'.$id;
    }

}