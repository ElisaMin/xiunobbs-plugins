?><?php include_once _include(APP_PATH."plugin/sl_repeat_follow/utils/repeats.php");
if($action == 'rfloor') {
    $comment = get_validate_comment();
    $page = &$comment["page_no"];
    if (!empty($page)) {
        $pid = &$comment['pid'];
        $repeats = json_decode($comment['repeat_follow'], true);
        $html = get_paged_floor_html($repeats, $page, $pid, $uid, $comment,);
        message(0, $html);
    }
}
?><?php