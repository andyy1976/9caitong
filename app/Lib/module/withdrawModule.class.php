<?php

class withdrawModule extends SiteBaseModule
{
	public function pay(){

		$withdraw_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_carry where id = ".intval($_REQUEST['id']));		
		if($withdraw_info){
			$GLOBALS['tmpl']->assign("withdraw_info",$withdraw_info);
			if(strim($_REQUEST["from"]) == "debit")
				$GLOBALS['tmpl']->display("debit/debit_payment_pay.html");
			else
				$GLOBALS['tmpl']->display("page/withdraw_pay.html");
		}
	}
	
}
?>