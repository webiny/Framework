<?php

namespace Webiny\Component\Bootstrap\Tests\ApplicationClasses;

use Webiny\Component\Bootstrap\ApplicationClasses\View;

class ViewTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetTitle()
    {
        $view = new View();
        $view->setTitle('Test Title');

        $this->assertSame('Test Title', $view->getTitle());
        $this->assertSame('<title>Test Title</title>', $view->getTitleHtml());
    }

    public function testScripts()
    {
        $view = new View();
        $scriptOne = 'scriptOne.js';
        $scriptTwo = 'scriptTwo.js';
        $scriptThree = 'scriptThree.js';

        $view->appendScript($scriptOne);
        $view->prependScript($scriptTwo);
        $view->appendScript($scriptThree);

        $scriptArray = $view->getScripts();

        $expectedArray = [
            [
                'path' => $scriptTwo,
                'type' => 'text/javascript'
            ],
            [
                'path' => $scriptOne,
                'type' => 'text/javascript'
            ],
            [
                'path' => $scriptThree,
                'type' => 'text/javascript'
            ]
        ];

        $this->assertSame($expectedArray, $scriptArray);

        $html = '';
        foreach ($expectedArray as $s) {
            $html .= '<script type="' . $s['type'] . '" src="' . $s['path'] . '"></script>' . "\n";
        }

        $this->assertSame($html, $view->getScriptsHtml());
    }

    public function testStyleSheets()
    {
        $view = new View();
        $styleOne = 'styleOne.js';
        $styleTwo = 'styleTwo.js';
        $styleThree = 'styleThree.js';

        $view->appendStyleSheet($styleOne);
        $view->prependStyleSheet($styleTwo);
        $view->appendStyleSheet($styleThree);

        $stylesArray = $view->getStyleSheets();

        $expectedArray = [
            $styleTwo,
            $styleOne,
            $styleThree
        ];

        $this->assertSame($expectedArray, $stylesArray);

        $html = '';
        foreach ($expectedArray as $s) {
            $html .= '<link rel="stylesheet" type="text/css" href="' . $s . '"/>' . "\n";
        }

        $this->assertSame($html, $view->getStyleSheetsHtml());
    }

    public function testMeta()
    {
        $view = new View();

        $view->setMeta('keyword', 'testing');
        $view->setMeta('description', 'Just testing');

        $expectedArray = [
            'keyword'     => 'testing',
            'description' => 'Just testing'
        ];

        $this->assertSame($expectedArray, $view->getMeta());

        $html = '';
        foreach ($expectedArray as $name => $content) {
            $html .= '<meta name="' . $name . '" content="' . $content . '"/>' . "\n";
        }

        $this->assertSame($html, $view->getMetaHtml());
    }

    public function testSetGetTemplate()
    {
        $view = new View();

        $view->setTemplate('layout/template.tpl');
        $this->assertSame('layout/template.tpl', $view->getTemplate());
    }

    public function testAssignments()
    {
        $view = new View();

        $view->assign(['name'=>'john']);
        $data = ['name'=>'john'];
        $this->assertSame($data, $view->getAssignedData()['Ctrl']);

        $view->assign(['new'=>'data']);
        $data['new']='data';
        $this->assertSame($data, $view->getAssignedData()['Ctrl']);
    }

    public function testSetGetAutload()
    {
        $view = new View();

        $this->assertTrue($view->getAutoload());
        $view->setAutoload(false);
        $this->assertFalse($view->getAutoload());
    }

}