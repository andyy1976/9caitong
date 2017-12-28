<?php 


define("FILE_PATH",""); //文件目录，空为根目录
require_once './system/common.php';
es_session::start();
//require APP_ROOT_PATH."system/utils/es_image.php";
require APP_ROOT_PATH."system/utils/Verify.class.php";
$vname = isset($_REQUEST['vname']) ? !empty($_REQUEST['vname']) ? strim($_REQUEST['vname']) : 'verify' : 'verify';
$w = isset($_REQUEST['w']) ? intval($_REQUEST['w']) : 50;
$h = isset($_REQUEST['h']) ? intval($_REQUEST['h']) : 22;
$code = 1;
if($verify=="smsVerify"){
	$code = 10;
}
//es_image::buildImageVerify(4,$code,'gif',$w,$h,$verify);
$sconfig['imageW'] = $w;
$sconfig['imageH'] = $h;
$sconfig['fontSize'] = ($w==50 ? 12 : 18);
$sconfig['useCurve'] = false;//($w==50 ? false : true);
$sconfig['useNoise'] = false;//($w==50 ? false : true);
$sconfig['length'] = 4;
$verify = new Verify ($sconfig);
$verify->entry ($vname);
?>