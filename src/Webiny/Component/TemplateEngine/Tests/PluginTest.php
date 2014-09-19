<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Tests\Bridge;

use Webiny\Component\TemplateEngine\Plugin;

class PluginTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider dataProvider
     */
    public function testSetGetAttribute(Plugin $p)
    {
        $p->setAttribute('attr1', 'val1');
        $p->setAttribute('attr2', 'val2');

        $this->assertSame('val1', $p->getAttribute('attr1'));
        $this->assertSame('val2', $p->getAttribute('attr2'));

        $this->assertFalse($p->getAttribute('attr3'));
        $this->assertSame('default', $p->getAttribute('attr4', 'default'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetName(Plugin $p)
    {
        $this->assertSame('pName', $p->getName());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetType(Plugin $p)
    {
        $this->assertSame('pType', $p->getType());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetCallbackFunction(Plugin $p)
    {
        $this->assertSame('self::callback', $p->getCallbackFunction());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetParams(Plugin $p)
    {
        $this->assertSame([
                              'p1' => 'a',
                              'p2' => 'b'
                          ], $p->getParams()
        );
    }

    public function dataProvider()
    {
        $plugin = new Plugin('pName', 'pType', 'self::callback', [
                'p1' => 'a',
                'p2' => 'b'
            ]
        );

        return [[$plugin]];
    }
}