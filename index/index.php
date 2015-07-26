<?php
$sIndexPath = dirname(__FILE__);//等于5.3中的__dir__
$sHost = $_SERVER['HTTP_HOST'];
$aHost = explode('.', $sHost);
define('ENV_CHANNEL', array_shift($aHost));//这里是设置了二级域名的，在这里可以设置为文件夹名
define("ROOT_PATH", '/home/wwwroot/default');//root目录
define("APP_PATH", ROOT_PATH.'/cms/app');//项目目录
define("LIB_PATH", ROOT_PATH.'/library');//基类目录


define('ENV_DOMAIN', join('.', $aHost));//当前域名
define("CONF_PATH", $sIndexPath . '/config/');//配置文件目录
define('ENV_CHANNEL', 'cms');//当前项目
define('ENV_SCENE', 'dev');//当前环境,线上环境这里要改
header("Content-type: text/html; charset=utf-8");

//特殊处理(nginx解析到index根目录，这里没有做解析，加上以下特殊处理)
//$aUrl = explode('.php',$_SERVER['REQUEST_URI']);
//$_SERVER['REQUEST_URI'] = isset($aUrl[1]) ? $aUrl[1] : '/';
try {
    require_once LIB_PATH . '/loader.php';
    $app = new Yaf_Application();
    $app->bootstrap()->run();
} catch (Exception $e) {
    echo $e->getMessage();
}
