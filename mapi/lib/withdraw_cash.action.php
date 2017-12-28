<?php
require_once APP_ROOT_PATH.'app/Lib/uc_func.php';
//提现资金信息,银行信息展示

class withdraw_cash{
    public function index(){
        $root = get_baseroot();
        $user =  $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $root['user_money_format'] = sprintf('%.2f',$user["money"]);//用户可提现金额
        $root['user_money_format_cunguan'] = sprintf('%.2f',$user['cunguan_money']);
        $cg_user_info = get_cg_user_info($GLOBALS['user_info']['id']);
        $root['withdrawalamount'] = $cg_user_info['withdrawalamount'];
        $in_money = $GLOBALS['db']->getOne("SELECT sum(money) from ".DB_PREFIX."user_carry where user_id=".$GLOBALS['user_info']['id']." and cunguan_pwd=1 and status in(0,3)");
        $root['withdrawalamount'] = $cg_user_info['withdrawalamount'] - $in_money;
        $root['withdrawalamount'] = strval($root['withdrawalamount']);
        //2.0银行卡
        $bank = $GLOBALS['db']->getRow("SELECT ub.id,ub.bankcard,ub.region_lv2,ub.region_lv3,ub.region_lv4,ub.bankzone,b.name,b.icon,b.single_quota,b.day_limit FROM ".DB_PREFIX."user_bank as ub join ".DB_PREFIX."bank as b on ub.bank_id=b.id where ub.user_id=".$GLOBALS['user_info']['id']." and ub.status=1 and b.is_rec=1 and ub.cunguan_tag=0 order by ub.redline desc limit 1");
       
        $root['bid'] = $bank['id'];
        $root['name'] = $bank['name'];
        $root['icon'] = get_abs_img_root(get_spec_image($bank['icon'],0,0,1));        
		$root['sub_card'] = substr($bank['bankcard'],-4,4);
        $root['bank_card'] = strval($bank['bankcard']);
        //list($root['bank_city'],$root['bank_branch'])=explode('-',$bank['bankzone']);
        $bank_branch = $bank['bankzone'];
        $root['bank_branch'] = empty($bank_branch)?'':$bank_branch;
        $region_lv2 = $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."region_conf where id = ".$bank['region_lv2']);
        $region_lv3 = $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."region_conf where id = ".$bank['region_lv3']);
        if($region_lv2 || $region_lv3){
            $root['bank_city'] = $region_lv2.' '.$region_lv3;
        }else{
            $root['bank_city'] = '';
        }
        //存管银行卡
        $bank_cunguan = $GLOBALS['db']->getRow("select ub.id,ub.user_id,ub.bank_id,ub.bankcard,ub.status,b.name,b.icon from " . DB_PREFIX . "user_bank as ub join " .DB_PREFIX."bank as b on ub.bank_id=b.bankid where ub.user_id=".$GLOBALS['user_info']['id']." and ub.status=1 and ub.cunguan_tag=1");
        if($GLOBALS['user_info']['user_type'] =="1"){
        	$bank_cunguan= $GLOBALS['db']->getRow("SELECT id,username as name,corpacc as bankcard FROM ".DB_PREFIX."company_reginfo where user_id='".$user[id]."'");
        	$root['withdrawalamount']=sprintf('%.2f',$user['cunguan_money']);
        	$bank_cunguan['name']='';
        }
        $root['bid_cunguan'] = $bank_cunguan['id'];
        $root['name_cunguan'] = $bank_cunguan['name'];
        $root['icon_cunguan'] = get_abs_img_root(get_spec_image($bank_cunguan['icon'],0,0,1));
        $root['sub_card_cunguan'] = substr($bank_cunguan['bankcard'],-4,4);
        $root['bank_card_cunguan'] = strval($bank_cunguan['bankcard']);
        //list($root['bank_city'],$root['bank_branch'])=explode('-',$bank['bankzone']);
        $bank_branch_cungaun = $bank_cunguan['bankzone'];
        $root['bank_branch_cunguan'] = empty($bank_branch_cungaun)?'':$bank_branch_cungaun;
        $region_lv2_cunguan = $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."region_conf where id = ".$bank_cunguan['region_lv2']);
        $region_lv3_cunguan = $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."region_conf where id = ".$bank_cunguan['region_lv3']);
        if($region_lv2_cunguan || $region_lv3_cunguan){
            $root['bank_city_cunguan'] = $region_lv2_cunguan.' '.$region_lv3_cunguan;
        }else{
            $root['bank_city_cunguan'] = '';
        }

        $withdraw_explain = strip_tags($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config_cg WHERE code = 'withdraw_explain'"));
        $root['withdraw_explain'] = str_replace("。","。\n\n" ,$withdraw_explain);
		$root['response_code'] = 1;
		$root['program_title'] = "提现信息";
		output($root);	
    }
}