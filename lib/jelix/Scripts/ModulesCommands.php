<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @link        http://www.jelix.org
 * @licence     MIT
 */
namespace Jelix\Scripts;

use Symfony\Component\Console\Application;

/**
 * Launch commands from modules
 *
 * @package Jelix\Scripts
 */
class ModulesCommands {


    static function run() {

        Utils::checkEnv();

        // init Jelix environment

        \jApp::setEnv('console');

        Utils::checkTempPath();


        $projectInfos = \Jelix\Core\Infos\AppInfos::load(\jApp::appPath());
        $ep = $projectInfos->getEntryPointInfo('index');

        \jApp::setConfig(\jConfigCompiler::read($ep->getConfigFile(), true, true, 'console.php'));
        \jFile::createDir(\jApp::tempPath(), \jApp::config()->chmodDir);

        // ----- init the Application object
        $application = new Application($projectInfos->name." commands");

        // try to read a commands.php file from each modules
        foreach(\jApp::getEnabledModulesPaths() as $module => $path) {
            if (file_exists($path.'commands.php')) {
                require($path.'commands.php');
            }
        }
        $application ->run();

    }

}