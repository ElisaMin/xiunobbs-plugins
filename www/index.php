<?php
ob_start();
include "index.php";
$s = ob_get_clean();
preg_replace("#=([\"|'])(view|plugin|upload)#",'=$1/$2',$s);
echo $s;