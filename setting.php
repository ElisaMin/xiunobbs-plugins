<?php

/*
	Xiuno BBS 4.0 插件实例：QQ 登陆插件设置
	admin/plugin-setting-xn_qq_login.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	$setting['sl_repeat_follow_color'] = setting_get('sl_repeat_follow_color');
	$setting['sl_repeat_follow_perpage'] = setting_get('sl_repeat_follow_perpage');
	$setting['sl_repeat_follow_b_c'] = setting_get('sl_repeat_follow_b_c');
	$setting['sl_repeat_follow_b_t'] = setting_get('sl_repeat_follow_b_t');
	$setting['sl_repeat_follow_b_w'] = setting_get('sl_repeat_follow_b_w');
	$setting['sl_repeat_follow_b_mw'] = setting_get('sl_repeat_follow_b_mw');
	include _include(APP_PATH.'plugin/sl_repeat_follow/setting.htm');
	
} else {

	setting_set('sl_repeat_follow_color', param('sl_repeat_follow_color', '', FALSE));
	setting_set('sl_repeat_follow_perpage', param('sl_repeat_follow_perpage', '', FALSE));
	setting_set('sl_repeat_follow_b_c', param('sl_repeat_follow_b_c', '', FALSE));
	setting_set('sl_repeat_follow_b_t', param('sl_repeat_follow_b_t', '', FALSE));
	setting_set('sl_repeat_follow_b_w', param('sl_repeat_follow_b_w', '', FALSE));
	setting_set('sl_repeat_follow_b_mw', param('sl_repeat_follow_b_mw', '', FALSE));
	message(0, '修改成功');
}
	
?>