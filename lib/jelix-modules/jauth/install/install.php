<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2009-2016 Laurent Jouanneau
* @copyright   2011 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleInstaller extends jInstallerModule {


    protected static $key = null;

    function install() {

        if (self::$key === null) {
            self::$key = jAuth::getRandomPassword(30, true);
        }

        $authconfig = $this->getConfigIni()->getValue('auth','coordplugins');
        $authconfigMaster = $this->getLocalConfigIni()->getValue('auth','coordplugins');
        $forWS = (in_array($this->entryPoint->type, array('json', 'jsonrpc', 'soap', 'xmlrpc')));

        if (!$authconfig || ($forWS && $authconfig == $authconfigMaster)) {

            if ($forWS) {
                $pluginIni = 'authsw.coord.ini.php';
            }
            else {
                $pluginIni = 'auth.coord.ini.php';
            }

            $authconfig = dirname($this->entryPoint->getConfigFile()).'/'.$pluginIni;

            if ($this->firstExec('auth:'.$authconfig)) {
                // no configuration, let's install the plugin for the entry point
                $this->config->setValue('auth', $authconfig, 'coordplugins');
                if (!file_exists(jApp::configPath($authconfig))) {
                    $this->copyFile('var/config/'.$pluginIni, jApp::configPath($authconfig));
                }
            }
        }

        $ini = new jIniFileModifier(jApp::configPath($authconfig));
        $key = $ini->getValue('persistant_crypt_key');
        if ($key === 'exampleOfCryptKey' || $key == '') {
            $ini->setValue('persistant_crypt_key', self::$key);
            $ini->save();
        }
    }
}