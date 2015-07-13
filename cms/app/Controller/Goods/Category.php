<?php

class Controller_Goods_Category extends Controller_Base
{
    /**
     * 分类列表
     */
    public function indexAction()
    {
        //$aCategoryData = Model_Category::getTree();
    }

    //添加分类
    public function addAction()
    {
        $aParam['sName'] = $this->getParam('sName');
        $aParam['iParentID'] = intval($this->getParam('iParentID'));
        if ($this->isPost()) {
            $aParam['iCreateUser'] = $aParam['iUpdateUser'] = $this->aCurrUser['iUserID'];
            $aNews = $this->_checkData($aParam);
            $aData = Model_Category::exsistCategory($aParam['sName'],$aParam['iParentID']);
            if (!empty($aData)) {
                return $this->showMsg('已存在该分类', false);
            }
            if (Model_Category::addData($aNews)) {
                return $this->showMsg('分类添加成功', true);
            }
        } else {
            $aData = Model_Category::getMenu();
            $this->assign('aTree',$aData);
        }
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