<?php

//关于代金券的全局函数
/**
 * 代金券发放
 * @param $ecv_type_id 代金券类型ID
 * @param $user_id  发放给的会员。0为线下模式的发放
 */
function send_voucher($ecv_type_id,$user_id=0,$is_password=false,$money = 0,$child_id=0,$msg="")
{
	$ecv_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where id = ".$ecv_type_id);
	if(!$ecv_type)return false;
	if($is_password)$ecv_data['password'] = rand(10000000,99999999);
	$ecv_data['use_limit'] = $ecv_type['use_limit'];
	if($ecv_type['begin_time']==0)
	{
		$ecv_type['begin_time'] = TIME_UTC;
	}
	$ecv_data['begin_time'] = $ecv_type['begin_time'];
	if(app_conf("INTERESTRATE_TIME")>0)
	{
		$ecv_data['end_time'] = to_timespan(to_date($ecv_type['begin_time'])." ".app_conf("INTERESTRATE_TIME")." month -1 day");
		$ecv_data['end_time'] = to_timespan(date("Y-m-d 23:59:59",$ecv_data['end_time']));
	}
	if($money > 0)
	{
		$ecv_data['money'] = $money;
	}
	else
	{
		$ecv_data['money'] = $ecv_type['money'];
	}
	$ecv_data['ecv_type_id'] = $ecv_type_id;
	$ecv_data['user_id'] = $user_id;	
	var_dump(1110);die;
	do{
		$sn = unpack('H12',str_shuffle(md5(uniqid())));
		$sn = $sn[1];
		$ecv_data['sn'] = $sn;
		$ecv_data['child_id'] = $child_id;
		if($msg){
			$ecv_data['content'] = $msg;
		}else{
			$ecv_data['content'] = $ecv_type['name'];
		}
		/*$user =$GLOBALS['user_info']['cunguan_tag'];
		if($user==1){*/
		$ecv_data['cunguan_tag'] =1;
		/*}*/
		//$ecv_data['sn'] = md5(TIME_UTC);
		$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$ecv_data,'INSERT','','SILENT');
		$insert_id = $GLOBALS['db']->insert_id();
	}while(intval($insert_id) == 0);
	if($insert_id)
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."ecv_type set gen_count = gen_count + 1 where id = ".$ecv_type_id);
	}
	return $insert_id;
}
/*加息卡发放
* interestrate_id 加息卡类型id
* $user_id 用户id
* msg 描述
*/
function send_interestrate($interestrate_id,$user_id=0,$msg='')
{
	$interest_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."coupon where id = ".$interestrate_id);
	if(!$interest_type)return false;
	$interest_data['rate'] = $interest_type['rate'];
    $interest_data['use_time'] = $interest_type['interest_time'];//加息时长
	$interest_data['coupon_id'] = $interestrate_id;
	$interest_data['begin_time'] = TIME_UTC;
	$day = $interest_type['term_validity'] - 1;	//结束时间按照当天开始计算
	$interest_data['end_time'] = strtotime(date('Y-m-d 23:59:59',strtotime("+$day day")));		//红包结束时间term_validity
	$interest_data['create_time'] = TIME_UTC;
	$interest_data['user_id'] = $user_id;	
	do{
		if($msg){
			$interest_data['content'] = $msg;
		}else{
			$interest_data['content'] = $interest_type["card_name"];
		}
		$GLOBALS['db']->autoExecute(DB_PREFIX."interest_card",$interest_data,'INSERT','','SILENT');
		$insert_id = $GLOBALS['db']->insert_id();
	}while(intval($insert_id) == 0);
	return $insert_id;
}

//加息券发放
/*function send_interestrate($ecv_type_id,$user_id=0,$is_password=false)
{
	$ecv_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."interestrate_type where id = ".$ecv_type_id);
	if(!$ecv_type)return false;
	if($is_password)$ecv_data['password'] = rand(10000000,99999999);
	$ecv_data['use_limit'] = $ecv_type['use_limit'];
	$ecv_data['begin_time'] = $ecv_type['begin_time'];
	$ecv_data['end_time'] = $ecv_type['end_time'];
	$ecv_data['rate'] = $ecv_type['rate'];
	$ecv_data['ecv_type_id'] = $ecv_type_id;
	$ecv_data['user_id'] = $user_id;	
	$ecv_data['use_type'] = $use_type["use_type"];	

	do{
		$sn = unpack('H12',str_shuffle(md5(uniqid())));
		$sn = $sn[1];
		$ecv_data['sn'] = $sn;
		//$ecv_data['sn'] = md5(TIME_UTC);
		$GLOBALS['db']->autoExecute(DB_PREFIX."interestrate",$ecv_data,'INSERT','','SILENT');
		$insert_id = $GLOBALS['db']->insert_id();
	}while(intval($insert_id) == 0);
	if($insert_id)
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."interestrate_type set gen_count = gen_count + 1 where id = ".$ecv_type_id);
	}
	return $insert_id;
}
*/
//体验金发放 2017.2.15 wwm
function send_taste($taste_id,$user_id=0,$get_way)
{ 
	$now_time = to_date(TIME_UTC,'Y-m-d');
	$taste_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."taste_cash_config where status = 1 and id = ".$taste_id);
	$taste_data['user_id'] = $user_id;
	$taste_data['taste_cash_id'] = $taste_id;
	$taste_data['disc'] = $taste_type['title'];
	$taste_data['money'] = $taste_type['money'];
	$taste_data['interest'] = round(($taste_type['money'] * $taste_type['rate'] * $taste_type['time_limit'])/365/100,2);
	$taste_data['end_time'] = TIME_UTC + $taste_type['able_time'] * 24*3600;
	$taste_data['create_time'] = TIME_UTC;
	$taste_data['use_status'] = 0;
	$taste_data['cunguan_tag'] = 1;
	$taste_data['get_way'] = $get_way;
	if($taste_type){
		$GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash",$taste_data,'INSERT','','SILENT');
	}
	$insert_id = $GLOBALS['db']->insert_id();
	if($insert_id){
		$data['user_id'] = $user_id;
		$data['taste_cash_id'] = $taste_id;
		$data['create_time'] = TIME_UTC;
		$data['change'] = $taste_type['money'];
		$data['device'] = $get_way;
		$data['detail'] = '获得-'.$taste_type['title'];
		$data['taste_id'] = $taste_type['id'];
		$data['cunguan_tag'] = 1;
		$GLOBALS['db']->autoExecute(DB_PREFIX."taste_cash_log",$data,'INSERT','','SILENT');
	}	
	return $insert_id;	
}
//体验金发放
function send_learn($learn_id,$user_id=0,$is_password=false)
{

	$now_time = to_date(TIME_UTC,'Y-m-d');
	$learn_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."learn_type where is_effect = 1 and id = ".$learn_id);
	$learn_send_data['user_id'] = $user_id;
    $learn_send_data['type_id'] = $learn_id;
    $learn_send_data['money'] = $learn_type['money'];
    $learn_send_data['type'] = 2;
    $learn_send_data['begin_time'] = $now_time;
    
    $end_time = to_timespan($learn_send_data['begin_time'])+$learn_type['time_limit'] * 24 * 3600 ;
    $learn_send_data['end_time'] = to_date($end_time,'Y-m-d');
    $learn_send_data['is_use'] = 0;
    $learn_send_data['is_effect'] = 1;
    if($learn_type){
    	$GLOBALS['db']->autoExecute(DB_PREFIX."learn_send_list",$learn_send_data,'INSERT','','SILENT');
    }
	$insert_id = $GLOBALS['db']->insert_id();
	
	return $insert_id;
}

    /**
     * 红包发放
     * @param $red_packet_type_id 红包类型ID
     * @param $user_id  发放给的会员。0为线下模式的发放
     */
    function red_packet($red_packet_type_id,$user_id=0,$msg="",$money)
    {
    	$red_packet_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."red_packet_newconfig where id = ".$red_packet_type_id);
    	if(!$red_packet_type)return false;
    	$red_packet_data['use_limit'] = $red_packet_type['use_limit']; //红包可用天数
    	$red_packet_data['begin_time'] = time();	//红包开始时间
    	$day = $red_packet_data['use_limit'] - 1;	//结束时间按照当天开始计算
    	$red_packet_data['end_time'] = strtotime(date('Y-m-d 23:59:59',strtotime("+$day day")));		//红包结束时间
    	$red_packet_data['packet_type'] = $red_packet_type['red_type'];
    	if($money > 0)
    	{
    		$red_packet_data['money'] = $money;
    	}
    	else
    	{
    		$red_packet_data['money'] = $red_packet_type['amount'];
    	}
    	$red_packet_data['create_time'] = time();
    	$red_packet_data['red_type_id'] = $red_packet_type_id;
    	$red_packet_data['user_id'] = $user_id;	
    
    	do{
    		$sn = unpack('H12',str_shuffle(md5(uniqid())));
    		$sn = $sn[1];
    		$red_packet_data['sn'] = $sn;
    
    		if($msg){
    			$red_packet_data['content'] = $msg;
    		}else{
    			$red_packet_data['content'] = $red_packet_type['red_name'];
    		}
    		$GLOBALS['db']->autoExecute(DB_PREFIX."red_packet",$red_packet_data,'INSERT','','SILENT');
    		$insert_id = $GLOBALS['db']->insert_id();
    	}while(intval($insert_id) == 0);
    	return $insert_id;
    }

      function send_honbao($red_packet_type_id,$user_id=0,$msg="",$money){
        $red_packet_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."red_packet_newconfig where id = ".$red_packet_type_id);
        if(!$red_packet_type)return false;
        $red_packet_data['use_limit'] = $red_packet_type['use_limit']; //红包可用天数
        $red_packet_data['begin_time'] = time();	//红包开始时间
        $day = $red_packet_data['use_limit'] - 1;	//结束时间按照当天开始计算
    	$red_packet_data['end_time'] = strtotime(date('Y-m-d 23:59:59',strtotime("+$day day")));		//红包结束时间
        $red_packet_data['packet_type'] = $red_packet_type['red_type'];
        $red_packet_data['money'] = $money;
        $red_packet_data['create_time'] = time();
        $red_packet_data['red_type_id'] = $red_packet_type_id;
        $red_packet_data['user_id'] = $user_id;
        do{
            $sn = unpack('H12',str_shuffle(md5(uniqid())));
            $sn = $sn[1];
            $red_packet_data['sn'] = $sn;
        
            if($msg){
                $red_packet_data['content'] = $msg;
            }else{
                $red_packet_data['content'] = $red_packet_type['red_name'];
            }
            $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet",$red_packet_data,'INSERT','','SILENT');
            $insert_id = $GLOBALS['db']->insert_id();
        }while(intval($insert_id) == 0);
        return $insert_id;
}

/*七夕加息卡发放
 * interestrate_id 加息卡类型id
 * $user_id 用户id
 * msg 描述
 */
function send_jiaxika($interestrate_id,$rate,$user_id=0,$msg='')
{
    $interest_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."coupon where id = ".$interestrate_id);
    if(!$interest_type)return false;
    $interest_data['rate'] = $rate;
    $interest_data['use_time'] = $interest_type['interest_time'];
    $interest_data['coupon_id'] = $interestrate_id;
    $interest_data['begin_time'] = TIME_UTC;
    $day = $interest_type['term_validity'] - 1;	//结束时间按照当天开始计算
    $interest_data['end_time'] = strtotime(date('Y-m-d 23:59:59',strtotime("+$day day")));		//红包结束时间term_validity
    $interest_data['create_time'] = TIME_UTC;
    $interest_data['user_id'] = $user_id;
    do{
        if($msg){
            $interest_data['content'] = $msg;
        }else{
            $interest_data['content'] = $interest_type["card_name"];
        }
        $GLOBALS['db']->autoExecute(DB_PREFIX."interest_card",$interest_data,'INSERT','','SILENT');
        $insert_id = $GLOBALS['db']->insert_id();
    }while(intval($insert_id) == 0);
    return $insert_id;
}

       /*
       * 活动时间
       * author:zhangyi
       */
   function timeDay($st,$et){

        if (empty($st) || empty($et)) {
            $time['status'] = false;
            return $time;
        }

        if (time() < $st) {
            $time['status'] = false;
            $time['info'] = "活动未开始";

        } else if (time() > $et) {
            $time['status'] = false;
            $time['info'] = "活动已结束";
        } else {

            $time['status'] = true;

        }

        return $time;
    }



    /**
     * 红包发放
     * @param $red_packet_type_id 红包类型ID
     * @param $user_id  发放给的会员。0为线下模式的发放
     */
    function send_red_packet($red_packet_type_id,$user_id=0,$msg="",$money)
    {
    	$red_packet_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."red_packet_newconfig where id = ".$red_packet_type_id);
    	if(!$red_packet_type)return false;
    	$red_packet_data['use_limit'] = $red_packet_type['use_limit']; //红包可用天数
    	$red_packet_data['begin_time'] = TIME_UTC;	//红包开始时间
    	$day = $red_packet_data['use_limit'] - 1;	//结束时间按照当天开始计算
    	$red_packet_data['end_time'] = strtotime(date('Y-m-d 23:59:59',strtotime("+$day day")));		//红包结束时间
    	$red_packet_data['packet_type'] = $red_packet_type['red_type'];
    	if($money > 0)
    	{
    		$red_packet_data['money'] = $money;
    	}
    	else
    	{
    		$red_packet_data['money'] = $red_packet_type['amount'];
    	}
    	$red_packet_data['create_time'] = TIME_UTC;
    	$red_packet_data['red_type_id'] = $red_packet_type_id;
    	$red_packet_data['user_id'] = $user_id;	
    
    	do{

            $GLOBALS['db']->startTrans();
            $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."red_packet WHERE id =".$user_id."  FOR UPDATE");
    		$sn = unpack('H12',str_shuffle(md5(uniqid())));
    		$sn = $sn[1];
    		$red_packet_data['sn'] = $sn;
    
    		if($msg){
    			$red_packet_data['content'] = $msg;
    		}else{
    			$red_packet_data['content'] = $red_packet_type['red_name'];
    		}
    		$GLOBALS['db']->autoExecute(DB_PREFIX."red_packet",$red_packet_data,'INSERT','','SILENT');
    		$insert_id = $GLOBALS['db']->insert_id();

            if($insert_id){
                $GLOBALS['db']->commit();
            }else{
                
                $GLOBALS['db']->rollback();
            }
    	}while(intval($insert_id) == 0);
    	
    	
    	
    	return $insert_id;
    }


?>