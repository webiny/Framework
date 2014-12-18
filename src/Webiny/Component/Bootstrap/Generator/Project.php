<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap\Generator;

/**
 * Project generator creates the base project structure.
 *
 * @package         Webiny\Component\Bootstrap\Generator
 */

class Project
{
    const STEPS = 4;

    static private $_projectMeta = [];

    public function run()
    {
        Cli::printSubTitle('Great! Let\'s setup your project');
        $this->_getProjectName();
        $this->_getWebPath();
        $this->_checkRootPath();
        $this->_createProjectStructure();
    }

    private function _getProjectName()
    {
        Cli::printSubTitle('Step: 1/' . self::STEPS);
        $ns = Cli::askQuestion('What is the name of your application? Only letters and numbers, no spaces.' . "\n" . 'This will be the namespace for your classes (for example MyAwesomeProject): '
        );

        $nsOk = true;
        if (!preg_match('/\w+/', $ns, $matches)) {
            $nsOk = false;
        }

        if (!isset($matches[0]) || $matches[0] == '' || $matches[0] != $ns) {
            $nsOk = false;
        }

        if (!$nsOk) {
            Cli::printErrorMessage('Not good ... the name you provided doesn\'t meet the requested criteria. Try again.'
            );
            $this->_getProjectName();
        } else {
            self::$_projectMeta['namespace'] = $ns;
            Cli::printSuccessMessage('Marvellous! Let\'s go to the next step.');
        }
    }

    protected function _getWebPath()
    {
        Cli::printSubTitle('Step: 2/' . self::STEPS);
        $webPath = Cli::askQuestion('What is the web location of your project. Don\'t worry, you can change it later.' . "\n" . '(for example http://www.example.com/): '
        );

        if (!filter_var($webPath, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED)) {
            Cli::printErrorMessage('That\'s not a good url. Try again.');
            $this->_getWebPath();
        } else {
            $webPath = rtrim($webPath . '/') . '/';
            self::$_projectMeta['webPath'] = $webPath;
            Cli::printSuccessMessage('Cool! Let\'s go to the next step.');
        }
    }


    private function _checkRootPath()
    {
        Cli::printSubTitle('Step: 3/' . self::STEPS);
        // ask where the root path is
        $rootPath = Cli::askQuestion('Please tell me what is the absolute path where your project files will be located?' . "\n" . '(for example /var/www/myProject): '
        );

        // check if root exists
        if (!file_exists($rootPath)) { // root doesn't exist
            Cli::printSubTitle('Seems the path doesn\'t exist. Shall I create it for you?');
            $a = Cli::printOptions([
                                       'y' => 'Yes',
                                       'n' => 'No'
                                   ]
            );

            if ($a == 'y') { // create the root folder
                $dirCreated = mkdir($rootPath, 0755, true);
                if (!$dirCreated) { // folder created
                    Cli::printErrorMessage('Oops...I can\'t create the folder for you. Probably some write permission problem.'
                    );
                    Cli::printMessage('You will need to create the folder manually and run the Generator again.');
                } else { // folder not created
                    Cli::printSuccessMessage('Awesome! Folder created.');
                }
            } else { // user will create is him self
                Cli::printSubTitle('Please create the folder and then run the Generator again.');
            }
        } else {
            Cli::printSuccessMessage('Great! The path already exits...let\'s go to the next step.');
        }

        self::$_projectMeta['rootPath'] = rtrim($rootPath, '/\\') . DIRECTORY_SEPARATOR;
    }

    private function _createProjectStructure()
    {
        Cli::printSubTitle('Step: 4/' . self::STEPS);
        Cli::printSubTitle('Very good! I now have all the details.');
        Cli::printMessage('I will now create the project structure.');
        Cli::acknowledgeMessage();

        // create the initial folder structure
        $structure = [
            'Public/static/css',
            'Public/static/js',
            'Public/static/img',
            'App/Config/Production',
            'App/Modules',
            'App/Layouts/Default',
            'App/Cache'
        ];

        foreach ($structure as $folder) {
            if (!@mkdir(self::$_projectMeta['rootPath'] . $folder, 0755, true)) {
                Cli::printErrorMessage('Oops...there was a permission error while trying to create a folder "'.self::$_projectMeta['rootPath'] . $folder.'"');
            }
        }

        // chmod the cache folder
        @chmod(self::$_projectMeta['rootPath'].'App/Cache', 0755);

        // symlink the vendors folder

        // create the files
        //TODO
    }


}