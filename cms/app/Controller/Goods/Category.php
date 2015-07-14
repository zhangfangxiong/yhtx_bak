<?php

class Controller_Goods_Category extends Controller_Base
{
    /**
     * 分类列表
     */
    public function indexAction()
    {
        $aCategoryData = Model_Category::getTree();
    }

    //添加分类
    public function addAction()
    {

    }

    public function actionAfter()
    {
        parent::actionAfter();
        $this->_assignUrl();
    }

    protected function _assignUrl()
    {
        $this->assign('sListUrl', '/goods/category/');
        $this->assign('sAddUrl', '/goods/category/add/');
        $this->assign('sEditUrl', '/goods/category/edit/');
        $this->assign('sDelUrl', '/goods/category/del/');
        $this->assign('sPublishUrl', '/goods/category/publish/');
        $this->assign('sOffUrl', '/goods/category/off/');
    }

}