<?php
/*
added by zhangteng

*/
class calculateModule extends SiteBaseModule
{
	public $transTimes = 0;
	public $writeTimes = 0;
	public $abort = 0;
	public $abortc = 0;
	//public $rubbishUser = array(0,2,3,4,6,9,25,26,27,30,32,35,38,39,40,75,161,163,58,59,98,142,152,154,156,168,219,252,273,1106394,106864,208670,388340,609127);
	public $rubbishUser = array(2,4,6,9,25,27,30,32,35,38,40,75,161);
	//查询所有影响资金的表  充值 jctp2p_payment_notice    投资 jctp2p_deal_load    提现 jctp2p_user_carry    还款 jctp2p_deal_load_repay   体验金 jctp2p_taste_cash
	public function rechargeOne(){
		//echo AES_DECRYPT_KEY;
		die;
		$id = 2758;
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id=".$id); //获取当前需要处理的充值记录
		$data['money'] = $infos['money'];
		$data['create_time'] = $infos['pay_time'];
		$this->modify_account($data, $infos['user_id'], $infos['outer_notice_sn'], 1);	//添加充值记录
		$statusArray['calculate_status'] = 1;
		$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$statusArray,'UPDATE',"id = ".$id); //更新状态
	}
	//提现 投资需要冻结资金，提现成功需要解冻并减去资金
	public function selectAll(){
/*  		if(time()>=1492185600){//4.15 0点停止
			die; die; die;
		}   */
 
		$this->startTrans(); //开启事务
		$SendInfo = $GLOBALS['db']->getRow("SELECT page,date FROM ".DB_PREFIX."calculate_modify where  id=1 FOR UPDATE");
		if(empty($SendInfo['date'])){
			$this->abort=1;//中止本批数据写入
			$this->rollback(); //回滚并中止本次操作
			//echo "<script>window.close();</script>"; 
			die;
		}
		//ini_set('max_execution_time', '300');  
		$pageNum = 500;
		//$SendInfo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."calculate_modify where id=1"); //当前日期
		$StartTime = strtotime($SendInfo['date']); //当前处理日期
		$Page =$SendInfo['page']; //当前日期到达页数  以上两个值均做成功后增加
		$EndTime = strtotime($SendInfo['date']) + 86400; //后一天
 
		if($StartTime>1495209600){//5.13 0:0:0 停止
			                 
			echo "<script>window.close();</script>"; 
			die; die; die;
		}  
/* 		if($EndTime>=strtotime('2017-04-17')){
			echo "<script>window.close();</script>"; //只算到4月1号
			die;
		} */
		
		$sql = "select * from 
		(select * from (select 1 as tag,id, create_time as timestamps from `jctp2p_deal_load` where  $StartTime <= create_time and  $EndTime > create_time and user_id in(".implode(',',$this->rubbishUser).")   order by create_time asc,trade_id10 asc) as invest 
		 UNION ALL
		 select * from (select 2 as tag,id, repay_time as timestamps  from `jctp2p_deal_load_repay`  where $StartTime <= repay_time and $EndTime > repay_time   and user_id in(".implode(',',$this->rubbishUser).") order by earn_id10 asc) as repay
		UNION ALL
		select * from (select 3 as tag,id,pay_time as timestamps from `jctp2p_payment_notice` where $StartTime <= pay_time  and  $EndTime > pay_time  and pay_time>0  and  is_paid=1  and user_id in(".implode(',',$this->rubbishUser).") order by pay_time asc,id asc ) as recharge  
		UNION ALL
		select * from (select 4 as tag,id,create_time as timestamps from `jctp2p_user_carry`  where $StartTime <= create_time  and $EndTime > create_time  and user_id in(".implode(',',$this->rubbishUser).")  order by create_time asc,id asc) as apply
		UNION ALL
		select * from (select 5 as tag,id,first_verify_time as timestamps from `jctp2p_user_carry`  where $StartTime <= first_verify_time  and $EndTime > first_verify_time and first_verify_time>0  and user_id in(".implode(',',$this->rubbishUser).") order by first_verify_time asc,id asc) as first 
		UNION ALL
		select * from (select 6 as tag,id,second_verify_time as timestamps from `jctp2p_user_carry`  where $StartTime <= second_verify_time  and $EndTime > second_verify_time and second_verify_time>0  and user_id in(".implode(',',$this->rubbishUser).")  order by second_verify_time asc,id asc) as second 
		UNION ALL
		select * from (select 7 as tag,id,third_verify_time as timestamps from `jctp2p_user_carry`  where $StartTime <= third_verify_time  and  $EndTime > third_verify_time and third_verify_time>0    and user_id in(".implode(',',$this->rubbishUser).") order by third_verify_time asc,id asc) as third
		UNION ALL
		select * from (select 8 as tag,id,get_interest_time as timestamps from `jctp2p_taste_cash` where  get_interest_status =1  and $StartTime <= get_interest_time  and $EndTime > get_interest_time and  get_interest_time>0  and user_id in(".implode(',',$this->rubbishUser).") order by get_interest_time asc,id asc) as tc  
		UNION ALL
		select * from (select 9 as tag,id,addtime as timestamps from `jctp2p_xianjin10` where $StartTime <= addtime  and $EndTime > addtime  and user_id in(".implode(',',$this->rubbishUser).")  order by addtime asc,id asc) as xianjin
		) 
		as allinfo
		where timestamps>0 
		order by timestamps asc,tag asc,id  asc  
		limit ".$Page*$pageNum.",".$pageNum;
		$list = $GLOBALS['db']->getAll($sql);                                              
		//哥的心都碎了！！！！！！！！！！！！！！！！！！！！！！
		//echo $sql;
		//print_r($list); die;
		foreach($list as $key=>$value){
			//echo $value['tag'].'=====';
    		switch($value['tag']){
				case 1:
					$this->dealload($value['id']);//处理投资
				break; 
				case 2:
					$this->dealLoadRepay($value['id']);//处理还款 单独处理
				break;
				case 3:
					$this->recharge($value['id']);//处理充值
				break;
				case 4:
					$this->cashApply($value['id']);//提现处理 资金冻结阶段
				break;
				case 5:
					$this->cashfirst($value['id']); //初审阶段 可能出现被驳回情况
				break;
				case 6:
					$this->cashsecond($value['id']);  //复审阶段 可能出现被驳回情况
				break;
				case 7:
					$this->cashthird($value['id']); //打款操作 可能出现被驳回情况
				break;
				case 8:
					$this->tasteCash($value['id']);//处理体验金收益
				break;	
				case 9:
					$this->xianjin($value['id']);//现金处理
				break;	
			}  
		}  
		
		if($this->abortc==0){
			
			$SendInfoNew = $GLOBALS['db']->getRow("SELECT page,date FROM ".DB_PREFIX."calculate_modify where  id=1"); //乐观锁，如果被改过，放弃本次操作
			if($SendInfo['date']!=$SendInfoNew['date']||$SendInfo['page']!=$SendInfoNew['page']){ //两个字段数据必须完全一致，不一致则被其它的事务改过了，回滚数据
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
				echo "<script>window.close();</script>"; 
				die;
			}
			$pageResult = false;
			//更新页数
			if(count($list)==$pageNum){ //  记录完整页
				$NextPage = $Page+1;
				$pageResult = $GLOBALS['db']->autoExecute(DB_PREFIX."calculate_modify",array('page'=>$NextPage),'UPDATE'," id=1 "); //保存已发送完成的页数
			}else{
				$pageResult = $GLOBALS['db']->autoExecute(DB_PREFIX."calculate_modify",array('page'=>0, 'date'=>date("Y-m-d", $EndTime)),'UPDATE'," id=1 ");//当前日期发送完成 页数置0 日期加1天
			}
			
			if(!$pageResult){
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
				echo "<script>window.close();</script>"; 
				die;
			}
			
			$this->commit(); //提交本次数据
			echo "<script>window.close();</script>"; 
			die;
 		}else{
			echo "<script>window.close();</script>"; 
			die;
		}
		die; exit; 
	}
	
	//现金处理
	public function xianjin($id){
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."xianjin10 where id=".$id); //获取当前需要处理的充值记录
		if($infos['calculate_status']){
			return;
		}
		
		$data['money'] = $infos['money'];
		$data['create_time'] = $infos['addtime'];
		if(in_array($infos['user_id'],$this->rubbishUser)){
			$this->modify_account($data, $infos['user_id'], $infos['remark'], 1);	//添加充值记录
			$statusArray['calculate_status'] = 1;
			$xianjinResult = $GLOBALS['db']->autoExecute(DB_PREFIX."xianjin10",$statusArray,'UPDATE',"id = ".$id); //更新状态
			$NewStatusInfo = $GLOBALS['db']->getRow("SELECT calculate_status FROM ".DB_PREFIX."xianjin10 where  id=".$id); //乐观锁，如果未被改过，放弃本次操作
			if($xianjinResult&&$NewStatusInfo['calculate_status']){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		

	}	
  
	//充值处理       充值需要添加用户资金记录 更新用户可用资金 更新当前数据状态共三个操作
	public function recharge($id){
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id=".$id); //获取当前需要处理的充值记录
		if($infos['calculate_status']){
			return;
		}
		$data['money'] = $infos['money'];
		$data['create_time'] = $infos['pay_time'];

		if(in_array($infos['user_id'],$this->rubbishUser)){
			$this->modify_account($data, $infos['user_id'], $infos['outer_notice_sn'], 1);	//添加充值记录
			$statusArray['calculate_status'] = 1;
			$recahrgeResult = $GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$statusArray,'UPDATE',"id = ".$id); //更新状态
			$NewStatusInfo = $GLOBALS['db']->getRow("SELECT calculate_status FROM ".DB_PREFIX."payment_notice where  id=".$id); //乐观锁，如果未被改过，放弃本次操作
			if($recahrgeResult&&$NewStatusInfo['calculate_status']){
				$this->writeTimes++;
			}else{;
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}
		
		

	}
	//投资记录处理  需要处理资金变化及冻结
	public function dealload($id){
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load where id=".$id);  
		if($infos['calculate_status']){
			return;
		} 		
		$dealinfos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id=".$infos['deal_id']);  
		$data['money'] = $infos['money'];
		$data['total_money'] = $infos['total_money'];
		$data['create_time'] = $infos['create_time'];
		if($data['money']<$data['total_money']){//处理虚拟货币
			$dataVirtual['deal_id'] = $infos['deal_id'];
			//$dataVirtual['from_deal_load_id'] = $infos['load_id'];
			//$dataVirtual['red_money'] = $infos['total_money'] - $infos['money'];
			$dataVirtual['money'] = $infos['total_money'] - $infos['money']; //增加总额
			$dataVirtual['create_time'] = $infos['create_time'];
			$msg = "投资获得虚拟货币转现金".$dataVirtual['money']."元";
			 
			if(in_array($infos['user_id'],$this->rubbishUser)){
				$this->modify_account($dataVirtual, $infos['user_id'], $msg, 29);	//添加虚拟货币转换记录
			}
		}
		$data['deal_id'] = $infos['deal_id'];
		$data['money'] = -$infos['total_money']; //资金减少---------------------
		$data['lock_money'] = $infos['total_money']; //冻结步骤+++++++++++++++++++
		
		$msg = '[<a href="/index.php?ctl=deal&id='.$infos['deal_id'].'" target="_blank">'.$dealinfos['name'].'</a>]的投资';

		if(in_array($infos['user_id'],$this->rubbishUser)){
			$this->modify_account($data, $infos['user_id'], $msg, 2);	//添加投资资金记录
			$statusArray['calculate_status'] = 1;
			$dealloadResult = $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$statusArray,'UPDATE',"id = ".$id); //更新状态
			$NewStatusInfo = $GLOBALS['db']->getRow("SELECT calculate_status FROM ".DB_PREFIX."deal_load where  id=".$id); //乐观锁，如果未被改过，放弃本次操作
			if($dealloadResult&&$NewStatusInfo['calculate_status']){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}

	}
	
	//还款处理
	public function dealLoadRepay($id){
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load_repay where id=".$id);  
		if($infos['calculate_status']){
			return;
		}
		
		if($infos['self_money']>0){ //还本还息才需要解冻投资资金
			$data['lock_money'] = -$infos['self_money']; //解冻步骤  投资资金+虚拟货币---------------------
		}
 
		$data['money'] = $infos['repay_money']; //资金增加++++++++++++++++++++++
		$data['create_time'] = $infos['repay_time']; //还款时间
		$data['brief'] = $infos['virtual_info']; //虚拟货币消息
		$data['deal_id'] = $infos['deal_id'];
		$data['load_repay_id'] = $infos['load_repay_id'];
		$data['load_id'] = $infos['load_id'];
		$msg = $infos['self_money']>0?"还本还息":"还息";
		

		if(in_array($infos['user_id'],$this->rubbishUser)){
			$this->modify_account($data, $infos['user_id'], $msg, 5, $data['brief']);	//添加资金记录
			$statusArray['calculate_status'] = 1;
			$statusArray['has_repay'] = 1; //设置已还款标志
			$dealloadRepayResult = $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$statusArray,'UPDATE',"id = ".$id); //更新状态
			$NewStatusInfo = $GLOBALS['db']->getRow("SELECT calculate_status FROM ".DB_PREFIX."deal_load_repay where  id=".$id); //乐观锁，如果未被改过，放弃本次操作
			if($dealloadRepayResult&&$NewStatusInfo['calculate_status']){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}

	}	
	//体验金处理
	public function tasteCash($id){
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."taste_cash where id=".$id);  //体验金获取
		if($infos['calculate_status']){
			return;
		}
		 
		$data['money'] = $infos['interest']; //资金增加++++++++++++++++++++++
		$data['create_time'] = $infos['get_interest_time']; //领取体验金收益时间
		$msg = "领取体验金收益,体验金ID:".$infos['id'];
		if(in_array($infos['user_id'],$this->rubbishUser)){
			$this->modify_account($data, $infos['user_id'], $msg, 47);	//添加资金记录
			$statusArray['calculate_status'] = 1;
			$tasteCashResult = $GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash",$statusArray,'UPDATE',"id = ".$id); //更新状态
			$NewStatusInfo = $GLOBALS['db']->getRow("SELECT calculate_status FROM ".DB_PREFIX."taste_cash where  id=".$id); //乐观锁，如果未被改过，放弃本次操作
			if($tasteCashResult&&$NewStatusInfo['calculate_status']){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}

	}
	//提现处理 此记录一定会出现所以不做其它处理
	public function cashApply($id){
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_carry where id=".$id); //提现处理 资金冻结阶段
		if($infos['calculate_status_apply']){
			return;
		}

	
		if(in_array($infos['user_id'],$this->rubbishUser)){
			$data['money'] = -$infos['money']; //资金减少---------------------
			$data['lock_money'] = $infos['money']; //冻结步骤+++++++++++++++++++
			$msg = "提现申请,提现ID为".$infos['id'];
			$data['create_time'] = $infos['create_time'];
			$this->modify_account($data, $infos['user_id'], $msg, 8);	//添加资金记录
			
			//提现驳回操作 因初审驳回的first_verify_time=0所以必须在此处验证是否驳回
			if($infos['status']==2&&$infos['first_verify_time']==0){//驳回
				$datas['money'] = $infos['money']; //资金增加++++++++++++++++++
				$datas['lock_money'] = -$infos['money']; //解冻步骤---------------
				$msg = "提现初审拒绝,提现ID为".$infos['id'];
				$datas['create_time'] = $infos['create_time'];	//驳回时间	
				$this->modify_account($datas, $infos['user_id'], $msg, 8);	//添加资金记录			 状态===========================
				 
			} 
			$statusArray['calculate_status_apply'] = 1;
			$cashApplyResult = $GLOBALS['db']->autoExecute(DB_PREFIX."user_carry",$statusArray,'UPDATE',"id = ".$id); //更新状态
			$NewStatusInfo = $GLOBALS['db']->getRow("SELECT calculate_status_apply FROM ".DB_PREFIX."user_carry where  id=".$id); //乐观锁，如果未被改过，放弃本次操作
			if($cashApplyResult&&$NewStatusInfo['calculate_status_apply']){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}  	


	}
	//提现处理  初审
	public function cashfirst($id){
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_carry where id=".$id); //提现处理 资金冻结阶段
		if($infos['calculate_status_first']){
			return;
		}
		//提现驳回操作  初审阶段要判断是否为复审驳回
		if($infos['status']==2&&$infos['second_verify_time']==0){//驳回
			$data['money'] = $infos['money']; //资金增加++++++++++++++++++
			$data['lock_money'] = -$infos['money']; //解冻步骤---------------
			$msg = "提现复审拒绝,提现ID为".$infos['id'];
			$data['create_time'] = $infos['first_verify_time'];	//驳回时间	
			
			if(in_array($infos['user_id'],$this->rubbishUser)){
				$this->modify_account($data, $infos['user_id'], $msg, 8);	//添加资金记录			 状态===========================
				$statusArray['calculate_status_first'] = 1;
				$cashfirstResult = $GLOBALS['db']->autoExecute(DB_PREFIX."user_carry",$statusArray,'UPDATE',"id = ".$id); //更新状态
				$NewStatusInfo = $GLOBALS['db']->getRow("SELECT calculate_status_first FROM ".DB_PREFIX."user_carry where  id=".$id); //乐观锁，如果未被改过，放弃本次操作
				if($cashfirstResult&&$NewStatusInfo['calculate_status_first']){
					$this->writeTimes++;
				}else{
					$this->abort=1;//中止本批数据写入
					$this->rollback(); //回滚并中止本次操作
				}
			}
		}	
		

	}
	//提现处理 复审
	public function cashsecond($id){
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_carry where id=".$id); //提现处理 资金冻结阶段
		if($infos['calculate_status_second']){
			return;
		}
		//提现驳回操作 复审阶段要判断是否为打款阶段驳回
		if($infos['status']==2&&$infos['third_verify_time']==0){//驳回
			$data['money'] = $infos['money']; //资金增加++++++++++++++++++
			$data['lock_money'] = -$infos['money']; //解冻步骤---------------
			$msg = "提现打款拒绝,提现ID为".$infos['id'];
			$data['create_time'] = $infos['second_verify_time'];	//驳回时间	
			
			
			if(in_array($infos['user_id'],$this->rubbishUser)){
				$this->modify_account($data, $infos['user_id'], $msg, 8);	//添加资金记录			 状态===========================
				$statusArray['calculate_status_second'] = 1;
				$cashsecondResult = $GLOBALS['db']->autoExecute(DB_PREFIX."user_carry",$statusArray,'UPDATE',"id = ".$id); //更新状态
				$NewStatusInfo = $GLOBALS['db']->getRow("SELECT calculate_status_second FROM ".DB_PREFIX."user_carry where  id=".$id); //乐观锁，如果未被改过，放弃本次操作
				if($cashsecondResult&&$NewStatusInfo['calculate_status_second']){
					$this->writeTimes++;
				}else{
					$this->abort=1;//中止本批数据写入
					$this->rollback(); //回滚并中止本次操作
				}
			}
		}
		

	}
	//提现处理 打款
	public function cashthird($id){
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_carry where id=".$id); //提现处理 资金冻结阶段
		if($infos['calculate_status_third']){
			return;
		}

		if($infos['status']==1){//打款操作
			$data['lock_money'] = -$infos['money']; //解冻步骤---------------
			$msg = "提现成功,提现ID为".$infos['id']."提现手续费:".$infos['fee'];
			$data['create_time'] = $infos['third_verify_time'];	//打款时间	
			
			
			if(in_array($infos['user_id'],$this->rubbishUser)){
				$this->modify_account($data, $infos['user_id'], $msg, 8);	//添加资金记录			 状态===========================
			}
			$user_info = get_user_info("*","id=".$infos['user_id']);
			$money_log_info['memo'] = $msg;
			$money_log_info['brief'] = '提现成功';
			$money_log_info['money'] = $infos['money'];
			$money_log_info['account_money'] = $user_info['money'];
			$money_log_info['user_id'] = $infos['user_id'];
			$money_log_info['create_time'] = $data['create_time'];
			$money_log_info['create_time_ymd'] = date("Y-m-d", $data['create_time']);
			$money_log_info['create_time_ym'] = date("Ym", $data['create_time']);
			$money_log_info['create_time_y'] = date("Y", $data['create_time']);
			$money_log_info['type'] = 8;
			
			if(in_array($infos['user_id'],$this->rubbishUser)){
				$cashthirdLogResult = $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$money_log_info);
			}
			
			if($cashthirdLogResult){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		}else if($infos['status']==0&&$infos['third_verify_remark']==''){
			//提现驳回操作 复审阶段要判断是否为打款阶段驳回
		 
			$data['money'] = $infos['money']; //资金增加++++++++++++++++++
			$data['lock_money'] = -$infos['money']; //解冻步骤---------------
			$msg = "提现打款拒绝,提现ID为".$infos['id'];
			$data['create_time'] = $infos['third_verify_time'];	//驳回时间	
			if(in_array($infos['user_id'],$this->rubbishUser)){
				$this->modify_account($data, $infos['user_id'], $msg, 8);	//添加资金记录			 状态===========================
			}
			 
		}
		
		$statusArray['calculate_status_third'] = 1;
		$cashthirdResult = $GLOBALS['db']->autoExecute(DB_PREFIX."user_carry",$statusArray,'UPDATE',"id = ".$id); //更新状态
		$NewStatusInfo = $GLOBALS['db']->getRow("SELECT calculate_status_third FROM ".DB_PREFIX."user_carry where  id=".$id); //乐观锁，如果未被改过，放弃本次操作
		if($cashthirdResult&&$NewStatusInfo['calculate_status_third']){
			$this->writeTimes++;
		}else{
			$this->abort=1;//中止本批数据写入
			$this->rollback(); //回滚并中止本次操作
		}
	}
 
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
 	//还款处理
	public function dealLoadRepayCurrentDayOne($id){
		die;
		$infos = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load_repay where id=".$id);  
		if($infos['calculate_status']){
			return true;//当作已成功
		}
		
		if($infos['self_money']>0){ //还本还息才需要解冻投资资金
			$data['lock_money'] = -$infos['self_money']; //解冻步骤  投资资金+虚拟货币---------------------
		}
 
		$data['money'] = $infos['repay_money']; //资金增加++++++++++++++++++++++
		$data['create_time'] = $infos['repay_time']; //还款时间
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
	public function dealLoadRepayCurrentDay(){
		die;
 		ini_set('max_execution_time', '0'); //防止数据过多引起超时
		
		$CurrentDateTime = strtotime(date('Y-m-d',time())); //当前处理日期
		$NextDateTime = $CurrentDateTime + 86400; //后一天0点时间戳
		if($NextDateTime>=strtotime('2017-04-02')){ //只算到x月x号   一定注意此处时间，想好了再改。搞错就等死吧
			echo "<script>window.close();</script>"; 
			die;
		}
		$this->startTrans(); //开启事务
		$RepayList = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load_repay where calculate_status=0 and repay_time => $CurrentDateTime and repay_time < $NextDateTime order by id asc");
		foreach($RepayList as $key=>$info){
			$resultRepay = $this->dealLoadRepayCurrentDayOne($info['id']);
			if($resultRepay){
				$this->writeTimes++;
			}else{
				$this->abort=1;//中止本批数据写入
				$this->rollback(); //回滚并中止本次操作
			}
		} 
		$this->commit(); //提交事务
	}
	
	public function clearAll(){
		die;
		$GLOBALS['db']->getAll("truncate table jctp2p_user_point_log");//清user_point_log
		$GLOBALS['db']->getAll("truncate table jctp2p_user_lock_money_log");//清user_lock_money_log
		$GLOBALS['db']->getAll("truncate table jctp2p_user_score_log");//清user_score_log
		$GLOBALS['db']->getAll("truncate table jctp2p_site_money_log");//清site_money_log
		$GLOBALS['db']->getAll("truncate table jctp2p_user_money_log");//清user_money_log
		$GLOBALS['db']->getAll("truncate table jctp2p_user_log");//清user_log
		
		
		$GLOBALS['db']->getAll("update jctp2p_user set money_encrypt = AES_ENCRYPT(0, '".AES_DECRYPT_KEY."') ");
		$GLOBALS['db']->getAll("update jctp2p_user set score=0");
		$GLOBALS['db']->getAll("update jctp2p_user set point=0");
		$GLOBALS['db']->getAll("update jctp2p_user set mortgage_money=0");
		$GLOBALS['db']->getAll("update jctp2p_user set lock_money=0");
		$GLOBALS['db']->getAll("update jctp2p_deal_load set calculate_status=0");
		$GLOBALS['db']->getAll("update jctp2p_deal_load_repay set calculate_status=0");
		$GLOBALS['db']->getAll("update jctp2p_payment_notice set calculate_status=0");
		$GLOBALS['db']->getAll("update jctp2p_user_carry set calculate_status_apply=0");
		$GLOBALS['db']->getAll("update jctp2p_user_carry set calculate_status_first=0");
		$GLOBALS['db']->getAll("update jctp2p_user_carry set calculate_status_second=0");
		$GLOBALS['db']->getAll("update jctp2p_user_carry set calculate_status_third=0");
		$GLOBALS['db']->getAll("update jctp2p_taste_cash set calculate_status=0");
		
		
		
		
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
 
 function plan_dobid(){
	  $redis = new Redis();
	  $redis->connect(REDIS_HOST, REDIS_PORT);
      $redis->auth(REDIS_PWD);
      $redis->select(6);
	  while($plan_load = json_decode($redis->lpop("plan_load"),true)){//出队列
		  $plan_totalmoney = $plan_load['total_money'];
		  $plan_redmoney =$plan_load['red_money'];
		  $deal_all = $GLOBALS['db']->getAll('select borrow_amount,interest_rate,load_money,repay_time_type,repay_time,loantype,old_deal_id,rate,id from '.DB_PREFIX.'deal where plan_id='.$plan_load['plan_id'].' and deal_status=1 order by id asc');
		  foreach ($deal_all as $key => $value) {
			  //$deal_ids[]=$value['id'];
			  $need_money = $value['borrow_amount'] - $value['load_money'];//子标的可投金额
			  if ($need_money > 0) {
					
					$bidinfo[$key]['plan_total_money'] = $plan_load['total_money'];
					$bidinfo[$key]['plan_red_money'] = $plan_load['red'];
					$bidinfo[$key]['deal_id'] = $value['id']; //子标的ID
					if ($plan_load['total_money'] >= $need_money) {
						if ($plan_load['red'] >= $need_money) {
							$plan_load['total_money'] = $plan_load['total_money'] -$need_money;
							$plan_load['red'] = $plan_load['red'] -$need_money;
							$bidinfo[$key]['red_money'] = $need_money;
							$bidinfo[$key]['red_id'] = $plan_load['ecv_id'];
							$deal_total_money = $need_money;
							$bidinfo[$key]['bid_money'] = 0;
							} else { //$map['red_money'] < $need_money
								$plan_load['total_money'] = $plan_load['total_money'] -$need_money;
								$plan_load['money'] = $plan_load['money'] - ($need_money - $plan_load['red']);
								$bidinfo[$key]['bid_money'] = $need_money - $plan_load['red'];
								$bidinfo[$key]['red_money'] = $plan_load['red'];
								$deal_total_money = $need_money;
								if($plan_load['red'] != 0) {
									$bidinfo[$key]['red_id'] = $plan_load['ecv_id'];
								}else{
									$bidinfo[$key]['red_id'] = 0;
								}
								$plan_load['red'] = 0;
									
								}
						} else {
							$bidinfo[$key]['bid_money'] = $plan_load['money'];
							$bidinfo[$key]['red_money'] = $plan_load['red'];
							$deal_total_money = $plan_load['total_money'];
								if ($plan_load['red'] != 0) {
									$bidinfo[$key]['red_id'] = $plan_load['ecv_id'];
								}else{
									$bidinfo[$key]['red_id'] = 0;
								}
								$plan_load['red'] = 0;
								$plan_load['total_money'] = 0;
						}
						if($plan_load['interestrate_id']){
							   $map['interestrate_money'] = get_interestrate_money($plan_load['interestrate_id'],$deal_total_money,$deal['id'],$plan_load['plan_id']); 
						}
						$bidinfo[$key]['interestrate_id'] = $plan_load['interestrate_id'];
						$bidinfo[$key]['interestrate_money'] = $map['interestrate_money'];
						$bidinfo[$key]['plan_id'] = $plan_load['plan_id'] ; //理财计划ID
						$bidinfo[$key]['plan_rate'] = $plan_load['rate']; //理财计划利息
						$bidinfo[$key]['plan_load_id'] = $plan_load['plan_load_id']; //理财计划利息
						$bidinfo[$key]['user_id'] = $plan_load['user_id']; //理财计划利息
						$bidinfo[$key]['user_name'] = $plan_load['user_name']; //理财计划利息
						$total_money = $plan_load['total_money']; 
						if ($plan_load['total_money'] <= 0 ) { 
							   break;
						}
					}
				}
			$plan_map = array_values($bidinfo); ////让数组编号从零开始
			$total_moneys=0;
				//二维数组的首个数组加入理财计划总相关信息
			foreach ($plan_map as $key => $value) {
						$data[$key]['ajax'] = intval($_REQUEST["ajax"]);
						$data[$key]['plan_id'] = intval($value["plan_id"]);
						$data[$key]['deal_id'] = $value['deal_id'];
						$data[$key]['bid_money'] = $value['bid_money'];
						$data[$key]['red_money'] = $value['red_money'];
						$data[$key]['red_id'] = $value['red_id'];
						$data[$key]['interestrate_id'] = $value['interestrate_id'];
						$data[$key]['interestrate_money'] = round($value['interestrate_money'],2);
						$data[$key]['plan_rate'] = $value['plan_rate'];
						$data[$key]['cungaun_tag'] = 1;
						$data[$key]['is_pc'] = 1;
						$data[$key]['repay_time'] = 1;
						$data[$key]['learn_id'] = 0;
						$data[$key]['user_id'] =$value['user_id'];
						$data[$key]['user_name'] =$value['user_name'];
						$data[$key]['plan_load_id'] =$value['plan_load_id'];
						$date = $data[$key];
						$deal_info = $deal_all[$key];
						$total_moneys += $this->plan_dobid2($date,$deal_info);
					}
					if(($total_moneys<$plan_totalmoney)&&$total_moneys>0){
						$money = $plan_totalmoney-$total_moneys;
						$GLOBALS['db']->query('update '.DB_PREFIX.'plan set load_money=load_money-'.$money.',deal_status=1  where id ='.$plan_load['plan_id']);
						$GLOBALS['db']->query('update '.DB_PREFIX.'plan_load set total_money=total_money-'.$money.',money=money-'.$money.' where id ='.$plan_load['plan_load_id']);
					} 
					if(!$total_moneys){
						$GLOBALS['db']->query('update '.DB_PREFIX.'plan set load_money=load_money-'.$plan_totalmoney.',deal_status=1 where id ='.$plan_load['plan_id']);
						$GLOBALS['db']->query('delete from '.DB_PREFIX.'plan_load  where id ='.$plan_load['plan_load_id']);
						$insertdata['cunguan_money'] = 0;
						$insertdata['cunguan_lock_money'] = $total_moneys;
						$insertdata['from_plan_id'] = $plan_load['plan_id'];
						$insertdata['from_plan_load_id'] = $plan_load['plan_load_id'];
						$insertdata['red_money'] = $plan_load['red'];
						$insertdata['cungaun_tag'] = 1;
						$insertdata['user_id'] = $plan_load['user_id'];
						$GLOBALS['db']->autoExecute(DB_PREFIX.'user_money_log',$insertdata,'INSERT');
					}else{
						$interestrate_money = $GLOBALS['db']->getOne('select sum(interestrate_money) from '.DB_PREFIX.'deal_load where user_id='.$plan_load['user_id'].' and plan_load_id ='.$plan_load['plan_load_id']);
						$GLOBALS['db']->query('update '.DB_PREFIX.'plan_load set interestrate_money='.$interestrate_money.' where id ='.$plan_load['plan_load_id']);
						$msg="[<a href='/index.php?ctl=plandeal&id=".$plan_load['plan_id']."' target='_blank'>".$plan_load['name']."</a>]的出借";
						$brief = '出借成功';
						require_once APP_ROOT_PATH."system/libs/user.php";
						$data['cunguan_money'] = -($total_moneys);
						$data['cunguan_lock_money'] = $total_moneys;
						$data['from_plan_id'] = $plan_load['plan_id'];
						$data['from_plan_load_id'] = $plan_load['plan_load_id'];
						$data['red_money'] = $plan_redmoney;
						/* if($plan_load['red']){//是否使用红包
							$data['red_money'] = $plan_load['red'];
							modify_account($data,$plan_load['user_id'],$msg.',使用红包',1);
							unset($data['red_money']);
						} */
						modify_account($data,$plan_load['user_id'],$msg,2,$brief,1);
					}
					
		 }	
		echo "成功";
  }
  
  function plan_dobid2($map,$deal_info){
	$root['status']=0;
    $deal_id = $map['deal_id'];
    $plan_id = $map['plan_id'];
    $bid_money = $map['bid_money'];
    $is_pc = $map['is_pc'];
    $interestrate_id = $map['interestrate_id'];
    $interestrate_money = $map['interestrate_money'];
    $red_id = $map['red_id'];
    $learn_id = $map['learn_id'];
    $red_money = $map['red_money']?$map['red_money']:0;
    $total_money=$red_money+$bid_money;
    $data['user_id'] = $user_info['id']=$map['user_id'];
    $data['user_name'] = $user_info['user_name']=$map['user_name'];
    $data['deal_id'] = $deal_id;
    $data['cunguan_tag']=$map['cunguan_tag'];
    $data['money'] = $bid_money;
    $data['red'] = $red_money;
    $data['red_id'] = $red_id;
    $data['interestrate_id'] = $interestrate_id;
    $data['create_time']=time();
    $data['interestrate_money']=$interestrate_money;
    $data['total_money'] = $bid_money+$red_money;
    $data['add_ip'] = $_SERVER['REMOTE_ADDR'];
    $data['plan_id'] = $plan_id;
    $data['plan_load_id'] = $plan_load_id = $map['plan_load_id'];
    /* $insertdata = return_deal_load_data($data,$GLOBALS['user_info'],$root['deal']);
    $insertdata['cunguan_tag'] = 1;
    // 存管入库的字段判断
    $insertdata['cunguan_tag']=$map['cunguan_tag'];
	$insertdata['interestrate_money']=$map['interestrate_money'];
	$insertdata['increase_interest']=$map['increase_interest'];
	$insertdata['old_deal_id'] = $deal_info['old_deal_id']?$deal_info['old_deal_id']:'';
	$insertdata['debts'] = $deal_info['debts']?2:0;
    $res1 = $GLOBALS['db']->query("update ".DB_PREFIX."deal set load_money = load_money+".$data['total_money']." where id =".$deal_id);
    $res2 = $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$insertdata,"INSERT");
    $load_id = $GLOBALS['db']->insert_id();
    if(intval($deal_info['borrow_amount'])==intval($deal_info['load_money'])+$data['total_money']){
         $GLOBALS['db']->query("update ".DB_PREFIX."deal set success_time = ".$insertdata['create_time'].",deal_status = 2 where id =".$deal_id);  
    } */
    
    // 处理虚拟货币，先充值
    if($red_money>0){
		require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
        $ideal_money=$red_money;
		$deal=new Deal;
		$pub = new Publics();
		$map['xuni_seqno'] = $pub->seqno();
        $status=$deal->deal($map['xuni_seqno'],'T10',$ideal_money,$deal_id,$user_info['id']);
		if($status['respHeader']['respCode']=='P2P0000'){
			$decository['status']=1;
			$res=$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$decository,"UPDATE","seqno='".$map['xuni_seqno']."'");
		}
            
	}
	//if($load_id>0){
		require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
		$deal=new Deal;
		$pub = new Publics();
		$load_seqno = $pub->seqno();
		$loads=$deal->deal($load_seqno,'T01',$total_money,$deal_id,$user_info['id'],true);
		if($loads['respHeader']['respCode']=='P2P0000'){
			$money=$total_money;
			//更改加息卡状态
			if($interestrate_id > 0){
				$GLOBALS['db']->query("UPDATE ".DB_PREFIX."interest_card SET status=1,deal_id =".$plan_id.",deal_load_id =".$plan_load_id." WHERE user_id=".$map['user_id']." and id=".$interestrate_id);
			}
			$insertdata = return_deal_load_data($data,$user_info,$root['deal']);
			$insertdata['cunguan_tag'] = 1;
			$insertdata['interestrate_money']=$map['interestrate_money'];
			$res1 = $GLOBALS['db']->query("update ".DB_PREFIX."deal set load_money = load_money+".$data['total_money']." where id =".$deal_id);
			$res2 = $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$insertdata,"INSERT");
			$load_id = $GLOBALS['db']->insert_id();
			if(intval($deal_info['borrow_amount'])==intval($deal_info['load_money'])+$data['total_money']){
				 $GLOBALS['db']->query("update ".DB_PREFIX."deal set success_time = ".$insertdata['create_time'].",deal_status = 2 where id =".$deal_id);  
			}
			$decository['status']=1;
			$result=$GLOBALS['db']->autoExecute(DB_PREFIX."decository",$decository,"UPDATE","seqno='".$load_seqno."'");
		}else{
			$money = 0;
		}
        $data['load_id'] = $load_id;
        //更改红包状态
        if($red_id){
            $GLOBALS['db']->query("UPDATE ".DB_PREFIX."red_packet SET status=1,deal_id =".$plan_id.",deal_load_id =".$plan_load_id." WHERE user_id=".$map['user_id']." and id in(".$red_id.")");
        }

        
        if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_sta WHERE user_id=".$GLOBALS['user_info']['id']) > 0) {
			$GLOBALS['db']->query('update '.DB_PREFIX.'user_sta set load_count=load_count+1,load_money=load_money+'.$bid_money.' where user_id='.$map['user_id']);
        }else{
            $data_arr['user_id'] = $map['user_id'];
            $data_arr['load_count'] = 1;
            $data_arr['load_money'] = $bid_money;
            $GLOBALS['db']->autoExecute(DB_PREFIX."user_sta",$data_arr,"INSERT");
        }
        
        
        
	//}
	
	return $money;
	
}
 
 
}
?>