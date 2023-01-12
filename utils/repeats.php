<?php include_once _include(APP_PATH."plugin/sl_repeat_follow/utils/conf.php");

/**
 * 退出
 * @return void
 */
function error_no_comment() {
    message(-1, lang('post_not_exists'));
    exit;
}

/**
 * params
 * @return int
 */
function pid() {
    $pid = intval(param(2,-1));
    if ($pid==-1) error_no_comment();
    return $pid;
}
function rid() {
    $rid = intval(param(3,-1));
    if ($rid==-1) error_no_comment();
    return $rid;
}
function uid() {
    return param(4,-1);
}
function msg($key="message") {
    $message = param($key,"",false);
    if (!empty($message)) {
        $message = clean_html($message);
        preg_replace("# {2,}#is"," ",str_replace(array("\n","\r","\t"),array(' ',' ',' '),$message));
        empty($message) AND message('message', lang('please_input_message'));
        xn_strlen($message) > 2028000 AND message('message', lang('message_too_long'));
    }
    return $message;

}

/**
 * users
 *
 * @param $usr
 * @return array|false|null
 */
function find_user_or_guest($usr) {
    return $usr < 1 ? user_guest() :
        ($usr == ($uid ?? 0) ? $user ?? user_read($usr) : user_read($usr));
}

$users = [];
function find_user_or_guest_by_cache($usr) {
    global $users;
    $u = $users[$usr] ?? find_user_or_guest($usr);
    $users[$usr] = $u;
    return $u;
}
function clean_usr_cache() {
    global $users;
    $users = [];
}

/**
 * get replies as array
 *
 * @param $pid
 * @param bool $mix
 * @param bool $min
 * @return array
 */
function replies($pid,bool $mix=false,bool $min = false):array {
    $pid = post_read($pid);
    empty($pid) && error_no_comment();
    $replies = json_decode($pid['repeat_follow'],true);
    if (!($min||$mix)) message(0,"shit");
    if ($min||$mix) $replies = array_map(function ($reply) use ($mix,$min) {
        if ($mix) {
            $reply['id'] = $reply['id'] ?? $reply['fl'];
            $reply["re"] = $reply['re'] ?? $reply['t_uid'];
            $reply["msg"] = $reply['msg'] ?? $reply['message'];
        }
        if ($min) {
            unset($reply['avatar_url']);
            unset($reply['username']);
            unset($reply['t_username']);
        }
        if ($mix&&$min) {
            unset($reply['t_uid']);
            unset($reply['fl']);
            unset($reply['message']);
        }
        return $reply;
    },$replies);
    return $replies;
}
function replies_full($pid): array {
    $replies = replies($pid,true,true);
    $replies = array_map(function ($reply) use ($pid) {
        $usr = find_user_or_guest_by_cache($reply['uid']);
        $reply["img"] = $usr["avatar_url"];
        $reply["usn"] = $usr["username"];
        $usr = $reply['re'];
        if ($usr>0) {
            $usr = find_user_or_guest_by_cache($usr);
            $reply["re_usn"] = $usr["username"];
        }
        return $reply;
    },$replies);
    clean_usr_cache();
    return $replies;

}

/**
 * curd
 *
 * @param int $page
 * @param int $the_post
 * @return void
 */
function all_replies(int $page,int $the_post) {
    $the_post = replies_full($the_post);
    $limit = getRepeatConfig()[peerPage];
    $limit = $page*$limit > count($the_post);
    if (empty($the_post)||$limit ) message(0,'[]');
    if ($page>0) {
        $the_post = array_chunk($the_post,getRepeatConfig()[peerPage])[$page-1];
    }
    message(0,json_encode($the_post));
}
function del_replay(int $pid,int $rid) {
    $replies = replies($pid,true,true);
    empty($replies) && message(1,lang("replay is empty"));
    foreach ($replies as $reply) if ($reply['id'] == $rid) {
        unset($reply);
        !update_replies($replies,$pid) && message(6,"delete_successfully");
        break;
    }
    message(1,lang("delete_failed"));
}
function put_reply(int $pid, int $rid, $re_user,string $msg, array $user, int $time) {
    $re_user = (!empty($re_user)) && intval($re_user) > 0
        ? intval($re_user) : 0 ;
    $re_user = $re_user == 0 ? [] : user_read($re_user);
    $replies = replies($pid,true,true);
    $reply = [
        "re"=> !empty($re_user) ? $re_user['uid'] : 0,
        "id"=>  max(array_map(fn($p)=>$p['id'],$replies)),
        "msg"=> $msg,
        "uid"=> $user['uid'],
        "update"=>$time,
    ];
    $replies[] = $reply;
    !update_replies($replies,$pid) && message(6,lang('create_thread_sucessfully'));
    message(1,lang("create_post_failed"));
}
function update_replies(array $data, $pid) {
    $count = 0;
    $lastIndex = 0;
    if (!empty($data)) {
        if (!sort_by_sub($data,'fl')) return xn_error(-255,"sort failed");
        $lastIndex = intval($data[array_key_last($data)]["fl"]);
        $count = count($data);
    }
    $data = json_encode($data);
    empty($data) && $data = "";
    return db_update("post",["pid"=>$pid],[
        "repeat_follow"=>$data,
        'r_f_c'=>$count,
        'r_f_a'=>$lastIndex,
    ]);
}


/**
 * 获取一个包含[pid\page_no\message]的post
 *
 * @return array|false
 */
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

    $message = msg();
    if (!empty($message)) $comment["repeat_msg"] = $message;

    return $comment;
}
function sort_by_sub(array $arr, $key): bool {
    return usort($arr,fn($it,$other)=> $it[$key]<=>$other[$key]);
}

function clean_html($html): string{
    $html = htmlspecialchars($html);
    return trim(xn_html_safe($html));
}
function get_all_floor_html($data,$pid,$allowdelete=false):string {
    global $uid;
    $html = '';
    foreach ($data as $item) {
         $html.=get_floor_html_dd($item,$pid,$item['uid'] == $uid||$allowdelete);
    }
    return $html;
}
function get_paged_floor_html($data,$page,$pid,$allowdelete=false):string {
    include_once _include(APP_PATH."plugin/sl_repeat_follow/utils/conf.php");
    $per_page=getRepeatConfig()[peerPage];
    $page=min($page,count($data));
    $page=max($page,1);
    $data = array_slice($data,($page-1)*$per_page,$per_page);
    return get_all_floor_html($data, $pid,);
}
function get_floor_html_dd(array $repeat_follow,$pid,bool $del):string {
    ob_start();
    include "plugin/sl_repeat_follow/hook/inside_post.phtml";
    return ob_get_clean();
}