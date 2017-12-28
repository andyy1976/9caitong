<?php
class verify_code{
	public function index()
	{
		$vname = isset($_REQUEST['vname']) ? !empty(base64_decode($_REQUEST['vname'])) ? strim(base64_decode($_REQUEST['vname'])) : 'verify' : 'verify';
		$w = isset($_REQUEST['w']) ? intval(base64_decode($_REQUEST['w'])) : 50;
		$h = isset($_REQUEST['h']) ? intval(base64_decode($_REQUEST['h'])) : 22;
		$code = 1;
		if($verify=="smsVerify"){
			$code = 10;
		}
		$sconfig['imageW'] = $w;
		$sconfig['imageH'] = $h;
		$sconfig['fontSize'] = ($w==50 ? 12 : 18);
		$sconfig['useCurve'] = false;//($w==50 ? false : true);
		$sconfig['useNoise'] = false;//($w==50 ? false : true);
		$sconfig['length'] = 4;
		$verify = new Verify ($sconfig);
		$verify->entry ($vname);
	}
	
}
?>