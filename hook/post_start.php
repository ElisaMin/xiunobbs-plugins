?><?php
function error_no_comment() {
    message(-1, lang('post_not_exists'));
    exit;
}
function get_validate_comment() {
    $pid = param(2);
    if (empty($pid)) return false;
    $comment = post_read($pid);
    if (empty($comment)) return false;
    $comment['pid'] = $pid;

    $repeats = $comment['repeat_follow'];
    if (empty($repeats) && param("pageno",0) > 0)
        return false;
    $comment["page_no"] = param("pageno",0);

    $del = param('delfloor');
    if (!empty($del)) $comment['del'] = $del;

    return $comment;
}
function update_repeat(array $data) {

}
function update_repeat_and_result(array $data) {

}

function clean_html($html) {
    $html = htmlspecialchars($html);
    return trim(xn_html_safe($html));
}
function get_all_floor_html($data,$pid,$uid,$comment,$filter) {
    $filter = empty($filter) ? function () { return true; } : $filter;
    $html = '';
    foreach ($data as $index => $item) {
        if ($filter($item,$index)) {
            $html.=get_floor_html_dd($item,$pid,$data['uid'] == $uid||$comment['floormanage']);
        }
    }
    return $html;
}
function get_floor_html_dd(array $repeat_follow,$pid,bool $del) {
    return include "plugin/sl_repeat_follow/hook/inside_post.phtml";
}
if($action == 'rfloor') {

    $comment = get_validate_comment();
    if (empty($comment)) error_no_comment();
    $pid = &$comment['pid'];

    $repeats = json_decode($comment['repeat_follow'],true);
    // not heizi
    $count = $comment['r_f_c'];
    $tid = $comment['tid'];
    $thread['uid']=$comment['uid'];
    $thread['subject']=$comment['message_fmt'];
    $thread['tid']=$comment['tid'];
    // heizi shit
    if ($method == "POST") {
        if (key_exists('del',$comment)) {
            if (empty($repeats)) message(1,lang('data_is_empty'));
            $fl = $comment['del'];
            foreach ($repeats as $i => $repeat) {
                if ($repeat['fl']==$fl) {
                    unset($repeat);
                    break;
                }
            }
            update_repeat_and_result($repeats);
            return true;
        } else {
            // not heizi
            //fixme 继续
            $message_start=',';
            $repeat_follows=$comment['repeat_follow'];
            $repeat_follows=substr($repeat_follows,1,-1);
            empty($repeat_follows) AND $message_start=$repeat_follows='';
            // post the repeat
            $t_username=$message_t='';
            $message = param('message', '', FALSE);
            $t_uid = 0;
            $t_username=trim(str_replace('回复','',strchr($message,':',true)));
            if($t_username!='') {
                $user = user_read_by_username($t_username);
                if (!$user || empty($user['uid'])) $t_username='';
                else {
                    $message=trim(strchr($message,':'),':');
                    $t_uid=$thread['uid']=$user['uid'];
                    $message_t='回复 <a href="'.url("user-".$t_uid).'" class="text-muted font-weight-bold">'.$t_username.'</a>: ';
                }
            }
            $message = clean_html($message);
            preg_replace("# {2,}#is"," ",str_replace(array("\n","\r","\t"),array(' ',' ',' '),$message));
            empty($message) AND message('message'.$pid, lang('please_input_message'));
            xn_strlen($message) > 2028000 AND message('message', lang('message_too_long'));
            if(function_exists("notice_send")){
                $thread['subject'] = notice_substr($thread['subject'], 20);
                $notice_message = '<div class="comment-info"><a class="mr-1 text-grey" href="'.url("thread-$thread[tid]").'#'.$pid.'">'.lang('notice_lang_comment').'</a>'.lang('notice_message_replytoyou').'<a href="'.url("thread-$thread[tid]").'#'.$pid.'">《回帖：'.$thread['subject'].'》</a></div><div class="single-comment"><a href="'.url("thread-$thread[tid]").'#'.$pid.'">'.notice_substr($message, 40, FALSE).'</a></div>';
                $recvuid = $thread['uid'];
                notice_send($uid, $recvuid, $notice_message, 2);
            }
            $count=$count+1;
            $r_f_a=$comment['r_f_a']+1;

            $return_message='<dd class="text-left media" id="pf_'.$pid.'_'.$r_f_a.'"><a href="'.url("user-".$uid).'" class="mr-2"><img class="avatar-3" onerror="this.src=\'view/img/avatar.png\'"  src="'.$user['avatar_url'].'"></a><div style="width:100%;"><span class="text-left"><a href="'.url("user-".$uid).'" class="text-muted font-weight-bold">'.$user['username'].'</a>: '.$message_t.$message.'</span><div class="text-muted text-right"><a href="javascript:delrfloor('.$pid.',\''.$r_f_a.'\');" class="post_update mr-2">删除</a>'.humandate($time).'<a href="javascript:showform('.$pid.',\''.$user['username'].'\');" class="post_update ml-2">回复</a></div></div></dd>';
            $dir = substr(sprintf("%09d", $user['uid']), 0, 3);
            $user_face=$conf['upload_url']."avatar/$dir/$uid.png";
            $message='['.$repeat_follows.$message_start.'{"fl":"'.$r_f_a.'","uid":"'.$uid.'","username":"'.$user['username'].'","avatar_url":"'.$user_face.'","t_uid":"'.$t_uid.'","t_username":"'.$t_username.'","message":"'.str_replace(array('"','\\'),array('\"','\\'.'\\'),$message).'","update":"'.$time.'"}]';
            $r = db_update('post', array('pid'=>$pid), array('repeat_follow'=>$message, 'r_f_c'=>$count, 'r_f_a'=>$r_f_a));
            $r === FALSE AND message(-1, lang('update_post_failed'));
            message(0,$return_message);

        }
    } else { // GET
        $page_no = &$comment['page_no'];
        if($page_no>0) {
            include_once _include(APP_PATH."plugin/sl_repeat_follow/utils/conf.php");
            $per_page=getRepeatConfig()[peerPage];
            $page_no=min($page_no,$count);
            $page_no=max($page_no,1);
            $return_message = get_all_floor_html(
                array_slice($repeats,($page_no-1)*$per_page,$per_page),
                $pid,$uid,$comment,null
            );
            $return_message = clean_html($return_message);
            $return_message.= '<div id="pushfloor_'.$pid.'" style="display:none;"></div>';
            message(0,$return_message);
        }
    }

}
?><?php