<?php

class Controller_Base extends Yaf_Controller
{
    /**
     * 是否进行登录检测
     * @var unknown
     */
    protected $bCheckLogin = false;

    /**
     * 当前用户
     * @var unknown
     */
    protected $aCurrUser = null;

    /**
     * 当前项目
     * @var unknown
     */
    protected $aCurrProject = null;

    /**
     * 执行Action前执行
     * @see Yaf_Controller::actionBefore()
     */
    public function actionBefore ()
    {
        $this->aCurrProject = Yaf_G::getConf('project');
        if ($this->bCheckLogin) {

        }
        $this->assign('sStaticRoot', 'http://' . Yaf_G::getConf('static', 'domain'));
        $this->assign('sRoot', 'http://' . Yaf_G::getConf('static', 'domain'));
        $this->assign('aMeta', array(
            'title' => $this->aCurrProject['name']
        ));

    }

    /**
     * 执行Action后的操作
     * @see Yaf_Controller::actionAfter()
     */
    public function actionAfter ()
    {
        if ($this->autoRender() == true) {
            if (! empty($this->aCurrUser)) {

            }
            $aDebug = Util_Common::getDebugData();
            if ($aDebug) {
                $this->assign('__showDebugInfo__', 'showDebugInfo(' . json_encode($aDebug) . ');');
            }
        } else {}
    }

    /**
     * 取得是否要检查登录权限
     * @return boolean
     */
    public function getCheckLogin ()
    {
        return $this->bCheckLogin;
    }

    public function redirect ($url)
    {
        $response = $this->getResponse();
        $response->setRedirect($url);
        $this->autoRender(false);
        return false;
    }
}