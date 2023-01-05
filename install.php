<?php !defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;


$sql = "ALTER TABLE {$tablepre}post ADD COLUMN `repeat_follow` LONGTEXT NOT NULL, ADD COLUMN `r_f_c` SMALLINT(6) UNSIGNED DEFAULT 0 NOT NULL, ADD COLUMN `r_f_a` SMALLINT(6) UNSIGNED DEFAULT 0 NOT NULL";
$r = db_exec($sql);
//$r === FALSE AND message(-1, '创建表结构失败');

?>