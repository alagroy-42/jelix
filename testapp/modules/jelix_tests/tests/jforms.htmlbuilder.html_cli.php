<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor Dominique Papin
* @copyright   2007-2008 Jouanneau laurent
* @copyright   2008 Dominique Papin
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'forms/jFormsBase.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsBuilderBase.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsDataContainer.class.php');
require_once(JELIX_LIB_PATH.'plugins/jforms/html/html.jformsbuilder.php');

class testHMLForm extends jFormsBase { 
}

class testJFormsHtmlBuilder extends htmlJformsBuilder {
    function getJsContent() { $js= $this->jsContent; $this->jsContent = '';return $js;}
    function clearJs() { $this->jsContent = ''; }
}


class UTjformsHTMLBuilder extends jUnitTestCaseDb {

    protected $form;
    protected $container;
    protected $builder;
    function testStart() {
        $this->container = new jFormsDataContainer('formtest','');
        $this->form = new testHMLForm('formtest', $this->container, true );
        $this->builder = new testJFormsHtmlBuilder($this->form);

    }

    function testOutputHeader(){
        $this->builder->setAction('jelix_tests~urlsig:url1',array());
        ob_start();
        $this->builder->outputHeader(array('method'=>'post'));
        $out = ob_get_clean();
        $result ='<form action="'.$GLOBALS['gJConfig']->urlengine['basePath'].'index.php" method="post" id="'.$this->builder->getName().'"><script type="text/javascript">
//<![CDATA[
jForms.tForm = new jFormsForm(\'jforms_formtest\');
jForms.tForm.setErrorDecorator(new jFormsErrorDecoratorAlert());
jForms.tForm.setHelpDecorator(new jFormsHelpDecoratorAlert());
jForms.declareForm(jForms.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="module" value="jelix_tests"/>
<input type="hidden" name="action" value="urlsig:url1"/>
</div>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());

        $this->builder->setAction('jelix_tests~urlsig:url1',array('foo'=>'b>ar'));
        ob_start();
        $this->builder->outputHeader(array('method'=>'get'));
        $out = ob_get_clean();
        $result ='<form action="'.$GLOBALS['gJConfig']->urlengine['basePath'].'index.php" method="get" id="'.$this->builder->getName().'"><script type="text/javascript">
//<![CDATA[
jForms.tForm = new jFormsForm(\'jforms_formtest1\');
jForms.tForm.setErrorDecorator(new jFormsErrorDecoratorAlert());
jForms.tForm.setHelpDecorator(new jFormsHelpDecoratorAlert());
jForms.declareForm(jForms.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="module" value="jelix_tests"/>
<input type="hidden" name="action" value="urlsig:url1"/>
</div>';
        $this->assertEqualOrDiff($result, $out);
        $this->formname = $this->builder->getName();
        $this->assertEqualOrDiff('', $this->builder->getJsContent());

    }
    function testOutputFooter(){
        ob_start();
        $this->builder->outputFooter();
        $out = ob_get_clean();
        $this->assertEqualOrDiff('<script type="text/javascript">
//<![CDATA[
(function(){var c, c2;

})();
//]]>
</script></form>', $out);
    }
    function testOutputInput(){
        $ctrl= new jFormsControlinput('input1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_input1">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="input1" id="'.$this->formname.'_input1" value=""/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('input1','toto');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="input1" id="'.$this->formname.'_input1" value="toto"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->defaultValue='laurent';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="input1" id="'.$this->formname.'_input1" value="toto"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->removeControl($ctrl->ref);
        $this->form->addControl($ctrl);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="input1" id="'.$this->formname.'_input1" value="laurent"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->required=true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="input1" id="'.$this->formname.'_input1" class=" jforms-required" value="laurent"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'input1\', \'Votre nom\');
c.required = true;
c.errRequired=\'La saisie de "Votre nom" est obligatoire\';
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        $ctrl->required=false;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="input1" id="'.$this->formname.'_input1" readonly="readonly" class=" jforms-readonly" value="laurent"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(false);
        $ctrl->help='some help';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="input1" id="'.$this->formname.'_input1" value="laurent"/><span class="jforms-help"><a href="javascript:jForms.showHelp(\''. $this->formname.'\',\'input1\')">?</a></span>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'input1\', \'Votre nom\');
c.help=\'some help\';
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->help='some 
help with \' and
line break.';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="input1" id="'.$this->formname.'_input1" value="laurent"/><span class="jforms-help"><a href="javascript:jForms.showHelp(\''. $this->formname.'\',\'input1\')">?</a></span>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'input1\', \'Votre nom\');
c.help=\'some \nhelp with \\\' and\nline break.\';
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        $ctrl->help='some help';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_input1" title="ceci est un tooltip">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="input1" id="'.$this->formname.'_input1" title="ceci est un tooltip" value="laurent"/><span class="jforms-help"><a href="javascript:jForms.showHelp(\''. $this->formname.'\',\'input1\')">?</a></span>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'input1\', \'Votre nom\');
c.help=\'some help\';
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->help='';
        $ctrl->hint='';
        $ctrl->datatype->addFacet('maxLength',5);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="input1" id="'.$this->formname.'_input1" maxlength="5" value="laurent"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'input1\', \'Votre nom\');
c.maxLength = \'5\';
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

    }
    function testOutputCheckbox(){
        $ctrl= new jFormsControlCheckbox('chk1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Une option';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_chk1">Une option</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk1" id="'.$this->formname.'_chk1" value="1"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlBoolean(\'chk1\', \'Une option\');
c.errInvalid=\'La saisie de "Une option" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('chk1','1');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk1" id="'.$this->formname.'_chk1" checked="checked" value="1"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlBoolean(\'chk1\', \'Une option\');
c.errInvalid=\'La saisie de "Une option" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl= new jFormsControlCheckbox('chk2');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Une option';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_chk2">Une option</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk2" id="'.$this->formname.'_chk2" value="1"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlBoolean(\'chk2\', \'Une option\');
c.errInvalid=\'La saisie de "Une option" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->defaultValue='1';
        $this->form->removeControl($ctrl->ref);
        $this->form->addControl($ctrl);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk2" id="'.$this->formname.'_chk2" checked="checked" value="1"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlBoolean(\'chk2\', \'Une option\');
c.errInvalid=\'La saisie de "Une option" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('chk2', '0');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk2" id="'.$this->formname.'_chk2" value="1"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlBoolean(\'chk2\', \'Une option\');
c.errInvalid=\'La saisie de "Une option" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk2" id="'.$this->formname.'_chk2" readonly="readonly" class=" jforms-readonly" value="1"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlBoolean(\'chk2\', \'Une option\');
c.errInvalid=\'La saisie de "Une option" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('chk2', '1');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk2" id="'.$this->formname.'_chk2" readonly="readonly" class=" jforms-readonly" checked="checked" value="1"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlBoolean(\'chk2\', \'Une option\');
c.errInvalid=\'La saisie de "Une option" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_chk2" title="ceci est un tooltip">Une option</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk2" id="'.$this->formname.'_chk2" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" checked="checked" value="1"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlBoolean(\'chk2\', \'Une option\');
c.errInvalid=\'La saisie de "Une option" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

    }

    function testOutputCheckboxes(){
        $ctrl= new jFormsControlcheckboxes('choixsimple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Vos choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $this->form->addControl($ctrl);

        $records = array(
            array('id'=>'10', 'name'=>'foo', 'price'=>'12'),
            array('id'=>'11', 'name'=>'bar', 'price'=>'54'),
            array('id'=>'23', 'name'=>'baz', 'price'=>'97'),
        );
        $this->insertRecordsIntoTable('product_test', array('id','name','price'), $records, true);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label">Vos choix</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_0" value="10"/><label for="'.$this->formname.'_choixsimple_0">foo</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_1" value="11"/><label for="'.$this->formname.'_choixsimple_1">bar</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_2" value="23"/><label for="'.$this->formname.'_choixsimple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'choixsimple[]\', \'Vos choix\');
c.errInvalid=\'La saisie de "Vos choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('choixsimple',11);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_0" value="10"/><label for="'.$this->formname.'_choixsimple_0">foo</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_1" value="11" checked="checked"/><label for="'.$this->formname.'_choixsimple_1">bar</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_2" value="23"/><label for="'.$this->formname.'_choixsimple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'choixsimple[]\', \'Vos choix\');
c.errInvalid=\'La saisie de "Vos choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl= new jFormsControlcheckboxes('choixmultiple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Vos choix';
        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label">Vos choix</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_0" value="10"/><label for="'.$this->formname.'_choixmultiple_0">foo</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_1" value="11"/><label for="'.$this->formname.'_choixmultiple_1">bar</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_2" value="23"/><label for="'.$this->formname.'_choixmultiple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'choixmultiple[]\', \'Vos choix\');
c.errInvalid=\'La saisie de "Vos choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('choixmultiple',11);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_0" value="10"/><label for="'.$this->formname.'_choixmultiple_0">foo</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_1" value="11" checked="checked"/><label for="'.$this->formname.'_choixmultiple_1">bar</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_2" value="23"/><label for="'.$this->formname.'_choixmultiple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'choixmultiple[]\', \'Vos choix\');
c.errInvalid=\'La saisie de "Vos choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('choixmultiple',array(10,23));
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_0" value="10" checked="checked"/><label for="'.$this->formname.'_choixmultiple_0">foo</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_1" value="11"/><label for="'.$this->formname.'_choixmultiple_1">bar</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_2" value="23" checked="checked"/><label for="'.$this->formname.'_choixmultiple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'choixmultiple[]\', \'Vos choix\');
c.errInvalid=\'La saisie de "Vos choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" title="ceci est un tooltip">Vos choix</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_0" value="10" checked="checked" readonly="readonly" class=" jforms-readonly"/><label for="'.$this->formname.'_choixmultiple_0">foo</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_1" value="11" readonly="readonly" class=" jforms-readonly"/><label for="'.$this->formname.'_choixmultiple_1">bar</label></span>';
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_2" value="23" checked="checked" readonly="readonly" class=" jforms-readonly"/><label for="'.$this->formname.'_choixmultiple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'choixmultiple[]\', \'Vos choix\');
c.errInvalid=\'La saisie de "Vos choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());
    }

    function testOutputRadiobuttons(){
        $ctrl= new jFormsControlradiobuttons('rbchoixsimple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label">Votre choix</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" value="10"/><label for="'.$this->formname.'_rbchoixsimple_0">foo</label></span>';
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" value="11"/><label for="'.$this->formname.'_rbchoixsimple_1">bar</label></span>';
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_2" value="23"/><label for="'.$this->formname.'_rbchoixsimple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'rbchoixsimple\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('rbchoixsimple',11);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" value="10"/><label for="'.$this->formname.'_rbchoixsimple_0">foo</label></span>';
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" value="11" checked="checked"/><label for="'.$this->formname.'_rbchoixsimple_1">bar</label></span>';
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_2" value="23"/><label for="'.$this->formname.'_rbchoixsimple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'rbchoixsimple\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" value="10"/><label for="'.$this->formname.'_rbchoixsimple_0">foo</label></span>';
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" value="11" checked="checked"/><label for="'.$this->formname.'_rbchoixsimple_1">bar</label></span>';
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_2" value="23"/><label for="'.$this->formname.'_rbchoixsimple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'rbchoixsimple\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('rbchoixsimple',23);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" value="10"/><label for="'.$this->formname.'_rbchoixsimple_0">foo</label></span>';
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" value="11"/><label for="'.$this->formname.'_rbchoixsimple_1">bar</label></span>';
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_2" value="23" checked="checked"/><label for="'.$this->formname.'_rbchoixsimple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'rbchoixsimple\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" title="ceci est un tooltip">Votre choix</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" value="10" readonly="readonly" class=" jforms-readonly"/><label for="'.$this->formname.'_rbchoixsimple_0">foo</label></span>';
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" value="11" readonly="readonly" class=" jforms-readonly"/><label for="'.$this->formname.'_rbchoixsimple_1">bar</label></span>';
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_2" value="23" checked="checked" readonly="readonly" class=" jforms-readonly"/><label for="'.$this->formname.'_rbchoixsimple_2">baz</label></span>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'rbchoixsimple\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

    }

    function testOutputMenulist(){
        $ctrl= new jFormsControlmenulist('menulist1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_menulist1">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('menulist1',11);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value=""></option>';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11" selected="selected">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_menulist1" title="ceci est un tooltip">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" title="ceci est un tooltip" class=" jforms-readonly" size="1">';
        $result.='<option value=""></option>';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11" selected="selected">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->required = true;
        $this->form->setData('menulist1',"23");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" title="ceci est un tooltip" class=" jforms-readonly" size="1">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23" selected="selected">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'menulist1\', \'Votre choix\');
c.required = true;
c.errRequired=\'La saisie de "Votre choix" est obligatoire\';
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->required = false;
        $this->form->setData('menulist1',"");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" title="ceci est un tooltip" class=" jforms-readonly" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(false);
        $ctrl->hint='';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findByMaxId','name','id','','15');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findByMaxId','name','id','','11');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="10">foo</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('menulist1',"10");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value=""></option>';
        $result.='<option value="10" selected="selected">foo</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('menulist1',"");

        $this->form->addControl(new jFormsControlHidden('hidden1'));
        $this->form->setData('hidden1',"25");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findByMaxId','name','id','',null, 'hidden1');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('hidden1',"15");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);



        $this->form->setData('menulist1',"10");
        $this->form->setData('hidden1',"11");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value=""></option>';
        $result.='<option value="10" selected="selected">foo</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);

        $this->form->setData('menulist1',"");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findByMaxId','name,price','id','','25',null);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="10">foo12</option>';
        $result.='<option value="11">bar54</option>';
        $result.='<option value="23">baz97</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findByMaxId','name,price','id','','25',null,' - ');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="10">foo - 12</option>';
        $result.='<option value="11">bar - 54</option>';
        $result.='<option value="23">baz - 97</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findBetweenId','name,price','id','','9,25',null,' - ');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="10">foo - 12</option>';
        $result.='<option value="11">bar - 54</option>';
        $result.='<option value="23">baz - 97</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findBetweenId','name,price','id','','10,25',null,' - ');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="11">bar - 54</option>';
        $result.='<option value="23">baz - 97</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);

        $this->form->addControl(new jFormsControlHidden('hidden2'));
        $this->form->setData('hidden1',"9");
        $this->form->setData('hidden2',"25");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findBetweenId','name,price','id','',null,'hidden1,hidden2',' - ');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="10">foo - 12</option>';
        $result.='<option value="11">bar - 54</option>';
        $result.='<option value="23">baz - 97</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);

        $this->form->setData('hidden1',"10");
        $this->form->setData('hidden2',"25");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findBetweenId','name,price','id','',null,'hidden1,hidden2',' - ');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" size="1">';
        $result.='<option value="" selected="selected"></option>';
        $result.='<option value="11">bar - 54</option>';
        $result.='<option value="23">baz - 97</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);

        $this->form->removeControl('hidden2');
        $this->form->setData('hidden1',"11");
        $this->builder->clearJs();
    }

    function testOutputListbox(){
        $ctrl= new jFormsControllistbox('listbox1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_listbox1">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listbox1" id="'.$this->formname.'_listbox1" size="4">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'listbox1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('listbox1',"23");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listbox1" id="'.$this->formname.'_listbox1" size="4">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23" selected="selected">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'listbox1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'listbox1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_listbox1">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listbox1" id="'.$this->formname.'_listbox1" class=" jforms-readonly" size="4">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23" selected="selected">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'listbox1\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());



        $ctrl= new jFormsControllistbox('lbchoixmultiple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $ctrl->multiple=true;
        $ctrl->hint='ceci est un tooltip';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_lbchoixmultiple" title="ceci est un tooltip">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="lbchoixmultiple[]" id="'.$this->formname.'_lbchoixmultiple" title="ceci est un tooltip" size="4" multiple="multiple">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'lbchoixmultiple[]\', \'Votre choix\');
c.multiple = true;
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('lbchoixmultiple',array(10,23));
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="lbchoixmultiple[]" id="'.$this->formname.'_lbchoixmultiple" title="ceci est un tooltip" size="4" multiple="multiple">';
        $result.='<option value="10" selected="selected">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23" selected="selected">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'lbchoixmultiple[]\', \'Votre choix\');
c.multiple = true;
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl= new jFormsControllistbox('listbox2');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $ctrl->defaultValue=array ('10');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_listbox2">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listbox2" id="'.$this->formname.'_listbox2" size="4">';
        $result.='<option value="10" selected="selected">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'listbox2\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl= new jFormsControllistbox('lbchoixmultiple2');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $ctrl->multiple=true;
        $ctrl->size=8;
        $ctrl->defaultValue=array ('11','23');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_lbchoixmultiple2">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="lbchoixmultiple2[]" id="'.$this->formname.'_lbchoixmultiple2" size="8" multiple="multiple">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11" selected="selected">bar</option>';
        $result.='<option value="23" selected="selected">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'lbchoixmultiple2[]\', \'Votre choix\');
c.multiple = true;
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

    }

    function testOutputListboxClassDatasource(){
        $ctrl= new jFormsControllistbox('listboxclass');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        jClasses::inc('mydatasource');
        $ctrl->datasource = new mydatasource(0);
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listboxclass" id="'.$this->formname.'_listboxclass" size="4">';
        $result.='<option value="aaa">label for aaa</option>';
        $result.='<option value="bbb">label for bbb</option>';
        $result.='<option value="ccc">label for ccc</option>';
        $result.='<option value="ddd">label for ddd</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'listboxclass\', \'Votre choix\');
c.errInvalid=\'La saisie de "Votre choix" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

    }


    function testOutputTextarea(){
        $ctrl= new jFormsControltextarea('textarea1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_textarea1">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" rows="5" cols="40"></textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'textarea1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('textarea1','laurent');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" rows="5" cols="40">laurent</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'textarea1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" readonly="readonly" class=" jforms-readonly" rows="5" cols="40">laurent</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'textarea1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_textarea1" title="ceci est un tooltip">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" rows="5" cols="40">laurent</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'textarea1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->rows=20;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" rows="20" cols="40">laurent</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'textarea1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->cols=60;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" rows="20" cols="60">laurent</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'textarea1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


    }
    function testOutputSecret(){
        $ctrl= new jFormsControlSecret('passwd');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='mot de passe';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_passwd">mot de passe</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="passwd" id="'.$this->formname.'_passwd" value=""/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'passwd\', \'mot de passe\');
c.errInvalid=\'La saisie de "mot de passe" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('passwd','laurent');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="passwd" id="'.$this->formname.'_passwd" value="laurent"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'passwd\', \'mot de passe\');
c.errInvalid=\'La saisie de "mot de passe" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="passwd" id="'.$this->formname.'_passwd" readonly="readonly" class=" jforms-readonly" value="laurent"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'passwd\', \'mot de passe\');
c.errInvalid=\'La saisie de "mot de passe" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_passwd" title="ceci est un tooltip">mot de passe</label>', $out);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="passwd" id="'.$this->formname.'_passwd" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" value="laurent"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'passwd\', \'mot de passe\');
c.errInvalid=\'La saisie de "mot de passe" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->datatype->addFacet('minLength',5);
        $ctrl->datatype->addFacet('maxLength',10);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="passwd" id="'.$this->formname.'_passwd" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" maxlength="10" value="laurent"/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'passwd\', \'mot de passe\');
c.maxLength = \'10\';
c.minLength = \'5\';
c.errInvalid=\'La saisie de "mot de passe" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());


    }
    function testOutputSecretConfirm(){
        $ctrl= new jFormsControlSecretConfirm('passwd_confirm');
        $ctrl->label='confirmation mot de passe';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_passwd_confirm">confirmation mot de passe</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="passwd_confirm" id="'.$this->formname.'_passwd_confirm" value=""/>', $out);
        $this->assertEqualOrDiff('c.confirmField = new jFormsControlSecretConfirm(\'passwd_confirm_confirm\', \'confirmation mot de passe\');
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="passwd_confirm" id="'.$this->formname.'_passwd_confirm" readonly="readonly" class=" jforms-readonly" value=""/>', $out);
        $this->assertEqualOrDiff('c.confirmField = new jFormsControlSecretConfirm(\'passwd_confirm_confirm\', \'confirmation mot de passe\');
', $this->builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_passwd_confirm" title="ceci est un tooltip">confirmation mot de passe</label>', $out);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="passwd_confirm" id="'.$this->formname.'_passwd_confirm" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" value=""/>', $out);
        $this->assertEqualOrDiff('c.confirmField = new jFormsControlSecretConfirm(\'passwd_confirm_confirm\', \'confirmation mot de passe\');
', $this->builder->getJsContent());

    }

    function testOutputOutput(){
        $ctrl= new jFormsControlOutput('output1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label">Votre nom</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="hidden" name="output1" id="'.$this->formname.'_output1" value=""/><span class="jforms-value"></span>', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $this->form->setData('output1','laurent');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="hidden" name="output1" id="'.$this->formname.'_output1" value="laurent"/><span class="jforms-value">laurent</span>', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="hidden" name="output1" id="'.$this->formname.'_output1" value="laurent"/><span class="jforms-value">laurent</span>', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" title="ceci est un tooltip">Votre nom</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="hidden" name="output1" id="'.$this->formname.'_output1" value="laurent"/><span class="jforms-value" title="ceci est un tooltip">laurent</span>', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());

    }

    function testOutputUpload(){
        $ctrl= new jFormsControlUpload('upload1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_upload1">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="file" name="upload1" id="'.$this->formname.'_upload1" value=""/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'upload1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="file" name="upload1" id="'.$this->formname.'_upload1" readonly="readonly" class=" jforms-readonly" value=""/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'upload1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_upload1" title="ceci est un tooltip">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="file" name="upload1" id="'.$this->formname.'_upload1" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" value=""/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'upload1\', \'Votre nom\');
c.errInvalid=\'La saisie de "Votre nom" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        ob_start();
        $this->builder->outputHeader(array('method'=>'post'));
        $out = ob_get_clean();
        $result ='<form action="'.$GLOBALS['gJConfig']->urlengine['basePath'].'index.php" method="post" id="'.$this->formname.'" enctype="multipart/form-data"><script type="text/javascript">
//<![CDATA[
jForms.tForm = new jFormsForm(\'jforms_formtest1\');
jForms.tForm.setErrorDecorator(new jFormsErrorDecoratorAlert());
jForms.tForm.setHelpDecorator(new jFormsHelpDecoratorAlert());
jForms.declareForm(jForms.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="module" value="jelix_tests"/>
<input type="hidden" name="action" value="urlsig:url1"/>
<input type="hidden" name="hidden1" id="'.$this->formname.'_hidden1" value="11"/>
</div>';
        $this->assertEqualOrDiff($result, $out);

        $this->form->removeControl('upload1');

    }
    function testOutputSubmit(){
        $ctrl= new jFormsControlSubmit('submit1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Ok';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="submit" name="submit1" id="'.$this->formname.'_submit1" class="jforms-submit" value="Ok"/>', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="submit" name="submit1" id="'.$this->formname.'_submit1" class="jforms-submit" value="Ok"/>', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="submit" name="submit1" id="'.$this->formname.'_submit1" title="ceci est un tooltip" class="jforms-submit" value="Ok"/>', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->standalone=false;
        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array('svg'=>'Sauvegarde','prev'=>'Preview');

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $output = '<input type="submit" name="submit1" id="'.$this->formname.'_submit1_svg" title="ceci est un tooltip" class="jforms-submit" value="Sauvegarde"/> ';
        $output .= '<input type="submit" name="submit1" id="'.$this->formname.'_submit1_prev" title="ceci est un tooltip" class="jforms-submit" value="Preview"/> ';
        $this->assertEqualOrDiff($output, $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());

    }
    function testOutputReset(){
        $ctrl= new jFormsControlReset('reset1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Effacer';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<button type="reset" name="reset1" id="'.$this->formname.'_reset1" class="jforms-reset">Effacer</button>', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<button type="reset" name="reset1" id="'.$this->formname.'_reset1" class="jforms-reset">Effacer</button>', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<button type="reset" name="reset1" id="'.$this->formname.'_reset1" title="ceci est un tooltip" class="jforms-reset">Effacer</button>', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());
    }
    function testOutputHidden(){
        $ctrl= new jFormsControlHidden('hidden2');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        ob_start();
        $this->builder->outputHeader(array('method'=>'post'));
        $out = ob_get_clean();
        $result ='<form action="'.$GLOBALS['gJConfig']->urlengine['basePath'].'index.php" method="post" id="'.$this->formname.'"><script type="text/javascript">
//<![CDATA[
jForms.tForm = new jFormsForm(\'jforms_formtest1\');
jForms.tForm.setErrorDecorator(new jFormsErrorDecoratorAlert());
jForms.tForm.setHelpDecorator(new jFormsHelpDecoratorAlert());
jForms.declareForm(jForms.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="module" value="jelix_tests"/>
<input type="hidden" name="action" value="urlsig:url1"/>
<input type="hidden" name="hidden1" id="'.$this->formname.'_hidden1" value="11"/>
<input type="hidden" name="hidden2" id="'.$this->formname.'_hidden2" value=""/>
</div>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl->defaultValue='toto';
        $this->form->removeControl($ctrl->ref);
        $this->form->addControl($ctrl);
        ob_start();
        $this->builder->outputHeader(array('method'=>'post'));
        $out = ob_get_clean();
        $result ='<form action="'.$GLOBALS['gJConfig']->urlengine['basePath'].'index.php" method="post" id="'.$this->formname.'"><script type="text/javascript">
//<![CDATA[
jForms.tForm = new jFormsForm(\'jforms_formtest1\');
jForms.tForm.setErrorDecorator(new jFormsErrorDecoratorAlert());
jForms.tForm.setHelpDecorator(new jFormsHelpDecoratorAlert());
jForms.declareForm(jForms.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="module" value="jelix_tests"/>
<input type="hidden" name="action" value="urlsig:url1"/>
<input type="hidden" name="hidden1" id="'.$this->formname.'_hidden1" value="11"/>
<input type="hidden" name="hidden2" id="'.$this->formname.'_hidden2" value="toto"/>
</div>';
        $this->assertEqualOrDiff($result, $out);
    }

    function testOutputCaptcha(){
        $ctrl= new jFormsControlcaptcha('cap');
        $ctrl->label='captcha for security';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label jforms-required" for="'.$this->formname.'_cap">captcha for security</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input type="text" name="cap" id="'.$this->formname.'_cap" class=" jforms-required" value=""/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'La saisie de "captcha for security" est obligatoire\';
c.errInvalid=\'La saisie de "captcha for security" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('cap','toto');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input type="text" name="cap" id="'.$this->formname.'_cap" class=" jforms-required" value=""/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'La saisie de "captcha for security" est obligatoire\';
c.errInvalid=\'La saisie de "captcha for security" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input type="text" name="cap" id="'.$this->formname.'_cap" value=""/>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'La saisie de "captcha for security" est obligatoire\';
c.errInvalid=\'La saisie de "captcha for security" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(false);
        $ctrl->help='some help';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input type="text" name="cap" id="'.$this->formname.'_cap" class=" jforms-required" value=""/><span class="jforms-help"><a href="javascript:jForms.showHelp(\''. $this->formname.'\',\'cap\')">?</a></span>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'cap\', \'captcha for security\');
c.help=\'some help\';
c.required = true;
c.errRequired=\'La saisie de "captcha for security" est obligatoire\';
c.errInvalid=\'La saisie de "captcha for security" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label jforms-required" for="'.$this->formname.'_cap" title="ceci est un tooltip">captcha for security</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input type="text" name="cap" id="'.$this->formname.'_cap" title="ceci est un tooltip" class=" jforms-required" value=""/><span class="jforms-help"><a href="javascript:jForms.showHelp(\''. $this->formname.'\',\'cap\')">?</a></span>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'cap\', \'captcha for security\');
c.help=\'some help\';
c.required = true;
c.errRequired=\'La saisie de "captcha for security" est obligatoire\';
c.errInvalid=\'La saisie de "captcha for security" est invalide\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

    }

    function testOutputHtmleditor(){
        $ctrl= new jFormsControlhtmleditor('contenu');
        $ctrl->label='Texte';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_contenu">Texte</label>', $out);

        $this->form->setData('contenu','<p>Ceci est un contenu</p>');

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" rows="5" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'contenu\', \'Texte\');
c.errInvalid=\'La saisie de "Texte" est invalide\';
jForms.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1");
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" readonly="readonly" class=" jforms-readonly" rows="5" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'contenu\', \'Texte\');
c.errInvalid=\'La saisie de "Texte" est invalide\';
jForms.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1");
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_contenu" title="ceci est un tooltip">Texte</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" rows="5" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'contenu\', \'Texte\');
c.errInvalid=\'La saisie de "Texte" est invalide\';
jForms.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1");
', $this->builder->getJsContent());


        $ctrl->rows=20;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" rows="20" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'contenu\', \'Texte\');
c.errInvalid=\'La saisie de "Texte" est invalide\';
jForms.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1");
', $this->builder->getJsContent());


        $ctrl->cols=60;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" rows="20" cols="60">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'contenu\', \'Texte\');
c.errInvalid=\'La saisie de "Texte" est invalide\';
jForms.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1");
', $this->builder->getJsContent());

        $ctrl->required=true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class=" jforms-readonly" rows="20" cols="60">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>', $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'contenu\', \'Texte\');
c.required = true;
c.errRequired=\'La saisie de "Texte" est obligatoire\';
c.errInvalid=\'La saisie de "Texte" est invalide\';
jForms.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1");
', $this->builder->getJsContent());
    }
}

