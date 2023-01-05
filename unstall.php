<?php

/*
	Xiuno BBS 4.0 插件实例：TAG 插件卸载
	admin/plugin-unstall-xn_tag.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "ALTER TABLE {$tablepre}post DROP COLUMN `repeat_follow`, DROP COLUMN `r_f_c`, DROP COLUMN `r_f_a`";
$r = db_exec($sql);

?>