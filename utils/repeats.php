<?php
/**
 * 退出
 * @return void
 */
function error_no_comment() {
    message(-1, lang('post_not_exists'));
    exit;
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
        if (!usort($data,fn($it,$other)=> $it['fl']<=>$other['fl'])) return xn_error(-255,"sort failed");
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
function clean_html($html): string{
    $html = htmlspecialchars($html);
    return trim(xn_html_safe($html));
}
function get_all_floor_html($data,$pid,$uid,$comment,$filter):string {
    $filter = empty($filter) ? function () { return true; } : $filter;
    $html = '';
    foreach ($data as $index => $item) {
        if ($filter($item,$index)) {
            $html.=get_floor_html_dd($item,$pid,$item['uid'] == $uid||$comment['floormanage']);
        }
    }
    return $html;
}
function get_paged_floor_html($data,$page,$pid,$uid,$comment):string {
    include_once _include(APP_PATH."plugin/sl_repeat_follow/utils/conf.php");
    $per_page=getRepeatConfig()[peerPage];
    $page=min($page,count($data));
    $page=max($page,1);
    return get_all_floor_html(
        array_slice($data,($page-1)*$per_page,$per_page),
        $pid,$uid,$comment,null
    );
}
function get_floor_html_dd(array $repeat_follow,$pid,bool $del):string {
    ob_start();
    include "plugin/sl_repeat_follow/hook/inside_post.phtml";
    return ob_get_clean();
}