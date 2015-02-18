<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ClassLoader\Tests\Loaders;

use Webiny\Component\ClassLoader\ClassLoader;

class Psr4Test extends \PHPUnit_Framework_TestCase
{

    public function testFindClassCaseOne()
    {
        ClassLoader::getInstance()->unregisterMap('Acme\Log\Writer');
        ClassLoader::getInstance()->registerMap([
                                                    'Acme\Log\Writer' => [
                                                        'Path' => './acme-log-writer/lib/',
                                                        'Psr'  => 4
                                                    ]
                                                ]
        );
        $classPath = ClassLoader::getInstance()->findClass('\Acme\Log\Writer\File_Writer');
        $this->assertSame('./acme-log-writer/lib/File_Writer.php', $classPath);
    }

    public function testFindClassCaseTwo()
    {
        ClassLoader::getInstance()->registerMap([
                                                    'Aura\Web' => [
                                                        'Path' => '/path/to/aura-web/src/',
                                                        'Psr'  => 4
                                                    ]
                                                ]
        );
        $classPath = ClassLoader::getInstance()->findClass('\Aura\Web\Response\Status');
        $this->assertSame('/path/to/aura-web/src/Response/Status.php', $classPath);
    }

    public function testFindClassCaseThree()
    {
        ClassLoader::getInstance()->registerMap([
                                                    'Zend' => [
                                                        'Path' => '/usr/includes/Zend/',
                                                        'Psr'  => 4
                                                    ]
                                                ]
        );
        $classPath = ClassLoader::getInstance()->findClass('\Zend\Acl');
        $this->assertSame('/usr/includes/Zend/Acl.php', $classPath);
    }

}