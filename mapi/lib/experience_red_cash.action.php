<?php
require APP_ROOT_PATH.'app/Lib/deal.php';
require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
class experience_red_cash
{
	public function index(){
		$root = array();
		$user =  $GLOBALS['user_info'];
		$root['session_id'] = es_session::id();
		$user_id  = intval($user['id']);
		$Publics = new Publics();
		$deal=new Deal();
		$seqno=$Publics->seqno();
		$rid= intval(base64_decode($GLOBALS['request']['rid'])); //标的id
        if($GLOBALS['user_info']['cunguan_tag']!=1){
            $root['response_code'] = 0;
            $root['show_err'] = "请先开通存管！";
            output($root);
        }

//     	$root['response_code'] = 0;
//         $root['show_err'] = "12月4日-12月10日红包系统升级维护,暂停兑换!";
//       	output($root);

		if ($user_id >0 && $rid){

			$GLOBALS['db']->startTrans();  //开始事务
			$red_money = $GLOBALS['db']->getRow("SELECT user_id,money,id,end_time,status,experience_load_id FROM ".DB_PREFIX."red_packet where id=$rid and status=0 and user_id=$user_id and packet_type=3 FOR UPDATE");
			$load_has_repay=$GLOBALS['db']->getOne("select has_repay from ".DB_PREFIX."experience_deal_load where id=".$red_money['experience_load_id']);
			
			if($load_has_repay && $load_has_repay==1){ 
				$root['response_code'] = 0;
	            $root['show_err'] = "该现金红包已被领取过！";
	          	output($root);					
			}

			if($red_money['status']==1 || $red_money['end_time']<time()){
	            $root['response_code'] = 0;
	            $root['show_err'] = "红包不可用或已过期！";
	          	output($root);		
	        }

	        			

            if ($red_money['experience_load_id']) {
                $experid_failed =array(
                    'has_repay' =>1,
                );
                $experid_recharge = $GLOBALS['db']->autoExecute(DB_PREFIX . "experience_deal_load", $experid_failed, "UPDATE", "id=".$red_money['experience_load_id'] . "");
                if(!$experid_recharge){
                    $GLOBALS['db']->rollback();
                    $root['response_code'] =0;
                    $root['show_err'] = '领取现金失败';
                    output($root);
                }
            }
		

			require_once APP_ROOT_PATH . 'system/libs/user.php';
			$trans_failed =array(
				'status' =>1,
				'create_time'=>time(),				
			);
			$recharge = $GLOBALS['db']->autoExecute(DB_PREFIX . "red_packet", $trans_failed, "UPDATE", "id=" . $red_money['id'] . "");	                
	        if(!$recharge){ 
	        	$GLOBALS['db']->rollback();
	        	$root['response_code'] =0;
				$root['show_err'] = '领取现金失败';
				output($root);
	        }

	        $sql = "update ".DB_PREFIX."user set cunguan_money_encrypt = AES_ENCRYPT(ROUND(ifnull(AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."'),0),2) + ".round($red_money['money'],2).",'".AES_DECRYPT_KEY."') where id =".$user_id;
	        $chmod=$GLOBALS['db']->query($sql);
	        if(!$chmod){ 
	        	$GLOBALS['db']->rollback();
	        	$root['response_code'] =0;
				$root['show_err'] = '领取现金失败';
				output($root);
	        }

			$useid = $GLOBALS['db']->getRow("SELECT accno FROM ".DB_PREFIX."user where id=$user_id");				
			//$data['accountList'] = array(array("oderNo"=>"1","oldbusinessSeqNo"=>"","oldOderNo"=>"","debitAccountNo"=>'',"cebitAccountNo"=>$useid['accno'],"currency"=>"CNY","amount"=>$red_money['money'],"otherAmounttype"=>"","otherAmount"=>""));
			$data['user_id'] = $red_money['user_id'];
			//$data['accNo'] = $useid['accno'];
			//$data['deal_id'] ='';
			$data['money'] = $red_money['money'];
			$res = $deal->deal($seqno,'T10',$data['money'],'',$data['user_id']);//还款
					
			if($res['respHeader']['respCode']=="P2P0000"){ 	
			  			       
				$result= $GLOBALS['db']->getOne("SELECT AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as cunguan_money FROM ".DB_PREFIX."user WHERE id=".$user_id);
	            $money_log_info = array();
	            $money_log_info['memo'] = "领取现金红包";
	            $money_log_info['brief'] = "领取现金红包";
	            $money_log_info['money'] = round($red_money['money'],2);
	            $money_log_info['account_money'] = $result;
	            $money_log_info['user_id'] = $user_id;
	            $money_log_info['create_time'] = TIME_UTC;
	            $money_log_info['create_time_ymd'] = to_date(TIME_UTC,"Y-m-d");
	            $money_log_info['create_time_ym'] = to_date(TIME_UTC,"Ym");
	            $money_log_info['create_time_y'] = to_date(TIME_UTC,"Y");
	            $money_log_info['type'] =61;
	            $money_log_info['cunguan_tag'] =1;
	            $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);
	            if ($GLOBALS['user_info']['id'] == $user_id){
	                $GLOBALS['user_info']['cunguan_money'] = $result;
	            }	     
	            $GLOBALS['db']->commit();      
	            $root['code'] = $res['respHeader']['respCode'];
	            $root['info']=	$res['respHeader']['respMsg'];
				$root['show_err']='领取现金成功';
				$root['response_code'] =1;
				output($root);		
			}else{ 
				//$GLOBALS['db']->rollback();
				$root['response_code'] =0;
				$root['show_err'] =$res['respHeader']['respCode'];
				$root['code'] = $res['respHeader']['respCode'];
				$root['info']=	$res['respHeader']['respMsg'];
				output($root);
			}	
		}
	}
}
?>
