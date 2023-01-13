<?php
ob_start();
!defined('DEBUG') AND define('DEBUG', 2);
define('APP_PATH', dirname(__FILE__,4).'/'); // __DIR__
define(__FILE__,APP_PATH);
!defined('ADMIN_PATH') AND define('ADMIN_PATH', APP_PATH.'admin/');
!defined('XIUNOPHP_PATH') AND define('XIUNOPHP_PATH', APP_PATH.'xiunophp/');
$conf = (@include APP_PATH.'conf/conf.php') OR exit('<script>window.location="install/"</script>');
!isset($conf['user_create_on']) AND $conf['user_create_on'] = 1;
!isset($conf['logo_mobile_url']) AND $conf['logo_mobile_url'] = 'view/img/logo.png';
!isset($conf['logo_pc_url']) AND $conf['logo_pc_url'] = 'view/img/logo.png';
!isset($conf['logo_water_url']) AND $conf['logo_water_url'] = 'view/img/water-small.png';
$conf['version'] = '4.0.4';
substr($conf['log_path'], 0, 2) == './' AND $conf['log_path'] = APP_PATH.$conf['log_path'];
substr($conf['tmp_path'], 0, 2) == './' AND $conf['tmp_path'] = APP_PATH.$conf['tmp_path'];
substr($conf['upload_path'], 0, 2) == './' AND $conf['upload_path'] = APP_PATH.$conf['upload_path'];
$_SERVER['conf'] = $conf;
include XIUNOPHP_PATH.(DEBUG>1 ? "xiunophp.php": "xiunophp.min.php") ;
include APP_PATH.'model/plugin.func.php';
include _include(APP_PATH.'model.inc.php');
include _include(APP_PATH.'index.inc.php');

echo preg_replace("#=([\"|'])(view|plugin|upload|lang)#",'=$1/$2',ob_get_clean());;
?>