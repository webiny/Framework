<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap;

use Webiny\Component\Bootstrap\Generator\Cli;
use Webiny\Component\Bootstrap\Generator\Project;
use Webiny\Component\ClassLoader\ClassLoader;

/**
 * This is a command line interface for generating project structures.
 *
 * @package         Webiny\Component\Bootstrap
 */
class GeneratorCli
{
    public static function run()
    {
        // init
        $gen = new self();
        $gen->_setupAutoloader();

        // show menu
        Cli::printTitle('Welcome to Webiny Bootstrap Generator');
        $gen->_showMainMenu();
    }

    private function _setupAutoloader()
    {
        require_once __DIR__ . '/../ClassLoader/ClassLoader.php';
        ClassLoader::getInstance()->registerMap(['Webiny' => __DIR__ . '/../../']);
    }

    private function _showMainMenu()
    {
        $options = [
            1   => 'Create a new project',
            2   => 'Create a module for existing project',
            3   => 'Create a controller for existing module',
            4   => 'Create a module for existing module'
        ];

        $selected = Cli::printOptions($options);

        switch ($selected) {
            case 1:
                $project = new Project();
                $project->run();
                break;

            default:
                die('Invalid option.');
                break;
        }
    }
}

// automatically run the Generator
if (php_sapi_name() == 'cli') {
    GeneratorCli::run();
} else {
    die('You can run the Generator only from command line.');
}