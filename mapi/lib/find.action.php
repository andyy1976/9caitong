<?php

require APP_ROOT_PATH.'app/Lib/deal.php';
class find
{
	public function index(){
		$root = get_baseroot();
		$root['response_code'] = 1;
		$root['session_id'] = es_session::id();
		$id=$GLOBALS['user_info']['id'];
		$url = WAP_SITE_DOMAIN;
		//轮播图片
		$adv = $GLOBALS['db']->getAll(" select id,app_page,img,type,url from ".DB_PREFIX."app_fx_nav_cg where is_effect =1 and device!=1 and UNIX_TIMESTAMP(add_time) < ".TIME_UTC." and UNIX_TIMESTAMP(end_time) > ".TIME_UTC." order by sort desc ");
		foreach($adv as $k=>$v)
		{
			if ($v['img'] != ''){
				$v['img'] = get_abs_img_root(get_spec_image($v['img'],0,0,1));

			}
			if($v['type']==2){
				$v['url']=$GLOBALS['db']->getOne("select url_cg from ".DB_PREFIX."app_internal where is_effect=1 and id=".$v['app_page']);
        	}
            $adv_list[] = $v;
		}
		$find['adv_list'] = $adv_list;
		//正在进行中的活动
		//$activity = $GLOBALS['db']->getAll("select id,title,end_time,add_time,app_page,is_login,img,name,type,url,act_type from ".DB_PREFIX."app_activity_cg where act_type = 1 and device != 1 and is_effect =1 and UNIX_TIMESTAMP(end_time) > ".TIME_UTC." order by sort desc");
 		$activity = $GLOBALS['db']->getAll("select id,title,end_time,app_page,is_login,img,newimg,name,type,url,act_type from ".DB_PREFIX."app_activity_cg where act_type = 1 and device != 1 and is_effect =1 and UNIX_TIMESTAMP(add_time)<".TIME_UTC." and  UNIX_TIMESTAMP(end_time) > ".TIME_UTC." order by sort desc");
		foreach ($activity as $k => $v) {
			$v['is_login'] = 0;
			$v['end_time'] = intval((strtotime($v['end_time']) - TIME_UTC)/24/60/60);
//			$v['wap_img'] = get_abs_img_root(get_spec_image($v['wap_img'],0,0,1));
//			$v['appwap_url']= $v['appwap_url'];
			$v['wap_img'] =$v['img'];
			$v['img'] =$v['newimg'];
			$v['appwap_url']=$v['url'];
			$v['act_msg'] = "进行中";
			$v['title'] = $v['name'];
			$v['name'] = "";
			

			/* if(strtotime($v['add_time'])>TIME_UTC){
				unset($v);
			}else{
				$act_list[] = $v;
			} */
			$act_list[] = $v;

		}
		$find['act_list'] = $act_list;
		//已弃用 
		$act_long = $GLOBALS['db']->getAll("select id,title,app_page,is_login,img,name,type,url,act_type from ".DB_PREFIX."app_activity_cg where act_type = 2 and device != 1 and is_effect =1 and UNIX_TIMESTAMP(add_time) < ".TIME_UTC." order by sort desc");
		foreach ($act_long as $k => $v) {
            $MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
            if($MachineInfo[0]=='iOS'){
                if($MachineInfo[3]<'2.1.2'){
                	if($v['app_page']==14){
                        unset($act_long[$k]);
                        continue;
					}

                }
            }elseif($MachineInfo[0]=='Android'){
                if($MachineInfo[1]<'2.1.2'){
                    if($v['app_page']==14){
                        unset($act_long[$k]);
                        continue;
					}

                }
            }
			$v['img'] = get_abs_img_root(get_spec_image($v['img'],0,0,1));
            if($v['type']==2){
                $v['url']=$GLOBALS['db']->getOne("select url_cg from ".DB_PREFIX."app_internal where is_effect=1 and id=".$v['app_page']);
            }
			$int_mall[] = $v;
		}
		$find['int_mall'] = $int_mall;
		//已弃用 
		$A_MachineInfo = base64_decode($GLOBALS['request']['MachineInfo']);
		$App = explode("|||",$A_MachineInfo);
		if($App[0]=='iOS' &&  str_replace(".","",$App[3]) == '218'){
			$icons = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."app_cloumn_cg where versions != 1 and clo_type = 'find' order by id ASC ");
		}elseif ($App[0]=='Android' &&  str_replace(".","",$App[1]) == '220'){
			$icons = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."app_cloumn_cg where versions != 1 and clo_type = 'find' order by id ASC ");
		}else{
			$icons = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."app_cloumn_cg where versions != 2 and clo_type = 'find' order by id ASC ");
		}
		foreach($icons as $k=>$v)
		{
			$v['img'] = get_abs_img_root(get_spec_image($v['img'],0,0,1));
            if($v['type']==2){
                $v['url']=$GLOBALS['db']->getOne("select url_cg from ".DB_PREFIX."app_internal where is_effect=1 and id=".$v['app_page']);
            }
			$column[] = $v;	
		}
		$find['column'] = $column;


		//平台共计发放现金(元) 2017.12.10 wangwenming
	    $start_time = strtotime(date("Y-m-d"));
	    $end_time = strtotime(date("Y-m-d 23:59:59"));
	    $red_packet['money'] = number_format(count($GLOBALS['db']->getAll("SELECT user_id FROM ".DB_PREFIX."red_packet_rob  where rob_time >=".$start_time." and rob_time <=".$end_time." group by user_id")));
	    $red_packet['money_msg'] = "当前参与人数(人)";
	    $red_packet['count'] = number_format($GLOBALS['db']->getOne("SELECT sum(send_red_money) FROM ".DB_PREFIX."red_packet_send"));
	    $red_packet['count_msg'] = "平台共计发放现金(元)";
	    $root['red_packet'] = $red_packet;
	    //全平台排名
	    /*$pt_list = $GLOBALS['db']->getAll("SELECT r.user_id,sum(rob_red_money) as money,u.real_name,u.header_url from ".DB_PREFIX."red_packet_rob r LEFT JOIN ".DB_PREFIX."user u on r.user_id = u.id GROUP BY user_id ORDER BY money DESC limit 0,3");
	    foreach ($pt_list as $k => $v) {
	      if($k == 0)
	      	$pt_list[$k]['icon'] = WAP_SITE_DOMAIN."/app/Tpl/wap/images/wap2/fi/icon_rank_1.png";
	      else if($k == 1)
	      	$pt_list[$k]['icon'] = WAP_SITE_DOMAIN."/app/Tpl/wap/images/wap2/fi/icon_rank_2.png";
	      else
	      	$pt_list[$k]['icon'] = WAP_SITE_DOMAIN."/app/Tpl/wap/images/wap2/fi/icon_rank_3.png";
	      $pt_list[$k]['real_name'] = $v['real_name']?'*'.cut_str($v['real_name'], 1, -1):"";
	      $pt_list[$k]['header_url'] = $v['header_url']?$v['header_url']:"";
	      $pt_list[$k]['key']  = $k;
	    }
	    $root['pt_list'] = $pt_list;*/
	    //好友排名
	    /*$redis = new Redis();
	    //$redis->connect('127.0.0.1', 6379);
	    $redis->connect(REDIS_HOST, REDIS_PORT);
	    $redis->auth(REDIS_PWD);
	    $redis->select(8);
	    //查找好友本周抢到的红包总额
	    $last_monday_time = strtotime('-2 monday');
	    $next_monday_time = strtotime("next monday"); //下周一零点
	    $monday_time = strtotime(date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600))); 
	    $red_friends_list = json_decode($redis->hGet(REDIS_PREFIX."red_friends_list",$id),true);
	    $friends_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."red_packet_friends where status = 0 and user_id=".$id);
	    if(empty($red_friends_list) || count($red_friends_list)<$friends_count){
	      $red_friends_list = $GLOBALS['db']->getAll("select friend_id  from ".DB_PREFIX."red_packet_friends  where status = 0 and user_id = ".$id );
	      $redis->hset(REDIS_PREFIX."red_friends_list",$id,json_encode($red_friends_list));
	    }
	    
	    foreach ($red_friends_list as $k => $v) {
	        //好友信息
	        $friend_info = json_decode($redis->hGet(REDIS_PREFIX."user_info",$v['friend_id']),true);
	        if(empty($friend_info)){
	          $friend_info = $GLOBALS['db']->getRow("select id,header_url,real_name,mobile from ".DB_PREFIX."user where id=".$v['friend_id']);
	          $friend_info['mobile'] = cut_str($friend_info['mobile'], 3, 0).'****'.cut_str($friend_info['mobile'], 2, -2);
	          $redis->hSet(REDIS_PREFIX.'user_info',$v['friend_id'],json_encode($friend_info));
	        }
	        
	        if($id == $v['friend_id']){
	          $friend_info['realname'] = '我';
	          $friend_info['mobile'] = '我';
	      }
	      $red_friends_info[$k] = $friend_info;
	      $red_money_total = $redis->zScore(REDIS_PREFIX."red_money_total",$v['friend_id']);
	      if(empty($red_money_total)){
	        $red_money_total = $GLOBALS['db']->getOne("select SUM(rob_red_money) from ".DB_PREFIX."red_packet_rob where user_id = ".$v['friend_id']." and rob_time >=".$monday_time." and rob_time <=".$next_monday_time);
	        $red_money_total = $red_money_total?$red_money_total:0;
	        $redis->zAdd(REDIS_PREFIX."red_money_total",$red_money_total,$v['friend_id']);
	        $redis->expire(REDIS_PREFIX."red_money_total",$next_monday_time-time()+600);
	      }
	      
	      $red_friends_info[$k]['money'] = strval(sprintf("%.2f", $red_money_total));
	      $sort[] = $red_friends_info[$k]['money'];
	    }
	    array_multisort($sort,SORT_DESC,$red_friends_info);
	    $red_list=array_slice($red_friends_info,0,3);
	    foreach ($red_list as $k => $v) {
	    	if($k == 0)
		      	$red_list[$k]['icon'] = WAP_SITE_DOMAIN."/app/Tpl/wap/images/wap2/fi/icon_rank_1.png";
		      else if($k == 1)
		      	$red_list[$k]['icon'] = WAP_SITE_DOMAIN."/app/Tpl/wap/images/wap2/fi/icon_rank_2.png";
		      else
		      	$red_list[$k]['icon'] = WAP_SITE_DOMAIN."/app/Tpl/wap/images/wap2/fi/icon_rank_3.png";
		    if (!$v['realname']) {
		        $red_list[$k]['real_name'] = $v['mobile'];
		     }else{
	        $red_list[$k]['real_name'] = $v['realname'];
	      }
	    }
	    $root['friends_list'] = $red_list;*/
	    //商城列表
	    $goods=$GLOBALS['db']->getAll("SELECT id,is_new,img as banner_img,name,discount_score,is_flash_sale,discount_score,score,sub_name FROM ".DB_PREFIX."goods where is_ground != 0 and (is_new = 1 or is_flash_sale=1) and (if(is_ground = 3, ground_time <= ".TIME_UTC .",1=1)) order by is_flash_sale desc,is_new desc LIMIT 0,4");
	    foreach ($goods as $key => $value) {
	    	$goods[$key]['url'] = WAP_SITE_DOMAIN."/index.php?ctl=find&act=mall_details_goods&id=".$value['id'];
	    	$goods[$key]['score'] = $value['score']."积分";
	    	$goods[$key]['type'] = "1";
	    	$goods[$key]['name'] = $value['sub_name'];
	    	if($value['is_flash_sale'] == 1)
	    		$goods[$key]['title'] = "限时折扣";
	    	else
	    		$goods[$key]['title'] = "新品";
	    }
	    $root['goods_url'] = WAP_SITE_DOMAIN."/index.php?ctl=find&act=new_mall";
	    $root['goods'] = $goods;
		$find['recharge_url']=$url.'/member.php?ctl=uc_money&act=incharge';
		//好友排名

		$find['webview'] = $GLOBALS['db']->getRow("select name,height,is_effect,url from ".DB_PREFIX."app_web_view where name='发现' limit 1");
		$root['item'] = $find;
		output($root);
	}
}
?>

