<?php
//数据库配制
$config['database']['dfs_db']['master'] = array(
    'dsn' => 'mysql:host=127.0.0.1;dbname=dfs_db',
    'user' => 'dfsadmin',
    'pass' => 'dfspass',
    'init' => array(
        'SET CHARACTER SET utf8',
        'SET NAMES utf8'
    )
);

$config['database']['dfs_db']['salve'] = array(
    'dsn' => 'mysql:host=127.0.0.1;dbname=dfs_db',
    'user' => 'dfsadmin',
    'pass' => 'dfspass',
    'init' => array(
        'SET CHARACTER SET utf8',
        'SET NAMES utf8'
    )
);

// 图片类型
$config['file']['aImageType'] = array(
    'gif',
    'jpg',
    'png'
);

//文件系统支持的文件格式
$config['file']['aAllowedType'] = array(
    '/ipo\.com|fangjiadp\.com/' => array(
        'gif',
        'jpg',
        'png',
        'pdf',
        'doc',
        'docx',
        "flv",
        "swf",
        "mp4",
    )
);

//文件系统支持的文件格式
$config['file']['aAllowedViewType'] = array(
    '/ipo\.com|fangjiadp\.com/' => array(
        'gif',
        'jpg',
        'png',
        'pdf',
        'doc',
        'docx'
    )
);

//文件系统支持的文件格式
$config['file']['aAllowedDownloadType'] = array(
    '/ipo\.com|fangjiadp\.com/' => array(
        'gif',
        'jpg',
        'png',
        'pdf',
        'doc',
        'docx',
        'zip'
    )
);

//文件系统支持的大小
$config['file']['aAllowedSize'] = array(
    '/ipo\.com|fangjiadp\.com/' => array(
        'iMin' => 1,
        'iMax' => 15728640
    )
);

//文件系统开放的域名
$config['file']['aAllowedDomain'] = array(
   '/fangjiadp\.com|ipo\.com/'
);

//文件系统存储
$config['file']['aStorageHost'] = array(
    array(
        'iHostID' => 1,
        'sRouteKeys' => array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'),
    ),
    array(
        'iHostID' =>2,
        'sRouteKeys' => array('a', 'b', 'c', 'd', 'd', 'e', 'f', 'g', 'h','i', 'j', 'k',
            'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        )
    )
);

$config['file']['browseCache'] = 315360000;
$config['file']['sRawDir'] = '/data/www/dfs/raw'; // 文件存储位置

return $config;