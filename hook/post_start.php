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
	if($delfloor!==false)
	{
		empty($repeat_follows) AND message(-1, lang('post_not_exists'));
		$arrs=json_decode($repeat_follows,true);
		$n=0;
		$m_s=$message_json='';
		foreach($arrs as $arr){
			if($arr['fl']!=$delfloor)
			{
				if($n>0) $m_s=',';
				$message_json.=$m_s.'{"fl":"'.$arr['fl'].'","uid":"'.$arr['uid'].'","username":"'.$arr['username'].'","avatar_url":"'.$arr['avatar_url'].'","t_uid":"'.$arr['t_uid'].'","t_username":"'.$arr['t_username'].'","message":"'.str_replace(array('"','\\',),array('\"','\\'.'\\'),$arr['message']).'","update":"'.$arr['update'].'"}';
				$n++;
			}
		}
		if($message_json!=''){
			$message_json='['.$message_json.']';
			$r = db_update('post', array('pid'=>$pid), array('repeat_follow'=>$message_json, 'r_f_c'=>$n));
		}
		else $r = db_update('post', array('pid'=>$pid), array('repeat_follow'=>'', 'r_f_c'=>0, 'r_f_a'=>0));
		$r === FALSE AND message(-1, lang('update_post_failed'));
		message(0, lang('delete_successfully'));
	}
	$repeat_follows=substr($repeat_follows,1,-1);
	empty($repeat_follows) AND $m_s=$repeat_follows='';
	$tid = $post['tid'];
	$thread['uid']=$post['uid'];
	$thread['subject']=$post['message_fmt'];
	$thread['tid']=$post['tid'];
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
	$t_username=$message_t='';
	$message = param('message', '', FALSE);
	$t_uid = 0;
	$t_username=trim(str_replace('回复','',strchr($message,':',true)));
	if($t_username!='')
	{
		$t_u = user_read_by_username($t_username);
		if (!$t_u || empty($t_u['uid'])) $t_username='';
		else
		{
			$message=trim(strchr($message,':'),':');
			$t_uid=$thread['uid']=$t_u['uid'];
			$message_t='回复 <a href="'.url("user-".$t_uid).'" class="text-muted font-weight-bold">'.$t_username.'</a>: ';
		}
	}
	$message = htmlspecialchars($message);
	$message = trim(xn_html_safe($message));
	$message = preg_replace("#[ ]{2,}#is"," ",str_replace(array("\n","\r","\t"),array(' ',' ',' '),$message));
	if(empty($message) || $message=='') message('message'.$pid, lang('please_input_message'));
	xn_strlen($message) > 2028000 AND message('message', lang('message_too_long'));
	if(function_exists("notice_send")){
		$thread['subject'] = notice_substr($thread['subject'], 20);
		$notice_message = '<div class="comment-info"><a class="mr-1 text-grey" href="'.url("thread-$thread[tid]").'#'.$pid.'">'.lang('notice_lang_comment').'</a>'.lang('notice_message_replytoyou').'<a href="'.url("thread-$thread[tid]").'#'.$pid.'">《回帖：'.$thread['subject'].'》</a></div><div class="single-comment"><a href="'.url("thread-$thread[tid]").'#'.$pid.'">'.notice_substr($message, 40, FALSE).'</a></div>';
		$recvuid = $thread['uid'];
		notice_send($uid, $recvuid, $notice_message, 2);
	}
	$r_f_c=$post['r_f_c']+1;
	$r_f_a=$post['r_f_a']+1;
	$return_message='<dd class="text-left media" id="pf_'.$pid.'_'.$r_f_a.'"><a href="'.url("user-".$uid).'" class="mr-2"><img class="avatar-3" onerror="this.src=\'view/img/avatar.png\'"  src="'.$user['avatar_url'].'"></a><div style="width:100%;"><span class="text-left"><a href="'.url("user-".$uid).'" class="text-muted font-weight-bold">'.$user['username'].'</a>: '.$message_t.$message.'</span><div class="text-muted text-right"><a href="javascript:delrfloor('.$pid.',\''.$r_f_a.'\');" class="post_update mr-2">删除</a>'.humandate($time).'<a href="javascript:showform('.$pid.',\''.$user['username'].'\');" class="post_update ml-2">回复</a></div></div></dd>';
	$dir = substr(sprintf("%09d", $user['uid']), 0, 3);
	$user_face=$conf['upload_url']."avatar/$dir/$uid.png";
	$message='['.$repeat_follows.$m_s.'{"fl":"'.$r_f_a.'","uid":"'.$uid.'","username":"'.$user['username'].'","avatar_url":"'.$user_face.'","t_uid":"'.$t_uid.'","t_username":"'.$t_username.'","message":"'.str_replace(array('"','\\'),array('\"','\\'.'\\'),$message).'","update":"'.$time.'"}]';
	$r = db_update('post', array('pid'=>$pid), array('repeat_follow'=>$message, 'r_f_c'=>$r_f_c, 'r_f_a'=>$r_f_a));
	$r === FALSE AND message(-1, lang('update_post_failed'));
	message(0,$return_message);
}
?>