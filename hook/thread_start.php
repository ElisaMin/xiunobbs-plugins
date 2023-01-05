<?php exit;
if($action == 'rfloor') {
	$pid = param(2);
	$pageno = param('pageno', 0);
	$delfloor = param('delfloor', false);
	$post = post_read($pid);
	empty($post) AND message(-1, lang('post_not_exists'));
	$m_s=',';
	if(empty($post['repeat_follow']))
	{
		$m_s=$repeat_follows='';
		if($pageno>0) message(-1, lang('post_not_exists'));
	}
	else $repeat_follows=$post['repeat_follow'];
	$repeat_follows=substr($repeat_follows,1,-1);
	empty($repeat_follows) AND $m_s=$repeat_follows='';
	if($pageno>0)
	{
		$return_message='';
		$r_f_g=setting_get('sl_repeat_follow_perpage');
		$pageno=min($pageno,$post['r_f_c']);
		$pageno=max($pageno,1);
		$repeat_follows=json_decode($post['repeat_follow'], true);
		$repeat_follows=array_slice($repeat_follows,($pageno-1)*$r_f_g,$r_f_g);
		$message_t=$deltag='';
		foreach($repeat_follows as $repeat_follow){
			if($repeat_follow['uid']==$uid || $post['floormanage']) $deltag='<a href="javascript:delrfloor('.$pid.',\''.$repeat_follow['fl'].'\');" class="post_update mr-2">删除</a>';
			if($repeat_follow['t_uid']>0 && $repeat_follow['t_username']!='') $message_t='回复 <a href="'.url("user-".$repeat_follow['t_uid']).'" class="text-muted font-weight-bold">'.$repeat_follow['t_username'].'</a>: ';
			$return_message.='<dd class="text-left media" id="pf_'.$pid.'_'.$repeat_follow['fl'].'"><a href="'.url("user-".$repeat_follow['uid']).'" class="mr-2"><img class="avatar-3" onerror="this.src=\'view/img/avatar.png\'"  src="'.$repeat_follow['avatar_url'].'"></a><div style="width:100%;"><span class="text-left"><a href="'.url("user-".$repeat_follow['uid']).'" class="text-muted font-weight-bold">'.$repeat_follow['username'].'</a>: '.$message_t.$repeat_follow['message'].'</span><div class="text-muted text-right">'.$deltag.humandate($repeat_follow['update']).'<a href="javascript:showform('.$pid.',\''.$repeat_follow['username'].'\');" class="post_update ml-2">回复</a></div></div></dd>';
			$message_t=$deltag='';
		}
		message(0,$return_message.'<div id="pushfloor_'.$pid.'" style="display:none;"></div>');
	}
}
?>