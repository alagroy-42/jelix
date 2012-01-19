<?php
/**
* @package    jelix
* @subpackage core
* @author     Laurent Jouanneau
* @author     Gerald Croes
* @contributor Julien Issler, Yannick Le Guédart
* @copyright  2001-2005 CopixTeam, 2005-2012 Laurent Jouanneau
* Some parts of this file are took from Copix Framework v2.3dev20050901, CopixI18N.class.php, http://www.copix.org.
* copyrighted by CopixTeam and released under GNU Lesser General Public Licence.
* initial authors : Gerald Croes, Laurent Jouanneau.
* enhancement by Laurent Jouanneau for Jelix.
* @copyright 2008 Julien Issler, 2008 Yannick Le Guédart
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * static class to get a localized string
 * @package  jelix
 * @subpackage core
 */
class jLocale {
    /**
     *
     */
    static $bundles = array();

    /**
     * static class...
     */
    private function __construct(){}

    /**
     * gets the current lang
     * @return string
     */
    static function getCurrentLang(){
        $s=jApp::config()->locale;
        return substr($s,0, strpos($s,'_'));
    }
    /**
     * gets the current country.
     * @return string
     */
    static function getCurrentCountry (){
        $s = jApp::config()->locale;
        return substr($s,strpos($s,'_')+1);
    }

    /**
    * gets the correct string, for a given language.
    *   if it can't get the correct language, it will try to gets the string
    *   from the default language.
    *   if both fails, it will raise an exception.
    * @param string $key the key of the localized string
    * @param array $args arguments to apply to the localized string with sprintf
    * @param string $locale  the lang code. if null, use the default language
    * @param string $charset the charset code. if null, use the default charset
    * @return string the localized string
    */
    static function get ($key, $args=null, $locale=null, $charset=null) {

        $config = jApp::config();
        try {
            $file = new jSelectorLoc($key, $locale, $charset);
        }
        catch (jExceptionSelector $e) {
            // the file is not found
            if ($e->getCode() == 12) throw $e;
            if ($locale === null)  $locale = $config->locale;
            if ($charset === null) $charset = $config->charset;
            if ($locale != $config->fallbackLocale && $config->fallbackLocale) {
                return jLocale::get ($key, $args, $config->fallbackLocale, $charset);
            }
            else
                throw new Exception('(200)The given locale key "'.$key
                                .'" is invalid (for charset '.$charset
                                .', lang '.$locale.')');
        }

        $locale = $file->locale;
        $keySelector = $file->module.'~'.$file->fileKey;

        if (!isset (self::$bundles[$keySelector][$locale])) {
            self::$bundles[$keySelector][$locale] =  new jBundle ($file, $locale);
        }

        $bundle = self::$bundles[$keySelector][$locale];

        //try to get the message from the bundle.
        $string = $bundle->get ($file->messageKey, $file->charset);
        if ($string === null) {
            if ($locale == $config->fallbackLocale) {
                throw new Exception('(210)The given locale key "'.$file->toString().'" does not exists in the default lang and in the fallback lang for the '.$file->charset.' charset');
            }
            // if the message was not found, we're gonna
            //use the default language and country.
            else if ($locale == $config->locale) {
                if ($config->fallbackLocale)
                    return jLocale::get ($key, $args, $config->fallbackLocale, $charset);
                throw new Exception('(210)The given locale key "'.$file->toString().'" does not exists in the default lang for the '.$file->charset.' charset');
            }
            return jLocale::get ($key, $args, $config->locale);
        }
        else {
            //here, we know the message
            if ($args !== null && $args !== array()) {
                $string = call_user_func_array('sprintf', array_merge (array ($string), is_array ($args) ? $args : array ($args)));
            }
            return $string;
        }
    }
}
