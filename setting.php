<?php !defined('DEBUG') AND exit('Access Denied.');
include_once _include(APP_PATH."plugin/sl_repeat_follow/utils/conf.php");

if($method == 'GET') {
    include_once _include(APP_PATH.'plugin/sl_repeat_follow/setting.phtml');
} else {
    $r = setRepeatConfig([
        backgroundColor => param('color'),
        peerPage => param('ppg'),
        lineColor => param('b_c'),
        lineStyle => param('b_t'),
        width => param('b_w'),
        minWidth => param('b_mw'),
    ]) ? 0 : 1 ;
    message(-1, "修改". $r = $r == 0 ? "成功": "失败");
    echo "<script>alert('$r')</script>";
}