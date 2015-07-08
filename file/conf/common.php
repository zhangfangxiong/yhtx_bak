<?php
//URL规则
$config['route']['type']    = 'static';
$config['route']['rewrite'] = array(
    '/^view\/fj(?<biz>banner)\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)\.(?<ext>jpg|gif|png|bmp)$/i'            => '/file/index/view',
    '/^view\/fj(?<biz>banner)\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)_(?<crop>c)\.(?<ext>jpg|gif|png|bmp)$/i' => '/file/index/view',
    '/^view\/fj(?<biz>banner)\/(?<key>[a-z0-9]{40})\.(?<ext>.*)$/i'                                 => '/file/index/view',
    '/^view\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)\.(?<ext>jpg|gif|png|bmp)$/i'                                 => '/file/index/view',
    '/^view\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)_(?<crop>c)\.(?<ext>jpg|gif|png|bmp)$/i'                      => '/file/index/view',
    '/^view\/(?<key>[a-z0-9]{40})\.(?<ext>.*)$/i'                                                      => '/file/index/view',

    //download
    '/^download\/fj(?<biz>banner)\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)\.(?<ext>jpg|gif|png|bmp)$/i'            => '/file/index/download',
    '/^download\/fj(?<biz>banner)\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)_(?<crop>c)\.(?<ext>jpg|gif|png|bmp)$/i' => '/file/index/download',
    '/^download\/fj(?<biz>banner)\/(?<key>[a-z0-9]{40})\.(?<ext>.*)$/i'                                 => '/file/index/download',
    '/^download\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)\.(?<ext>jpg|gif|png|bmp)$/i'                                 => '/file/index/download',
    '/^download\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)_(?<crop>c)\.(?<ext>jpg|gif|png|bmp)$/i'                      => '/file/index/download',
    '/^download\/(?<key>[a-z0-9]{40})\.(?<ext>.*)$/i'                                                      => '/file/index/download',
);

$config['image']['waterMarkPath'] = [
    1 => APP_PATH . '/watermark/fjdp.png'
];

$config['image']['defaultWaterMarkPath'] = APP_PATH . '/watermark/fjdp.png';

$config['image']['waterMarkPosition'] = [
    1 => 'bottom-left',
    2 => 'bottom-right',
    3 => 'bottom-middle',
    4 => 'top-left',
    5 => 'top-right',
    6 => 'top-middle',
];

$config['image']['dimension'] = array(
    '/ipo\.com|fangjiadp\.com/' => array(
        '54x80'   => array('width' => 54, 'height' => 80),
        '75x60'   => array('width' => 75, 'height' => 60),
        '70x55'   => array('width' => 70, 'height' => 55),
        '90x60'   => array('width' => 90, 'height' => 60),
        '120x90'  => array('width' => 120, 'height' => 90),
        '130x94'  => array('width' => 130, 'height' => 94),
        '160x120' => array('width' => 160, 'height' => 120),
        '180x130' => array('width' => 180, 'height' => 130),
        '208x150' => array('width' => 208, 'height' => 150),
        '220x160' => array('width' => 220, 'height' => 160),
        '213x160' => array('width' => 213, 'height' => 160, 'waterMark' => false),

        '250x210' => array('width' => 250, 'height' => 210),
        '310x215' => array('width' => 310, 'height' => 215),
        '310x275' => array('width' => 310, 'height' => 275),
        '640x420' => array('width' => 640, 'height' => 420, 'waterMark' => false),
        '600x338' => array('width' => 600, 'height' => 338, 'waterMark' => false),
        '600x450' => array('width' => 600, 'height' => 450),
        '400x300' => array('width' => 400, 'height' => 300),
        '960x800' => array('width' => 960, 'height' => 800),
    )
);

return $config;