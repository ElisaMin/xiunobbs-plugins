?><?php include_once _include(APP_PATH."plugin/sl_repeat_follow/utils/repeats.php");

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
        $page = &$comment["page_no"];
        if (!empty($page)) {
            $html = get_paged_floor_html($repeats,$page,$pid,$uid,$comment,);
            message(0,$html);
        }
    }

}
?><?php