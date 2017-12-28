<?php
//define(ACTION_NAME,"user");
//define(ACTN,"login_reg");
//define(MODULE_NAMEN,"index");
class usercollectModule extends SiteBaseModule
{
	
	
	//业务员信息
	public function staffcollect(){
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}		
		$data['staff_status'] = $_POST['status'];
		$data['staff_invite_id'] = $_POST['staff_id'];
		$data['staff_employee_number'] = $_POST['employee_number'];
		$data['is_stuff'] = 1;
		$list = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,'UPDATE',"idno = ".$_POST['card_num']); //更新数据
		if($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
	}
	
	//红包余额
	public function hbyueecollect(){
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}		
		$data['red_money'] = $_POST['red_package'];
		$list = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,'UPDATE',"user_id_1_0 = ".$_POST['user_id']); //更新数据
		if($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
	}
	
	
 
	//体验金批量
	public function tastecashcollect(){
		die;
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}		
		$userifo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_id_1_0='".$_POST['user_id']."'"); 
		$data['user_id'] = $userifo['id']; //new user_id
		$data['taste_cash_id'] = $_POST['taste_cash_id'];
		$data['disc'] = $_POST['disc'];
		$data['money'] = $_POST['money'];
		$data['interest'] = $_POST['interest'];
		$data['end_time'] = $_POST['end_time'];
		$data['create_time'] = $_POST['create_time'];
		$data['use_time'] = $_POST['use_time'];
		$data['use_status'] = $_POST['use_status'];
		$data['get_interest_time'] = $_POST['get_interest_time'];
		$data['get_interest_status'] = $_POST['get_interest_status'];
		$data['get_way'] = $_POST['get_way'];
		$data['is_use'] = $_POST['is_use'];
		$data['taste_cash_id10'] = $_POST['id'];
		$um = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."taste_cash where taste_cash_id10='".$_POST['id']."'");
		if(!empty($um)){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash",$data,'UPDATE',"taste_cash_id10 = ".$_POST['id']); //存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash",$data,"INSERT"); //不存在则插入
		}
		if($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
	}	
	//体验金批量
	public function tastecashlogcollect(){
		die;
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}		
		$userifo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_id_1_0='".$_POST['user_id']."'"); 
		$data['user_id'] = $userifo['id']; //new user_id
		//$taste_cash_id = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."taste_cash where taste_cash_id10='".$_POST['id']."'");
		//$data['taste_id'] = $taste_cash_id['id'];
		$data['taste_cash_id'] = $_POST['taste_cash_id'];
		$data['create_time'] = $_POST['create_time'];
		$data['change'] = $_POST['change'];
		$data['device'] = $_POST['device'];
		$data['detail'] = $_POST['detail'];
		$data['ip'] = $_POST['ip'];
		$data['taste_cash_log_id10'] = $_POST['id'];
		$um = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."taste_cash_log where taste_cash_log_id10='".$_POST['id']."'");
		if(!empty($um)){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash_log",$data,'UPDATE',"taste_cash_log_id10 = ".$_POST['id']); //存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash_log",$data,"INSERT"); //不存在则插入
		}
		if($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
	}
	//红包批量
	public function hongbaocollect(){
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}
		
		$TagName = strpos($_POST['content'], "代金券");  
		$TagName = $TagName!=false?$TagName:strpos($_POST['content'], "现金券");  
		if($TagName!=false){
			$return['status'] = 0;
			$return['msg'] = "数据为代金券，丢弃当前数据";
			ajax_return($return); 
		}
		$userifo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_id_1_0='".$_POST['user_id']."'"); 
		$data['user_id'] = $userifo['id']; //new user_id
		$data['award_log_id10'] = $_POST['id']; // ID
		$data['memo'] = $_POST['content'].$TagName; // memo
		$data['money'] = $_POST['award_value']; // award_value
		$data['create_time'] = $_POST['addtime']; // create_time 
		$data['create_time_ymd'] = date('Y-m-d',$_POST['addtime']); // create_time
		$data['create_time_ym'] = date('Y-m',$_POST['addtime']); // create_time
		$data['create_time_y'] = date('Y',$_POST['addtime']); // create_time
		$data['activity_id'] =$_POST['activity_id']; // activity_id
		$um = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."user_red_money_log where award_log_id10='".$_POST['id']."'");
		if(!empty($um)){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."user_red_money_log",$data,'UPDATE',"award_log_id10 = ".$_POST['id']); //存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."user_red_money_log",$data,"INSERT"); //不存在则插入
		}
		if($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
	}
	

	//代金券批量
	public function daijinquancollect(){
		die;
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}
		$data['award_log_id10'] = $_POST['id']; // ID
		$data['user_id10'] = $_POST['user_id']; //user_id
		$data['sn'] = $_POST['id']; //sn
		
		$userifo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_id_1_0='".$_POST['user_id']."'"); 
		$data['user_id'] = $userifo['id']; //new user_id
		$data['begin_time'] = $_POST['addtime'];   //实际上没法对应这些鬼。
		$data['add_time'] = $_POST['addtime'];   //实际上没法对应这些鬼。
		$data['end_time'] = $_POST['endtime'];  
		$data['deltime'] = $_POST['deltime'];  
		$data['reissue'] = $_POST['reissue'];  
		$data['activity_id'] = $_POST['activity_id'];  //无法做转换 只做标记 
		$data['content'] = $_POST['content'];  
		$data['money'] = $_POST['award_value'];  
		$userifoiii = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_id_1_0='".$_POST['userfriend_id']."'"); 
		$data['userfriend_id'] = $userifoiii['id'];  
 
		if($_POST['status']==0){
			$data['status'] = 2;  //邀请好友未实名
		}else if($_POST['status']==1){
			$data['status'] = 0;  //未使用
		}else if($_POST['status']==2){
			$data['status'] = 1;  //已使用
		}else if($_POST['status']==3){
			$data['status'] = 0;  // 未使用,但已过期  deltime不为空
		}
		
		$ecv = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."ecv where award_log_id10='".$_POST['id']."'");
		if(!empty($ecv)){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$data,'UPDATE',"award_log_id10 = ".$_POST['id']); //存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$data,"INSERT"); //不存在则插入
		}
		if($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
	}
	

	//收益金批量
	public function earncollect(){
		die;
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}
		$tradeifo = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."deal_load where trade_id10='".$_POST['trade_id']."'");  
		$dealifo = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."deal where pid_1_0='".$_POST['product_id']."'");  
		$userifo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_id_1_0='".$_POST['user_id']."'"); 
		$data['deal_id'] = $dealifo['id']; //项目ID
		$data['user_id'] = $userifo['id']; //用户ID
		$data['user_id10'] = $_POST['user_id']; //1.0用户ID
		$data['product_id10'] = $_POST['product_id']; // product_id
		$data['load_id'] = $tradeifo['id']; //dealID
		$data['earn_id10'] = $_POST['id']; //earn_id10
		$data['repay_time'] = strtotime($_POST['earn_date']); //add_time
		$data['repay_date'] = $_POST['earn_date']; //earn_date
		$data['true_repay_time'] = $_POST['update_time']; //add_time
		$data['true_repay_date'] = $_POST['update_date']; //earn_date
		
	 
		$data['virtual_info'] = $_POST['virtual_info']; //virtual_info
		
		
		$data['interest_money'] = $_POST['earn_money']; //earn_money
		$data['self_money'] = $_POST['original_money']; //original_money
		$data['repay_money'] = $_POST['original_money'] + $_POST['earn_money']; //总还款金额
		$data['true_self_money'] = $_POST['original_money']; //original_money
		$data['true_repay_money'] = $_POST['original_money'] + $_POST['earn_money']; //总还款金额
  
		if($_POST['status']){ //已还款  1.0只有 1 0两个状态 
			$data['status'] = $_POST['status']; // 0提前，1准时，2逾期，3严重逾期
		}else{
			$data['status'] = 0;
		}
 
		$deal_load_repay_id = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."deal_load_repay where earn_id10='".$_POST['id']."'");
		if(!empty($deal_load_repay_id)){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$data,'UPDATE',"earn_id10 = ".$_POST['id']); //存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$data,"INSERT"); //不存在则插入
		}
		if($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
	}


	//现金批量
	public function xianjincollect(){
		die;
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}
 
		$userifo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_id_1_0=".$_POST['user_id']); 
		$xianjinifo['user_id10'] = $_POST['user_id'];  
		$xianjinifo['user_id'] = $userifo['id'];  
		$xianjinifo['money'] = $_POST['money'];  
		$xianjinifo['id10'] = $_POST['id'];  
		$xianjinifo['remark'] = $_POST['remark'];  
		$xianjinifo['addtime'] = strtotime($_POST['created_time']);  

		$xianjinid = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."xianjin10 where id10='".$_POST['id']."'");  
		if(!empty($xianjinid)){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."xianjin10",$xianjinifo,'UPDATE',"id10 = ".$_POST['id']); //存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."xianjin10",$xianjinifo,"INSERT"); //不存在则插入
		}
		
		if($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
		
		
 
	}
	//投资批量
	public function tradecollect(){
		die;
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}
		
		$dealifo = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."deal where pid_1_0='".$_POST['product_id']."'");  
		$userifo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_id_1_0='".$_POST['user_id']."'"); 
		$tradeInfo['trade_id10'] = $_POST['id']; //1.0trade_id
		$tradeInfo['add_ip'] = $_POST['add_ip']; //add_ip
		$tradeInfo['deal_id'] = $dealifo['id']; //项目ID
		$tradeInfo['user_id'] = $userifo['id']; //用户ID
		$tradeInfo['user_name'] = $userifo['user_name']; //用户名
		$tradeInfo['money'] = $_POST['user_money']; //投资金额
		$tradeInfo['total_money'] = $_POST['total_money']; //投资金额+虚拟货币
		$tradeInfo['create_time'] = $_POST['add_time'];
		$tradeInfo['is_has_loans'] = 1; //是否已经放款给招标人
		$tradeInfo['create_date'] = date("Y-m-d",$tradeInfo['create_time']); 
		//$tradeInfo['red'] = $_POST['user_id'];  //红包金额 
		//$tradeInfo['ecv_id'] = $_POST['user_id'];  //使用的代金券的ID
		$deal_load_id = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."deal_load where trade_id10='".$tradeInfo['trade_id10']."'");
		if(!empty($deal_load_id)){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$tradeInfo,'UPDATE',"trade_id10 = ".$tradeInfo['trade_id10']); //存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$tradeInfo,"INSERT"); //不存在则插入
		}
		
		if($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
		
		
 
	}
	
	//充值批量   by 1010
	public function rechargecollect(){
		die;
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return);
		}

		$datas['recharge_id10'] = $_POST['id'];
		$datas['notice_sn'] = $_POST['order_no'];
		$datas['money'] = $_POST['recharge_money'];
		//支付方式映射关系
		if($_POST['payment_type']==1){ //连连支付
			$datas['payment_id']=29;
		}else if($_POST['payment_type']==2){ //网银在线
			$datas['payment_id']=3;
		}else if($_POST['payment_type']==3){ //WEB连连支付
			$datas['payment_id']=29;
		}else if($_POST['payment_type']==4){ //汇付宝网银支付
			$datas['payment_id']=30;
		}else if($_POST['payment_type']==5){ //汇付宝网银支付
			$datas['payment_id']=30;
		}else if($_POST['payment_type']==6){ //畅捷网银支付
			$datas['payment_id']=31;
		}else if($_POST['payment_type']==7){ //宝付支付
			$datas['payment_id']=26;
		}else if($_POST['payment_type']==8){ //宝付支付APP
			$datas['payment_id']=28;
		}else if($_POST['payment_type']==9){ //宝付支付WAP
			$datas['payment_id']=27;
		}
 
		$user_new = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."user where user_id_1_0='".$_POST['user_id']."'");
		$datas['user_id'] =  $user_new['id']; // new user_id
		$datas['is_paid'] = $_POST['payment_status'];
		//$datas['phone'] = $_POST['ui_phone'];
		$datas['bankcard'] = $_POST['bankcard'];
		$datas['bank_id'] = $_POST['bankcard'];
		$datas['create_time'] = $_POST['addtime'];
		$datas['create_date'] = date('Y-m-d',$_POST['addtime']);
		
 
		$datas['pay_time'] = $_POST['verifytime'];
		$datas['pay_date'] = date('Y-m-d H:i:s', $_POST['verifytime']);
		
		$datas['addip'] = $_POST['addip'];
		$datas['outer_notice_sn'] = $_POST['remark']."-".$_POST['verifyremark'];
		$recharge_id = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."payment_notice where recharge_id10=".$datas['recharge_id10']);
		//$order_id = intval($GLOBALS['db']->insert_id());
		if(!empty($recharge_id)){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$datas,'UPDATE',"recharge_id10 = ".$datas['recharge_id10']); //存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$datas,"INSERT"); //不存在则插入
		}
		if($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
		
	}
	
	//提现批量
	public function cashcollect(){
		die;
		//数据转换
 		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "数据为空";
			ajax_return($return);
		}
		
		
		//银行关系映射
		if($_POST["bank"]=="300"){
			$data['bank_id'] = 1;
		}elseif($_POST["bank"]=="301"){
			$data['bank_id'] = 8;
		}elseif($_POST["bank"]=="302"){
			$data['bank_id'] = 3;
		}elseif($_POST["bank"]=="303"){
			$data['bank_id'] = 2;
		}elseif($_POST["bank"]=="463"){
			$data['bank_id'] = 9;
		}elseif($_POST["bank"]=="465"){
			$data['bank_id'] = 15;
		}elseif($_POST["bank"]=="466"){
			$data['bank_id'] = 4;
		}elseif($_POST["bank"]=="467"){
			$data['bank_id'] = 41;
		}elseif($_POST["bank"]=="468"){
			$data['bank_id'] = 7;
		}elseif($_POST["bank"]=="469"){
			$data['bank_id'] = 17;
		}elseif($_POST["bank"]=="470"){
			$data['bank_id'] = 11;
		}elseif($_POST["bank"]=="472"){
			$data['bank_id'] = 10;
		}elseif($_POST["bank"]=="473"){
			$data['bank_id'] = 5;
		}elseif($_POST["bank"]=="508"){
			$data['bank_id'] = 6;
		}elseif($_POST["bank"]=="615"){
			$data['bank_id'] = 42;
		}elseif($_POST["bank"]=="617"){
			$data['bank_id'] = 12;
		}elseif($_POST["bank"]=="618"){
			$data['bank_id'] = 44;
		}elseif($_POST["bank"]=="619"){
			$data['bank_id'] = 23;
		}
		$data['cash_id10'] =  $_POST['id']; //1.0cashid
		$data['user_id10'] =  $_POST['user_id']; //1.0userid
		//1.0提现状态 status finance_status= 1复审通过       status=1 finance_status= 2   已打款     status=0未处理    status=4 初审通过  status=2拒绝 驳回
		//2.0  0待审核，1已付款，2未通过，3待付款
		$data['third_verify_remark'] =  ""; //打款
		if($_POST['status']==0){ //待审核
			$data['status'] = 0;
		}elseif($_POST['status']==1&&$_POST['finance_status']==2){ //已付款   此处未来要做触点算帐用
			$data['status'] = 1;
			$data['third_verify_remark'] =  "已打款"; //打款
		}elseif($_POST['status']==2){ //拒绝 驳回
			$data['status'] = 2;
		}elseif($_POST['status']==1&&$_POST['finance_status']==1){ //待付款   实际上是复审通过
			$data['status'] = 3;
		}
		
		//$data['msg'] =  $_POST['verify_remark']; //原因
		//$data['desc'] =  $_POST['verify_remark']; //备注
		$data['bankcard'] =  $_POST['bankcard']; //卡号
		$data['bankzone'] =  $_POST['branch']; //开户行 
		$data['money'] =  $_POST['total']; //提现金额
		$data['fee'] =  $_POST['fee']; // 手续费
		$data['create_time'] =  $_POST['addtime']; // 创建时间
		$data['create_date'] =  date('Y-m-d',$_POST['addtime']); // 创建时间
		$data['update_time'] =  $_POST['verify_time']; // 确认时间
		$data['addip'] =  $_POST['addip']; // IP
		$data['first_verify_admin_id'] =  $_POST['verify_userid']; //初审
		$data['first_verify_time'] =  $_POST['verify_time']; //初审
		$data['first_verify_remark'] =  $_POST['verify_remark']; //初审
		$data['second_verify_admin_id'] =  $_POST['verify_userid2']; //复审
		$data['second_verify_time'] =  $_POST['verify_time2']; //复审
		$data['second_verify_remark'] =  $_POST['verify_remark2']; //复审
		$data['third_verify_admin_id'] =  $_POST['finance_userid']; //打款
		$data['third_verify_time'] =  $_POST['finance_time']; //打款

		$data['orderno'] =  $_POST['orderno']; // orderno
		$data['cash_id10'] =  $_POST['id']; // cash_id10
		$data['user_id10'] =  $_POST['user_id']; // user_id10
		
		//取省市县
		$zone = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_bank where bankcard='".$_POST['bankcard']."'");
		$data['region_lv1'] =  $zone['region_lv1'];
		$data['region_lv2'] =  $zone['region_lv2'];
		$data['region_lv3'] =  $zone['region_lv3'];
		$data['region_lv4'] =  $zone['region_lv4'];
		
		$user_new = $GLOBALS['db']->getRow("select id,real_name from ".DB_PREFIX."user where user_id_1_0='".$data['user_id10']."'");
		$data['user_id'] =  $user_new['id']; // new user_id
		$data['real_name'] = $user_new['real_name']; //真实姓名
		$cash_id = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."user_carry where cash_id10='".$data['cash_id10']."'");
		if(!empty($cash_id)){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."user_carry",$data,'UPDATE',"cash_id10 = ".$data['cash_id10']); //存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."user_carry",$data,"INSERT"); //不存在则插入
		}
		if ($list) {
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
 
	}

 
 
	public function collectdeal() {
		die;
 		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "数据为空";
			ajax_return($return);
		}
		//系统字段映射关系
		$data['pid_1_0'] =  $_POST['pid']; //1.0pid
		$data['name'] =  $_POST['product_name']; //贷款名称
		$data['sub_name'] =  $_POST['product_name']; //简短名称
		$data['deal_sn'] =  $_POST['product_name']; //标的编号
		$data['cate_id'] =  1; //分类ID
		$data['agency_id'] =  0; //担保机构
		$data['user_id'] =  1; //融资人userID 现在默认给为1的用户，此用户将做为永久平台先发标融资人
		$data['description'] =  $_POST['product_description']; //贷款描述
		$data['risk_security'] =  $_POST['product_safe']; //风险控制
		$data['seo_title'] = $_POST['product_name'];
		$data['seo_keyword'] = $_POST['product_name'];
		$data['seo_description'] = $_POST['product_description'];
		$data['borrow_amount'] = $_POST['total_money']; //融资额
		$data['deal_status'] = 5; //0待等材料，1进行中，2满标，3流标，4还款中，5已还清
		$data['min_loan_money'] = $_POST['min_money']; //起投额
		$data['max_loan_money'] = $_POST['max_money']; //最大投资额
		$data['repay_time'] = $_POST['limit_time']; //投资期限 月
		$data['rate'] = $_POST['rate']*100; //利率 1.0与2.0记录不同
		$data['sort'] =  1; //排序
		$data['enddate'] =  999;  //募集期 现在定20天
		$data['deal_status'] =  1;  //标的状态
		$data['warrant'] =  0; //担保范围
		$data['risk_rank'] =  0; //风险等级 0:低；1：中；2：高；
		$data['repay_time_type'] =  1;  //月标 0：天标
		$data['type_id'] =  1; //借款用途
		$data['loantype'] =  1;  //还款方式 付息还本
		$data['icon'] =  ""; 
		$data['manage_fee'] =  0; //借款者管理费
		$data['user_loan_manage_fee'] =  0; //投资者管理费
		$data['user_loan_interest_manage_fee'] =  0; //投资者利息管理费
		$data['manage_impose_fee_day1'] =  0; //普通逾期管理费
		$data['manage_impose_fee_day2'] =  0; //严重逾期管理费
		$data['impose_fee_day1'] =  0; //普通逾期罚息
		$data['impose_fee_day2'] =  0; //严重逾期罚息
		$data['user_load_transfer_fee'] =  0; //债权转让管理费
		$data['transfer_day'] =  0; //债权转让期限  满标放款多少天后才可以进行转让 0代表不限制
		$data['compensate_fee'] =  0; //提前还款补偿 补偿金额 = 剩余本金×补偿利率 0即不收取
		$data['user_bid_rebate'] =  0; //投资人返利
		$data['guarantees_amt'] =  0; //借款保证金[第三方托管]
		$data['guarantor_amt'] =  0; //担保金额[第三方托管]
		$data['guarantor_margin_amt'] =  0; //担保保证金[第三方托管
		$data['guarantor_pro_fit_amt'] =  0; //担保收益[第三方托管]
		$data['generation_position'] =  100; //申请延期
		$data['uloadtype'] =  0; //投标类型 0:按金额;1:按份额;
		$data['portion'] =  0; //分成多少份
		$data['max_portion'] =  0; //最高买多少份  0为不限制
		$data['contract_id'] =  3; //借款合同范本
		$data['tcontract_id'] =  3; //转让合同范本
		$data['score'] =  0; //借款者获得积分
		$data['user_bid_score_fee'] =  0; //投资返还积分比率
		
		$data['is_mortgage'] =  0; //是否有抵押物  0:否；1：是；
	//	$data['mortgage_desc'] =  ""; //抵押物说明
		$data['use_interestrate'] =  0; //可否使用加息券 0:否；1：是；
/* 	未来再做修正	if($_POST['total_money']=="all"){
			$data['use_ecv'] =  1; //可否使用红包  0:否；1：是；
			$data['use_learn'] = 1; //可否使用体验金 0:否；1：是；
		}elseif($_POST['total_money']=="hb"){
			$data['use_ecv'] =  0; //可否使用红包  0:否；1：是；
			$data['use_learn'] = 0; //可否使用体验金 0:否；1：是；
		}elseif($_POST['total_money']=="djq"){
			$data['use_ecv'] =  0; //可否使用红包  0:否；1：是；
			$data['use_learn'] = 0; //可否使用体验金 0:否；1：是；
		}else{

		} */

		
		
		$data['use_ecv'] =  0; //可否使用红包  0:否；1：是；
		$data['use_learn'] = 0; //可否使用体验金 0:否；1：是；
		$data['mortgage_fee'] = 0; //抵押物管理费
		$data['is_effect'] = 1; //标的否有效 0:否；1：是；
		$data['deal_status'] = 5; //只要采到就处于满标状态
		//标的状态转换--- 0待等材料，1进行中，2满标，3流标，4还款中，5已还清
		if($_POST['product_status']==0){
			//$data['deal_status'] = 0;
			$data['deal_status'] = 2;
		}elseif($_POST['product_status']==1&&$_POST['full_status']==0){
			//$data['deal_status'] = 1;
			$data['deal_status'] = 2;
		}elseif($_POST['product_status']==1&&$_POST['full_status']==1){
			$huankuan=$_POST['add_time']+$_POST['limit_time']*30*3600*24;
			$nowtime=time();
			if($huankuan>$nowtime){
				$data['deal_status'] = 4;
			}else{
				$data['deal_status'] = 5;
			}
		}
		

 
		// 更新数据
		$data['create_time'] =  $_POST['add_time'];
		$data['update_time'] = $_POST['add_time'];
		$data['start_time'] = $_POST['add_time']; //标的开始时间
		if($data['start_time'] > 0)
			$data['start_date'] = to_date($data['start_time'],"Y-m-d");
		
		if($data['uloadtype']==1){
		    if((int)$data['portion'] > 0)
		      $data['min_loan_money'] = $data['borrow_amount'] / $data['portion'];
		    else{
				$data['min_loan_money'] = 0;
			}
		      
		       
		}
		
		//$data['mortgage_infos'] = $this->mortgage_info(); //处理标的资料图片
        //$data['mortgage_contract'] = $this->mortgage_info("contract"); //处理合同图片
		//$GLOBALS['db']->query("update ".DB_PREFIX."user set referral_count=referral_count+1 where id=".$p_user_id['id']);
		$Deal = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."Deal where pid_1_0='".$data['pid_1_0']."'");

		if($Deal){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data,'UPDATE',"pid_1_0 = ".$data['pid_1_0']); //借款存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data,"INSERT"); //不存在则插入
		}
		
		
		
		
		if ($list) {
			$deal_city_link['deal_id'] = $GLOBALS['db']->insert_id();
			$deal_city_link['city_id'] = 1;

			if($Deal){
				$list = $GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data,'UPDATE',"pid_1_0 = ".$data['pid_1_0']); //借款存在则更新数据
			}else{
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_city_link",$deal_city_link,"INSERT"); //不存在则插入
			}
			
			//require_once(APP_ROOT_PATH."app/Lib/common.php");
			//成功提示
			//syn_deal_status($list);
			//syn_deal_match($list);
			$return['status'] = 1;
			$return['msg'] = "成功";
			ajax_return($return);
		} else {
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return);
		}
	}
	private function mortgage_info($type="infos"){
		die;
		$mortgage_infos = array();
		for($i=1;$i<=20;$i++){
			if(strim($_REQUEST['mortgage_'.$type.'_img_'.$i])!=""){
				$vv['name'] = strim($_REQUEST['mortgage_'.$type.'_name_'.$i]);
				$vv['img'] = strim($_REQUEST['mortgage_'.$type.'_img_'.$i]);
				$mortgage_infos[] = $vv;
			}
				
		}
		
		return serialize($mortgage_infos);
	}
	
	//采集银行卡 added by zhangteng 
	public function savecollectbank(){
		die;
 
		//银行关系映射
		if($_POST["bank"]=="工商银行"){
			$data['bank_id'] = 1;
		}elseif($_POST["bank"]=="中国银行"){
			$data['bank_id'] = 8;
		}elseif($_POST["bank"]=="建设银行"){
			$data['bank_id'] = 3;
		}elseif($_POST["bank"]=="农业银行"){
			$data['bank_id'] = 2;
		}elseif($_POST["bank"]=="交通银行"){
			$data['bank_id'] = 9;
		}elseif($_POST["bank"]=="广发银行"){
			$data['bank_id'] = 15;
		}elseif($_POST["bank"]=="招商银行"){
			$data['bank_id'] = 4;
		}elseif($_POST["bank"]=="平安银行"){
			$data['bank_id'] = 41;
		}elseif($_POST["bank"]=="兴业银行"){
			$data['bank_id'] = 7;
		}elseif($_POST["bank"]=="民生银行"){
			$data['bank_id'] = 17;
		}elseif($_POST["bank"]=="华夏银行"){
			$data['bank_id'] = 11;
		}elseif($_POST["bank"]=="中信银行"){
			$data['bank_id'] = 10;
		}elseif($_POST["bank"]=="光大银行"){
			$data['bank_id'] = 5;
		}elseif($_POST["bank"]=="中国邮政储蓄银行"){
			$data['bank_id'] = 6;
		}elseif($_POST["bank"]=="北京银行"){
			$data['bank_id'] = 42;
		}elseif($_POST["bank"]=="上海浦东发展银行"){
			$data['bank_id'] = 12;
		}elseif($_POST["bank"]=="杭州银行"){
			$data['bank_id'] = 44;
		}elseif($_POST["bank"]=="浙商银行"){
			$data['bank_id'] = 23;
		}
		$data['bankid_1_0'] = $_POST['ub_id']; //1.0 userbank表id
		$data['addip'] = $_POST['addip']; //ip
		
/* 		foreach($_POST as $key=>$value){
			$postArrayString .= $key."=>".$value."\n"; 
		}
		file_put_contents("log.txt", $postArrayString, FILE_APPEND ); //接收日志 */
		$data['real_name'] =  $_POST['realname'] ; //真实姓名
		$data['status'] =  $_POST['status']==1?0:1; //卡是否删除
		$data['region_lv1'] = 1; //中国
		$provinceName = str_replace(array("省", "自治区", "特区" , "区", "自治州"),"",$_POST['pid'],$i); //去掉省 自治区
		$province = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."region_conf where region_level = 2 and name='".$provinceName."'");
		$data['region_lv2'] = $province['id']; //省ID
		
		$CityName = str_replace(array("市", "自治县", "县", "自治州"),"",$_POST['cid'],$i); //去掉市县做模糊匹配
		$city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."region_conf where region_level = 3 and name like '".$CityName."%'");
		$data['region_lv3'] = $city['id']; //地级市ID
		if($_POST['xid']=="市辖区"){
			$XianName = "市区";
		}else{
			$XianName = str_replace(array("市", "自治县", "县", "区", "自治州"),"",$_POST['xid'],$i); //去掉市县区做模糊匹配
		}
 
		$xian = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."region_conf where region_level = 4 and name like '".$XianName."%'");
		$data['region_lv4'] = $xian['id']; //县级市ID
		$data['bankzone'] = $_POST['branch']; //开户行
		$data['bankcard'] = $_POST['bankcard'];  //卡号
		$data['create_time'] = $_POST['addtime'];  //添加时间
		$data['bank_mobile'] = $_POST['ui_phone'];  //绑定的手机号
		$data['redline'] = $_POST['redline'];  //绑定的手机号
		$userinfo = $GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."user WHERE user_id_1_0=".$_POST['user_id']);
/* 		foreach($userinfo as $key=>$value){ //处理结果日志
			$postArrayString .= $key."=>".$value."\n"; 
		}
		file_put_contents("log.txt", $postArrayString, FILE_APPEND ); */
		$data['user_id'] = $userinfo["id"]; //user_id
		if($data['user_id']==""){
			$return['status'] = 1;
			$return['msg'] = "用户不存在";
			ajax_return($return);
		}
		if(is_array($GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."user_bank WHERE bankcard='".$data['bankcard']."'  AND user_id=".$data['user_id']))){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$data,"UPDATE", "bankcard='".$data['bankcard']."'  AND user_id=".$data['user_id']); //更新
		}else{
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$data,"INSERT");
		}
		
  
/* 		foreach($data as $key=>$value){ //处理结果日志
			//$postArrayString .= $key."=>".$value."\n"; 
		} */
		//file_put_contents("log.txt", $postArrayString, FILE_APPEND );
		if($list){
			//省市县未匹配到数据依然入库，但返回错误数据供排查做数据修正。
			if($data['region_lv2']==""){
				$return['status'] = 1;
				$return['msg'] = "省未匹配到，但数据已入库。请检查数据做数据修正";
			}elseif($data['region_lv3']==""){
				$return['status'] = 1;
				$return['msg'] = "地级市未匹配到，但数据已入库。请检查数据做数据修正";
			}elseif($data['region_lv4']==""){
				$return['status'] = 1;
				$return['msg'] = "县级市未匹配到，但数据已入库。请检查数据做数据修正";
			}else{
				$return['status'] = 1;
				$return['msg'] = "添加成功";
			}
			ajax_return($return);
		}else{
			$return['status'] = 0;
			$return['msg'] = "添加失败，未知原因";
			ajax_return($return);
		}
	}
	
	
	
	
	
	public function register()
	{	
		die;
		$login_info = es_session::get("user_info");
		if($login_info)
		{
			app_redirect(url("index"));		
		}
		$code=$_REQUEST['code'];
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_REGISTER']);
		
		$field_list =load_auto_cache("user_field_list");
		foreach($field_list as $k=>$v){
			if($v['is_show']==0){
				unset($field_list[$k]);
			}
		}
		
		$api_uinfo = es_session::get("api_user_info");
		$GLOBALS['tmpl']->assign("code",$code);
		$GLOBALS['tmpl']->assign("reg_name",$api_uinfo['name']);
		
		$GLOBALS['tmpl']->assign("field_list",$field_list);
		
		$GLOBALS['tmpl']->assign("agreement",app_conf("AGREEMENT"));
		$GLOBALS['tmpl']->assign("privacy",app_conf("PRIVACY"));
		$referer = "";
		if(isset($_REQUEST['r'])){
			$referer = strim(base64_decode($_REQUEST['r']));
		}
		$GLOBALS['tmpl']->assign("referer",$referer);
		$GLOBALS['tmpl']->assign("ACT",ACTN);
		$GLOBALS['tmpl']->display("user_register.html");
	}
	public function agreement(){
		$GLOBALS['tmpl']->display("page/user_agreement.html");
	}
	public function getregister(){
		$mobile = strim($_REQUEST['mobile']);
		$info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."user WHERE mobile='".$mobile."'");
		if($info  > 0){
			$return['status'] = 1;
			$return['info'] = "手机号码已被注册";
			ajax_return($return);
		}
	}
	//用户更新  手机号  密码 支付密码 身份证号 修正业务员线下邀请关系 
	public function oneuserupdate(){
 
/*   		foreach($_POST as $key=>$value){
			$postArrayString .= $key."=>".$value."\n"; 
		}
		file_put_contents("log.txt", $postArrayString, FILE_APPEND ); //接收日志  */ 
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}
 
		$infos['user_id_1_0'] = $_POST['user_id'];   
 
		$inviteuserinfo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_id_1_0='".$_POST['invite_uid']."'"); //取邀请人信息
		$infos['pid'] = $inviteuserinfo['id']; // 邀请人id
		$infos['referer'] = $inviteuserinfo['phone']; // referer
		$infos['referer_memo'] = $inviteuserinfo['phone']; // referer_memo
		
 
		$result = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$infos,"UPDATE","user_id_1_0=".$_POST['user_id']);
	 
 
		if($result){
			$return['status'] = 1;
			$return['msg'] = "修改资料成功";
			ajax_return($return); 
		}else{
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return); 
		}

	}
	
	
	
	//用户更新  手机号  密码 支付密码 身份证号
	public function oneuserupdates(){
		die;
/*   		foreach($_POST as $key=>$value){
			$postArrayString .= $key."=>".$value."\n"; 
		}
		file_put_contents("log.txt", $postArrayString, FILE_APPEND ); //接收日志  */ 
		if(empty($_POST)){
			$return['status'] = 0;
			$return['msg'] = "未提交数据";
			ajax_return($return); 
		}
 
		if($_POST['real_status']){
			$sex = substr($_POST['card_num'], (strlen($_POST['card_num'])==15 ? -2 : -1), 1) % 2 ? 1 : 0;
		}else{
			$sex = -1;
		}
		
		
		
		$infos['user_name'] = "w".$_POST['username'];
		$infos['real_name'] = $_POST['realname'];
		$infos['sex'] = $sex;
		$infos['real_name_encrypt'] = " AES_ENCRYPT('".$_POST['realname']."','".AES_DECRYPT_KEY."') ";
		$infos['idno'] = $_POST['card_num'];
		$infos['idno_encrypt'] = " AES_ENCRYPT('".$_POST['card_num']."','".AES_DECRYPT_KEY."') ";
		$infos['paypassword'] = $_POST['pay_password'];  
		$infos['mobile'] = $_POST['phone'];  
		$infos['mobilepassed'] =1;  
		$infos['update_time'] = $_POST['last_login_time']; //更新时间
		//$infos['update_time'] = $_POST['addtime']; //更新时间
		$infos['create_time'] = $_POST['addtime']; //更新时间
		$infos['create_date'] = date('Y-m-d', $_POST['addtime']); //更新时间
		$infos['register_ip'] = $_POST['addip']; //注册IP
		$infos['login_ip'] = $_POST['addip']; //注册IP
		$infos['login_time'] = $_POST['this_login_time']; //登录时间
		$infos['open_id'] = $_POST['open_id']; //微信open_id
		$infos['phone'] = $_POST['phone']; 
		$infos['mobile_encrypt'] = " AES_ENCRYPT('".$_POST['phone']."','".AES_DECRYPT_KEY."') "; 
		$infos['idcardpassed'] = $_POST['real_status']; 
		$infos['idcardpassed_time'] = $_POST['real_check_time']; 
		$infos['user_pwd'] = $_POST['password'];   //密码
		$infos['user_id_1_0'] = $_POST['user_id'];   
		$infos['device'] = $_POST['device'];   
		$infos['is_effect'] = 1;   //更新时间
		//$infos['update_time'] = time();   //更新时间
		$inviteuserinfo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where user_id_1_0='".$_POST['invite_uid']."'"); //取邀请人信息
		$infos['pid'] = $inviteuserinfo['id']; // 邀请人id
		$infos['referer'] = $inviteuserinfo['phone']; // referer
		$infos['referer_memo'] = $inviteuserinfo['phone']; // referer_memo
		
		$userifo = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."user where user_id_1_0='".$_POST['user_id']."'");  
		if(empty($userifo)){
			$result = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$infos,"INSERT");
		}else{
			$result = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$infos,"UPDATE","user_id_1_0=".$_POST['user_id']);
		}
		

/* 		if($Deal){
			$list = $GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data,'UPDATE',"pid_1_0 = ".$data['pid_1_0']);; //借款存在则更新数据
		}else{
			$list=$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data,"INSERT"); //不存在则插入
		} */
		
		
		if($result){
			$return['status'] = 1;
			$return['msg'] = "修改资料成功";
			ajax_return($return); 
		}else{
			$return['status'] = 0;
			$return['msg'] = "失败";
			ajax_return($return); 
		}

	}
	
//会员采集数据接收  by zhangteng
	public function doregistercollect(){
		if(isset($_POST["collect2017"])&&$_POST["collect2017"]){ //来自采集数据，对$_POST数组做重装处理
			$existuser_1_0 = get_user_info("id","user_id_1_0 = ".$_POST['user_id_1_0']);
			if(is_array($existuser_1_0)){
				$return['status'] = 0;
				$return['msg'] = "已存在";
				ajax_return($return);die;
			}
	
		
			$newPostDatas = array();
			//前端表单数据
			$newPostDatas['agreement'] = 1; //我已阅读并且同意玖财通
			$newPostDatas['mobile'] = $_POST['phone']; 
			$newPostDatas['phone'] = $_POST['phone']; 
			$newPostDatas['user_pwd'] = $_POST['password'];  //登录密码 MD5值 处理时需跳过
			$newPostDatas['user_pwd_confirm'] = $_POST['password'];  //确认登录密码
			$newPostDatas['referer'] = $_POST['ui_phone']; //邀请人手机号
			$newPostDatas['user_type'] = 0; //0:普通会员; 1:企业会员
			
			//其它数据
			
			$newPostDatas['collect2017'] = 1; //collect2017
			$newPostDatas['device'] = $_POST['device']; //device
			$newPostDatas['user_id_1_0'] = $_POST['user_id']; //1.0user_id
			$newPostDatas['user_name'] = "w".$_POST['username']; //用户名
			$newPostDatas['real_name'] = $_POST['realname']; //真实姓名
			$newPostDatas['idno'] = $_POST['card_num']; //身份证号
			$newPostDatas['create_time'] = $_POST['addtime']; //注册时间
			$newPostDatas['paypassword'] = $_POST['pay_password']; //支付密码
			$newPostDatas['idcardpassed'] = $_POST['real_status']; //实名认证状态
			$newPostDatas['idcardpassed_time'] = $_POST['real_check_time']; //实名认证 时间
			$newPostDatas['mobilepassed'] = $_POST['phone_status']; //手机号认证状态
			
			$newPostDatas['update_time'] = $_POST['last_login_time']; //更新时间
			$newPostDatas['register_ip'] = $_POST['addip']; //注册IP
			$newPostDatas['login_ip'] = $_POST['addip']; //注册IP
			$newPostDatas['login_time'] = $_POST['this_login_time']; //登录时间
			$newPostDatas['open_id'] = $_POST['open_id']; //微信open_id
			unset($_POST); //清空POST数组
			$_POST = $newPostDatas;
/* 			foreach($_POST as $key=>$value){
				$postArrayString .= $key."=>".$value."\n"; 
			} */
			//file_put_contents("log.txt", $postArrayString, FILE_APPEND );	
		}
		
		
		if(!isset($_POST["collect2017"])){ //采集数据跳过此处
			//注册验证码
			if(intval(app_conf("VERIFY_IMAGE")) == 1 && intval(app_conf("USER_VERIFY")) >= 3){
				$verify = strim($_REQUEST['verify']);
				if(!checkVeifyCode($verify))
				{				
					showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],0,url("shop","user#register"));
				}
			}
		}
		require_once APP_ROOT_PATH."system/libs/usercollect.php";
		$user_data = $_POST;
		if(!isset($_POST["collect2017"])){ //采集数据跳过此处
			if(!$user_data){
				 //app_redirect("404.html");
				 exit();
			}
		}
		foreach($user_data as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		//防止wap冲突 XXXXXXXX
		if(trim($user_data['user_pwd_confirm']) != ""){
			if(trim($user_data['user_pwd'])!=trim($user_data['user_pwd_confirm']))
			{
				// $return['status'] = 1;
				$return['info'] = $GLOBALS['lang']['USER_PWD_CONFIRM_ERROR'];
				ajax_return($return);
				//showErr($GLOBALS['lang']['USER_PWD_CONFIRM_ERROR']);
			}
		}
		//XXXXXXXX
		if(trim($user_data['user_pwd'])=='')
		{	
			// $return['status'] = 1;
			$return['info'] = $GLOBALS['lang']['USER_PWD_ERROR'];
			ajax_return($return);
			//showErr($GLOBALS['lang']['USER_PWD_ERROR']);
		}
		
		//邀请人处理
		if(isset($user_data['referer']) && $user_data['referer']!=""){
			$p_user_data = get_user_info("id,user_type"," user_name='w".$user_data['referer']."'");

			if($p_user_data["user_type"] == 3)
			{
				$user_data['referer_memo'] = $p_user_data['id'];
				//$user_data['pid'] = $p_user_data['id'];
				$user_data['pid'] = 0;
			}
			elseif($p_user_data["user_type"] < 2)
			{
				$user_data['pid'] = $p_user_data["id"];
/* 				if($user_data['pid'] > 0){
					$refer_count = get_user_info("count(*)","pid='".$user_data['pid']."' ","ONE");
					if($refer_count == 0){
						$user_data['referral_rate'] = (float)trim(app_conf("INVITE_REFERRALS_MIN"));
					}
					elseif((float)trim(app_conf("INVITE_REFERRALS_MIN")) + $refer_count*(float)trim(app_conf("INVITE_REFERRALS_RATE")) > (float)trim(app_conf("INVITE_REFERRALS_MAX"))){
						$user_data['referral_rate'] =(float)trim(app_conf("INVITE_REFERRALS_MAX"));
					}
					else{
						$user_data['referral_rate'] =(float)trim(app_conf("INVITE_REFERRALS_MIN")) + $refer_count*(float)trim(app_conf("INVITE_REFERRALS_RATE"));
					}
						
					
					if(intval(app_conf("REFERRAL_IP_LIMIT")) > 0 && get_user_info("count(*)","register_ip ='".CLIENT_IP."' AND pid='".$user_data['pid']."'","ONE") > 0){
						$user_data['referral_rate'] = 0;
					}
				}
				else{
					$user_data['pid'] = 0;
					$user_data['referer_memo'] = $user_data['referer'];
				} */
			}
		}
			$user_data['is_effect'] = 1;
			$user_data['mobilepassed'] = 1;
			$user_data["mobile_encrypt"] = " AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."') ";
		
		//判断是否为手机注册
/* 		if((app_conf("REGISTER_TYPE") == 0 || app_conf("REGISTER_TYPE") == 1) && (app_conf("USER_VERIFY") == 0 || app_conf("USER_VERIFY") == 2)){
			if(!isset($_POST["collect2017"])){ //采集数据跳过此处
				if(strim($user_data['sms_code']) == ""){
					// $return['status'] = 1;
					$return['info'] = "请输入手机验证码";
					ajax_return($return);
					//showErr("请输入手机验证码");
				}
				//判断验证码是否正确
				if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($user_data['mobile'])."' AND verify_code='".strim($user_data['sms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
					// $return['status'] = 1;
					$return['info'] = "手机验证码出错,或已过期";
					ajax_return($return);
					//showErr("手机验证码出错,或已过期");
				}
			}
			
			
			$user_data['is_effect'] = 1;
			$user_data['mobilepassed'] = 1;
			$user_data["mobile_encrypt"] = " AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."') ";
		} */
		
			$user_data['is_effect'] = 1;
			$user_data['mobilepassed'] = 1;
			$user_data["mobile_encrypt"] = " AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."') ";
		
		if(!isset($_POST["collect2017"])){ //采集数据跳过此处
			//判断是否为邮箱注册
			if((app_conf("REGISTER_TYPE") == 0 || app_conf("REGISTER_TYPE") == 2) && (app_conf("USER_VERIFY") == 1 || app_conf("USER_VERIFY") == 2)){
				
				if(strim($user_data['emsms_code'])==""){
					showErr("请输入邮箱验证码");
				}
				//判断验证码是否正确
				if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."email_verify_code WHERE email='".strim($user_data['email'])."' AND verify_code='".strim($user_data['emsms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
					showErr("邮箱验证码出错,或已过期");
				}
				$user_data['is_effect'] = 1;
				$user_data['emailpassed'] = 1;
					
			}
		}
		
		$res = save_collect_user($user_data);
/* 		if($res){
			$p_user_id=get_user_info("id","mobile_encrypt =AES_ENCRYPT('".$user_data['referer']."','".AES_DECRYPT_KEY."') OR user_name='w".$user_data['referer']."'");
			$param['referral_count']=$param['referral_count']+1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."user",$param,"UPDATE","id=".$p_user_id['id']);

		} */
		//xxxxxxxxx
/* 		if($_REQUEST['subscribe']==1)
		{
			//订阅
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mail_list where mail_address = '".$user_data['email']."'")==0)
			{
				$mail_item['city_id'] = intval($_REQUEST['city_id']);
				$mail_item['mail_address'] = $user_data['email'];
				$mail_item['is_effect'] = app_conf("USER_VERIFY");
				$GLOBALS['db']->autoExecute(DB_PREFIX."mail_list",$mail_item,'INSERT','','SILENT');
			}
			if($user_data['mobile']!=''&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mobile_list where mobile = '".$user_data['mobile']."'")==0)
			{
				$mobile['city_id'] = intval($_REQUEST['city_id']);
				$mobile['mobile'] = $user_data['mobile'];
				$mobile['is_effect'] = app_conf("USER_VERIFY");
				$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_list",$mobile,'INSERT','','SILENT');
			}
			
		} */
		
		if(isset($_POST["collect2017"])){ //采集数据返回结果
			if($res['status'] == 1){
				$return['status'] = 1;
				$return['msg'] = "注册成功";
				ajax_return($return);
			}else{
				$error = $res['data'];		
				if(!$error['field_show_name']){
						$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
				}
				if($error['error']==EMPTY_ERROR){
					$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
				}
				if($error['error']==FORMAT_ERROR){
					$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
				}
				if($error['error']==EXIST_ERROR){
					$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
				}
				$return['status'] = 0;
				$return['msg'] = $error_msg;
				ajax_return($return);
			}
			
		}
		
		
		if(!isset($_POST["collect2017"])){ //采集数据跳过此处
			if($res['status'] == 1){
				$user_id = intval($res['data']);
				//更新来路
				//$GLOBALS['db']->query("update ".DB_PREFIX."user set referer = '".$GLOBALS['referer']."' where id = ".$user_id);
				$user_info = get_user_info("*","id = ".$user_id);
				if($user_info['is_effect']==1){
					//在此自动登录
					$result = do_login_user($user_data['user_name'],$user_data['user_pwd']);
					$return['status'] = 1;
					$return['msg'] = "恭喜注册成功";
					ajax_return($return);
					/*$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);				
					app_redirect(url("index","user#steptwo"));*/
				}else{
					showSuccess($GLOBALS['lang']['WAIT_VERIFY_USER'],0,APP_ROOT."/");
				}
			}else{
				$error = $res['data'];		
				if(!$error['field_show_name']){
						$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
				}
				if($error['error']==EMPTY_ERROR){
					$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
				}
				if($error['error']==FORMAT_ERROR){
					$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
				}
				if($error['error']==EXIST_ERROR){
					$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
				}
				showErr($error_msg);
			}
		}
	}
 
	
	
	
	
	
	
	
	
	
	
	
	public function doregister()
	{
		//注册验证码
		if(intval(app_conf("VERIFY_IMAGE")) == 1 && intval(app_conf("USER_VERIFY")) >= 3){
			$verify = strim($_REQUEST['verify']);
			if(!checkVeifyCode($verify))
			{
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],0,url("shop","user#register"));
			}
		}
		require_once APP_ROOT_PATH."system/libs/user.php";
		$user_data = $_POST;
		if(!$user_data){
			 app_redirect("404.html");
			 exit();
		}
		foreach($user_data as $k=>$v)
		{
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		//防止wap冲突
		if(trim($user_data['user_pwd_confirm']) != ""){
			if(trim($user_data['user_pwd'])!=trim($user_data['user_pwd_confirm']))
			{
				// $return['status'] = 1;
				$return['info'] = $GLOBALS['lang']['USER_PWD_CONFIRM_ERROR'];
				ajax_return($return);
				//showErr($GLOBALS['lang']['USER_PWD_CONFIRM_ERROR']);
			}
		}
		
		if(trim($user_data['user_pwd'])=='')
		{	
			// $return['status'] = 1;
			$return['info'] = $GLOBALS['lang']['USER_PWD_ERROR'];
			ajax_return($return);
			//showErr($GLOBALS['lang']['USER_PWD_ERROR']);
		}
		
		
		if(isset($user_data['referer']) && $user_data['referer']!=""){
			$p_user_data = get_user_info("id,user_type","mobile_encrypt =AES_ENCRYPT('".$user_data['referer']."','".AES_DECRYPT_KEY."')");

			if($p_user_data["user_type"] == 3)
			{
				$user_data['referer_memo'] = $p_user_data['id'];
				//$user_data['pid'] = $p_user_data['id'];
				$user_data['pid'] = 0;
			}
			elseif($p_user_data["user_type"] < 2)
			{
				$user_data['pid'] = $p_user_data["id"];
				if($user_data['pid'] > 0){
					$refer_count = get_user_info("count(*)","pid='".$user_data['pid']."' ","ONE");
					if($refer_count == 0){
						$user_data['referral_rate'] = (float)trim(app_conf("INVITE_REFERRALS_MIN"));
					}
					elseif((float)trim(app_conf("INVITE_REFERRALS_MIN")) + $refer_count*(float)trim(app_conf("INVITE_REFERRALS_RATE")) > (float)trim(app_conf("INVITE_REFERRALS_MAX"))){
						$user_data['referral_rate'] =(float)trim(app_conf("INVITE_REFERRALS_MAX"));
					}
					else{
						$user_data['referral_rate'] =(float)trim(app_conf("INVITE_REFERRALS_MIN")) + $refer_count*(float)trim(app_conf("INVITE_REFERRALS_RATE"));
					}
						
					
					if(intval(app_conf("REFERRAL_IP_LIMIT")) > 0 && get_user_info("count(*)","register_ip ='".CLIENT_IP."' AND pid='".$user_data['pid']."'","ONE") > 0){
						$user_data['referral_rate'] = 0;
					}
				}
				else{
					$user_data['pid'] = 0;
					$user_data['referer_memo'] = $user_data['referer'];
				}
			}
		}
		
		
		//判断是否为手机注册
		if((app_conf("REGISTER_TYPE") == 0 || app_conf("REGISTER_TYPE") == 1) && (app_conf("USER_VERIFY") == 0 || app_conf("USER_VERIFY") == 2)){
			if(strim($user_data['sms_code']) == ""){
				// $return['status'] = 1;
				$return['info'] = "请输入手机验证码";
				ajax_return($return);
				//showErr("请输入手机验证码");
			}
			//判断验证码是否正确
			if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($user_data['mobile'])."' AND verify_code='".strim($user_data['sms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				// $return['status'] = 1;
				$return['info'] = "手机验证码出错,或已过期";
				ajax_return($return);
				//showErr("手机验证码出错,或已过期");
			}
			$user_data['is_effect'] = 1;
			$user_data['mobilepassed'] = 1;
			$user_data["mobile_encrypt"] = " AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."') ";
		}
		
		//判断是否为邮箱注册
		if((app_conf("REGISTER_TYPE") == 0 || app_conf("REGISTER_TYPE") == 2) && (app_conf("USER_VERIFY") == 1 || app_conf("USER_VERIFY") == 2)){
			
			if(strim($user_data['emsms_code'])==""){
				showErr("请输入邮箱验证码");
			}
			//判断验证码是否正确
			if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."email_verify_code WHERE email='".strim($user_data['email'])."' AND verify_code='".strim($user_data['emsms_code'])."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				showErr("邮箱验证码出错,或已过期");
			}
			$user_data['is_effect'] = 1;
			$user_data['emailpassed'] = 1;
				
		}
		// 判断是否已注册
		$self_user_id=get_user_info("id","mobile_encrypt =AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."')","ONE");
		if($user_data['mobile']==''){
			$return['info'] = "手机号不能为空";
			ajax_return($return);
		}elseif($self_user_id>0){
				$return['info'] = "该手机号已被注册";
				ajax_return($return);
		}
		// 判断邀请码是否有效
		$p_user_id=get_user_info("id","mobile_encrypt =AES_ENCRYPT('".$user_data['referer']."','".AES_DECRYPT_KEY."')");
		if(isset($user_data['referer'])&&$user_data['referer']!=''){
			if(empty($p_user_id)){
				$return['info'] = "该邀请码无效";
				ajax_return($return);
			}
		}

		$res = save_user($user_data);
		if($res){
				$GLOBALS['db']->query("update ".DB_PREFIX."user set referral_count=referral_count+1 where id=".$p_user_id['id']);
		}
		if($_REQUEST['subscribe']==1)
		{
			//订阅
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mail_list where mail_address = '".$user_data['email']."'")==0)
			{
				$mail_item['city_id'] = intval($_REQUEST['city_id']);
				$mail_item['mail_address'] = $user_data['email'];
				$mail_item['is_effect'] = app_conf("USER_VERIFY");
				$GLOBALS['db']->autoExecute(DB_PREFIX."mail_list",$mail_item,'INSERT','','SILENT');
			}
			if($user_data['mobile']!=''&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."mobile_list where mobile = '".$user_data['mobile']."'")==0)
			{
				$mobile['city_id'] = intval($_REQUEST['city_id']);
				$mobile['mobile'] = $user_data['mobile'];
				$mobile['is_effect'] = app_conf("USER_VERIFY");
				$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_list",$mobile,'INSERT','','SILENT');
			}
			
		}
		
		if($res['status'] == 1)
		{
			$user_id = intval($res['data']);
			//更新来路
			//$GLOBALS['db']->query("update ".DB_PREFIX."user set referer = '".$GLOBALS['referer']."' where id = ".$user_id);
			$user_info = get_user_info("*","id = ".$user_id);
			if($user_info['is_effect']==1)
			{
				//在此自动登录
				$result = do_login_user($user_data['user_name'],$user_data['user_pwd']);
				$return['status'] = 1;
				$return['info'] = "恭喜注册成功";
				$return['msg'] = "18888元体验金+25000元分享体验金+50元代金券";
				$return['jump'] = url("index","user#steptwo");
				ajax_return($return);
				/*$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);				
				app_redirect(url("index","user#steptwo"));*/
			}
			else{
				showSuccess($GLOBALS['lang']['WAIT_VERIFY_USER'],0,APP_ROOT."/");
			}
		}
		else
		{
			$error = $res['data'];		
			if(!$error['field_show_name'])
			{
					$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
			}
			if($error['error']==EMPTY_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
			}
			if($error['error']==FORMAT_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
			}
			if($error['error']==EXIST_ERROR)
			{
				$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
			}
			showErr($error_msg);
		}
	}
	
	public function login()
	{
		$count = es_session::get("check_login_count");
		$mobile = es_session::get("mobile");
		$login_info = es_session::get("user_info");
		if($login_info)
		{
			app_redirect(url("index"));		
		}
		if(!$count){
			$GLOBALS['tmpl']->assign("count","1");
		}else{
			$GLOBALS['tmpl']->assign("count",$count);
		}
		$jump_url = explode("jumpUrl=",$_SERVER['REQUEST_URI']);
		$GLOBALS['tmpl']->assign("jump_url",$jump_url[1]);	
		$GLOBALS['tmpl']->assign("mobile",$mobile);			
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['USER_LOGIN']);
		$GLOBALS['tmpl']->assign("CREATE_TIP",$GLOBALS['lang']['REGISTER']);
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
		$GLOBALS['tmpl']->assign("ACT",ACTN);
		
		$GLOBALS['tmpl']->display("user_login.html");
	}
	public function api_login()
	{

		$s_api_user_info = es_session::get("api_user_info");
		if($s_api_user_info)
		{
			 
			$GLOBALS['tmpl']->assign("page_title",$s_api_user_info['name'].$GLOBALS['lang']['HELLO'].",".$GLOBALS['lang']['USER_LOGIN_BIND']);
			$GLOBALS['tmpl']->assign("CREATE_TIP",$GLOBALS['lang']['REGISTER_BIND']);
			$GLOBALS['tmpl']->assign("api_callback",true);
			$GLOBALS['tmpl']->display("user_login.html");
		}
		else
		{
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
	}	
	public function dologin()
	{
		if(!$_POST)
		{
			 app_redirect("404.html");
			 exit();
		}
		foreach($_POST as $k=>$v)
		{
			$_POST[$k] = htmlspecialchars(addslashes($v));
		}
		$ajax = intval($_REQUEST['ajax']);
		if(!check_hash_key()){
			showErr("非法请求!",$ajax);
		}
		//验证码
		
		if($_REQUEST['verify'] != "")
		{
			$verify = strim($_REQUEST['verify']);
			if(!checkVeifyCode($verify))
			{				
				showErr($GLOBALS['lang']['VERIFY_CODE_ERROR'],$ajax,url("shop","user#login"));
			}
		}
		
		require_once APP_ROOT_PATH."system/libs/user.php";
		
		$_POST['user_pwd'] = trim(FW_DESPWD($_POST['user_pwd']));
		
		if(intval(es_session::get("check_login_count")) <= 3 && check_ipop_limit(CLIENT_IP,"user_dologin",intval(app_conf("SUBMIT_DELAY")))){
			$result = do_login_user($_POST['email'],$_POST['user_pwd']);
		}
		else
			showErr($GLOBALS['lang']['SUBMIT_TOO_FAST'],$ajax,url("shop","user#login"));
		if($result['status'])
		{
			es_session::set("check_login_count",0);	
			$s_user_info = es_session::get("user_info");
			if(intval($_POST['auto_login'])==1)
			{
				//自动登录，保存cookie
				$user_data = $s_user_info;
				$ck_user_name = "";
				if($user_data['email']==""){
					$ck_user_name = $user_data['user_name'] ;
				}
				if($ck_user_name==""){
					$ck_user_name = $user_data['mobile'] ;
				}
				es_cookie::set("user_name",$ck_user_name,3600*24*30);		
				es_cookie::set("user_pwd",md5($user_data['user_pwd']."_EASE_COOKIE"),3600*24*30);
			}
			if($ajax == 0 && trim(app_conf("INTEGRATE_CODE")) ==''){
				$redirect = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:url("index");
				app_redirect($redirect);
			}else{	
				if($_REQUEST['jump_url']){
					$jump_url = $_REQUEST['jump_url'];
				}else{
					es_session::set("before_login",$_SERVER['HTTP_REFERER']);
					$jump_url = get_gopreview();
				}
				$s_user_info = es_session::get("user_info");
				/*if($s_user_info['ips_acct_no']== null && app_conf("OPEN_IPS")){
					if($ajax==1){
						$return['status'] = 2;
						$return['info'] = "本站需绑定第三方托管账户，是否马上去绑定";
						$return['data'] = $result['msg'];
						$return['jump'] = $jump_url;
						$return['jump1'] = APP_ROOT."/index.php?ctl=collocation&act=CreateNewAcct&user_type=0&user_id=".$s_user_info['id'];
						ajax_return($return);
					}else{
						$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);					
						showSuccess($GLOBALS['lang']['LOGIN_SUCCESS'],$ajax,$jump_url);
					}
				}else{*/
					if($ajax==1){
						$return['status'] = 1;
						$return['info'] = $GLOBALS['lang']['LOGIN_SUCCESS'];
						$return['data'] = $result['msg'];
						$return['jump'] = $jump_url;
						ajax_return($return);
					}else{
						$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);					
						showSuccess($GLOBALS['lang']['LOGIN_SUCCESS'],$ajax,$jump_url);
					}
				/*}*/
			}
			
		}else{
			if($result['data'] == ACCOUNT_NO_EXIST_ERROR){
				$err = $GLOBALS['lang']['USER_NOT_EXIST'];
			}
			if($result['data'] == ACCOUNT_PASSWORD_ERROR){	
				$err = $GLOBALS['lang']['PASSWORD_ERROR'];
			}
			if($result['data'] == ACCOUNT_NO_VERIFY_ERROR){
				$err = $GLOBALS['lang']['USER_NOT_VERIFY'];
				if(app_conf("MAIL_ON")==1&&$ajax==0){				
					$GLOBALS['tmpl']->assign("page_title",$err);
					$GLOBALS['tmpl']->assign("user_info",$result['user']);
					$GLOBALS['tmpl']->display("verify_user.html");
					exit;
				}
			}			
			if(es_session::is_set("check_login_count")){
				$check_count = es_session::get("check_login_count");
			}else{
				$check_count = 0;
			}
			if(es_session::get("mobile")){
				es_session::delete("mobile");
				es_session::set("mobile",$_POST['email']);
			}else{
				es_session::set("mobile",$_POST['email']);
			}			
			es_session::set("check_login_count",$check_count+1);			
			showErr($err,$ajax);
			
		}
	}
	
	
	
	public function steptwo(){
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		
	   if($GLOBALS['user_info']['idno']!=""){
	        showErr("操作失败");
	    }
		$bank_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."bank where is_rec=1 ORDER BY is_rec DESC,sort DESC,id ASC");
        $GLOBALS['tmpl']->assign("bank_list",$bank_list);
		$GLOBALS['tmpl']->display("user_step_two.html");
		exit;
	}
	public function stepthree(){
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		if($GLOBALS['user_info']['paypassword']!=""){
	        showErr("操作失败");
	    }
	    $GLOBALS['tmpl']->display("user_register_success.html");
		exit;
	}
	
	public function stepsave(){
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		$user_id=intval($GLOBALS['user_info']['id']);
		$focus_list = explode(",",$_REQUEST['user_ids']);
		foreach($focus_list as $k=>$focus_uid)
		{
			if(intval($focus_uid) > 0){
				$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".intval($focus_uid));
				if(!$focus_data)
				{
						$focused_user_name = get_user_info("user_name","id = ".$focus_uid,"ONE");
						$focus_data = array();
						$focus_data['focus_user_id'] = $user_id;
						$focus_data['focused_user_id'] = $focus_uid;
						$focus_data['focus_user_name'] = $GLOBALS['user_info']['user_name'];
						$focus_data['focused_user_name'] = $focused_user_name;
						$GLOBALS['db']->autoExecute(DB_PREFIX."user_focus",$focus_data,"INSERT");
						$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count + 1 where id = ".$user_id);
						$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count + 1 where id = ".$focus_uid);
				}
			}
		}		
		showSuccess($GLOBALS['lang']['REGISTER_SUCCESS'],0,url("shop","uc_center"));
	}
	
	public function loginout()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$result = loginout_user();
		if($result['status'])
		{
			$s_user_info = es_session::get("user_info");
			es_cookie::delete("user_name");
			es_cookie::delete("user_pwd");
			$GLOBALS['tmpl']->assign('integrate_result',$result['msg']);
			if(WAP == 1){
				$before_loginout = url("index","user#login");
			}else{
				$before_loginout = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:url("index");
			}			
			if(trim(app_conf("INTEGRATE_CODE"))=='')
			{
				app_redirect($before_loginout);
			}
			else
			showSuccess($GLOBALS['lang']['LOGINOUT_SUCCESS'],0,$before_loginout);
		}
		else
		{
			app_redirect(url("index"));		
		}
	}
	
	public function getpassword()
	{
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('user_get_password.html', $cache_id))	
		{
			 
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['GET_PASSWORD_BACK']);
		}
		$sms_count = es_session::get('sms_count');
		$GLOBALS['tmpl']->assign("sms_count",$sms_count);
		$GLOBALS['tmpl']->display("user_get_password.html",$cache_id);
	}
	
	public function send_password()
	{
		$email = addslashes(trim($_REQUEST['email']));
		$user_pwd = strim($_REQUEST['pwd_m']);
		$sms_code =strim($_REQUEST['sms_codes']);
				
		if(!check_email($email))
		{
			showErr($GLOBALS['lang']['MAIL_FORMAT_ERROR']); //没输入邮件
		}

		if(get_user_info("count(*)","email_encrypt =AES_ENCRYPT('".$email."','".AES_DECRYPT_KEY."')","ONE") == 0)
		{
			showErr($GLOBALS['lang']['NO_THIS_MAIL']);  //无此邮箱用户
		}
		if($sms_code==""){
			showErr("请输入手机验证码",1);
		}
		
		if($user_pwd==""){
			showErr("请输入密码",1);
		}
		
		$yanzheng = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."email_verify_code WHERE email='".$email."' AND verify_code='".$sms_code."'");
		
		if(!$yanzheng){
			showErr("邮箱验证码出错",1);
		}
		
		if($user_info = get_user_info("*","email_encrypt =AES_ENCRYPT('".$email."','".AES_DECRYPT_KEY."')"))
		{
			$result = 1;  //初始为1
			//载入会员整合
			$integrate_code = trim(app_conf("INTEGRATE_CODE"));
			if($integrate_code!='')
			{
				$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
				if(file_exists($integrate_file))
				{
					require_once $integrate_file;
					$integrate_class = $integrate_code."_integrate";
					$integrate_obj = new $integrate_class;
				}
			}
				
			if($integrate_obj)
			{
				$result = $integrate_obj->edit_user($user_info,$user_pwd);
			}
				
			if($result>0)
			{
				$user_info_m['user_pwd'] = md5($user_pwd);
				$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_info_m,"UPDATE","id=".$user_info['id']);
			}
				
			showSuccess($GLOBALS['lang']['MOBILE_SUCCESS'],1);//密码修改成功
				
		}
		else{
			showErr("邮箱账户不存在",1);  //没有该手机账户
		}
		
	}

	// wap密码手机修改 code验证  2016.9.2
	public function phone_send_code(){
		foreach ($_POST as $k=> $v) {
			$user_data[$k] = htmlspecialchars(addslashes($v));
		}
		$mobile = strim($user_data['mobile']);
		$sms_code=strim($user_data['sms_code']);
		/*$verify =  strim($user_data['verify']);*/
		if(!$mobile){
			showErr($GLOBALS['lang']['MOBILE_FORMAT_ERROR']); //手机格式错误
		}
		/*if($verify){
			if(!checkVeifyCode($verify)){
				$data['status'] = 0;
				$data['info'] = "图形验证码错误";
				ajax_return($data);
			}
		}*/
		if($sms_code==""){
			showErr("请输入手机验证码",1);
		}
		//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".strim($mobile)."' AND verify_code='".strim($sms_code)."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
				showErr("手机验证码出错,或已过期",1);
		}else{
			session_start();
			$_SESSION['mobile'] = $mobile;
			showSuccess("手机验证码正确",1);
			
		}
		/*//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".$sms_code."'")==0){
			showErr("手机验证码出错",1);
		}else{
			session_start();
			$_SESSION['mobile'] = $mobile;
			showSuccess("手机验证码正确",1);
			
		}*/
	}
	// wap密码手机修改 更改密码 2016.9.2
	public function user_set_password(){
		//禁止url直接访问
		if(!$_SESSION['mobile']){
			exit('非法请求');
		}
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('user_get_password.html', $cache_id))	
		{
			 
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['GET_PASSWORD_BACK']);
		}
		$GLOBALS['tmpl']->display("user_set_password.html",$cache_id);
	}
	// wap 密码修改 2016.9.2
	public function set_password(){
		$user_pwd = strim($_REQUEST['pwd_m']);
		$mobile = $_SESSION['mobile'];
		if($user_info = get_user_info("*","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') "))
		{
			$result = 1;  //初始为1
			//载入会员整合
			$integrate_code = trim(app_conf("INTEGRATE_CODE"));
			if($integrate_code!='')
			{
				$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
				if(file_exists($integrate_file))
				{
					require_once $integrate_file;
					$integrate_class = $integrate_code."_integrate";
					$integrate_obj = new $integrate_class;
				}	
			}
			
			if($integrate_obj)
			{
				$result = $integrate_obj->edit_user($user_info,$user_pwd);				
			}
			
			if($result>0)
			{
				$user_info_m['user_pwd'] = md5($user_pwd);
				$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_info_m,"UPDATE","id=".$user_info['id']);
			}
			es_session::delete('sms_count');
			showSuccess($GLOBALS['lang']['MOBILE_SUCCESS'],1);//密码修改成功
			
		}
		else{
			showErr($GLOBALS['lang']['NO_THIS_MOBILE'],1);  //没有该手机账户
		}
	}
	
	public function phone_send_password()
	{	
		$mobile = strim($_REQUEST['phone']);
		$user_pwd = strim($_REQUEST['pwd']);
		$sms_code=strim($_POST['sms_code']);
		
		if(!$mobile)
		{
			showErr($GLOBALS['lang']['MOBILE_FORMAT_ERROR']); //手机格式错误
		}
		
		if($sms_code==""){
			showErr("请输入手机验证码!",1);
		}
		
		if($user_pwd==""){
			showErr("请输入密码!",1);
		}
		//判断验证码是否正确
		if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".$sms_code."'")==0){
			showErr("手机验证码出错",1);
		}
		if(empty(get_user_info("*","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') "))){
			showErr("该用户不存在!",1);
		}
		if(get_user_info("user_pwd","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') ","ONE")==MD5($user_pwd)){
			showErr("不能修改为原密码!",1);
		}
		if(get_user_info("paypassword","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') ","ONE")==MD5($user_pwd)){
			showErr("登录密码不能与交易密码一样!",1);
		}
		
	
		if($user_info = get_user_info("*","mobile_encrypt =AES_ENCRYPT('".$mobile."','".AES_DECRYPT_KEY."') "))
		{
			$result = 1;  //初始为1
			//载入会员整合
			$integrate_code = trim(app_conf("INTEGRATE_CODE"));
			if($integrate_code!='')
			{
				$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
				if(file_exists($integrate_file))
				{
					require_once $integrate_file;
					$integrate_class = $integrate_code."_integrate";
					$integrate_obj = new $integrate_class;
				}	
			}
			
			if($integrate_obj)
			{
				$result = $integrate_obj->edit_user($user_info,$user_pwd);				
			}
			
			if($result>0)
			{
				$user_info_m['user_pwd'] = md5($user_pwd);
				$GLOBALS['db']->autoExecute(DB_PREFIX."user",$user_info_m,"UPDATE","id=".$user_info['id']);
			}
			
			showSuccess($GLOBALS['lang']['MOBILE_SUCCESS'],1,url("index","user#login"));//密码修改成功
			
		}
		else{
			showErr($GLOBALS['lang']['NO_THIS_MOBILE'],1);  //没有该手机账户
		}
		
		
		
	}
	
	
	
	public function api_create()
	{
		$s_api_user_info = es_session::get("api_user_info");
		if($s_api_user_info)
		{
			if($s_api_user_info['field'])
			{
				$module = str_replace("_id","",$s_api_user_info['field']);
				$module = strtoupper(substr($module,0,1)).substr($module,1);
				require_once APP_ROOT_PATH."system/api_login/".$module."_api.php";
				$class = $module."_api";
				$obj = new $class();
				$obj->create_user();
				app_redirect(APP_ROOT."/");
				exit;
			}			
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
		else
		{
			showErr($GLOBALS['lang']['INVALID_VISIT']);
		}
	}
	
	public function do_re_name_id()
	{

	    if(!$GLOBALS['user_info'] || $GLOBALS['user_info']['idno']!=""){
	        showErr("操作失败");
	    }
	    
		$id= $GLOBALS['user_info']['id'];
		$real_name = strim($_REQUEST['real_name']);
		$idno = strim($_REQUEST['idno']);
		$sex = strim($_REQUEST['sex']);
		$byear = intval($_REQUEST['byear']);
		$bmonth = intval($_REQUEST['bmonth']);
		$bday = intval($_REQUEST['bday']);
		
		$user_type = intval($GLOBALS['user_info']['user_type']);
		if($user_type ==  1)
		{
			$enterpriseName = strim($_REQUEST['enterpriseName']);
			$bankLicense = strim($_REQUEST['bankLicense']);
			$orgNo = strim($_REQUEST['orgNo']);
			$businessLicense  = strim($_REQUEST['businessLicense']);
			$taxNo = strim($_REQUEST['taxNo']);
			
			if($enterpriseName==""){
				showErr("请输入企业名称");
			}
			if($bankLicense==""){
				showErr("请输入开户银行许可证");
			}
			if($orgNo==""){
				showErr("请输入组织机构代码");
			}
			if($businessLicense==""){
				showErr("请输入营业执照编号");
			}
			if($taxNo==""){
				showErr("请输入税务登记号");
			}
			
			
		}
		
		if(!$id)
		{
			showErr("该用户尚未登陆",url("index","user#login")); 
		}
		
		if(!$real_name)
		{
			showErr("请输入真实姓名"); //姓名格式错误
		}
		
		
		if($idno==""){
			showErr("请输入身份证号");
		}
		
		if(getIDCardInfo($idno)==0){
    			showErr("身份证号码错误！");
    	}
    	
    	
		
	
	
		//判断该实名是否存在
		if(get_user_info("count(*)","idno_encrypt = AES_ENCRYPT('.$idno.','".AES_DECRYPT_KEY."') and id<> $id ","ONE") > 0 ){
			showErr("该实名已被其他用户认证，非本人请联系客服");
		}
		
		
		if($user = get_user_info("*","id =".$id))
		{	
			$user_info_re = array();
			$user_info_re['id'] = $id;
			$user_info_re['real_name'] = $real_name;
			$user_info_re['idno'] = $idno;
			$user_info_re['sex'] = $sex;
			$user_info_re['byear'] = $byear;
			$user_info_re['bmonth'] = $bmonth;
			$user_info_re['bday'] = $bday;
			if( $user_type == 1){
				$user_info_re['enterpriseName'] = $enterpriseName;
				$user_info_re['bankLicense'] = $bankLicense;
				$user_info_re['orgNo'] = $orgNo;
				$user_info_re['businessLicense'] = $businessLicense;
				$user_info_re['taxNo'] = $taxNo;
			}
			$user_info_re['email'] = $user['email'];
			if($user['email']=="" && (int)app_conf("OPEN_IPS") > 0){
				$user_info_re['email'] = get_site_email($id);
			}
			
			
			require_once APP_ROOT_PATH."system/libs/user.php";
			$res = save_user($user_info_re,"UPDATE");
			
			if($res['status'] == 1)
			{
				/*
				$data['user_id'] = $GLOBALS['user_info']['id'];
		    	$data['type'] = "credit_identificationscanning";
		    	$data['status'] = 0;
		    	$data['create_time'] = TIME_UTC;
		    	$data['passed'] = 0;
		    	
		    	$condition = "";
		    	if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_credit_file WHERE user_id=".$GLOBALS['user_info']['id']." AND type='credit_identificationscanning'") > 0)
		    	{
		    		$mode = "UPDATE";
		    		$condition = "user_id=".$GLOBALS['user_info']['id']." AND type='credit_identificationscanning'";
		    	}
		    	else{
		    		$mode = "INSERT";
		    	}
		    	$GLOBALS['db']->autoExecute(DB_PREFIX."user_credit_file",$data,$mode,$condition);
				*/
				if( $user_type == 1){
					$user_company = array();
					$user_company['company_name'] = $enterpriseName;
					$user_company['contact'] = $real_name;
					
					$user_company['bankLicense'] = $bankLicense;
					$user_company['orgNo'] = $orgNo;
					$user_company['businessLicense'] = $businessLicense;
					$user_company['taxNo'] = $taxNo;
					if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_company WHERE user_id=".intval($GLOBALS['user_info']['id'])) > 0){
						$GLOBALS['db']->autoExecute(DB_PREFIX."user_company",$user_company,"UPDATE","user_id=".$id);
					}
					else{
						$user_company['user_id'] = $id;
						$GLOBALS['db']->autoExecute(DB_PREFIX."user_company",$user_company,"INSERT");
					}
				}
				
				
				if(app_conf("OPEN_IPS") == 1){
					//showSuccess("验证成功",0,APP_ROOT."/index.php?ctl=collocation&act=CreateNewAcct&user_type=0&user_id=".$id);
					showSuccessIPS("验证成功",0,APP_ROOT."/index.php?ctl=collocation&act=CreateNewAcct&user_type=0&user_id=".$id);
				}
				else{
					showSuccess("注册成功",0,APP_ROOT."/");
				}
			}
			else
			{
				$error = $res['data'];		
				if(!$error['field_show_name'])
				{
					$error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_'.strtoupper($error['field_name'])];
				}
				if($error['error']==EMPTY_ERROR)
				{
					$error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'],$error['field_show_name']);
				}
				if($error['error']==FORMAT_ERROR)
				{
					$error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'],$error['field_show_name']);
				}
				if($error['error']==EXIST_ERROR)
				{
					$error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$error['field_show_name']);
				}
				showErr($error_msg);
			}
				
		}
		else{
			showErr("该用户尚未注册");  //尚未注册
		}
		
	}
	
	public function wx_login()
	{
		$img = url("index","ajax#weixin_login",array("rand"=>time()));
		$GLOBALS['tmpl']->assign("img",$img);
		$GLOBALS['tmpl']->display("inc/weixin_login.html");
	}
	public function registerll(){
		 require APP_ROOT_PATH."system/utils/Verify.php";
        require APP_ROOT_PATH."system/utils/BinkCard/Imagebase64.php";
        require APP_ROOT_PATH."system/utils/bankList.php";

        $data['uc_IDcard']=$_REQUEST['IDcard'];//身份证

            if(strpos($data['uc_IDcard'],"****") ){
                $data['uc_IDcard'] = $GLOBALS['db']->getOne("SELECT AES_DECRYPT(idno_encrypt,'".AES_DECRYPT_KEY."') as idno FROM ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
            }else{
                $data['uc_IDcard']=$data['uc_IDcard'];
            }

        $data['bank_mobile']=$_REQUEST['phone'];//手机号
        $data['validateCode']=$_REQUEST['bank_phone_code'];//验证码

        $verify_code=$GLOBALS['db']->getOne("SELECT verify_code  FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$data['bank_mobile']."' AND verify_code='".$data['validateCode']."'  AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC."");
        if($data['validateCode'] !=$verify_code){
            showErr("验证码不一样或已过期",1);
        }


        $data['bank_id'] = intval($_REQUEST['bank_id']);

		if($data['bank_id'] == 0)
		{
			$data['bank_id'] = intval($_REQUEST['otherbank']);
		}
		
		if($data['bank_id'] == 0)
		{
			showErr($GLOBALS['lang']['PLASE_ENTER_CARRY_BANK'],1);
		}
		
		$data['real_name'] = trim($_REQUEST['realname']);
        if($data['real_name'] == ""){
            showErr("请输入开户名",1);
        }

		$data['bankcard'] = trim($_REQUEST['bankcard']);
        $data['bankcard']=str_replace(" ","",$data['bankcard']);
        #$data['bankcard'] = preg_replace("/\s/","",$data['bankcard']);

		if(str_replace(" ","",$data['bankcard']) == ""){
			showErr($GLOBALS['lang']['PLASE_ENTER_CARRY_BANK_CODE'],1);
		}
		
		if(strlen($data['bankcard']) < 10){
			showErr("最少输入10位账号信息！",1);
		}
		
		$data['user_id'] = $GLOBALS['user_info']['id'];

        if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_bank WHERE bankcard='".$data['bankcard']."'  AND user_id=".$GLOBALS['user_info']['id']) > 0){
			showErr("该银行卡已存在",1);
		}
		$isset_id=$GLOBALS['db']->getOne("SELECT id FROM  ".DB_PREFIX."user_bank WHERE real_name='".$data['real_name']."'");
        if($isset_id>0){
        	showErr("认证失败,同一身份证不能绑定多个账号!",1);
        }
        $idno = $GLOBALS['db']->getOne("SELECT idno FROM  ".DB_PREFIX."user WHERE id=".$GLOBALS['user_info']['id']);
        $url = "http://verifyapi.huiyuenet.com/zxbank/verifyApi.do";
        $name=$data['real_name'];
        
        $phone =$data['bank_mobile'];
        #$bankCard = str_replace(" ","",$data['bankcard']);
        $bankCard =  $data['bankcard'];
        $idnum=$data['uc_IDcard'];
        $sid = "jxdbc";
        $cpserialnum = $this->orderId();
        $md5key = "l46g6i";
        $despwd = "9cwcweunozhw15ul6elezl5y";
        $vtype = "03";
        $verifyFun = new VerifyFun($url);
        $result=$verifyFun->zXBank($sid, $name, $idnum, $vtype, $phone, $bankCard, $cpserialnum, $despwd,$md5key);
        $array = json_decode($result,1);

        switch ($array['result']) {
            case 'BANKCONSISTENT':
                // if(!$idno){
                //     $res = $this->addCard($data['uc_IDcard'],$data['bank_id'],$data['real_name'],$data['bankcard'],$data['bank_mobile']);
                // }else{
                //     $res = $this->saveCard($data['bank_id'],$data['real_name'],$data['bankcard'],$data['bank_mobile']);
                // }
                 $res = $this->addCard($data['uc_IDcard'],$data['bank_id'],$data['real_name'],$data['bankcard'],$data['bank_mobile']);
                
                if($res){
                	 // 判断是否满足给邀请人发放奖励的条件
                	if(is_get_rewards($GLOBALS['user_info']['id'])){
                		$order_data['begin_time'] = TIME_UTC;
						$order_data['end_time'] = TIME_UTC + (12*24*60*60);
						$order_data['money'] = 20;
						$order_data['ecv_type_id'] = 5;
						$sn = unpack('H12',str_shuffle(md5(uniqid())));
						$order_data['sn'] = $sn[1];
						$order_data['password'] = rand(10000000,99999999);
						$order_data['user_id']=$GLOBALS['user_info']['pid'];
						$order_data['child_id']=$GLOBALS['user_info']['id'];
                		$result=$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$order_data,"INSERT");
                		if($result==false){
                			showErr("代金券发放失败",1);
                		}
                	}

                    showSuccess("绑定银行卡成功",1,'/index.php?ctl=user&act=stepthree');
                }else{
                    showErr("保存失败",1);
                }
                break;

            case 'BANKNOLIB':
                showErr("没有此银行卡信息",1);
                break;

            case 'BANKINCONSISTENT':
                showErr("银行卡信息不一致",1);
                break;

            case 'BANKUNKNOWN':
                showErr("银行卡信息未知",1);
                break;

            case 'FAIL':
            	$info = $this->fail($array['errmsg']);
                showErr("$info",1);
                break;
            default:
                showErr("银行卡信息未知",1);
                break;
        }

      //end
	}
	public function fail($err){
        switch ($err) {
            case 'ERR2011':
                $info = "不存在该银行账户";
                break;
            case 'ERR2012':
                $info = "数据传输错误，请稍后再试";
                break;
            case 'ERR2013':
                $info = "银行账户已停用";
                break;
            case 'ERR2014':
                $info = "数据传输错误，请稍后再试";
                break;
            case 'ERR2015':
                $info = "身份证号无效";
                break;
            case 'ERR2016':
                $info = "银行卡内部接口错误";
                break;
            case 'ERR2017':
                $info = "其他错误（银行返回）";
                break;
            case 'ERR9999':
                $info = "服务器错误，请稍后再试";
                break;
            default:
                # code...
                break;
        }
        return $info;
    }
	public function orderId(){
        $yCode = array('Q', 'W', 'E', 'R', 'T', 'Y', 'N', 'M', 'C', 'O');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
     //添加银行卡
    public function addCard($uc_IDcard,$bank_id,$real_name,$bankcard,$bank_mobile){
        $user_info['user_id'] = $GLOBALS['user_info']['id'];
        $user_info['real_name'] = $real_name;
        #$user_info['bankcard'] = str_replace(" ","",$bankcard);
        $user_info['bankcard'] =$bankcard;
        $user_info['bank_id'] = $bank_id;
        $user_info['bank_mobile'] = $bank_mobile;
        $res = $GLOBALS['db']->getOne("SELECT * FROM  ".DB_PREFIX."user_bank WHERE user_id=".$GLOBALS['user_info']['id']);
        if(!$res){
            $result	=$GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info,"INSERT");
        }
        if(isset($result)){
            $data['real_name'] = $real_name;
            #$data['idno'] = str_replace(" ","",$bankcard);
            $data['idno'] = $uc_IDcard;
            $data["real_name_encrypt"] = " AES_ENCRYPT('".$real_name."','".AES_DECRYPT_KEY."') ";
            $data["idno_encrypt"] = " AES_ENCRYPT('".$uc_IDcard."','".AES_DECRYPT_KEY."') ";
            $data["idcardpassed"] = 1;

            $addlt= $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);

            return $addlt;
        }
    }

    //更换银行卡
    // public function saveCard($bank_id,$real_name,$bankcard,$bank_mobile){
    //     $user_info['real_name'] = $real_name;
    //     #$user_info['bankcard'] = str_replace(" ","",$bankcard);
    //     $user_info['bankcard']  = $bankcard;
    //     $user_info['bank_id'] = $bank_id;
    //     $user_info['bank_mobile'] = $bank_mobile;
    //     $result	= $GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$user_info,"UPDATE","user_id=".$GLOBALS['user_info']['id']);
    //     if(isset($result)){
    //         $data['real_name'] = $real_name;
    //         $data['idno'] = $bankcard;
    //         $data["real_name_encrypt"] = " AES_ENCRYPT('".$real_name."','".AES_DECRYPT_KEY."') ";
    //         $data["idno_encrypt"] = " AES_ENCRYPT('".$uc_IDcard."','".AES_DECRYPT_KEY."') ";
    //         $data["idcardpassed"] = 1;
    //         $lt= $GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);
    //         return $lt;
    //     }
    // }
    // 设置交易密码
    public function user_reg_settingpwd(){
    	$ajax = intval($_REQUEST['ajax']);
		if(!$GLOBALS['user_info']){
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}

		$paypassword = FW_DESPWD(trim($_REQUEST['paypassword']));
		if(!preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/",$paypassword)){
			showErr("交易密码为6-16位字母数字组合",$ajax);
		}
		$user_pwd =  $GLOBALS['db']->getOne("SELECT user_pwd FROM ".DB_PREFIX."user where id =".$GLOBALS['user_info']['id']);
		if($user_pwd == MD5($paypassword)){
			showErr("交易密码不能是登录密码",$ajax);
		}
		$GLOBALS['db']->query("update ".DB_PREFIX."user set paypassword='".md5($paypassword)."', bind_verify = '', verify_create_time = 0 where id = ".intval($GLOBALS['user_info']['id']));
		if($GLOBALS['db']->affected_rows() > 0){
			showSuccess($GLOBALS['lang']['MOBILE_BIND_SUCCESS'],$ajax,'/index.php?ctl=user&act=success');
		}
		else{
			showErr("绑定失败",$ajax);
		}
    }
    public function success(){
    	if(!$GLOBALS['user_info']){
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}
		$GLOBALS['tmpl']->assign("jump","index.php");
		$GLOBALS['tmpl']->display("success.html");
    }
}
?>