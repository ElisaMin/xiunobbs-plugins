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
    if (empty($repeats) && param("p",0) > 0)
        return false;
    $comment["page_no"] = param("p",0);

    $del = param('delfloor');
    if (!empty($del)) $comment['del'] = $del;

    $message = param('message','', false);
    if (!empty($message)) {
        $message = clean_html($message);
        preg_replace("# {2,}#is"," ",str_replace(array("\n","\r","\t"),array(' ',' ',' '),$message));
        empty($message) AND message('message'.$pid, lang('please_input_message'));
        xn_strlen($message) > 2028000 AND message('message', lang('message_too_long'));
    }
    if (!empty($message)) $comment["repeat_msg"] = $message;

    return $comment;
}
function update_repeat(array $data,$pid) {
    $count = 0;
    $lastIndex = 0;
    if (!empty($data)) {
        if (!array_multisort($data,'fl')) return xn_error(-255,"sort failed");
        $lastIndex = intval($data[array_key_last($data)]["fl"]);
        $count = count($data);
    }
    $data = json_encode($data);
    empty($data) && $data = "";
    return db_update("post",["pid"=>$pid],[
        "repeat_follow"=>$data,
        'r-f-c'=>$count,
        'r-f-a'=>$lastIndex,
    ]);
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
function get_paged_floor_html($data,$page,$pid,$uid,$comment) {
    include_once _include(APP_PATH."plugin/sl_repeat_follow/utils/conf.php");
    $per_page=getRepeatConfig()[peerPage];
    $page=min($page,$count);
    $page=max($page,1);
     return get_all_floor_html(
        array_slice($data,($page-1)*$per_page,$per_page),
        $pid,$uid,$comment,null
    );
}
function get_floor_html_dd(array $repeat_follow,$pid,bool $del) {
    return include "plugin/sl_repeat_follow/hook/inside_post.phtml";
}
if($action == 'rfloor') {
    $comment = get_validate_comment();
    if (empty($comment)) error_no_comment();
    $pid = &$comment['pid'];
    $repeats = json_decode($comment['repeat_follow'],true);

    $count = $count($repeats);
    $uid ?? error_no_comment();
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
            update_repeat($repeats,$pid) AND message(-1, lang('delete_failed'));
            message(0,"delete_successfully");
            return true;
        } elseif(key_exists("repeat_msg",$comment)) {
            $message = &$comment["repeat_msg"];
            $count=$count+1;
            $data = [
                "fl" => $comment['r_f_a']+1,
                "uid" => $uid,
                "username" => $user["username"],
                "avatar_url"=>$conf['upload_url']."avatar/".substr(sprintf("%09d", $user['uid']), 0, 3)."/$uid.png",
                "update"=>$time,
                "t_username" => trim(str_replace('回复','',strchr($message,':',true))),
            ];
            $data["message"] = $message;
            $data["t_uid"] = empty($data["t_username"]) ? user_read_by_username($data["t_username"]) : 0;
            $repeats[] = $data;

            update_repeat($repeats,$pid) AND message(-1, lang('update_post_failed'));

            if(function_exists("notice_send")){
                $thread['subject'] = notice_substr($thread['subject'], 20);
                $notice_message = '<div class="comment-info"><a class="mr-1 text-grey" href="'.url("thread-$thread[tid]").'#'.$pid.'">'.lang('notice_lang_comment').'</a>'.lang('notice_message_replytoyou').'<a href="'.url("thread-$thread[tid]").'#'.$pid.'">《回帖：'.$thread['subject'].'》</a></div><div class="single-comment"><a href="'.url("thread-$thread[tid]").'#'.$pid.'">'.notice_substr($message, 40, FALSE).'</a></div>';
                $recvuid = $thread['uid'];
                notice_send($uid, $recvuid, $notice_message, 2);
            }
            message(0,get_floor_html_dd($data,$pid,true));
            return true;
        }
    } else { // GET
        $page_no = &$comment['page_no'];
        if($page_no>0) {
            $html = get_paged_floor_html($repeats,$page_no,$pid,$uid,$comment,);
            message(0,$html);
        }
    }

}
?><?php