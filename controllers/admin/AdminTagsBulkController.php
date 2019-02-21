<?php
if(!defined('_PS_VERSION_'))
    exit();

class AdminTagsBulkController extends AdminController
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

        $oldTags = array();

        if (Tools::isSubmit('mysubmit')) {

            $products =Tools::getValue('products');
            $tags =Tools::getValue('tags');
            $tagsArray=array();
            $productsArray = explode(",", $products);
            $tagsArray=explode(",", $tags);
            $res = array();
            $tagObj = new Tag();


            // remove  tags from a product or a list of products
            if($tags=="" && !$products=""){
                    $products =Tools::getValue('products');
                    Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'product_tag WHERE id_product in('.$products.')');
                    Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tag` WHERE `id_tag` NOT IN (SELECT `id_tag` FROM `'._DB_PREFIX_.'product_tag`)');
            }


            
            //adding tags to a product or  a list of products
            if(!$tags=="" && !$products=""){
                foreach ($productsArray as $product) {
           
                    $oldTags = array();
                    $oldTags =Tag::getProductTags($product);
                    $oldTags = $oldTags[1];
                    $result = array_intersect($tagsArray,$oldTags);

                    if(!empty($result)){
        
                        $tagsArray = array_diff($tagsArray,$result);
                        
                    }

                    for ($i=0;$i<count($productsArray);$i++){
                        Tag::addTags(1, $productsArray[$i], $tagsArray);
                    } 
                    $tagObj->setProducts($productsArray);
                } 
            }
        }

        $smarty = $this->context->smarty;
        $this->content=$this->createTemplate('TagsBulk.tpl')->fetch();
        parent::initContent();


    }


}
