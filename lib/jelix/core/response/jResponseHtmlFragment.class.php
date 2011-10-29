<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire, Dominique Papin, Olivier Demah, Laurent Jouanneau
* @copyright   2008 Tahina Ramaroson, Sylvain de Vathaire
* @copyright   2008 Dominique Papin
* @copyright   2009 Olivier Demah, 2009-2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * Send Html part
 * @package  jelix
 * @subpackage core_response
 */
class jResponseHtmlFragment extends jResponse {

    /**
    * jresponse id
    * @var string
    */
    protected $_type = 'htmlfragment';

    /**
    * template selector
    * set the template name in this property
    * @var string
    */
    public $tplname = '';

    /**
    * the jtpl object created automatically
    * @var jTpl
    */
    public $tpl = null;

    /**#@+
     * content surrounding template content
     * @var array
     */
    protected $_contentTop = array();
    protected $_contentBottom = array();
    /**#@-*/

    /**
    * constructor;
    * setup the template engine
    */
    function __construct (){
        $this->tpl = new jTpl();
        parent::__construct();
    }

    /**
    * send the Html part
    * @return boolean    true if it's ok
    */
    final public function output(){

        if($this->_outputOnlyHeaders){
            $this->sendHttpHeaders();
            return true;
        }
    
        global $gJConfig;

        $this->doAfterActions();

        $content = implode("\n",$this->_contentTop);
        if($this->tplname!=''){
            $content .= $this->tpl->fetch($this->tplname,'html');
        }

        $content .= implode("\n",$this->_contentBottom);

        $this->_httpHeaders['Content-Type']='text/plain;charset='.$gJConfig->charset;
        $this->_httpHeaders['Content-length']=strlen($content);
        $this->sendHttpHeaders();
        echo $content;
        return true;
    }

    /**
     * add content to the response
     * you can add additionnal content, before or after the content generated by the main template
     * @param string $content additionnal html content
     * @param boolean $beforeTpl true if you want to add it before the template content, else false for after
     */
    function addContent($content, $beforeTpl = false){
      if($beforeTpl){
        $this->_contentTop[]=$content;
      }else{
         $this->_contentBottom[]=$content;
      }
    }

    /**
     * The method you can overload in your inherited htmlfragment response
     * after all actions
     * @since 1.1
     */
    protected function doAfterActions(){

    }

    /**
     * output errors
     */
    final public function outputErrors(){

        global $gJConfig;
        $this->clearHttpHeaders();
        $this->_httpStatusCode ='500';
        $this->_httpStatusMsg ='Internal Jelix Error';
        // FIXME : text/plain or text/html ?
        $this->_httpHeaders['Content-Type'] = 'text/plain;charset='.$gJConfig->charset;

        $content = '<p class="htmlfragmenterror">';
        $content .= htmlspecialchars($GLOBALS['gJCoord']->getGenericErrorMessage());
        $content .= '</p>';

        $this->_httpHeaders['Content-length'] = strlen($content);
        $this->sendHttpHeaders();
        echo $content;
    }

    public function getFormatType(){ return 'html';}
}
