<?php

//银行卡列表接口
class bank_list{

    public function index(){
        $root = get_baseroot();
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        if($user['id']>0){
//         	if($user['user_type']=='1'){
//         		$root['response_code'] = 0;
//         		$root['show_err'] = '企业用户暂不可使用';
//         		output($root);
//         	}
            $root['user_login_status'] = 1;
            $root['bank_quota_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_money&act=account_bank';
            $root['bank_help_url'] = WAP_SITE_DOMAIN.'/member.php?ctl=uc_set&act=help&id=47';
            if($user['user_type']=='1'){
                $bank_list = $GLOBALS['db']->getAll("select * FROM ".DB_PREFIX."company_reginfo where user_id=".$user['id']);
                $bank_list['0']['bank_name'] = $bank_list['0']['corpAccBankNm'];
                $bank_list['0']['bank_img'] = '';
                $bank_list['0']['bank_card_haha'] = $bank_list['0']['corpacc'];
            	$root['response_code'] = 1;
            	$root['bank_list']=$bank_list;
            }else{
            	$bank_list = $GLOBALS['db']->getAll("SELECT  u.bankcard as bank_card, u.bankzone,u.region_lv2,u.region_lv3, b.name as bank_name,b.icon as bank_img,b.single_quota,b.day_limit FROM ".DB_PREFIX."user_bank u left join ".DB_PREFIX."bank b on b.bankid = u.bank_id where u.user_id=".$user['id']." and u.status=1 and b.is_rec=1 and u.cunguan_tag=1 order by u.redline desc limit 1");
            	
            	if($bank_list){
            		foreach($bank_list as $k=>$v){
            			$bank_branch = $v['bankzone'];
            			$bank_list[$k]['bank_branch'] = $bank_branch?$bank_branch:'';
            			//list($bank_list[$k]['bank_city'],$bank_list[$k]['bank_branch'])=explode('-',$v['bankzone']);
            			$region_lv2 = $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."region_conf where id = ".$v['region_lv2']);
            			$region_lv3 = $GLOBALS['db']->getOne("select `name` from ".DB_PREFIX."region_conf where id = ".$v['region_lv3']);
            			if($region_lv2 || $region_lv3){
            				$bank_list[$k]['bank_city'] = $region_lv2.' '.$region_lv3;
            			}else{
            				$bank_list[$k]['bank_city'] = '';
            			}
            			$bank_list[$k]['bank_card_haha'] = '尾号'.substr($v['bank_card'],-4);
            			//$bank_list[$k]['bank_img'] = str_replace("/mapi","",WAP_SITE_DOMAIN.APP_ROOT.$v['bank_img']);
            			//$bank_list[$k]['bank_img'] = str_replace("/mapi","",$v['bank_img']);
            			$bank_list[$k]['bank_img'] = $v['bank_img'];
            			$bank_list[$k]['quota'] = '单笔'.$v['single_quota'].',单日'.$v['day_limit'];
            		}
            		$root['bank_list'] = $bank_list;
            		$root['response_code'] = 1;
            		$root['show_err'] = '';
            	}else{
            		$root['response_code'] = 0;
            		
            		$root['show_err'] ="请先绑卡";
            	}
            }
           
        }else{
            $root['response_code'] = 0;
            $root['show_err'] ="未登录";
            $root['user_login_status'] = 0;
        }
        output($root);
    }

}












?>