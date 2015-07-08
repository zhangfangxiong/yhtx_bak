<?php

/**
 * Created by PhpStorm.
 * User: yaobiqing
 * Date: 14/12/24
 * Time: 下午1:48
 */
class Bll_File extends Bll_Base
{
    const FILE_NOT_EXISTS                    = 101;
    const FILE_IMAGE_DIMENSION_NOT_ALLOWED   = 102;
    const FILE_MIMETYPE_VIEW_NOT_ALLOWED     = 103;
    const FILE_EXT_INVAILD                   = 104;
    const FILE_MIMETYPE_DOWNLOAD_NOT_ALLOWED = 105;
    const FILE_MIMETYPE_NOT_ALLOWED = 106;

    const FILE_SAVE_SUCCESS = 0; // 保存文件成功
    const FILE_UPLOAD_ERROR = 1; // 文件上传错误
    const FILE_NOT_POST_UPLOAD_FILE = 2; // 不是通过POST上传文件
    const FILE_SIZE_LIMITED_ERROR = 3; // 上传文件大小超出限制范围
    const FILE_MIME_TYPE_ERROR = 4; // 文件上传类型超出限制
    const FILE_MOVE_TMP_FILE_ERROR = 5; // 移动上传临时文件出错
    const FILE_SAVE_INDEX_ERROR = 6; // 保存文件索引出错

    /**
     * 保存上传的文件
     *
     * @param string $p_sName
     * @param string $p_sTmpName
     * @param int $p_iError
     * @param int $p_iSize
     * @param string $p_sIP
     * @param int $p_iTime
     * @param array $o_aFileInfo
     * @return array
     */
    public function saveFile($p_sName, $p_sTmpName, $p_iError, $p_iSize, $p_sIP, $p_sDomain, $i_bID=Model_FileMeta::BID_DEFAULT)
    {
        $iError = self::FILE_SAVE_SUCCESS;

        do {
            if ($p_iError > 0) {
                // 文件上传出错
                $iError  = self::FILE_UPLOAD_ERROR;
                $mReturn = $this->getUploadErrMsg($p_iError);
                break;
            }

            if (!is_uploaded_file($p_sTmpName)) {
                $iError  = self::FILE_NOT_POST_UPLOAD_FILE;
                $mReturn = 'upload error, use http post to upload';
                break;
            }

            // 上传域名是否允许上传
            list($iError, $mReturn) = $this->checkAllowedSize($p_iSize, $p_sDomain, $iError);
            if (!empty($iError)) {
                break;
            }

            $oFInfo    = finfo_open();
            $sMimeType = finfo_file($oFInfo, $p_sTmpName, FILEINFO_MIME_TYPE);
            finfo_close($oFInfo);
            $sExtension = Util_File::getExtension($sMimeType);

            // 上传文件类型
            list($iError, $mReturn) = $this->checkAllowedType($sExtension, $p_sDomain, $iError);
            if (!empty($iError)) {
                break;
            }

            $sFileKey  = sha1_file($p_sTmpName);
            $aFileInfo = Model_File::getFileByKey($sFileKey);
            if (!empty($aFileInfo)) {
                $iError    = self::FILE_SAVE_SUCCESS;
                $aFileMeta = Model_FileMeta::getFileMetaByKey($sFileKey);
                $aFileMet['iBID'] = $aFileMeta['iBID'] | $i_bID;
                Model_FileMeta::updData(array('iBID'=>$aFileMet['iBID'], 'iAutoID'=>$aFileMeta['iAutoID']));
                $mReturn   = $this->normalizeFileInfo($aFileInfo, $aFileMeta);
                break;
            }

            $sDestFile = $this->getDestFile($sFileKey);
            $bImage    = $this->isImage($sExtension);
            if ($bImage) {
                $aImageInfo  = $this->getImageInfo($p_sTmpName);
                $o_aFileInfo = array(
                    'sKey'    => $sFileKey,
                    'sExt'    => $sExtension,
                    'sFile'    => $sFileKey . '.' . $sExtension,
                    'iSize'   => $p_iSize,
                    'iWidth'  => $aImageInfo['iWidth'],
                    'iHeight' => $aImageInfo['iHeight']
                );
                $aFileMeta   = array(
                    'sKey'      => $sFileKey,
                    'sName'     => $p_sName,
                    'iSize'     => $p_iSize,
                    'sMimeType' => $sMimeType,
                    'iBID'      => $i_bID,
                    'iIP'       => $this->ipToLong($p_sIP),
                    'iWidth'    => $aImageInfo['iWidth'],
                    'iHeight'   => $aImageInfo['iHeight']
                );
            } else {
                $o_aFileInfo = array(
                    'sKey'  => $sFileKey,
                    'sExt'  => $sExtension,
                    'sFile'    => $sFileKey . '.' . $sExtension,
                    'iSize' => $p_iSize
                );
                $aFileMeta   = array(
                    'sKey'      => $sFileKey,
                    'sName'     => $p_sName,
                    'iSize'     => $p_iSize,
                    'sMimeType' => $sMimeType,
                    'iBID'      => $i_bID,
                    'iIP'       => $this->ipToLong($p_sIP),
                );
            }

            // 移动文件
            if (false === move_uploaded_file($p_sTmpName, $sDestFile)) {
                $iError  = self::FILE_MOVE_TMP_FILE_ERROR;
                $mReturn = "Can not move upload file";
                break;
            }

            // 存储索引
            $iHostID     = $this->getDispatchHostID($sFileKey);
            $aFile       = array(
                'sKey'    => $sFileKey,
                'sExt'    => $sExtension,
                'iHostID' => $iHostID
            );
            $iFileID     = Model_File::addData($aFile);
            $iFileMetaID = Model_FileMeta::addData($aFileMeta);
            if (empty($iFileID) || empty($iFileMetaID)) {
                $iError  = self::FILE_SAVE_INDEX_ERROR;
                $mReturn = 'Save file index failed.';
                break;
            }

            // 文件上传成功
            $mReturn = $o_aFileInfo;
        } while (false);

        return array($iError, $mReturn);
    }

    public function checkAllowedSize($p_iSize, $p_sDomain, $iError, $mReturn='') {
        do {
            // 检查上传文件大小是否在限制范围内
            $aDomainAllowedSize = $this->getAllowedSize($p_sDomain);

            if (empty($aDomainAllowedSize)) {
                $iError  = self::FILE_SIZE_LIMITED_ERROR;
                $mReturn = 'The upload size of domain is configured.';
                break;
            }

            if ($p_iSize < $aDomainAllowedSize['iMin']) {
                $iError  = self::FILE_SIZE_LIMITED_ERROR;
                $mReturn = 'The upload file size is too small.';
                break;
            }

            if ($p_iSize > $aDomainAllowedSize['iMax']) {
                $iError  = self::FILE_SIZE_LIMITED_ERROR;
                $mReturn = 'The upload file size is too large.';
                break;
            }
        } while (false);

        return array($iError, $mReturn);
    }

    public function getAllowedSize($p_sDomain) {
        $aAllowedSize       = Yaf_G::getConf('aAllowedSize', 'file');
        $aDomainAllowedSize = array();
        foreach ($aAllowedSize as $sPattern => $aSize) {
            if (1 === preg_match($sPattern, $p_sDomain)) {
                $aDomainAllowedSize = $aSize;
                break;
            }
        }

        return $aDomainAllowedSize;
    }

    public function checkAllowedType($sExtension, $p_sDomain, $iError, $mReturn='') {
        do {
            // 检查文件格式
            if ('dat' == $sExtension) {
                // 未知文件类型
                $iError  = self::FILE_MIME_TYPE_ERROR;
                $mReturn = 'Unknown file mime type';
                break;
            }

            $aDomainAllowedExtension = $this->getAllowedTypes($p_sDomain);

            // 上传域名文件扩展名配置
            if (empty($aDomainAllowedExtension)) {
                $iError  = self::FILE_MIME_TYPE_ERROR;
                $mReturn = 'The file extension of current domain is not configured.';
                break;
            }

            if (!in_array($sExtension, $aDomainAllowedExtension)) {
                $iError  = self::FILE_MIME_TYPE_ERROR;
                $mReturn = 'The file extension of current domain is forbidden.';
                break;
            }
        } while(false);

        return array($iError, $mReturn);
    }

    public function getAllowedTypes($p_sDomain) {
        $aAllowedExtension       = Yaf_G::getConf('aAllowedType', 'file');
        $aDomainAllowedExtension = array();
        foreach ($aAllowedExtension as $sPattern => $aExtension) {
            if (1 === preg_match($sPattern, $p_sDomain)) {
                $aDomainAllowedExtension = $aExtension;
                break;
            }
        }

        return $aDomainAllowedExtension;
    }

    /**
     * @param $p_sKey
     * @param $p_sExt
     * @param $p_oFileInfo
     * @param $p_sDomain
     * @param int $p_iWidth
     * @param int $p_iHeight
     * @return int
     */
    public function getFile(
        $p_sKey,
        $p_sExt,
        &$p_oFileInfo,
        $p_sDomain,
        $p_iWidth = 0,
        $p_iHeight = 0,
        $p_bCrop = false,
        $p_iWaterMarkPosition = 0,
        $p_iWaterMarkPath = 0,
        $p_isDown = false,
        $p_sBid = Model_FileMeta::BID_DEFAULT
    ) {
        $iError = 0;

        do {
            $aFileInfo = $this->getFileInfo($p_sKey);
            $bWaterMark = true;

            if (Model_FileMeta::BID_BANNER == ($aFileInfo['iBID'] & $p_sBid)) {
                $bWaterMark = false;
            }
            if (!$aFileInfo) {
                $iError = self::FILE_NOT_EXISTS;
                break;
            }
            if ($aFileInfo['sExt'] != $p_sExt) {
                $iError = self::FILE_EXT_INVAILD;
                break;
            }

            $p_sMimeType     = $aFileInfo['sMimeType'];
            if ($p_isDown) {
                $aDomainViewType = $this->getAllowedDownloadType($p_sDomain);
            } else {
                $aDomainViewType = $this->getAllowedViewType($p_sDomain);
            }


            if (!in_array($p_sExt, $aDomainViewType)) {
                $iError = self::FILE_MIMETYPE_NOT_ALLOWED;
                break;
            }

            $sFilePath    = $this->getDestFile($p_sKey);
            $sFileContent = Util_File::tryReadFile($sFilePath);
            if (false === $sFileContent) {
                $iError = self::FILE_NOT_EXISTS;
                break;
            }


            if ($this->isImage($p_sExt)) {

                $utilImg = Util_ImageFactory::instance('gd');

                $aDomainDimension = $this->getAllowedImageDimension($p_sDomain);
                //针对图片操作
                $imgResource = imagecreatefromstring($sFileContent);

                if (!$imgResource) {
                    $iError = self::FILE_NOT_EXISTS;
                    break;
                }

                $sDimensionKey = $p_iWidth . 'x' . $p_iHeight;
                if ($p_iWidth > 0 && $p_iHeight > 0 && !array_key_exists($sDimensionKey, $aDomainDimension)) {
                    $iError = self::FILE_IMAGE_DIMENSION_NOT_ALLOWED;
                    break;
                }

                $sFileContent             = $this->resizeImage(
                    $imgResource,
                    $p_iWidth,
                    $p_iHeight,
                    $aDomainDimension,
                    $p_sMimeType,
                    $p_bCrop,
                    $bWaterMark,
                    $p_iWaterMarkPosition,
                    $p_iWaterMarkPath
                );
                $p_sETag            = $utilImg->buildEtagFromImg($sFileContent);
                $aFileInfo['iSize'] = sizeof($sFileContent);

            } else {
                $p_sETag = $p_sKey;
            }

            $p_oFileInfo = [
                'sContent'  => $sFileContent,
                'sETag'     => $p_sETag,
                'sExt'      => $aFileInfo['sExt'],
                'sMimeType' => $p_sMimeType,
                'iSize'     => $aFileInfo['iSize'],
                'sName'     => substr($aFileInfo['sName'], 0, strrpos($aFileInfo['sName'], '.'))
            ];

            $iError = true;


        } while (false);


        return $iError;
    }

    public function getAllowedDownloadType($p_sDomain)
    {
        $aAllowedViewType = Yaf_G::getConf('aAllowedDownloadType', 'file');

        $aDomainViewType = array();
        foreach ($aAllowedViewType as $sPattern => $aViewType) {
            if (1 === preg_match($sPattern, $p_sDomain)) {
                $aDomainViewType = $aViewType;
                break;
            }
        }

        return $aDomainViewType;
    }

    /**
     * 检查使用方是否是允许的域名
     *
     * @param string $p_sReferer
     * @return true/string
     */
    public function isAllowedDomain($p_sReferer)
    {
        $sDomain        = Util_Uri::getDomain($p_sReferer);
        $aAllowedDomain = Yaf_G::getConf('aAllowedDomain', 'file');

        foreach ($aAllowedDomain as $sDomainPattern) {
            if (1 === preg_match($sDomainPattern, $sDomain)) {
                return true;
                break;
            }
        }

        return false;
    }

    /**
     * 获取文件存储位置
     *
     * @param $p_sFileKey
     * @return string
     */
    public function getDestFile($p_sFileKey)
    {
        $sFileRawDir = Yaf_G::getConf('sRawDir', 'file');
        $iHostID     = $this->getDispatchHostID($p_sFileKey);
        $sFilePath   = $this->dispatchFilePath($p_sFileKey);
        $sDestFile   = sprintf(
            '%s%s%s%s%s%s',
            $sFileRawDir,
            DIRECTORY_SEPARATOR,
            $iHostID,
            DIRECTORY_SEPARATOR,
            $sFilePath,
            $p_sFileKey
        );

        return $sDestFile;
    }

    /**
     * 获取存储设备ID
     *
     * @param $p_sFileKey
     * @return int
     */
    public function getDispatchHostID($p_sFileKey)
    {
        $aStorageHost = Yaf_G::getConf('aStorageHost', 'file');
        $iHostID      = $this->dispatchHostID($aStorageHost, $p_sFileKey);

        return $iHostID;
    }

    /**
     * @param $p_sDomain
     * @return array
     */
    public function getAllowedImageDimension($p_sDomain)
    {
        $aImageDimension  = Yaf_G::getConf('dimension', 'image');
        $aDomainDimension = array();
        foreach ($aImageDimension as $sPattern => $aDimension) {
            if (1 === preg_match($sPattern, $p_sDomain)) {
                $aDomainDimension = $aDimension;
                break;
            }
        }

        return $aDomainDimension;
    }

    /**
     * @param $p_sDomain
     * @return array
     */
    public function getAllowedViewType($p_sDomain)
    {

        $aAllowedViewType = Yaf_G::getConf('aAllowedViewType', 'file');

        $aDomainViewType = array();
        foreach ($aAllowedViewType as $sPattern => $aViewType) {
            if (1 === preg_match($sPattern, $p_sDomain)) {
                $aDomainViewType = $aViewType;
                break;
            }
        }

        return $aDomainViewType;
    }

    /**
     * @param $p_sKey
     * @return array
     */
    public function getFileInfo($p_sKey)
    {
        $mReturn   = [];
        $aFileInfo = Model_File::getFileByKey($p_sKey);
        if (!empty($aFileInfo)) {
            $aFileMeta = Model_FileMeta::getFileMetaByKey($p_sKey);
            $mReturn   = array_merge($aFileInfo, $aFileMeta);
        }

        return $mReturn;
    }

    /**
     * 检查是否为图片
     *
     * @param $p_sExtension
     */
    protected function isImage($p_sExtension)
    {
        $aImageType = Yaf_G::getConf('aImageType', 'file');;
        $bImage = in_array($p_sExtension, $aImageType) ? true : false;

        return $bImage;
    }

    /**
     * 给文件分配路径
     * @param string $p_sFileKey
     * @return string
     */
    protected function dispatchFilePath($p_sFileKey)
    {
        $sSubPath = '';

        for ($i = 1; $i <= 3; $i++) {
            $sSubPath = sprintf('%s%s%s', $sSubPath, $p_sFileKey[$i], DIRECTORY_SEPARATOR);
        }

        return $sSubPath;
    }

    /**
     * 分配存储设备
     * @param array $p_aHostCfg
     * @return int
     */
    protected function dispatchHostID($p_aStorageHost, $p_sFileKey)
    {
        $sHostKey = $p_sFileKey[0];
        $iHostID  = 1;
        foreach ($p_aStorageHost as $p_aHost) {
            if (isset($p_aHost['sRouteKeys']) && in_array($sHostKey, $p_aHost['sRouteKeys'])) {
                $iHostID = $p_aHost['iHostID'];
                break;
            }
        }

        return $iHostID;
    }

    /**
     * 标准化返回信息
     *
     * @param $p_aFileMeta
     */
    protected function normalizeFileInfo($p_aFile, $p_aFileMeta)
    {
        $aFileInfo          = array();
        $aFileInfo['sKey']  = isset($p_aFile['sKey']) ? $p_aFile['sKey'] : '';
        $aFileInfo['sExt']  = isset($p_aFile['sExt']) ? $p_aFile['sExt'] : '';
        $aFileInfo['iSize'] = isset($p_aFileMeta['iSize']) ? $p_aFileMeta['iSize'] : 0;
        $aFileInfo['sFile'] = $aFileInfo['sKey'] . '.' . $aFileInfo['sExt'];

        if ($this->isImage($aFileInfo['sExt'])) {
            $aFileInfo['iWidth']  = isset($p_aFileMeta['iWidth']) ? $p_aFileMeta['iSize'] : 0;
            $aFileInfo['iHeight'] = isset($p_aFileMeta['iHeight']) ? $p_aFileMeta['iHeight'] : 0;
        }

        return $aFileInfo;
    }


    protected function resizeImage(
        $p_sFileContent,
        $p_iWidth,
        $p_iHeight,
        $p_aDomainDimension,
        $p_sMimeType,
        $p_bCrop,
        $p_bIsWaterMark = true,
        $p_iWaterMarkPosition = 0,
        $p_iWaterMarkPath = 0
    ) {

        $utilImg = Util_ImageFactory::instance('gd');
        if ($p_iWidth > 0 && $p_iHeight > 0) {
            $sDimensionKey = $p_iWidth . 'x' . $p_iHeight;
            $aDimensionConf = $p_aDomainDimension[$sDimensionKey];
            $p_bIsWaterMark = isset($aDimensionConf['waterMark']) ? $aDimensionConf['waterMark'] : true;

            $sWaterMarkPath = '';
            if ($p_iWaterMarkPath > 0) {
                $aWaterMarkPath = Util_Common::getConf('waterMarkPath', 'image');
                if (isset($aWaterMarkPath[$p_iWaterMarkPath])) {
                    $sWaterMarkPath = $aWaterMarkPath[$p_iWaterMarkPath];
                }
            }
            if (empty($sWaterMarkPath) && isset($aDimensionConf['waterMarkPath'])) {
                $sWaterMarkPath = $aDimensionConf['waterMarkPath'];
            }


            if (!empty($sWaterMarkPath)) {
                //设置水印的图片地址
                $utilImg->setWaterMarkImg($sWaterMarkPath);
            }

            $sWaterMarkPosition = '';
            if ($p_iWaterMarkPosition > 0) {
                $aWaterMarkPosition = Util_Common::getConf('waterMarkPosition', 'image');
                if (isset($aWaterMarkPosition[$p_iWaterMarkPosition])) {
                    $sWaterMarkPosition = $aWaterMarkPosition[$p_iWaterMarkPosition];
                }
            }

            if (!empty($sWaterMarkPosition)) {
                //设置水印的图片位置
                $utilImg->setWaterMarkPosition($sWaterMarkPosition);
            }
        }

        //图片操作
        $p_sFileContent = $utilImg->resize($p_sFileContent, $p_iWidth, $p_iHeight, $p_bCrop, $p_bIsWaterMark);
        return $utilImg->getImageBlob($p_sFileContent, $p_sMimeType);
    }

    /**
     * 得到图片的扩展信息
     * @param string $p_sTmpName
     * @return array
     */
    protected function getImageInfo($p_sTmpName)
    {
        $aImageInfo = getimagesize($p_sTmpName);

        return array(
            'iWidth'    => $aImageInfo[0],
            'iHeight'   => $aImageInfo[1],
            'iChannels' => isset($aImageInfo['channels']) ? $aImageInfo['channels'] : '0',
            'iBits'     => isset($aImageInfo['bits']) ? $aImageInfo['bits'] : '0'
        );
    }

    /**
     * 获取文件上传错误信息
     *
     * @param $p_iError
     * @return string
     */
    protected function getUploadErrMsg($p_iError)
    {

        switch ($p_iError) {
            case 0:
                $sErrMsg = 'The file uploaded with success';
                break;
            case 1:
                $sErrMsg = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                break;
            case 2:
                $sErrMsg = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                break;
            case 3:
                $sErrMsg = 'The uploaded file was only partially uploaded';
                break;
            case 4:
                $sErrMsg = 'No file was uploaded';
                break;
            case 6:
                $sErrMsg = 'Missing a temporary folder';
                break;
            case 7:
                $sErrMsg = 'Failed to write file to disk';
                break;
            case 8:
                $sErrMsg = 'A PHP extension stopped the file upload';
                break;
            default:
                $sErrMsg = 'Unkown error';
        }

        return $sErrMsg;
    }

    /**
     * IP 地址转换称无符号整数类型，ip2long 转成二进位, 再转回十进位避免负值
     * @param $p_sIP
     */
    protected function ipToLong($p_sIP) {
        return bindec(decbin(ip2long($p_sIP)));
    }
}
