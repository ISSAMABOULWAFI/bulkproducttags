<?php
if(!defined('_PS_VERSION_'))
    exit();

class AdminTagsStatController extends AdminController
{
    public function __construct(){
        $this->bootstrap = true;
        parent::__construct();
    }


    public function getTemplatePath()
    {
        return dirname(__FILE__).'/../../views/templates/admin/';
    }



    public function createTemplate($tpl_name) {
        if (file_exists($this->getTemplatePath() . $tpl_name) && $this->viewAccess())
            return $this->context->smarty->createTemplate($this->getTemplatePath() . $tpl_name, $this->context->smarty);
        return parent::createTemplate($tpl_name);
    }



    public function initContent(){

  

        $smarty = $this->context->smarty;
        $this->content=$this->createTemplate('TagsStats.tpl')->fetch();
        parent::initContent();


    }


}
