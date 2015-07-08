<?php
/**
 * @method array getParams()
 * @method mixed getParam($name, $default = null)
 * @method mixed assign($name, $value)
 *
 */
class Controller_Image_Index extends Yaf_Controller
{

    const FILE_NAME = 'file';

    public function uploadAction()
    {

        $sFromURL = $this->getRequest()->getHttpReferer();
        $oBllFile = new Bll_File();
        $mResult = $oBllFile->isAllowedDomain($sFromURL);

        if (true === $mResult) {
            $aFiles = $this->getUploadFiles();
            $sIP =  $this->getRequest()->getClientIP();
            $sDomain = Util_Uri::getDomain($sFromURL);
            $aUpdFiles = array();
            $iRetError = 1;

            // 批量上传图片 @todo 待优化
            foreach($aFiles as $aFile)
            {
                $aUpdFile = array();
                list($iError, $mResult) = $oBllFile->saveFile($aFile['name'], $aFile['tmp_name'], $aFile['error'], $aFile['size'], $sIP, $sDomain);
                if ($iError == 0) {
                    $iRetError = 0;
                    $mResult['iError'] = $iError;
                    $aUpdFiles[$aFile['key']] = $mResult;
                } else {
                    $aUpdFiles[$aFile['key']] = array('iError'=>$iError, 'sMsg'=>$mResult);
                }
            }

            $mResult = $aUpdFiles;
        } else {
            $iRetError = 1;
            $mResult = array('sMsg' => 'The upload domain is forbidden.');
        }

        $aRet = array_merge(array('iError'=>$iRetError), $mResult);
        $this->getResponse()->setBody(json_encode($aRet));

        return false;
    }

    public function demoAction() {

    }

    public function wallAction()
    {
        Yaf_Logger::getInstance()->info('hello');
        $aParams = $this->getParams();
        $iPage = isset($aParams['page']) && intval($aParams['page']) > 0 ? intval($aParams['page']) : 1;
        $aRet = Model_File::getList([], $iPage, 'iAutoID Desc', 20);
        $this->assign('aList', $aRet['aList']);
        $this->assign('aPager', $aRet['aPager']);
        $this->assign('sStaticRoot', 'http://' . Yaf_G::getConf('static', 'domain'));

        //print_r($aRet);
    }
    /**
     * 获取所有上传的文件信息
     *
     * @return array
     */
    protected function getUploadFiles() {
        $aFiles = $_FILES;
        $aMultiFiles = array();

        foreach($aFiles as $sKey => $mFiles){
            if(is_array($mFiles['name'])){
                $iCnt = count($mFiles['name']);
                for($i = 0; $i < $iCnt; ++$i){
                    $aMultiFiles[] = array(
                        'key' => $sKey . '_' . $i,
                        'name' => $mFiles['name'][$i],
                        'tmp_name' => $mFiles['tmp_name'][$i],
                        'error' => $mFiles['error'][$i],
                        'size' => $mFiles['size'][$i]
                    );
                }
            }else{
                $aMultiFiles[] = array(
                    'key' => $sKey,
                    'name' => $mFiles['name'],
                    'tmp_name' => $mFiles['tmp_name'],
                    'error' => $mFiles['error'],
                    'size' => $mFiles['size']
                );
            }
        }

        return $aMultiFiles;
    }
}