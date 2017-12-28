<?php

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_bonusModule extends SiteBaseModule
{
	public function index()
	{
        //判断是否是企业用户
        if($GLOBALS['user_info']['user_type']){
            $jump = url("index","uc_center#index");
            if(WAP==1) app_redirect(url("index","uc_center#index"));
            showErr("企业用户暂不可使用",0,$jump);
        }
        $user_id =$GLOBALS['user_info']['id'];
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		if(WAP == 1){
			$limit = "0,1000";
		}else{
            $page_size=6;
			$limit = (($page-1)*$page_size).",".$page_size;
		}
        $use_status=intval($_REQUEST['use_status']);
        $red_type=intval($_REQUEST['red_type']);
        $condition="";
        $time=time();
        if($use_status==1){
            $condition.=" and rp.status=0 and rp.end_time>=".$time;
        }elseif($use_status==2){
            $condition.=" and rp.status=1";
        }elseif($use_status==3){
            $condition.=" and rp.status=0 and rp.end_time<".$time;
        }
        if($red_type){
            $condition.=" and rpn.red_type=".$red_type;
        }
        $red_money_list = get_uc_red_list($limit,$GLOBALS['user_info']['id'],$condition);
//        print_r($red_money_list);die;
        foreach($red_money_list['list'] as $k=>$v){
            $red_money_list['list'][$k]['max_use_money']=$v['ratio'];
            $red_money_list['list'][$k]['begin_date']=date("Y-m-d",$v['begin_time']);
            $red_money_list['list'][$k]['end_date']=date("Y-m-d",$v['end_time']);
        }
		$page = new Page($red_money_list['count'],$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);


//        $red_totals = $GLOBALS['db']->getAll("SELECT money FROM ".DB_PREFIX."user_red_money_log where user_id=$user_id and type in(56,0)");
//        static $red_total;
//        foreach($red_totals as $v){
//            if(substr($v['money'],0,1) !=='-'){
//                $red_total+=$v['money'];
//            }
//        }
//        $red_money = $GLOBALS['db']->getOne("select red_money from ".DB_PREFIX."user  where id = ".$GLOBALS['user_info']['id']);
        /*********优惠券 红包使用说明**********/
//		$explain['voucher_explain'] = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'voucher_explain'"));
//		$explain['red_explain'] = trimall($GLOBALS['db']->getOne("SELECT val FROM ".DB_PREFIX."m_config WHERE code = 'red_explain'"));
//		$GLOBALS['tmpl']->assign("explain",$explain);
//        $GLOBALS['tmpl']->assign("total",$red_total);
//		$GLOBALS['tmpl']->assign("red_money",$red_money);
        $GLOBALS['tmpl']->assign('use_status',$use_status);
        $GLOBALS['tmpl']->assign('red_type',$red_type);
        $GLOBALS['tmpl']->assign('time',$time);
        $GLOBALS['tmpl']->assign("red_money_list",$red_money_list['list']);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_VOUCHER']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_bonus_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function exchange()
	{
        $page = intval($_REQUEST['p']);
		if($page==0)
        $page = 1;
        $limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
        $result = get_exchange_voucher_list($limit);
        $GLOBALS['tmpl']->assign("list",$result['list']);
        $page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
        $p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_VOUCHER']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_voucher_exchange.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function do_exchange()
	{
		$id = intval($_REQUEST['id']);
		$ecv_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where id = ".$id);
		if(!$ecv_type)
		{
			showErr($GLOBALS['lang']['INVALID_VOUCHER'],1);
		}
		else
		{
			$exchange_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ecv where ecv_type_id = ".$id." and user_id = ".intval($GLOBALS['user_info']['id']));
			if($ecv_type['exchange_limit']>0&&$exchange_count>=$ecv_type['exchange_limit'])
			{
				$msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_LIMIT'],$ecv_type['exchange_limit']);
				showErr($msg,1);
			}
			elseif($ecv_type['exchange_score']>intval($GLOBALS['db']->getOne("select score from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']))))
			{
				showErr($GLOBALS['lang']['INSUFFCIENT_SCORE'],1);
			}
			else
			{
				require_once APP_ROOT_PATH."system/libs/voucher.php";
				$rs = send_voucher($ecv_type['id'],$GLOBALS['user_info']['id'],1);
				if($rs)
				{
					require_once APP_ROOT_PATH."system/libs/user.php";
					$msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_USE_SCORE'],$ecv_type['name'],$ecv_type['exchange_score']);
					modify_account(array('score'=>"-".$ecv_type['exchange_score']),$GLOBALS['user_info']['id'],$msg,'22');
					showSuccess($GLOBALS['lang']['EXCHANGE_SUCCESS'],1,url('index','uc_voucher'));
				}
				else
				{
					showErr($GLOBALS['lang']['EXCHANGE_FAILED'],1);
				}
			}
		}
	}
	
	public function do_snexchange()
	{
		$sn = strim($_REQUEST['sn']);
		$ecv_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where exchange_sn = '".$sn."'");
		if(!$ecv_type)
		{
			showErr($GLOBALS['lang']['INVALID_VOUCHER'],1);
		}
		else
		{
			$exchange_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ecv where ecv_type_id = ".$ecv_type['id']." and user_id = ".intval($GLOBALS['user_info']['id']));
			if($ecv_type['exchange_limit']>0&&$exchange_count>=$ecv_type['exchange_limit'])
			{
				$msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_LIMIT'],$ecv_type['exchange_limit']);
				showErr($msg,1);
			}
			else
			{
				require_once APP_ROOT_PATH."system/libs/voucher.php";
				$rs = send_voucher($ecv_type['id'],$GLOBALS['user_info']['id'],1);
				if($rs)
				{
					showSuccess($GLOBALS['lang']['EXCHANGE_SUCCESS'],1,url('index','uc_voucher'));
				}
				else
				{
					showErr($GLOBALS['lang']['EXCHANGE_FAILED'],1);
				}
			}
		}
	}

	public function red_carry(){
		$data['status'] = 1;
		$data['info'] ="恭喜您提取成功";
		ajax_return($data);			
	}
    /**
     * 领取现金红包
     */
    public function Receive_red()
    {
        $red_id = intval($_REQUEST['id']);
        $user_id =  $GLOBALS['user_info']['id'];
        if (!$GLOBALS['user_info']['id']) {
            $data['status'] = 0;
            $data['info'] = "请先登录！";
            ajax_return($data);
        }
        if($GLOBALS['user_info']['cunguan_tag']!=1){
            $data['status'] = 0;
            $data['info'] = "请先开通存管！";
            ajax_return($data);
        }
//         $data['status'] = 0;
//         $data['info'] = "12月4日-12月10日红包系统升级维护,暂停兑换！";
//         ajax_return($data);
        
        $user_accno = $GLOBALS['db']->getOne("SELECT accno FROM " . DB_PREFIX . "user where id=" . $GLOBALS['user_info']['id']);
        $GLOBALS['db']->startTrans();   //开始事务
        $red = $GLOBALS['db']->getRow("select user_id,money,experience_load_id,status,end_time from " . DB_PREFIX . "red_packet where id=" . $red_id." and user_id=$user_id and packet_type=3 and status=0 FOR UPDATE");
        $load_has_repay=$GLOBALS['db']->getOne("select has_repay from ".DB_PREFIX."experience_deal_load where id=".$red['experience_load_id']);
        if($load_has_repay && $load_has_repay==1){
            $data['status'] = 0;
            $data['info'] = "该现金红包已被领取过！";
            ajax_return($data);
        }
        if ($red['status'] == 1 || $red['end_time'] < time()) {
            $data['status'] = 0;
            $data['info'] = "红包不可用或已过期！";
            ajax_return($data);
        }
        if ($red_id && $red['money'] > 0) {
            require_once APP_ROOT_PATH . "system/libs/user.php";
            // 修改红包状态
            $red_info['status'] = 1;
            $red_info['create_time'] = time();
            $red_status = $GLOBALS['db']->autoExecute(DB_PREFIX . "red_packet", $red_info, "UPDATE", "id=" . $red_id);
            if (!$red_status) {
                $GLOBALS['db']->rollback(); //回滚
                $data['status'] = 0;
                $data['info'] = "领取失败，请重试！";
                ajax_return($data);
            }
            // 修改体验金投资信息的状态
            if ($red['experience_load_id']) {
                $experience['has_repay'] = 1;
                $experience_status = $GLOBALS['db']->autoExecute(DB_PREFIX . "experience_deal_load", $experience, "UPDATE", "id=" . $red['experience_load_id']);
                if (!$experience_status) {
                    $GLOBALS['db']->rollback(); //回滚
                    $data['status'] = 0;
                    $data['info'] = "领取失败，请重试！";
                    ajax_return($data);
                }
            }
            // 营销T10
            $datas['accountList'] = array(array("oderNo" => "1", "debitAccountNo" => '', "cebitAccountNo" => $user_accno, "currency" => "CNY", "amount" => $red['money'], "otherAmounttype" => "", "otherAmount" => "","summaryCode"=>"T10"));
            $datas['user_id'] = $red['user_id'];
            $datas['accNo'] = $user_accno;
            $datas['deal_id'] = '';
            $datas['money'] = $red['money'];
            require_once APP_ROOT_PATH . "system/utils/Depository/Require.php";
            $publics = new Publics();
            $xuni_seqno = $publics->seqno();
            $deal = new Deal;
            $status = $deal->do_repay($xuni_seqno, 'T10', $datas);
            if ($status['respHeader']['respCode'] != 'P2P0000') {
                $GLOBALS['db']->rollback();  //回滚
                $data['status'] = 0;
                $data['info'] = $status['respHeader']['respMsg'];
                ajax_return($data);
            } else {
                $GLOBALS['db']->commit();   // 提交
				// 资金日志
				$red_data['cunguan_money']=$red['money'];
                $msg="领取现金红包";
                $brief="领取现金红包";
                $cunguan_tag=1;
				modify_account($red_data,$GLOBALS['user_info']['id'],$msg,61,$brief,$cunguan_tag);

                $data['status'] = 1;
                $data['info'] = "恭喜您提取成功";
                ajax_return($data);
            }
        }
    }

}
?>