
?><?php

$rewrite_file_path = APP_PATH."/rewrite-rule.conf";
function gen_nginx_config_content() {
    global $conf;
    $content = file_get_contents($conf['tmp_path']."index.inc.php");
    if (empty($content)) return -1;
    $content = trim($content,"\r\t");
    $content = explode("switch (\$route) {",$content);
    if (empty($content)) return -2;
    $content = explode("}", $content[1]);
    if (empty($content)) return -2;
    $content = $content[0];
    if (empty($content)) return -2;
    preg_match_all("#case\\s*'(\\w+)'\\s*:\\s*include#",$content,$content);
    if (empty($content) or empty($content[1])) return -3;
    $content = (array) $content[1];

    $content = array_map(function($action){
        $content = "location /$action/ { \n";
        $content.= "   try_files @xiunobbs_rewrite =502; \n";
        $content.= "}\n\n";
        return $content;
    },$content);
    $content= implode("",$content);
    $i = 13;
    $content.="location @xiunobbs_rewrite {\n";
    while ($i>1) {
        $j = 1;
        $content.="   rewrite ";
        while ($j<$i) {
            $content.="/([^/]*)";
            $j++;
        }
        $j = 1;
        $content.=" index.php?";
        while ($j<$i) {
            $content.="\$$j-";
            $j++;
        }
        $content = substr($content,0,strlen($content)-1);
        $content.=".htm last;\n";
        $i--;
    }
    $content.="}\n\n";
    return $content;
}

//fixme check config rewrite to 4
if (!file_exists($rewrite_file_path)) {
    $result = file_put_contents($rewrite_file_path,gen_nginx_config_content());
    empty($result) && xn_log("create failed",$rewrite_file_path);
}
unset($rewrite_file_path);
?><?php
