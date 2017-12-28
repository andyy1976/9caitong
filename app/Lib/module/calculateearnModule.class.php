<?php
/*
added by zhangteng

*/
require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
require_once APP_ROOT_PATH."system/user_level/Level.php";
require_once APP_ROOT_PATH."app/Lib/deal_func.php";

class calculateearnModule extends SiteBaseModule
{
	public $transTimes = 0;
	public $writeTimes = 0;
	public $abort = 0;
	public $abortc = 0;
	public $rubbishUser = array(0,3,26,39,163,58,59,98,142,152,154,156,168,219,252,273,1106394,106864,208670,388340,609127);
 
 
 /**
	 * 会员资金积分变化操作函数
	 * @param array $data 包括 score,money,point,site_money
	 * @param integer $user_id
	 * @param string $log_msg 日志内容
	 * @param string $brief 资金明细简介（供wap/app使用）---新添加（gaojin）
	 * @param integer $type  0结存，1充值，2投标成功，3招标成功，4偿还本息，5回收本息，6提前还款，7提前回收，8申请提现，9提现手续费，10借款管理费，11逾期罚息，12逾期管理费，13人工充值，14借款服务费，15出售债权，16购买债权，17债权转让管理费，18开户奖励，19流标还返，20投标管理费，21投标逾期收入，22兑换，23邀请返利，24投标返利，26逾期罚金（垫付后），27其他费用 ，28投资奖励，29红包奖励
	 * 					30:配资本金(冻结); 31:配资预交款(冻结);32:配资审核费(冻结);33:配资服务费(平台收入);34:配资利息(出资者收入);35:配资平仓收益;36:配资投资;37:配资提取赢余;38:配资交易佣金;47:体验金收益
	 */
	public function modify_account($data,$user_id,$log_msg='',$type=0,$brief='')
	{
		if(isset($data['score']) && intval($data['score'])!=0)
		{
			$scoreUpdateResult = $GLOBALS['db']->query("update ".DB_PREFIX."user set score = score + ".intval($data['score'])." where id =".$user_id);
			if($scoreUpdateResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		if(isset($data['point']) && intval($data['point'])!=0)
		{
			$pointUpdateResult = $GLOBALS['db']->query("update ".DB_PREFIX."user set point = point + ".intval($data['point'])." where id =".$user_id);
			if($pointUpdateResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		if(isset($data['mortgage_money']) && round($data['mortgage_money'],2)!=0)
		{
			$sql = "update ".DB_PREFIX."user set mortgage_money = mortgage_money + ".round($data['mortgage_money'],2)." where id =".$user_id;
			$mortgage_moneyResult = $GLOBALS['db']->query($sql);
			if($mortgage_moneyResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		else
		{
			unset($data['mortgage_money']);
		}
        /*
		if(isset($data['red_money']) && intval($data['red_money'])!=0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set red_money = red_money + ".round($data['red_money'],2)." where id =".$user_id);
		}*/
		//资金增加或减少  money用正负数
		if(isset($data['money']) && round($data['money'],2)!=0)
		{
			$sql = "update ".DB_PREFIX."user set money_encrypt = AES_ENCRYPT(ROUND(ifnull(AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."'),0),2) + ".round($data['money'],2).",'".AES_DECRYPT_KEY."') where id =".$user_id;
			//echo $sql;exit;
			$moneyResult = $GLOBALS['db']->query($sql);
			if($moneyResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		
		if(isset($data['quota']) && round($data['quota'],2)!=0)
		{
			$quotaResult = $GLOBALS['db']->query("update ".DB_PREFIX."user set quota = quota + ".round($data['quota'],2)." where id =".$user_id);
			if($quotaResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		//冻结资金增加或减少
		if(isset($data['lock_money']) && round($data['lock_money'],2)!=0)
		{
			$lock_moneyResult = $GLOBALS['db']->query("update ".DB_PREFIX."user set lock_money = lock_money + ".round($data['lock_money'],2)." where id =".$user_id);
			if($lock_moneyResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		
		//不可提现的金额??????????????????
		if(isset($data['nmc_amount']) && round($data['nmc_amount'],2)!=0){
			$nmc_amountResult = $GLOBALS['db']->query("update ".DB_PREFIX."user set nmc_amount = nmc_amount + ".round($data['nmc_amount'],2)." where id =".$user_id);
			if($nmc_amountResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		
		$user_info = get_user_info("*","id = ".$user_id);
		
		if(intval($data['score'])!=0||round($data['money'],2)!=0||intval($data['point'])!=0||round($data['quota'],2)!=0 || round($data['lock_money'],2) != 0)
		{		
			$log_info['log_info'] = $log_msg;
			$log_info['log_time'] = $data['create_time'];
			$adm_session = es_session::get(md5(app_conf("AUTH_KEY")));
			
			$adm_id = intval($adm_session['adm_id']);
			if($adm_id!=0)
			{
				$log_info['log_admin_id'] = $adm_id;
			}
			else
			{
				$log_info['log_user_id'] = intval($user_info['id']);
			}
			$log_info['money'] = round($data['money'],2);
			$log_info['score'] = intval($data['score']);
			$log_info['point'] = intval($data['point']);
			$log_info['quota'] = round($data['quota'],2);
			$log_info['lock_money'] = round($data['lock_money'],2);
			$log_info['user_id'] = $user_id;
			$user_logResult = $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$log_info);
			if($user_logResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
			
		}
		//资金记录
		if (isset($data['money'])&&$data['money']){
			$money_log_info = array();			
			$money_log_info['memo'] = $log_msg;
			$money_log_info['brief'] = $brief;
			$money_log_info['money'] = round($data['money'],2);
			$money_log_info['account_money'] = $user_info['money'];
			$money_log_info['user_id'] = $user_id;
			$money_log_info['create_time'] = $data['create_time'];
			$money_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
			$money_log_info['create_time_ym'] = date("Ym", $data['create_time']);
			$money_log_info['create_time_y'] = date("Y", $data['create_time']);
			$money_log_info['type'] = $type;
			$money_log_info['from_user_id'] = $user_id;
			$money_log_info['from_deal_id'] = $data['deal_id'];
			$money_log_info['from_load_repay_id'] = $data['repay_id'];//此处有问题注意 不存在load_repay_id字段
			$money_log_info['from_deal_load_id'] = $data['load_id'];
			$user_money_logResult = $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);
			if($user_money_logResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
			if ($GLOBALS['user_info']['id'] == $user_id){
				$GLOBALS['user_info']['money'] = $user_info['money'];
			}
		}
		//存管可用资金操作
		if(isset($data['cunguan_money']) && round($data['cunguan_money'],2)!=0)
		{
			$sql = "update ".DB_PREFIX."user set cunguan_money_encrypt = AES_ENCRYPT(ROUND(ifnull(AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."'),0),2) + ".round($data['cunguan_money'],2).",'".AES_DECRYPT_KEY."') where id =".$user_id;
			//echo $sql;exit;
			$user_money_logResult=$GLOBALS['db']->query($sql);
			if($user_money_logResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		if (isset($data['red_money'])&&intval($data['red_money'])){
			$money_log_info = array();
			$money_log_info['memo'] = $log_msg . ',使用红包';
			$money_log_info['brief'] = '使用红包';
			$money_log_info['money'] = round($data['red_money'],2);
			$money_log_info['account_money'] =  $user_info['money'];
			$money_log_info['user_id'] = $user_id;
			$money_log_info['create_time'] = $data['create_time'];
			$money_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
			$money_log_info['create_time_ym'] = date("Ym", $data['create_time']);
			$money_log_info['create_time_y'] = date("Y", $data['create_time']);
			$money_log_info['type'] = 56;
			$money_log_info['from_user_id'] = $user_id;
			$money_log_info['from_deal_id'] = $data['deal_id'];
			$money_log_info['from_deal_load_id'] = $data['load_id'];
			$user_money_logRE = $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);
			if($user_money_logRE){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
			if ($GLOBALS['user_info']['id'] == $user_id){
				$GLOBALS['user_info']['red_money'] = $user_info['red_money'];
			}
		}
		if (isset($data['ecv_money'])&&intval($data['ecv_money'])){
			$money_log_info = array();
			$money_log_info['memo'] = $log_msg.',使用代金券';
			$money_log_info['brief'] = '使用代金券';
			$money_log_info['money'] = round($data['ecv_money'],2);
            $money_log_info['account_money'] =  $user_info['money'];
			$money_log_info['user_id'] = $user_id;
			$money_log_info['create_time'] = $data['create_time'];
			$money_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
			$money_log_info['create_time_ym'] = date("Ym", $data['create_time']);
			$money_log_info['create_time_y'] = date("Y", $data['create_time']);
			$money_log_info['type'] = 57;
			$money_log_info['from_user_id'] = $user_id;
			$money_log_info['from_deal_id'] = $data['deal_id'];
			$money_log_info['from_deal_load_id'] = $data['load_id'];
			$user_money_logRRRRR = $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);
			if($user_money_logRRRRR){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}

		if (isset($data['nmc_amount'])){
			
			$money_log_info = array();			
			$money_log_info['memo'] = $log_msg;
			$money_log_info['money'] = round($data['nmc_amount'],2);
			$money_log_info['account_money'] = $user_info['nmc_amount'];
			$money_log_info['user_id'] = $user_id;
			$money_log_info['create_time'] = $data['create_time'];
			$money_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
			$money_log_info['create_time_ym'] = date("Ym", $data['create_time']);
			$money_log_info['create_time_y'] = date("Y", $data['create_time']);
			$money_log_info['type'] = $type;
			$user_nmc_money_logResultt = $GLOBALS['db']->autoExecute(DB_PREFIX."user_nmc_money_log",$money_log_info);
			if($user_nmc_money_logResultt){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
			if ($GLOBALS['user_info']['id'] == $user_id){
				$GLOBALS['user_info']['nmc_amount'] = $user_info['nmc_amount'];
			}
		}
		if (isset($data['cunguan_money'])&&$data['cunguan_money']){
			$money_log_info = array();
			$money_log_info['memo'] = $log_msg;
			$money_log_info['brief'] = $brief;
			$money_log_info['money'] = round($data['cunguan_money'],2);
			$money_log_info['account_money'] = $user_info['cunguan_money'];
			$money_log_info['user_id'] = $user_id;
			$money_log_info['create_time'] = TIME_UTC;
			$money_log_info['create_time_ymd'] = to_date(TIME_UTC,"Y-m-d");
			$money_log_info['create_time_ym'] = to_date(TIME_UTC,"Ym");
			$money_log_info['create_time_y'] = to_date(TIME_UTC,"Y");
			$money_log_info['type'] = $type;
			$money_log_info['from_user_id'] = $user_id;
			$money_log_info['from_deal_id'] = $data['deal_id'];
			$money_log_info['from_load_repay_id'] = $data['load_repay_id'];
			$money_log_info['from_deal_load_id'] = $data['load_id'];
			$money_log_info['cunguan_tag'] = $cunguan_tag;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);
			if($user_nmc_money_logResultt){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
			if ($GLOBALS['user_info']['id'] == $user_id){
				$GLOBALS['user_info']['cunguan_money'] = $user_info['cunguan_money'];
			}
		}
		if(isset($data['site_money'])){
			$money_log_info = array();
			$money_log_info['memo'] = $log_msg;
			$money_log_info['money'] = round($data['site_money'],2);
			$money_log_info['user_id'] = $user_id;
			$money_log_info['create_time'] = $data['create_time'];
			$money_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
			$money_log_info['create_time_ym'] = date("Ym", $data['create_time']);
			$money_log_info['create_time_y'] = date("Y", $data['create_time']);
			$money_log_info['type'] = $type;
			$site_money_logResulttt = $GLOBALS['db']->autoExecute(DB_PREFIX."site_money_log",$money_log_info);
			if($site_money_logResulttt){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
			
		}else{		
			if(isset($data['money']) || isset($data['fee_amount'])){
				//网站收益表  跟会员的刚好相反
				$money_log_info = array();
				$is_add = false;
				switch((int)$type){ 
					//case 7 : //提前回收 + 
					case 1 : //充值手续费 +
						$is_add = true;
						if(round($data['fee_amount'],2)==0){
							$is_add = false;
						}
						else{
							$site_money = round($data['fee_amount'],2);
							$money_log_info['money'] = $site_money;
						}
						break;
					case 9 : //提现手续费 +
					case 10 : //借款管理费 +
					case 12 : //逾期管理费 +
					case 13 : //人工充值
					case 14 : //借款服务费 +
					case 17 : //债权转让管理费  +
					case 18 : //开户奖励   -
					case 20 : //投标管理费 +
					case 22 : //兑换  
					case 23 : //邀请返利 -
					case 24 : //投标返利 -
					case 25 : //签到成功 -
					case 26 : //逾期罚金（垫付后）
					case 27 : //其他费用
					case 28 : //投资奖励
					case 29 : //红包奖励
					case 47 : //体验金收益
						$is_add = true;
						$site_money = round($data['money'],2);
						$money_log_info['money'] = -$site_money;
						break;
				}
				
				if($is_add == true){
					$money_log_info['memo'] = $log_msg;					
					$money_log_info['user_id'] = $user_id;
					$money_log_info['create_time'] = $data['create_time'];
					$money_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
					$money_log_info['create_time_ym'] = date("Ym", $data['create_time']);
					$money_log_info['create_time_y'] = date("Y", $data['create_time']);
					$money_log_info['type'] = $type;
					$site_money_logREStttt = $GLOBALS['db']->autoExecute(DB_PREFIX."site_money_log",$money_log_info);
					if($site_money_logREStttt){
						$this->writeTimes++;
					}else{
						$this->abort=1;//中止本批数据写入
						$this->rollback(); //回滚并中止本次操作
					}
				}
			}
		}
		
		if(isset($data['score'])){
			$score_log_info['memo'] = $log_msg;
			$score_log_info['score'] = intval($data['score']);
			$score_log_info['account_score'] = $user_info['score'];
			$score_log_info['user_id'] = $user_id;
			$score_log_info['create_time'] = $data['create_time'];
			$score_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
			$score_log_info['create_time_ym'] = date("Ym", $data['create_time']);
			$score_log_info['create_time_y'] = date("Y", $data['create_time']);
			$score_log_info['type'] = $type;
			$user_score_logResult111 = $GLOBALS['db']->autoExecute(DB_PREFIX."user_score_log",$score_log_info);
			if($user_score_logResult111){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
			
		}
		//资金冻结日志
		if(isset($data['lock_money'])){
			$money_log_info['memo'] = $log_msg;
			$money_log_info['lock_money'] = round($data['lock_money'],2);
			$money_log_info['account_lock_money'] = $user_info['lock_money'] + $user_info['mortgage_money'];
			$money_log_info['user_id'] = $user_id;
			$money_log_info['create_time'] = $data['create_time'];
			$money_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
			$money_log_info['create_time_ym'] = date("Ym", $data['create_time']);
			$money_log_info['create_time_y'] = date("Y", $data['create_time']);
			$money_log_info['type'] = $type;
			$user_lock_money_logResultttt = $GLOBALS['db']->autoExecute(DB_PREFIX."user_lock_money_log",$money_log_info);
			if($user_lock_money_logResultttt){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
			
		}
		
		if(isset($data['mortgage_money'])){
			$money_log_info['memo'] = $log_msg;
			$money_log_info['lock_money'] = round($data['mortgage_money'],2);
			$money_log_info['account_lock_money'] = $user_info['mortgage_money'] + $user_info['lock_money'];
			$money_log_info['user_id'] = $user_id;
			$money_log_info['create_time'] = $data['create_time'];
			$money_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
			$money_log_info['create_time_ym'] = date("Ym", $data['create_time']);
			$money_log_info['create_time_y'] = date("Y", $data['create_time']);
			$money_log_info['type'] = $type;
			$user_lock_money_logRE222 = $GLOBALS['db']->autoExecute(DB_PREFIX."user_lock_money_log",$money_log_info);
			if($user_lock_money_logRE222){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		
		if(isset($data['point'])){
			$point_log_info['memo'] = $log_msg;
			$point_log_info['point'] = intval($data['point']);
			$point_log_info['account_point'] = $user_info['point'];
			$point_log_info['user_id'] = $user_id;
			$point_log_info['create_time'] = $data['create_time'];
			$point_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
			$point_log_info['create_time_ym'] = date("Ym", $data['create_time']);
			$point_log_info['create_time_y'] = date("Y", $data['create_time']);
			$point_log_info['type'] = $type;
			$user_point_logREsult21 = $GLOBALS['db']->autoExecute(DB_PREFIX."user_point_log",$point_log_info);
			if($user_point_logREsult21){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		
	}
	
	public function test(){
		echo 11111;
	}
	
	//还款处理
	public function dealLoadRepayCurrentDayOne($id){
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load_repay where id=".$id);
		if($infos['calculate_status']){
			return true;//当作已成功
		}
		
		if($infos['self_money']>0){ //还本还息才需要解冻投资资金
			$data['lock_money'] = -$infos['self_money']; //解冻步骤  投资资金+虚拟货币---------------------
		}
 
		$data['money'] = $infos['repay_money']; //资金增加++++++++++++++++++++++
		$data['create_time'] = time(); //还款时间
		$data['brief'] = $infos['virtual_info']; //虚拟货币消息
		$data['deal_id'] = $infos['deal_id'];
		$data['load_repay_id'] = $infos['load_repay_id'];
		$data['load_id'] = $infos['load_id'];
		$msg = $infos['self_money']>0?"还本还息":"还息";	
		if(!in_array($infos['user_id'],$this->rubbishUser)){
			$this->modify_account($data, $infos['user_id'], $msg, 5, $data['brief']);	//添加资金记录
		}
		$statusArray['calculate_status'] = 1;
		$statusArray['has_repay'] = 1; //设置已还款标志
		$statusArray['true_repay_time'] = time();
		$statusArray['true_repay_date'] = date('Y-m-d', time());
		$dealloadRepayResult = $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$statusArray,'UPDATE',"id = ".$id); //更新状态
		return $dealloadRepayResult;//如果更新失败则返回错误 

	}  
	//还款定时器处理方法
	public function dealloadrepaycurrentday(){
		//echo "正在处理，请稍候---------------"; 
 		//ini_set('max_execution_time', '0'); //防止数据过多引起超时
		set_time_limit(300);
		$CurrentDateTime = strtotime(date('Y-m-d',time())); //当前处理日期
		$NextDateTime = $CurrentDateTime + 86400; //后一天0点时间戳
		
		
		
		if($CurrentDateTime>=strtotime('2017-06-12')){ //只算到x月x号   一定注意此处时间，想好了再改。搞错就等死吧
			echo "<script>window.close();</script>"; 
			die;
		}
		$CalculateInfo = $GLOBALS['db']->getRow("SELECT page,date FROM ".DB_PREFIX."calculate where  id=1 FOR UPDATE");
				
		if(time()<strtotime($CalculateInfo['date'])){ //只算今天  如果是下一天则不执行
			echo "已完成";
			echo "<script>window.close();</script>"; 
			die;
		}
		
		$pageNum = 500;
		$Page =$CalculateInfo['page']; //当前日期到达页数  以上两个值均做成功后增加
		$this->startTrans(); //开启事务
		$sql = "select id from ".DB_PREFIX."deal_load_repay where cunguan_tag=0 and  repay_time >= $CurrentDateTime and repay_time < $NextDateTime order by id asc limit ".$Page*$pageNum.",".$pageNum; 
		$RepayList = $GLOBALS['db']->getAll($sql);
		foreach($RepayList as $key=>$info){
			$resultRepay = $this->dealLoadRepayCurrentDayOne($info['id']);
			if($resultRepay){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
				die;
			}
		} 
		//更新页数
		if(count($RepayList)==$pageNum){ //  记录完整页
			$NextPage = $Page+1;
			$pageResult = $GLOBALS['db']->autoExecute(DB_PREFIX."calculate",array('page'=>$NextPage),'UPDATE'," id=1 "); //保存已发送完成的页数
		}else{
			$pageResult = $GLOBALS['db']->autoExecute(DB_PREFIX."calculate",array('page'=>0, 'date'=>date("Y-m-d", $NextDateTime)),'UPDATE'," id=1 ");//当前日期发送完成 页数置0 日期加1天
		}

		$this->commit(); //提交事务
		echo "成功"; 
	}
	//	体验标收益还款
	public function experience_repay(){
		set_time_limit(300);
		//$CurrentDateTime = strtotime(date('Y-m-d',strtotime("-1 day"))); //当前处理日期
		$CurrentDateTime = strtotime(date('Y-m-d',time())); //当前处理日期
		$NextDateTime = $CurrentDateTime + 86400; //后一天0点时间戳
		
		
		
		/* if($CurrentDateTime>=strtotime('2017-10-12')){ //只算到x月x号   一定注意此处时间，想好了再改。搞错就等死吧
			echo "<script>window.close();</script>"; 
			die;
		} */
		$CalculateInfo = $GLOBALS['db']->getRow("SELECT page,date FROM ".DB_PREFIX."calculate where  id=2 FOR UPDATE");
				
		if(time()<strtotime($CalculateInfo['date'])){ //只算今天  如果是下一天则不执行
			echo "已完成";
			echo "<script>window.close();</script>"; 
			die;
		}
		
		$pageNum = 500;
		$Page =$CalculateInfo['page']; //当前日期到达页数  以上两个值均做成功后增加
		$this->startTrans(); //开启事务
		
		//$sql = "select dl.deal_id as deal_id,dl.id as id,dl.experience_money as experience_money,d.objectaccno as objectaccno,dl.user_id as user_id from ".DB_PREFIX."experience_deal_load dl left join ".DB_PREFIX." deal d on d.id=dl.deal_id where d.cunguan_tag=1 and  dl.repay_time >= $CurrentDateTime and dl.repay_time < $NextDateTime  and dl.has_repay =0 order by dl.id asc limit ".$Page*$pageNum.",".$pageNum; 
		$sql = "select dl.id,dl.experience_money,dl.user_id,dl.calculate_status,u.mobile,dl.deal_id from ".DB_PREFIX."experience_deal_load dl left join ".DB_PREFIX."user u on u.id=dl.user_id  where dl.repay_time >= $CurrentDateTime and dl.repay_time < $NextDateTime and dl.calculate_status=0  order by dl.id asc limit ".$Page*$pageNum.",".$pageNum; 
	
		$RepayList = $GLOBALS['db']->getAll($sql);
		
		foreach($RepayList as $key=>$info){
			if($info['calculate_status']){
			return true;//当作已成功
		}
		/* $data['red_packet'] = $info['experience_money']; //资金增加++++++++++++++++++++++
		$data['create_time'] = time(); //还款时间
		$data['brief'] = $info['virtual_info']; //虚拟货币消息
		$data['deal_id'] = $info['deal_id'];
		$data['load_id'] = $info['id'];
		$msg = "体验金收益";	
		 if(!in_array($info['user_id'],$this->rubbishUser)){
			$this->modify_account($data, $info['user_id'], $msg, 61, $data['brief']);	//添加资金记录
		} */  
		$statusArray['calculate_status'] = 1;
		$statusArray['has_repay'] = 0; //设置已还款标志
		$statusArray['true_repay_time'] = time();
		$dealloadRepayResult = $GLOBALS['db']->autoExecute(DB_PREFIX."experience_deal_load",$statusArray,'UPDATE',"id =".$info['id']); //更新状态
			if($dealloadRepayResult){
				$sn=unpack('H12',str_shuffle(md5(uniqid())));
				$arr['sn'] = $sn[1];
				$arr['user_id'] = $info['user_id'];
				$arr['begin_time'] = time();
				$arr['use_limit'] =7;
				$arr['money'] =$info['experience_money'];
				$arr['red_type_id'] =10;
				$arr['experience_load_id'] =$info['id'];
				$arr['deal_id'] =$info['deal_id'];
				$arr['create_time'] =time();
				$arr['content'] ="体验金现金红包";
				$arr['packet_type'] =3;
				$arr['status'] =0;
				$arr['end_time'] =to_timespan(to_date($arr['begin_time'],'Y-m-d')." 7 day")-1;
				$arr['publish_wait'] =1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."red_packet",$arr,"INSERT"); //更新状态
				/* $experience_msg = "【玖财通】您的体验金收益共计".$info['experience_money']."元已发送到您的现金红包！请注意查收";
				$this->sendSMS($info['mobile'],$experience_msg);    */
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
				die;
			}
		} 
		//var_dump($experience);die;
		
		//更新页数
		if(count($RepayList)==$pageNum){ //  记录完整页
			$NextPage = $Page+1;
			$pageResult = $GLOBALS['db']->autoExecute(DB_PREFIX."calculate",array('page'=>$NextPage),'UPDATE'," id=2 "); //保存已发送完成的页数
		}else{
			$pageResult = $GLOBALS['db']->autoExecute(DB_PREFIX."calculate",array('page'=>0, 'date'=>date("Y-m-d", $NextDateTime)),'UPDATE'," id=2 ");//当前日期发送完成 页数置0 日期加1天
		}

		$this->commit(); //提交事务
		echo "成功"; 
	}


	/*
	*  投资到期前一天群发短信
	*/
	public function investment_maturity() { 

		// set_time_limit(300);
		// $CurrentDateTime = strtotime(date('Y-m-d',time())); //当前处理日期
		// $NextDateTime = $CurrentDateTime + 86400; //后一天0点时间戳

					
		$deal_info=$GLOBALS['db']->getAll("select id from ".DB_PREFIX."deal where cunguan_tag=1 and debts=0 and deal_status=4");
		$uIds = array();
        foreach($deal_info as $v){
            $uIds[] = $v['id'];
        }
        $treetop_id=implode(',',$uIds);
        $this->startTrans(); //开启事务   
        $user_count =$GLOBALS['db']->getAll("select max(repay_time)as repay_time,deal_id from " . DB_PREFIX . "deal_repay where cunguan_tag=1 and deal_id in (".$treetop_id.") group by deal_id order by id desc  ");      
   		foreach ($user_count as $key => $v) {  			   					
   			if(strtotime(date('Y-m-d',$v['repay_time']))-strtotime(date('Y-m-d',time()))<=86400){ 		
   				$user_number =$GLOBALS['db']->getAll("select DISTINCT(user_id),deal_id from " . DB_PREFIX . "deal_load_repay where cunguan_tag=1 and debts=0 and deal_id in (".$v['deal_id'].")");
	   				foreach ($user_number as $va) {
	   						if(app_conf("SMS_ON")==1){
					            $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$va['user_id']);
					            $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_MATURITY_SUCCESS'");
					            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					            $msg_data['dest'] = $user_info['mobile'];
					            $msg_data['send_type'] = 0;
					            $msg_data['title'] = " 投资到期短信通知";
					            $msg_data['content'] = addslashes($msg);
					            $msg_data['send_time'] = time();
					            $msg_data['is_send'] = 0;
					            $msg_data['create_time'] = TIME_UTC;
					            $msg_data['user_id'] = $va['user_id'];
					            $msg_data['is_success'] = 0;
					            $msg_data['is_html'] = 0;
					           // send_lbsms_email($msg_data);另个短信平台
					            send_sms_email($msg_data);
					            $daunxin=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				            
					            if($daunxin){
					            	$this->writeTimes++;
					            }else{
					        		$this->abort=1;//中止本批数据写入
									$this->rollback(); //回滚并中止本次操作
									die;
					       		}

			        		}
   					}

   			}else{ 
   				continue;
   			}
   		}
		$this->commit(); //提交事务
   		echo "成功"; 
	}

    /*
*  投资到期前一天群发短信
*/
    public function investment_maturitys() {

        // set_time_limit(300);
        // $CurrentDateTime = strtotime(date('Y-m-d',time())); //当前处理日期
        // $NextDateTime = $CurrentDateTime + 86400; //后一天0点时间戳

        $this->startTrans(); //开启事务
        $deal_info=$GLOBALS['db']->getAll("select id from ".DB_PREFIX."deal where cunguan_tag=1 and debts=0 and type_id!=12 and deal_status=4");

        $uIds = array();
        foreach($deal_info as $v){
            $uIds[] = $v['id'];
        }
        $treetop_id=implode(',',$uIds);
        $timebets =86400*7;
        $daytime =time();
        $daylasttime =$timebets+$daytime;
        $user_count =$GLOBALS['db']->getAll("select repay_time,deal_id,user_id from " . DB_PREFIX . "deal_repay where cunguan_tag=1 and repay_time<= $daylasttime  and repay_time >=$daytime and deal_id in (".$treetop_id.") group by deal_id order by id desc  ");
//            var_dump($GLOBALS['db']);die;
//        $counts =count($user_count);
        $this->startTrans(); //开启事务
//        var_dump($user_count);die;
//            $a=0; $b=0; $c=0;
        foreach ($user_count as $key => $v) {

//            || strtotime(date('Y-m-d',$v['repay_time']))-strtotime(date('Y-m-d',time()))<=86400*3 || strtotime(date('Y-m-d',$v['repay_time']))-strtotime(date('Y-m-d',time()))<=86400
            if(strtotime(date('Y-m-d',$v['repay_time']-86400))-strtotime(date('Y-m-d',time()))<=86400*7 && strtotime(date('Y-m-d',$v['repay_time']-86400))-strtotime(date('Y-m-d',time()))>=86400*6){
//                $a++;
                    if(app_conf("SMS_ON")==1){
                        $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$v['user_id']);
                        if($user_info['mobile']){
                            $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_REPAYMENT_REMINDER'");
				            $notice['release_date'] =date("m月d日",$v['repay_time']-86400);
	                        $GLOBALS['tmpl']->assign("notice",$notice);	
				            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                            $msg_data['dest'] = $user_info['mobile'];
                            $msg_data['send_type'] = 0;
                            $msg_data['title'] = " 投资到期短信通知";
                            $msg_data['content'] = addslashes($msg);
                            $msg_data['send_time'] = time();
                            $msg_data['is_send'] = 1;
                            $msg_data['create_time'] = TIME_UTC;
                            $msg_data['user_id'] = $v['user_id'];
                            $msg_data['is_success'] = 0;
                            $msg_data['is_html'] = 0;
//                        send_lbsms_email($msg_data);
                            send_sms_email($msg_data);
//                        var_dump($msg_data);die;
                            $daunxin=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                            if($daunxin){
                                $this->writeTimes++;

                            }else{
                                $this->abort=1;//中止本批数据写入
                                $this->rollback(); //回滚并中止本次操作
                                die;
                            }


                        }

//
                    }

            }else if(strtotime(date('Y-m-d',$v['repay_time']-86400))-strtotime(date('Y-m-d',time()))<=86400*3 && strtotime(date('Y-m-d',$v['repay_time']-86400))-strtotime(date('Y-m-d',time()))>=86400*2){
//                $b++;

                if(app_conf("SMS_ON")==1){
                    $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$v['user_id']);
                    if($user_info['mobile']){
                    	$tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_REPAYMENT_REMINDER'");
			            $notice['release_date'] =date("m月d日",$v['repay_time']-86400);
                        $GLOBALS['tmpl']->assign("notice",$notice);	
			            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                        $msg_data['dest'] = $user_info['mobile'];
                        $msg_data['send_type'] = 0;
                        $msg_data['title'] = " 投资到期短信通知";
                        $msg_data['content'] = addslashes($msg);
                        $msg_data['send_time'] = time();
                        $msg_data['is_send'] = 1;
                        $msg_data['create_time'] = TIME_UTC;
                        $msg_data['user_id'] = $v['user_id'];
                        $msg_data['is_success'] = 0;
                        $msg_data['is_html'] = 0;
//                        send_lbsms_email($msg_data);
                        send_sms_email($msg_data);
//                        var_dump($msg_data);die;
                        $daunxin=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                        if($daunxin){
                            $this->writeTimes++;

                        }else{
                            $this->abort=1;//中止本批数据写入
                            $this->rollback(); //回滚并中止本次操作
                            die;
                        }


                    }
                }




            }else if(strtotime(date('Y-m-d',$v['repay_time']-86400))-strtotime(date('Y-m-d',time()))<=86400){
//                    $c++;
                if(app_conf("SMS_ON")==1){
                    $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$v['user_id']);
                    if($user_info['mobile']){
                        $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_REPAYMENT_REMINDER'");
			            $notice['release_date'] =date("m月d日",$v['repay_time']-86400);
                        $GLOBALS['tmpl']->assign("notice",$notice);	
			            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
                        $msg_data['dest'] = $user_info['mobile'];
                        $msg_data['send_type'] = 0;
                        $msg_data['title'] = " 投资到期短信通知";
                        $msg_data['content'] = addslashes($msg);
                        $msg_data['send_time'] = time();
                        $msg_data['is_send'] = 1;
                        $msg_data['create_time'] = TIME_UTC;
                        $msg_data['user_id'] = $v['user_id'];
                        $msg_data['is_success'] = 0;
                        $msg_data['is_html'] = 0;
//                        send_lbsms_email($msg_data);

                        send_sms_email($msg_data);

                        $daunxin=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
                        if($daunxin){
                            $this->writeTimes++;

                        }else{
                            $this->abort=1;//中止本批数据写入
                            $this->rollback(); //回滚并中止本次操作
                            die;
                        }


                    }

                }


            }else{
                continue;
            }
        }
        $this->commit(); //提交事务
        echo "成功";
//        echo "<br/>";
//        echo "  a=".$a."   b=".$b."  c=".$c."   满足7天以内的所有还款时间条数为:$counts";
    }



	/*
	* 	未复投7天之后群发短信
	*/
	public function re_voting(){ 
		// set_time_limit(300);
		// $CurrentDateTime = strtotime(date('Y-m-d',time())); //当前处理日期
		// $NextDateTime = $CurrentDateTime + 86400; //后一天0点时间戳
		$this->startTrans(); //开启事务
		$deal_info=$GLOBALS['db']->getAll("select max(create_time)as create_time,user_id from ".DB_PREFIX."deal_load where cunguan_tag=1 and debts=0 group by user_id order by id ");			
			foreach ($deal_info as $v) {
				if(strtotime(date('Y-m-d',time()))-strtotime(date('Y-m-d',$v['create_time']))>=604800){		
					$user_info=$GLOBALS['db']->getRow("select is_send,is_success,send_time from  ".DB_PREFIX."deal_msg_list where user_id=".$v['user_id']." and is_success=3  order by id desc ");
   					if((time()-$user_info['send_time']>=604800) || $user_info['is_success'] !=3){	
						if(app_conf("SMS_ON")==1){
				            $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$v['user_id']);
				            $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_NON_VOTING_SUCCESS'");
				            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				            $msg_data['dest'] = $user_info['mobile'];
				            $msg_data['send_type'] = 0;
				            $msg_data['title'] = " 未复投到期短信通知";
				            $msg_data['content'] = addslashes($msg);
				            $msg_data['send_time'] = time();
				            $msg_data['is_send'] = 1;
				            $msg_data['create_time'] = TIME_UTC;
				            $msg_data['user_id'] = $v['user_id'];
				            $msg_data['is_success'] = 3;
				            $msg_data['is_html'] = 0;
				            send_lbsms_email($msg_data);
				            $weitou=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				            if($weitou){ 
				            	$this->writeTimes++;
				            	
				            }else{ 
				            	$this->abort=1;//中止本批数据写入
								$this->rollback(); //回滚并中止本次操作
								die;
				            }
			        	}
			        }else{ 
			        	continue;
			        }  	

				}else{ 
						continue;
				}
		}	
		$this->commit(); //提交事务
		echo "成功";
	}	



	/*加息卡快到期发短信*/
	public function plus_card(){ 
			$this->startTrans(); //开启事务
			$deal_info=$GLOBALS['db']->getAll("select end_time,user_id from ".DB_PREFIX."interest_card where  end_time > ".time()." and status=0");
			foreach($deal_info as $v ){ 
				if(strtotime(date('Y-m-d',$v['end_time']))-strtotime(date('Y-m-d',time()))<=259200){									
					$user_info=$GLOBALS['db']->getRow("select is_send,is_success,send_time from  ".DB_PREFIX."deal_msg_list where user_id=".$v['user_id']." and is_success=4  order by id desc ");	   
	   	 			if((time()-$user_info['send_time']>=259200) || $user_info['is_success'] !=4){
						if(app_conf("SMS_ON")==1){
				            $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$v['user_id']);
				            $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_VIRTUAL_CURRENCY_INTEREST_SUCCESS'");
				            $notice['release_date'] = to_date($v['end_time'],"Y-m-d"); 
	                        $GLOBALS['tmpl']->assign("notice",$notice);	
				            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				            $msg_data['dest'] = $user_info['mobile'];
				            $msg_data['send_type'] = 0;
				            $msg_data['title'] = "加息卡提前通知短信通知";
				            $msg_data['content'] = addslashes($msg);
				            $msg_data['send_time'] = time();
				            $msg_data['is_send'] = 1;
				            $msg_data['create_time'] = TIME_UTC;
				            $msg_data['user_id'] = $v['user_id'];
				            $msg_data['is_success'] = 4;
				            $msg_data['is_html'] = 0;
				            //send_lbsms_email($msg_data);
				            send_sms_email($msg_data);
				            $daunxin=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				            if($daunxin){ 
				            	$this->writeTimes++;				            	
				            }else{ 
				            	$this->abort=1;//中止本批数据写入
								$this->rollback(); //回滚并中止本次操作
								die;
				            }	

					    }else{ 
					    	continue;	
					    }
					}else{ 

						continue;
					}
				}else{ 
					continue;

				} 

			}

		$this->commit(); //提交事务
		echo "成功";
	}

	/*红包快到期发短信*/
	public function  red_envelope(){ 
		$this->startTrans(); //开启事务
		$deal_info=$GLOBALS['db']->getAll("select end_time,user_id from ".DB_PREFIX."red_packet where status=0 and end_time > ".time()."");
		foreach($deal_info as $v ){ 
			if(strtotime(date('Y-m-d',$v['end_time']))-strtotime(date('Y-m-d',time()))<=259200){				
				$user_info=$GLOBALS['db']->getRow("select is_send,is_success,send_time from  ".DB_PREFIX."deal_msg_list where user_id=".$v['user_id']." and is_success=5 order by id desc");
   				if((time()-$user_info['send_time']>=259200) || $user_info['is_success'] !=5){
					if(app_conf("SMS_ON")==1){
			            $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$v['user_id']);
			            $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_VIRTUAL_MONEY_ENVELOPES_SUCCESS'");
			            $notice['release_date'] = to_date($v['end_time'],"Y-m-d"); 
                        $GLOBALS['tmpl']->assign("notice",$notice);	
			            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			            $msg_data['dest'] = $user_info['mobile'];
			            $msg_data['send_type'] = 0;
			            $msg_data['title'] = "虚拟货币短信通知";
			            $msg_data['content'] = addslashes($msg);
			            $msg_data['send_time'] = time();
			            $msg_data['is_send'] = 1;
			            $msg_data['create_time'] = TIME_UTC;
			            $msg_data['user_id'] = $v['user_id'];
			            $msg_data['is_success'] = 5;
			            $msg_data['is_html'] = 0;
			            //send_lbsms_email($msg_data);
			             send_sms_email($msg_data);
			            $daunxin=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
		             	if($daunxin){ 
			            	$this->writeTimes++;				            	
			            }else{ 
			            	$this->abort=1;//中止本批数据写入
							$this->rollback(); //回滚并中止本次操作
							die;
			            }	

				    }else{ 
				    	continue;	
				    }

				}else{ 
					continue;	
				}

			}else{ 
				continue;
			} 	
		}

		$this->commit(); //提交事务
		echo "成功";
	}

	/*生日快乐 */
	public function happy_birthday(){ 
		$this->startTrans(); //开启事务
		$deal_info=$GLOBALS['db']->getAll("select idno,id from ".DB_PREFIX."user where cunguan_tag=1 and user_type=0 order by id desc");
		foreach ($deal_info as $v) {
			$time =date('Ymd');						
			$birthday=substr($v['idno'],6,8);
			if($time==$birthday){ 
				if(app_conf("SMS_ON")==1){
		            $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$v['id']);
		            $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_BIRTHDAY_SUCCESS'");
		            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
		            $msg_data['dest'] = $user_info['mobile'];
		            $msg_data['send_type'] = 0;
		            $msg_data['title'] = " 生日快乐短信通知";
		            $msg_data['content'] = addslashes($msg);
		            $msg_data['send_time'] = time();
		            $msg_data['is_send'] = 0;
		            $msg_data['create_time'] = TIME_UTC;
		            $msg_data['user_id'] = $v['id'];
		            $msg_data['is_success'] = 0;
		            $msg_data['is_html'] = 0;
		            //send_lbsms_email($msg_data);
		            send_sms_email($msg_data);
		            $daunxin=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
		            if($daunxin){ 
			            $this->writeTimes++;				            	
			        }else{ 
		            	$this->abort=1;//中止本批数据写入
						$this->rollback(); //回滚并中止本次操作
						die;
			        }	
		  
			    }else{ 
			    	continue;
			    }

			}else{ 
				continue;
			}

		}
		$this->commit(); //提交事务
		echo "成功";
	}


	/*
	*  周年感恩 用户满一年的群发短信
	*/
	public function anniversary_thanksgiving(){ 

		// set_time_limit(300);
		// $CurrentDateTime = strtotime(date('Y-m-d',time())); //当前处理日期
		// $NextDateTime = $CurrentDateTime + 86400; //后一天0点时间戳

		$this->startTrans(); //开启事务
		$user_info=$GLOBALS['db']->getAll("select create_date,id from ".DB_PREFIX."user group by id order by id limit 0,10");
		foreach ($user_info as $v) {
			if(strtotime(date('Y-m-d',time()))-strtotime(date('Y-m-d',$v['create_time']))>31536000){
				$user_info=$GLOBALS['db']->getRow("select is_send,is_success ".DB_PREFIX."deal_msg_list where user_id=".$v['user_id']);
   				if($user_info['is_success'] !=6){
					if(app_conf("SMS_ON")==1){
			            $user_info=$GLOBALS['db']->getRow("select user_name,mobile from ".DB_PREFIX."user where id=".$v['user_id']);
			            $tmpl_content =  $GLOBALS['db']->getOne("select content from ".DB_PREFIX."msg_template where name = 'TPL_SMS_NON_VOTING_SUCCESS'");
			            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			            $msg_data['dest'] = $user_info['mobile'];
			            $msg_data['send_type'] = 0;
			            $msg_data['title'] = " 未复投到期短信通知";
			            $msg_data['content'] = addslashes($msg);
			            $msg_data['send_time'] = time();
			            $msg_data['is_send'] = 1;
			            $msg_data['create_time'] = TIME_UTC;
			            $msg_data['user_id'] = $v['user_id'];
			            $msg_data['is_success'] = 6;
			            $msg_data['is_html'] = 0;
			            send_lbsms_email($msg_data);
			            $weitou=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			            if($weitou){ 
			            	$this->writeTimes++;			    
			            }else{ 
			            	$this->abort=1;//中止本批数据写入
							$this->rollback(); //回滚并中止本次操作
							die;
			            }

			        }  
			    }else{ 
			    	continue;
			    }	
				
			}
			
		}
		$this->commit(); //提交事务
		echo "成功";
	}



    /**
     开启事务
     */
    public function startTrans() {
        //数据rollback 支持
		mysql_query('START TRANSACTION');
        //if ($this->transTimes == 0) {
            //mysql_query('START TRANSACTION');
       // }
        //$this->transTimes++;
        return ;
    }

    /**
	 非自动提交状态下面的查询提交  
     */
    public function commit()
    {
        if ($this->transTimes > 0) {
            //$result = mysql_query('COMMIT');
           // $this->transTimes = 0;
            if(!$result){
                //throw_exception($this->error()); //忽略错误
            }
        }
		$result = mysql_query('COMMIT');
        return true;
    }

    /**
     回滚
     */
    public function rollback()
    {
       // if ($this->transTimes > 0) {
         //   $result = mysql_query('ROLLBACK');
         //   $this->transTimes = 0;
         //   if(!$result){
                //throw_exception($this->error());
          //  }
			
        //}
		$result = mysql_query('ROLLBACK');
		$this->abortc=1;
        return true;
    }
 //短信通知
	public function sendSMS($mobile_number,$content)
	{
	 
			$post_data = array();
			$post_data['pswd'] = 'Jct888888';
			$post_data['account'] = 'Jct888888';
			$post_data['mobile'] = $mobile_number; //手机号码，多个用英文逗号隔开，推荐群发一次少于1000条
			$post_data['msg'] = $content;
			$post_data['needstatus'] = 'true';
			$post_data['product'] = ''; //定时时间 格式为2011-6-29 11:09:21
			$post_data['extno'] = ''; //默认空 如果空返回系统生成的标识串 如果传值保证值唯一 成功则返回传入的值
			$url='http://222.73.117.156/msg/HttpBatchSendSM?';
			$data = http_build_query($post_data);
			$curl = curl_init();    //启动一个curl会话
			curl_setopt($curl, CURLOPT_URL, $url);   //要访问的地址
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
			curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
			curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
			curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
			curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
			curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
			$return = curl_exec($curl);  //执行curl会话
			//print_r($return);
			curl_close($curl);      //关闭curl会话
			$result = $return;
			return $result;
	}
	
	//抢红包每日0点检查redis里面好友红包的剩余金额并退回用户红包余额
	public function check_friend_packet(){
	    $redis = new Redis();
	    $conrs = $redis->connect(REDIS_HOST, REDIS_PORT);
	    $redis->auth(REDIS_PWD);
	    $redis->select(8);
	    
	    //昨天开始时间戳
	    $yesterday_start_time = strtotime(date("Y-m-d",strtotime("-1 days")));
	    //昨天结束时间戳
	    $yesterday_end_time = $yesterday_start_time + 86399;
	    //获取前一天所有的 '好友红包'ID
	    $friend_packet_ids = $redis->zRangeByScore(REDIS_PREFIX.'friend_packet_zset', $yesterday_start_time, $yesterday_end_time, array('withscores'=>false,'limit'=>array(0,50)));
	    if(empty($friend_packet_ids)){
	        echo "已完成";
	        echo "<script>window.close();</script>";
	        die;
	    }
	    
	    foreach ($friend_packet_ids as $v){
	        $red_packet = json_decode($redis->get(REDIS_PREFIX.'pid'.$v), true);
	        if($red_packet['last_money'] > 0) {
	            //该红包被抢走多少钱
	            $rob_money = $GLOBALS['db']->getOne("select sum(rob_red_money) from ".DB_PREFIX."red_packet_rob where red_packet_id=".$v);
	            if(($red_packet['send_red_money'] - $rob_money) == $red_packet['last_money']){
	                //更新用户红包余额
	                $rs = $GLOBALS['db']->query("update ".DB_PREFIX."user set new_red_money = new_red_money+".$red_packet['last_money']." where id = ".$red_packet['user_id']);
	                if($rs){
	                    $redis->del(REDIS_PREFIX.'pid'.$v);//删除红包信息
	                    $redis->zRem(REDIS_PREFIX.'user_friend_packet'.$red_packet['user_id'],$v);//删除用户好友红包集合里面的红包ID
	                    $redis->zRem(REDIS_PREFIX.'friend_packet_zset',$v);//删除好友红包总集合里面的红包ID
	                    //判断用户好友红包集合是否为空 为空则删除 避免冗余数据
	                    $num = $redis->zCount(REDIS_PREFIX.'user_friend_packet'.$red_packet['user_id'], 0 , -1);
	                    if(empty($num)) $redis->del(REDIS_PREFIX.'user_friend_packet'.$red_packet['user_id']);//删除信息
	                    //记录日志
	                    $new_red_money = $GLOBALS['db']->getOne("select new_red_money from ".DB_PREFIX."user where id=".$red_packet['user_id']);
	                    $red_data_log['user_id'] = $red_packet['user_id'];
	                    $red_data_log['red_money'] = $red_packet['last_money'];
	                    $red_data_log['new_red_money'] = $new_red_money;
	                    $red_data_log['addtime'] = date('Y-m-d H:i:s');
	                    $red_data_log['remark'] = '好友红包'.$v.'余额退回';
	                    $red_data_log['type'] = $red_packet['type'];
	                    $red_data_log['action'] = 3;
	                    $resll = $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_log",$red_data_log,"INSERT");
	                
	                }
	            }
	       }
	    }
	    echo "成功";
	}
    
	public function send_async_notice(){
	    $result = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."mjn_async where async_status=0 order by create_time asc limit 20");
	    foreach ($result as $k=>$v){
	        if($v['send_num'] >= 5){//发送多少次后就不发送了
	            continue;
	        }elseif($v['update_time'] + 300 < time()){
	            continue;
	        }else{
	            $notice['SerialNumber'] = $v['SerialNumber'];
	            $notice['Status'] = $v['status'];
	            $notice['ResonText'] = $v['reson_text'];
	            
	            $curl = curl_init();    //启动一个curl会话
	            curl_setopt($curl, CURLOPT_URL, $url);   //要访问的地址
	            //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	            //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
	            //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
	            //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	            //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
	            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
	            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($notice)); // Post提交的数据包
	            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	            //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	            $rs = curl_exec($curl);  //执行curl会话
	            curl_close($curl);
	            $rs = json_decode($rs,true);
	            
	            if($rs['Result'] == "OK"){
	                $data['async_status'] = 1;
	                $data['send_num'] = $v['send_num'] + 1;
	                $data['update_time'] = time();
	                
	            }else{
	                $data['send_num'] = $v['send_num'] + 1;
	                $data['update_time'] = time();
	            }
	            $GLOBALS['db']->autoExecute(DB_PREFIX."mjn_async",$data,"UPDATE","id=".$v['id']);
	            usleep(300000);//暂停300毫秒
	        }
	        
	        
	    }
	}



	function testMsg($msg,$tell=13671280826){

        $msg_data['send_time'] = time();
        $msg_data['dest'] = $tell;
        $msg_data['send_type'] = 0;
        $msg_data['title'] = " 投资到期短信通知";
        $msg_data['content'] = addslashes($msg);
        $msg_data['is_send'] = 0;
        $msg_data['create_time'] = TIME_UTC;
        $msg_data['user_id'] = 1123487;
        $msg_data['is_success'] = 0;
        $msg_data['is_html'] = 0;
//                        send_lbsms_email($msg_data);
        send_sms_email($msg_data);


    }

    function testSms(){
        $msg ='测试短信延迟';
       // for($i=0;$i<=1;$i++){
             $this->testMsg($msg);
       // }


    }
 
}
?>