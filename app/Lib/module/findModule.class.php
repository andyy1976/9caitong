<?php
/**
* wap手机版本发现栏目
*/
require APP_ROOT_PATH.'app/Lib/page.php';
require APP_ROOT_PATH.'system/libs/user.php';
class findModule extends SiteBaseModule
{
	public function index(){
    jumpUrl("jump_url_invite");
		//广告列表
	  $id=$GLOBALS['user_info']['id'];
	  $mobile=$GLOBALS['user_info']['mobile'];
		$adv_list = get_wap_nav();

    //国庆活动单独设置 活动banner
    foreach ($adv_list as $key => $value) {
       if($value['id']==53){//正式环境id需改
             $adv_list[$key]['url']="http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=42&phone=".$mobile."&activity_id=".$value['id'];
       }
    }
		//以前的活动列表展示被弃用--朱湘
		/*$activity = get_activity_cloumn(1);
        foreach ($activity as $k => $v) {
            $v['time'] = intval((strtotime($v['end_time']) - TIME_UTC)/24/60/60);
            $list[] = $v;
        }*/
        //正在进行中的活动
//        $activity = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."activity where is_effect = 1 and disable =1 and is_delete=1 and cunguan_tag=1 and start_time < ".TIME_UTC." and end_time > ".TIME_UTC." and use_way=1 or use_way=3 order by activity_id desc");
        $activity =   $GLOBALS['db']->getAll("select id,title,end_time,app_page,img,wapimg,name,type,url,act_type from ".DB_PREFIX."app_activity_cg where act_type = 1  and device !=2 and is_effect =1 and UNIX_TIMESTAMP(add_time) < ".TIME_UTC." and UNIX_TIMESTAMP(end_time) > ".TIME_UTC." order by sort desc");
        foreach ($activity as $k => $v) {
            $v['end_time'] = intval((strtotime($v['end_time']) - TIME_UTC)/24/60/60);
            $v['appwap_url']= $v['url'];
            $v['wap_img']= $v['wapimg'];

            $list[] = $v;
        }
        foreach ($list as $key=>$value){
            if($value['id']==22){
               $list[$key]['appwap_url']="http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=34&phone=".$mobile."&activity_id=".$value['id'];
            }else{
               $list[$key]['appwap_url']=$value['appwap_url'];
            }
        }


        foreach ($list as $key=>$value){
          //国庆活动单独设置 活动列表
            if($value['id']==11){//正式环境需改11 测试15
                 $list[$key]['appwap_url']="http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=42&phone=".$mobile."&activity_id=".$value['id'];
           }
        }
        $wap_cloumn = get_wap_cloumn();

		//每日任务与邀请有礼
		$code = $GLOBALS['user_info']['mobile'];
		$wap_cloumn[0]['url'] = "http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=28&phone=".$code;//每日任务
		//$wap_cloumn[1]['url'] = "http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=29&code=".$code;//邀请有礼
		$wap_cloumn[1]['url'] = "http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=32&code=".$code;//邀请有礼
		
		$act_long = get_activity_cloumn(2);
        

		$GLOBALS['tmpl']->assign("act_long",$act_long);
    $GLOBALS['tmpl']->assign("act_long_json",json_encode($act_long,JSON_UNESCAPED_UNICODE));
		$GLOBALS['tmpl']->assign("wap_cloumn",$wap_cloumn);
    jumpUrl("jump_url_info");
        /*移动端交互处理*/
    $jump = machineInfo();
    $GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("user_id",$id);
		$GLOBALS['tmpl']->assign("mobile",$mobile);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("adv_list",$adv_list);
    //积分商城
    $goods=$GLOBALS['db']->getAll("SELECT id,is_new,img as banner_img,name,discount_score,is_flash_sale,discount_score,score,sub_name FROM ".DB_PREFIX."goods where is_ground != 0 and (is_new = 1 or is_flash_sale=1) and (if(is_ground = 3, ground_time <= ".TIME_UTC .",1=1)) order by is_flash_sale desc,is_new desc LIMIT 0,4");
    $GLOBALS['tmpl']->assign("goods",$goods);
    //平台共计发放现金(元)
    $start_time = strtotime(date("Y-m-d"));
    $end_time = strtotime(date("Y-m-d 23:59:59"));
    $red_packet['count'] = number_format(count($GLOBALS['db']->getAll("SELECT user_id FROM ".DB_PREFIX."red_packet_rob  where rob_time >=".$start_time." and rob_time <=".$end_time." group by user_id")));
    $red_packet['money'] = number_format($GLOBALS['db']->getOne("SELECT sum(send_red_money) FROM ".DB_PREFIX."red_packet_send"));
    $GLOBALS['tmpl']->assign("red_packet",$red_packet);
    //全平台排名
    /*$pt_list = $GLOBALS['db']->getAll("SELECT r.user_id,sum(rob_red_money) as money,u.real_name,u.header_url from ".DB_PREFIX."red_packet_rob r LEFT JOIN ".DB_PREFIX."user u on r.user_id = u.id GROUP BY user_id ORDER BY money DESC limit 0,3");
    foreach ($pt_list as $k => $v) {
      $pt_list[$k]['real_name'] = $v['real_name']?'*'.cut_str($v['real_name'], 1, -1):'';
      $pt_list[$k]['key']  = $k;
    }
    $GLOBALS['tmpl']->assign("pt_list",$pt_list);*/
    //好友排名
   /* $redis = new Redis();
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

      if (!$v['realname']) {
        $red_list[$k]['real_name'] = $v['mobile'];
      }else{
        $red_list[$k]['real_name'] = $v['realname'];
      }
      
    }

    $GLOBALS['tmpl']->assign("red_list",$red_list);*/
    $GLOBALS['tmpl']->assign("cate_title","发现");
		$GLOBALS['tmpl']->display("page/find.html");
	}
	public function find_ago(){
        //已经结束的活动

        $activity =   $GLOBALS['db']->getAll("select id,title,end_time,app_page,img,wapimg,name,type,url,act_type from ".DB_PREFIX."app_activity_cg where act_type = 1  and device !=2 and is_effect =1  and UNIX_TIMESTAMP(end_time) < ".TIME_UTC." order by sort desc limit 0,10");
        foreach ($activity as $k => $v) {
            $v['end_time'] = intval((strtotime($v['end_time']) - TIME_UTC)/24/60/60);
            $v['appwap_url']= $v['appwap_url'];
            $list[] = $v;
        }
        //以前的活动列表展示被弃用--朱湘
		/*$activity = get_activity_cloumn(3);
		foreach ($activity as $k => $v) {
			$v['time'] = intval((strtotime($v['end_time']) - TIME_UTC)/24/60/60);
			$list[] = $v;
		}*/
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->display("page/active_ago.html");
	}
	public function find_view(){
		$id = intval($_REQUEST['id']);
		$activity = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."article where id = ".$id);
		
		$GLOBALS['tmpl']->assign("activity",$activity);
		$GLOBALS['tmpl']->display("page/find_view.html");
	}
	public function W1(){
		$activity_id = $_REQUEST['id'];
		$user_list = get_referer_list($activity_id,1);
		$data = $this->happy_zuo($user_list,$activity_id);
		$GLOBALS['tmpl']->assign("data",$data);
		$data_y = $this->happy_you($user_list,$activity_id);
		$GLOBALS['tmpl']->assign("data_y",$data_y);
		$GLOBALS['tmpl']->assign("user_list",$user_list);
		$GLOBALS['tmpl']->assign("activity_id",$activity_id);
		$GLOBALS['tmpl']->assign("user_id",$GLOBALS['user_info']['id']);
		if(file_exists("./app/Tpl/wap/page/activity/W1.html")){
			$GLOBALS['tmpl']->display("page/activity/W1.html");
		}
	}
	function happy_zuo($list,$activity_id)
    {
        $data = array(
            0 => array('num' => '1', 'key' => '5人以下', 'val' => '10', 'yes' => '0'),
            1 => array('num' => '5', 'key' => '5', 'val' => '30', 'yes' => '0'),
            2 => array('num' => '6', 'key' => '6', 'val' => '40', 'yes' => '0'),
            3 => array('num' => '7', 'key' => '7', 'val' => '40', 'yes' => '0'),
            4 => array('num' => '8', 'key' => '8', 'val' => '70', 'yes' => '0'),
            5 => array('num' => '9', 'key' => '9', 'val' => '80', 'yes' => '0'),
            6 => array('num' => '10', 'key' => '10', 'val' => '120', 'yes' => '0'),
            7 => array('num' => '15', 'key' => '15', 'val' => '150', 'yes' => '0'),
            8 => array('num' => '20', 'key' => '20', 'val' => '300', 'yes' => '0'),
            9 => array('num' => '40', 'key' => '40', 'val' => '400', 'yes' => '0'),
            10 => array('num' => '50', 'key' => '50', 'val' => '800', 'yes' => '0'),
            11 => array('num' => '100', 'key' => '100', 'val' => '1500', 'yes' => '0'),
        );
        foreach ($data as $k => $v) {
            if ($list['count'] >= $v['num']) {
                $data[$k]['yes'] = 1;
            }
            $user = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."activity_log where user_id=".$GLOBALS['user_info']['id']." and activity_id=".$activity_id." and unique_id =".$v['num']." and award_value=".$v['val']);
            if($user){
            	$data[$k]['yes'] = 2;
            }
        }
        return $data;
    }
    function happy_you($list,$activity_id)
    {
        $data = array(
            0 => array('num' => '1000', 'key' => '1000', 'val' => '5', 'yes' => '0'),
            1 => array('num' => '5000', 'key' => '5000', 'val' => '30', 'yes' => '0'),
            2 => array('num' => '10000', 'key' => '10000', 'val' => '30', 'yes' => '0'),
            3 => array('num' => '50000', 'key' => '50000', 'val' => '150', 'yes' => '0'),
            4 => array('num' => '100000', 'key' => '100000', 'val' => '300', 'yes' => '0'),
            5 => array('num' => '200000', 'key' => '200000', 'val' => '800', 'yes' => '0'),
            6 => array('num' => '500000', 'key' => '500000', 'val' => '2000', 'yes' => '0'),
            7 => array('num' => '1000000', 'key' => '1000000', 'val' => '3000', 'yes' => '0'),
            8 => array('num' => '2000000', 'key' => '2000000', 'val' => '4000', 'yes' => '0'),
            9 => array('num' => '3000000', 'key' => '3000000', 'val' => '4500', 'yes' => '0'),
            10 => array('num' => '4000000', 'key' => '4000000', 'val' => '6000', 'yes' => '0'),
            11 => array('num' => '5000000', 'key' => '5000000', 'val' => '8000', 'yes' => '0'),
        );
        foreach ($data as $k => $v) {
            if ($list['money'] >= $v['num']) {
                $data[$k]['yes'] = 1;
            }
            $user = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."activity_log where user_id=".$GLOBALS['user_info']['id']." and activity_id=".$activity_id." and unique_id =".$v['num']." and award_value=".$v['val']);
            if($user){
            	$data[$k]['yes'] = 2;
            }
        }
        return $data;
    }
    /**
     * 本金复投
     */
    function W632(){
        $GLOBALS['tmpl']->display("page/activity/W632.html");
    }
    /**
     * 专注首尾标
     */
    function W633(){
        $GLOBALS['tmpl']->display("page/activity/W633.html");
    }
	/**
     *  banner合规之路 
     */
    function banner_hegui(){
        $GLOBALS['tmpl']->display("page/activity/banner_hegui.html");
    }

    /*
     *  宜宾存款
     * */
    function W634(){
        $GLOBALS['tmpl']->display("page/activity/W634.html");
    }

    /*
     * 注册送体验金
     * */
   function  W635(){
         $GLOBALS['tmpl']->display("page/activity/W635.html");
   }
   /**
    * 高质用户，红包任性拿
    */
   function W636(){
       $GLOBALS['tmpl']->display("page/activity/W636.html");
   }
          /**
    * 新手福利-wap
    */
   function W640(){
       //添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
        $jump = machineInfo();
        if($GLOBALS['user_info']['id']){
            $jump['code'] = 1;
        }
        $GLOBALS['tmpl']->assign('jump',$jump);
       $GLOBALS['tmpl']->display("page/activity/W640.html");
   }
      /**
    * 推广页面
    */
   // function W641(){
   //     $GLOBALS['tmpl']->display("page/activity/W641.html");
   // }
          /**
    * 新手福利-wap 复制640
    */
   function W642(){
       //添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
        $jump = machineInfo();
        if($GLOBALS['user_info']['id']){
            $jump['status'] = 1;
        }else{
        	$jump['status'] = 0;
        }
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->display("page/activity/W642.html");
   }

	function W643(){
       //添加渠道来源
        if($_GET['source_id']){
            es_session::set("source_id",$_GET['source_id']);
        }
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
        $jump = machineInfo();
        if($GLOBALS['user_info']['id']){
            $jump['code'] = 1;
        }
        $GLOBALS['tmpl']->assign('jump',$jump);
		if(WAP == 1){
			$GLOBALS['tmpl']->display("page/activity/W643.html");
		}else{
			$GLOBALS['tmpl']->display("user_step_one.html");
		}
	}
    function W644(){
      $user_code=$_REQUEST['code'];
      if ($user_code != "") {
        $numstr = substr_replace($user_code,'****',3,4);  
        $GLOBALS['tmpl']->assign("numstr",$numstr);
        $GLOBALS['tmpl']->assign("user_code",$user_code);
      }else{
        $user_code = 0;
        $GLOBALS['tmpl']->assign("user_code",$user_code);
      }
      
      $user_info=$GLOBALS['user_info'];
      $GLOBALS['tmpl']->assign("user_data",$user_info);
      $wapregistered_user=1286453;
      $GLOBALS['tmpl']->assign("wapregistered_user",$wapregistered_user);
       //添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
        $jump = machineInfo();
        if($GLOBALS['user_info']['id']){
            $jump['code'] = 1;
        }
        $GLOBALS['tmpl']->assign('jump',$jump);
       $GLOBALS['tmpl']->display("page/activity/W644.html");
   }
       function W644_success(){
       //添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
        $jump = machineInfo();
        if($GLOBALS['user_info']['id']){
            $jump['code'] = 1;
        }
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->display("page/activity/W644_success.html");
   }
   
 function W645(){ 
	   $user_code=$_REQUEST['code'];
	   $id = $_REQUEST['id'];  
	   $GLOBALS['tmpl']->assign("id",$id);
      if ($user_code != "") {
        $numstr = substr_replace($user_code,'****',3,4);  
        $GLOBALS['tmpl']->assign("numstr",$numstr);
        $GLOBALS['tmpl']->assign("user_code",$user_code);
      }else{
        $user_code = 0;
        $GLOBALS['tmpl']->assign("user_code",$user_code);
      }
      $user_name ="w".$user_code;
	  $first = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));
	  $end = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));
	  $id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where user_name='".$user_name."'");
      $user_rob = $GLOBALS['db']->getOne('select sum(rob_red_money) from '.DB_PREFIX.'red_packet_rob where user_id='.$id);
	  $pai =$GLOBALS['db']->getOne('select sum(rob_red_money) from '.DB_PREFIX.'red_packet_rob where user_id='.$id.' and rob_time>='.$first.' and rob_time<='.$end);
// 	  $all =$GLOBALS['db']->getAll('select sum(rob_red_money) as money,user_id from '.DB_PREFIX.'red_packet_rob where rob_time>='.$first.' and rob_time<='.$end.' group by user_id');
	  $red_packet_friends = $GLOBALS['db']->getAll("select friend_id  from ".DB_PREFIX."red_packet_friends  where status = 0 and user_id = ".$id );
	  foreach ($red_packet_friends as $k=>$v){
	      $all[$k]['money'] = $GLOBALS['db']->getOne('select sum(rob_red_money) from '.DB_PREFIX.'red_packet_rob where user_id='.$v['friend_id'].' and rob_time>='.$first.' and rob_time<='.$end);
	  }
	  
	  $rank=1;
	  foreach($all as $k=>$v){
		  if($v['money']>$pai){
			  $rank++;
		  }
	  }
	  $GLOBALS['tmpl']->assign("user_rob",$user_rob);
	  $GLOBALS['tmpl']->assign("rank",$rank);
	  
      $user_info=$GLOBALS['user_info'];
	  
      $GLOBALS['tmpl']->assign("user_data",$user_info);
      $GLOBALS['tmpl']->assign("wapregistered_user",$wapregistered_user);
       //添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
        $jump = machineInfo();
        if($GLOBALS['user_info']['id']){
            $jump['code'] = 1;
        }
        $GLOBALS['tmpl']->assign('jump',$jump);
       $GLOBALS['tmpl']->display("page/activity/W645.html");
   }
   function W645_success(){
       //添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
        $jump = machineInfo();
        if($GLOBALS['user_info']['id']){
            $jump['code'] = 1;
        }
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->display("page/activity/W645_success.html");
   }
   //七夕活动
   function  W646(){
        $user_id=$GLOBALS['user_info']['id'];
        $GLOBALS['tmpl']->assign('user_id',$user_id);
        $sign = $GLOBALS['db']->getOne("select sign from ".DB_PREFIX."send_qixi where user_id=".$user_id." order by id desc");
        if($sign == ''){
          $sign = -1;
        }
        $GLOBALS['tmpl']->assign('sign',$sign);
        $begin_time=strtotime("2017-08-28 0:0:0");
        $end_time=strtotime("2017-08-30 23:59:59");
        //计算任务五
        $id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id=".$user_id." and cunguan_tag=1");
        //查询同一推荐人满足条件人数
        $invited_count = count($GLOBALS['db']->getAll("select u.id,sum(dl.money) as money from ".DB_PREFIX."user u inner join ".DB_PREFIX."deal_load dl on u.id=dl.user_id  where u.cunguan_tag=1 and u.pid =".$id." and u.create_time >".$begin_time." and u.create_time < ".$end_time." group by u.id"));
         //计算投资金额
         //活动期间玖财通存管版出借普通标的10000元及以上的用户（折标后）
         //活动期间玖财通存管版出借普通标的50000元及以上的用户（折标后）
         $conditon = " d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and dl.user_id = ".$id." and dl.create_time between  $begin_time and $end_time and d.cunguan_tag=1";
         $investInfo = $GLOBALS['db']->getAll("SELECT dl.money,d.repay_time FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON d.id =dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id where" .$conditon);
         $zhebiao_money = 0;
          foreach($investInfo as $k => $v){
            $zhebiao_money += round($v['money'] * $v['repay_time']/12,2);
         }
        $GLOBALS['tmpl']->assign("zhebiao_money",$zhebiao_money);
        $GLOBALS['tmpl']->assign("invited_count",$invited_count);
        //全部列表
        $qlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."send_qixi where type<>7 limit 0,100");
        foreach ($qlist as $k => $v) {
          $name =substr($v["user_name"],1,11);
          $v['user_name']= substr_replace($name,'***',3,4);
          $v['addtime'] = date("m/d",$v['addtime']);
          $qblist[] = $v;
        }
        

        $GLOBALS['tmpl']->assign("qblist",$qblist);
        //我的
        $wlist = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."send_qixi where user_id =".$user_id." and type<>7  limit 0,100");
        foreach ($wlist as $k => $v) {
          $name =substr($v["user_name"],1,11);
          $v['user_name']= substr_replace($name,'***',3,4);
          $v['addtime'] = date("m/d",$v['addtime']);
          $userlist[] = $v;
        }
        
        $GLOBALS['tmpl']->assign("userlist",$userlist);
        //搭建鹊桥排行榜
        $paihang = $GLOBALS['db']->getAll("select user_id from ".DB_PREFIX."send_qixi group by user_id");
        
        $ranking=array();
        foreach($paihang as $k=>$vs){
            // 满足全部完成任务的
            $finish = $GLOBALS['db']->getAll("select distinct(question) from ".DB_PREFIX."send_qixi where user_id=".$vs['user_id']);
            if(count($finish) ==7){  //完成任务的
                //计算折标金额
                $zhebiao= $this->zhebiao($vs['user_id']);
                if($zhebiao){
                    $ranking[$k]['zhebiao']=$zhebiao;
                    $name =$GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id=".$vs['user_id']);
                    $name = substr($name,1,11);
                    $ranking[$k]['user_name']=substr_replace($name,'***',3,4);
                }

        
            }
        }
        // 对折标后的用户根据金额排序
        $sort = array(
            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'zhebiao',       //排序字段
        );
        
        $arrSort = array();
        foreach($ranking AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        
        array_multisort($arrSort[$sort['field']], constant($sort['direction']), $ranking);
       $i=0;
       $ranks =array();
       foreach($ranking AS $m => $value){
            $i++;
           if($i<=5){
               $ranking[$m]['mci']=$m+1;
               $ranks[]=$value;
               $ranks[$m]['mci']=$ranking[$m]['mci'];
           }

        }
        $GLOBALS['tmpl']->assign("ranking",$ranks);
        //移动端跳转交互
        $jump = machineInfo();
        $GLOBALS['tmpl']->assign('jump',$jump);
        $jumpUrl = "http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=29&code=".$GLOBALS['user_info']['mobile'];
        $GLOBALS['tmpl']->assign('jumpUrl',$jumpUrl);
        $GLOBALS['tmpl']->assign('mobile',$GLOBALS['user_info']['mobile']);
        $GLOBALS['tmpl']->assign("url",WAP_SITE_DOMAIN);
        //创建session 保存用户是否需要分享
        if(!es_session::get("task")){
            es_session::set("task",0);
        }
        //$share_rs
        //$share_rs = is_share($data['user_id']);
        //$GLOBALS['tmpl']->assign("share_rs",$share_rs);
        $GLOBALS['tmpl']->display("page/activity/W646.html");
   }
   function Run_reports_list(){
       //添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
        $jump = machineInfo();
        if($GLOBALS['user_info']['id']){
            $jump['code'] = 1;
        }
        
       $GLOBALS['tmpl']->display("page/activity/Run_reports_list.html");
   }
   //第二季度运营报告
   function p201702(){
       //添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
        $jump = machineInfo();
        if($GLOBALS['user_info']['id']){
            $jump['code'] = 1;
        }
        
       $GLOBALS['tmpl']->display("page/activity/p201702.html");
   }
   //第三季度运营报告
   function p201703(){
       //添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
        $jump = machineInfo();
        if($GLOBALS['user_info']['id']){
            $jump['code'] = 1;
        }
        
       $GLOBALS['tmpl']->display("page/activity/p201703.html");
   }
   
   //wap视频列表
   
   function Wap_video(){
    
      $_REQUEST['id']=40;
      $GLOBALS['tmpl']->caching = true;
        $cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['id']).intval($_REQUEST['p']));     
        if (!$GLOBALS['tmpl']->is_cached('page/wap_video_index.html', $cache_id))   
        {       
            $id = intval($_REQUEST['id']);
            
            $cate_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."article_cate_cg where id = ".$id."  and is_delete = 0");
            
            
            
            $cate_id = intval($cate_item['id']);
            
            
            $condition ='ac.type_id = 0';
            
          
            //分页
            $page = intval($_REQUEST['p']);
            if($page==0)
            $page = 1;
            $limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");       
            $result = $this-> get_article_lists($limit,$cate_id,$condition,'');
            
            $GLOBALS['tmpl']->assign("list",$result['list']);
            $page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象       
            $p  =  $page->show();
            $GLOBALS['tmpl']->assign('pages',$p);
            
        
        }
        
            $GLOBALS['tmpl']->display("page/wap_video_index.html",$cache_id);
        
        
   }
   /**
   * 获取wap视频文章列表
   */
    function get_article_lists($limit, $cate_id=0, $where='',$orderby = '',$cached = true)
    {       
            $key = md5("ARTICLE".$limit.$cate_id.$where.$orderby);  
            if($cached)
            {               
                $res = $GLOBALS['cache']->get($key);
            }
            else
            {
                $res = false;
            }
            if($res===false)
            {
                    
                $count_sql = "select count(*) from ".DB_PREFIX."article_cg as a left join ".DB_PREFIX."article_cate_cg as ac on a.cate_id = ac.id where a.is_effect = 1 and a.is_delete = 0 and ac.is_delete = 0 ";
                $sql = "select a.*,ac.type_id from ".DB_PREFIX."article_cg as a left join ".DB_PREFIX."article_cate_cg as ac on a.cate_id = ac.id where a.is_effect = 1 and a.is_delete = 0 and ac.is_delete = 0  ";
                
                if($cate_id>0)
                {
                    $ids = load_auto_cache("deal_shop_acate_belone_ids",array("cate_id"=>$cate_id));
                    $sql .= " and a.cate_id in (".implode(",",$ids).")";
                    $count_sql .= " and a.cate_id in (".implode(",",$ids).")";
                }
                    
                
                if($where != '')
                {
                    $sql.=" and ".$where;
                    $count_sql.=" and ".$where;
                }
                
                if($orderby=='')
                $sql.=" order by a.sort desc limit ".$limit;
                else
                $sql.=" order by ".$orderby." limit ".$limit;
                
                $articles_count = $GLOBALS['db']->getOne($count_sql);
                $articles = array();
                if($articles_count > 0){
                    $articles = $GLOBALS['db']->getAll($sql);   
                    foreach($articles as $k=>$v)
                    {
                        
                            $module = 'find';
                        
                        
                        if($v['uname']!='')
                        $aurl = url("index",$module.'#article',array("id"=>$v['uname']));
                        else
                        $aurl = url("index",$module.'#article',array("id"=>$v['id']));
                            
                        $articles[$k]['url'] = $aurl;
                        $articles[$k]['content'] = strip_tags($v['content']);
                    }
                }
                    
                
                
                $res = array('list'=>$articles,'count'=>$articles_count);   
                $GLOBALS['cache']->set($key,$res);
            }           
            return $res;
    }

    /*
     * wap视频文章详情
     */
    function article(){

        $GLOBALS['tmpl']->caching = true;
        $cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['id']).$GLOBALS['deal_city']['id']);        
        if (!$GLOBALS['tmpl']->is_cached('page/article_video_index.html', $cache_id)) 
        {
            
                       
            $id = intval($_REQUEST['id']);
                                 
            $article = $GLOBALS['db']->getRow("select a.*,ac.type_id from ".DB_PREFIX."article_cg as a left join ".DB_PREFIX."article_cate_cg as ac on a.cate_id = ac.id where a.id = ".intval($id)." and a.is_effect = 1 and a.is_delete = 0");;    
             
            
            //var_dump($article);exit;
            $GLOBALS['tmpl']->assign("article",$article);
            $seo_title = $article['seo_title']!=''?$article['seo_title']:$article['title'];
            $GLOBALS['tmpl']->assign("page_title",$seo_title);
            $seo_keyword = $article['seo_keyword']!=''?$article['seo_keyword']:$article['title'];
            $GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
            $seo_description = $article['seo_description']!=''?$article['seo_description']:$article['title'];
            $GLOBALS['tmpl']->assign("page_description",$seo_description.",");
        }
        $GLOBALS['tmpl']->display("page/article_video_index.html",$cache_id);
    }
    /**
     * 庆存管
     */
    function happy_cg(){
        $MachineInfo = explode("|||",$_REQUEST['MachineInfo']);
        switch ($MachineInfo[0]) {
            case 'iOS':
                $jump['ToProductList'] = "iosToProductList";
               
                break;
            case 'Android':
                $jump['ToProductList'] = "androidToProductList";
                break;
            default:
                $jump['ToProductList'] = "ToProductList";
                break;
        }
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->display("page/activity/happy_cg.html");
    }
    
    // 折标方法
    public function zhebiao($user_id){
        $begin_time=strtotime("2017-08-28 0:0:0");
        $end_time=strtotime("2017-08-31 0:0:0");
        $conditon = " d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and dl.user_id = ".$user_id." and dl.create_time between ".$begin_time ." and " .$end_time. " and d.cunguan_tag=1";
        $investInfo = $GLOBALS['db']->getAll("SELECT dl.money,d.repay_time FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON d.id =dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id where " .$conditon);
        $zhebiao_money = 0;
        foreach($investInfo as $k => $v){
            $zhebiao_money += round($v['money'] * $v['repay_time']/12,1);
        }
        return $zhebiao_money;
    }
	
	//新版积分商城
	function new_mall(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			app_redirect(url("index","user#login"));
		}
		$score = $GLOBALS['db']->getOne('select score from '.DB_PREFIX.'user where id='.$GLOBALS['user_info']['id']);
		//banner轮播图
		$banner_list = $GLOBALS['db']->getAll('select banner_img,id,is_virtual  from '.DB_PREFIX.'goods where banner_rotation=1  and (is_ground=1 or (is_ground=3 and ground_time<'.time().')) order by banner_rotation_sort,`id` desc ');
		//快捷链接
		$shortcut_list =$GLOBALS['db']->getAll('select img,name,type,url from '.DB_PREFIX.'goods_cloumn order by code limit 3 ');
		$module_list = $GLOBALS['db']->getAll('select name,id from '.DB_PREFIX.'goods_cate where is_effect =1 and is_delete=0 order by sort asc');
		//$good= array();
		$time = time();
		foreach($module_list as $key =>$value){
            $good_one = $GLOBALS['db']->getAll('select id,is_vip,Consumer_integration,name,sub_name,img,score,discount_score,is_virtual from '.DB_PREFIX.'goods where cate_id='.$value['id'].' and is_ground=1');
            $good_two = $GLOBALS['db']->getAll('select id,is_vip,Consumer_integration,img,name,sub_name,score,discount_score,is_virtual from '.DB_PREFIX.'goods where cate_id='.$value['id'].' and is_ground=3 and ground_time<'.$time);
            $module_list[$key]['goods'] = array_merge($good_one,$good_two);
            $module_list[$key]['banner'] = $GLOBALS['db']->getAll('select banner_img,is_vip,Consumer_integration,id,is_virtual  from '.DB_PREFIX.'goods where cate_id='.$value['id'].' and module_rotation=1  and (is_ground=1 or (is_ground=3 and ground_time<'.time().')) order by module_rotation_sort,`id` desc ');
            if(!$module_list[$key]['goods']){
                unset($module_list[$key]);
            }
            /*
            //模块商品
			$good[$value['name']] = $GLOBALS['db']->getAll('select id,name,img,score,discount_score,is_virtual from '.DB_PREFIX.'goods where cate_id='.$value['id'].' and is_ground=1');
			$ground =$GLOBALS['db']->getAll('select id,img,name,score,discount_score,is_virtual from '.DB_PREFIX.'goods where cate_id='.$value['id'].' and is_ground=3 and ground_time<'.$time);
			if($ground){
				$good[$value['name']] = array_merge($good[$value['name']],$ground); 
			}
			
			//模块轮播图片
			//$good[$value['name']]['banner'] =$GLOBALS['db']->getAll('select id,img from '.DB_PREFIX.'goods where cate_id='.$value['id'].' and module_rotation=1 order by module_rotation_sort asc');
			if(!$good[$value['name']]){
				unset($good[$value['name']]);
			}
            */
		}
        $module_list = array_values($module_list);
        //var_dump($module_list);die;
        jumpUrl("jump_url_info");
        /*移动端交互处理*/
        $jump = machineInfo();
        $GLOBALS['tmpl']->assign('jump',$jump);
		$MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
		$type = $MachineInfo[0];
		$GLOBALS['tmpl']->assign("type",$type);
		$GLOBALS['tmpl']->assign("shortcut_list",$shortcut_list);
        $GLOBALS['tmpl']->assign("shortcut_list_json",json_encode($shortcut_list,JSON_UNESCAPED_UNICODE));
		$GLOBALS['tmpl']->assign("banners",$banner);
		$GLOBALS['tmpl']->assign("module_list",$module_list);
        $GLOBALS['tmpl']->assign("module_list_json",json_encode($module_list,JSON_UNESCAPED_UNICODE));
		$GLOBALS['tmpl']->assign("user_id",$id);
		$GLOBALS['tmpl']->assign("score",$score);
		$GLOBALS['tmpl']->assign("banner_list",$banner_list);
        $GLOBALS['tmpl']->assign("cate_title","积分商城");
		$GLOBALS['tmpl']->display("page/find/mall.html");
	}
	public function mall_details_goods(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			app_redirect(url("index","user#login"));
		}
		$score = $GLOBALS['db']->getOne('select score from '.DB_PREFIX.'user where id='.$GLOBALS['user_info']['id']);
		$goods_id =$_REQUEST['id'];
		$goods_info = $GLOBALS['db']->getRow('select id,is_vip,Consumer_integration,name,sub_name,score,description,is_ground,ground_time,invented_number,max_bought,user_max_bought,description,img,discount_score from '.DB_PREFIX.'goods where id='.$goods_id.' limit 1');
		if(!$goods_info){
			app_redirect("404.html");
		}
		$conf = $GLOBALS['db']->getOne('select kind_desc from '.DB_PREFIX.'goods_conf');
		//每日任务
		$code = $GLOBALS['user_info']['mobile'];
		$wap_cloumn_url = "http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=33&phone=".$code;
		$GLOBALS['tmpl']->assign("wap_cloumn_url",$wap_cloumn_url);
		$goods_info['exchange'] = $GLOBALS['db']->getOne('select count(1) from '.DB_PREFIX.'goods_order where goods_id='.$goods_id)+$goods_info['invented_number'];
		$GLOBALS['tmpl']->assign("goods",$goods_info);
		$GLOBALS['tmpl']->assign("score",$score);
		$GLOBALS['tmpl']->assign("conf",$conf);
		$GLOBALS['tmpl']->assign("user_login",$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("cate_title",$goods_info['sub_name']);
		$GLOBALS['tmpl']->display("page/find/mall_details_goods.html");
	}
	public function mall_details_coupon(){
		$goods_id =$_REQUEST['id'];
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			app_redirect(url("index","user#login"));
		}
		$score = $GLOBALS['db']->getOne('select score from '.DB_PREFIX.'user where id='.$GLOBALS['user_info']['id']);
		$coupon_info = $GLOBALS['db']->getRow('select id,is_vip,Consumer_integration,name,score,max_bought,invented_number,is_ground,ground_time,description,discount_score,img,is_virtual,dummy_type,dummy,user_max_bought from '.DB_PREFIX.'goods where id='.$goods_id);
		//用户兑换次数
		$user_maxbought = $GLOBALS['db']->getOne('select count(1) from '.DB_PREFIX.'goods_order where user_id='.$user_id.' and goods_id='.$goods_id);
		if(!$coupon_info){
			app_redirect("404.html");
		}
		if($coupon_info['is_ground']==0){
			app_redirect("404.html");	
		}
		if($coupon_info['is_ground']==3&&$coupon_info['ground_time']>time()){
			app_redirect("404.html");
		}
		if($user_max_bought >= $coupon_info['user_max_bought']){
			$limit = 1;
		}else{
			$limit = 0;
		}
		if($coupon_info['dummy_type']==1){//红包
			$virtual_award = $GLOBALS['db']->getRow('select red_name,use_condition,ratio,use_limit from '.DB_PREFIX.'red_packet_newconfig where id='.$coupon_info['dummy']);
		}elseif($coupon_info['dummy_type']==2){//加息卡
			$virtual_award = $GLOBALS['db']->getRow('select card_name,use_condition,begin_time,term_validity,interest_time from '.DB_PREFIX.'coupon where id='.$coupon_info['dummy']);
			//有效期
			$virtual_award['use_limit'] = $virtual_award['term_validity'];
			//加息时长
			$virtual_award['use_time'] = $virtual_award['interest_time'];
		}
		$conf = $GLOBALS['db']->getOne('select dummy_desc from '.DB_PREFIX.'goods_conf');
		//每日任务
		$code = $GLOBALS['user_info']['mobile'];
		$wap_cloumn_url = "http://wxglcs.jiuchengjr.com/app/index.php?i=4&c=entry&do=activity&m=amouse_tel114&id=33&phone=".$code;
		$GLOBALS['tmpl']->assign("wap_cloumn_url",$wap_cloumn_url);
		//已兑换数量
		$coupon_info['exchange'] = $GLOBALS['db']->getOne('select count(1) from '.DB_PREFIX.'goods_order where goods_id='.$goods_id)+$coupon_info['invented_number'];
		$GLOBALS['tmpl']->assign("goods",$coupon_info);
		$GLOBALS['tmpl']->assign("virtual_award",$virtual_award);
		$GLOBALS['tmpl']->assign("score",$score);
		$GLOBALS['tmpl']->assign("limit",$limit);
		$GLOBALS['tmpl']->assign("conf",$conf);
		$GLOBALS['tmpl']->assign("cate_title",$coupon_info['name']);
		$GLOBALS['tmpl']->assign("user_login",$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->display("page/find/mall_details_coupon.html");
	}
	public function exchange_limit(){
		$goods_id =$_REQUEST['id'];
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			
			$res['status'] =0;
			$res['info'] ="请先登陆";
			echo json_encode($res);exit;
		}
		if(!$goods_id){
			$res['status'] =0;
			$res['info'] ="非法操作";
			echo json_encode($res);exit;
		}
		$goods_info = $GLOBALS['db']->getRow("select score,discount_score,max_bought,name,user_max_bought,is_virtual,dummy,dummy_type from ".DB_PREFIX."goods where id =".$goods_id);
		if($goods_info['max_bought']<=0){
			$res['status'] =0;
			$res['info'] ="奖品已兑换完";
			echo json_encode($res);exit;
		}
		$user_maxbought = $GLOBALS['db']->getOne('select count(1) from '.DB_PREFIX.'goods_order where user_id='.$user_id.' and goods_id='.$goods_id);
		if($goods_info['user_max_bought']){
			if($user_maxbought>=$goods_info['user_max_bought']){
				$res['status'] =0;
				$res['info'] ="您已达兑奖上限";
				echo json_encode($res);exit;
			}
		}
		$res['status'] =1;
		echo json_encode($res);exit;
	}
	public function exchange(){
    require_once APP_ROOT_PATH."system/user_level/Level.php";
		$goods_id =$_REQUEST['id'];
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			
			$res['status'] =0;
			$res['info'] ="请先登陆";
			echo json_encode($res);exit;
		}
		if(!$goods_id){
			$res['status'] =0;
			$res['info'] ="非法操作";
			echo json_encode($res);exit;
		}
		$goods_info = $GLOBALS['db']->getRow("select score,Consumer_integration,discount_score,max_bought,name,user_max_bought,is_virtual,dummy,dummy_type from ".DB_PREFIX."goods where id =".$goods_id);
    $level = new Level();
    $user_level = $level->get_user_vip_level($user_id);
    if($user_level>=2){
      $goods_info['score'] =$goods_info['Consumer_integration'];
    }
		if(!$goods_info){
			$res['status'] =0;
			$res['info'] ="非法操作";
			echo json_encode($res);exit;
		}
		if($goods_info['max_bought']<=0){
			$res['status'] =0;
			$res['info'] ="奖品已兑换完";
			echo json_encode($res);exit;
		}
		$user_maxbought = $GLOBALS['db']->getOne('select count(1) from '.DB_PREFIX.'goods_order where user_id='.$user_id.' and goods_id='.$goods_id);
		if($goods_info['user_max_bought']){
			if($user_maxbought>=$goods_info['user_max_bought']){
				$res['status'] =0;
				$res['info'] ="您已达到兑奖上限";
				echo json_encode($res);exit;
			}
		}
		
		if(!$goods_info['is_virtual']){
			$address_info = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'address where user_id='.$user_id.' order by id limit 1');
			if(!$address_info){
				$res['status'] =0;
				$res['info'] ="请先选择地址";
				echo json_encode($res);exit;
			}
		}
		$GLOBALS['db']->startTrans();
		//查询用户可用积分
		$score = $GLOBALS['db']->getOne('select score from '.DB_PREFIX.'user where id ='.$user_id.' for update');
		if($score>=$goods_info['score']){//可用积分大于奖品积分
				list($msec, $sec) = explode(' ', microtime());
				$time = date('Y-m-dH-i-s',$sec);
				$orderno = str_replace('-','',$time).intval($msec*100);
				//$address_id = $GLOBALS['db']->getOne('select id from '.DB_PREFIX.'address where user_id='.$user_id);
				$order['user_id'] = $user_id;
				$order['goods_id'] = $goods_id;
				$order['goods_name'] = $goods_info['name'];
				$order['order_sn'] = $orderno;

				$order['score'] = $goods_info['score'];
				$order['is_delivery'] = 1;
				$order['delivery_addr'] = $address_info['area'].$address_info['detail_address'];
				$order['delivery_tel'] = $address_info['phone'];
				$order['delivery_name'] = $address_info['user_name'];
				$order['memo'] = $address_info['memo'];
				$order['total_score'] = $goods_info['score'];
				$order['number'] = 1;
				if($goods_info['is_virtual']){
					$order['order_status'] = 4;
				}
				$order['ex_time'] = time();
				$order['ex_date'] = date('Y-m-d',time());
				//$order['address_id'] = $address_id;
				$score_list['score']=-$goods_info['score'];;
				if($goods_info['is_virtual']==1){
					//$msg='兑换'.'<a href="/index.php?ctl=find&act=mall_details_coupon&id='.$goods_id.'" target="_blank">'.$goods_info['name'].'</a>';
					$msg=$goods_info['name'];
					 if($goods_info['dummy_type']==1){
						$red_packet['red_type_id'] = $goods_info['dummy'];
						$red_packet_config = $GLOBALS['db']->getRow('select amount,use_limit from '.DB_PREFIX.'red_packet_newconfig where id='.$goods_info['dummy']);
						$red_packet['money']=$red_packet_config['amount'];
						$red_packet['user_id']=$user_id;
						$red_packet['begin_time'] = TIME_UTC;
						$red_packet['end_time'] = strtotime(date('Y-m-d',strtotime("+".$red_packet_config['use_limit']." days")))-1;
						$red_packet['use_limit'] = $red_packet_config['use_limit'];
						$red_packet['content'] = "积分兑换红包";
						$red_packet['create_time'] = TIME_UTC;
						$red_packet['packet_type'] = 1;
						$sn = unpack('H12',str_shuffle(md5(uniqid())));
						$red_packet['sn'] = $sn[1];
						$GLOBALS['db']->autoExecute(DB_PREFIX.'red_packet',$red_packet,'INSERT');
					} elseif($goods_info['dummy_type']==2){
						 $interest_card['user_id'] = $user_id;
						$card_info = $GLOBALS['db']->getRow('select id,rate,interest_time,term_validity,begin_time,end_time from '.DB_PREFIX.'coupon where id='.$goods_info['dummy']);
						$interest_card['coupon_id'] =$card_info['id'];
						$interest_card['begin_time'] =strtotime(date('Y-m-d'));;
						$interest_card['end_time'] =strtotime(date('Y-m-d',strtotime("+".$card_info['term_validity']."days")))-1;
						$interest_card['create_time'] =TIME_UTC;
						$interest_card['content'] ='积分兑换加息卡';
						$interest_card['rate'] =$card_info['rate'];
						$interest_card['use_time'] =$card_info['interest_time'];
						$GLOBALS['db']->autoExecute(DB_PREFIX.'interest_card',$interest_card,'INSERT');
					} 
					modify_account($score_list,$user_id,$msg,22);
				}else{
					//$msg='兑换'.'<a href="/index.php?ctl=find&act=mall_details_goods&id='.$goods_id.'" target="_blank">'.$goods_info['name'].'</a>';
					$msg=$goods_info['name'];
					modify_account($score_list,$user_id,$msg,22);
				}
					$order_id = $GLOBALS['db']->autoExecute(DB_PREFIX.'goods_order',$order,'INSERT');
					$insert_id = $GLOBALS['db']->insert_id();
					$good['max_bought']=$goods_info['max_bought']-1;
					$GLOBALS['db']->autoExecute(DB_PREFIX.'goods',$good,'UPDATE','id='.$goods_id);
					if($order_id){
						$res['order_id'] =$insert_id;
						insert_red_log($user_id,4);
						$res['status'] =1;
						$GLOBALS['db']->commit();
					}else{
						$GLOBALS['db']->rollback();
						$res['status'] =0;
						$res['info'] ="请稍后再试";
					}
				
		}else{
			$res['status'] =0;
			$res['info'] ="积分不足";
			
		}
		echo json_encode($res);exit;
	}
	public function mall_exchange(){
		$goods_id =$_REQUEST['id'];
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			app_redirect(url("index","user#login"));
		}
		$goods = $GLOBALS['db']->getRow("select id,is_vip,Consumer_integration,sub_name,score,img,discount_score,max_bought,name from ".DB_PREFIX."goods where id =".$goods_id);
		if(!$goods){
			app_redirect("404.html");
		}
		$address_info = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'address where user_id='.$user_id.' order by id limit 1');
		if($address_info){
			if(empty($address_info['area'])|| empty($address_info['phone'])||empty($address_info['user_name'])||empty($address_info['detail_address'])){
				$is_empty =0;
			}else{
				$is_empty =1;
			}
		}else{
			$is_empty =0;
		}
		$conf = $GLOBALS['db']->getOne('select reminder from '.DB_PREFIX.'goods_conf');
		$GLOBALS['tmpl']->assign("conf",$conf);
		$GLOBALS['tmpl']->assign("goods",$goods);
		$GLOBALS['tmpl']->assign("is_empty",$is_empty);
		$GLOBALS['tmpl']->assign("address_info",$address_info);
		$GLOBALS['tmpl']->assign("cate_title","确认兑换");
		$GLOBALS['tmpl']->display("page/find/mall_exchange.html");
	}
	public function exchange_success(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			app_redirect(url("index","user#login"));
		}
		$id = intval($_REQUEST['order_id']);
		$goods_id = intval($_REQUEST['goods_id']);
		/* if(!$id){
			app_redirect("404.html");
		} */
		if($id){
			$order = $GLOBALS['db']->getRow('select order_sn,ex_time,id,goods_id,order_status from '.DB_PREFIX.'goods_order where id='.$id);
			$order['ex_time'] = date('Y-m-d H:i:s',$order['ex_time']);
			$order['is_virtual'] = $GLOBALS['db']->getOne('select is_virtual from '.DB_PREFIX.'goods where id='.$order['goods_id']);
		}
		if($goods_id){
			$goods['id'] = $goods_id;
		}
		$GLOBALS['tmpl']->assign("order",$order);
		$GLOBALS['tmpl']->assign("goods",$goods);
		$GLOBALS['tmpl']->assign("cate_title","兑换成功");
		$GLOBALS['tmpl']->display("page/find/mall_exchange_success.html");
	}
	public function mall_address(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			app_redirect(url("index","user#login"));
		}
		$id = intval($_REQUEST['id']);
		$goods_id = intval($_REQUEST['goods_id']);
		if($id){
			$address_info = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'address where id='.$id.' order by id limit 1');
		}
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign("jump",$jump);
		$GLOBALS['tmpl']->assign("app_tag",$_REQUEST['app_tag']);
		$GLOBALS['tmpl']->assign("address_info",$address_info);
		$GLOBALS['tmpl']->assign("goods_id",$goods_id);
		$GLOBALS['tmpl']->assign("cate_title","填写收货地址");
		$GLOBALS['tmpl']->display("page/find/mall_address.html");
	}
	public function mall_address_app(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			app_redirect(url("index","user#login"));
		}
// 		$id=es_session::get("user_id");
// 		print_r($id);die;
		$id = intval($_REQUEST['id']);
		$goods_id = intval($_REQUEST['goods_id']);
		if($id){
			$address_info = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'address where user_id='.$user_id.' order by id limit 1');
		}
		$jump = machineInfo();
		$GLOBALS['tmpl']->assign("jump",$jump);
		$GLOBALS['tmpl']->assign("app_tag",$_REQUEST['app_tag']);
		$GLOBALS['tmpl']->assign("address_info",$address_info);
		$GLOBALS['tmpl']->assign("goods_id",$goods_id);
		$GLOBALS['tmpl']->assign("cate_title","填写收货地址");
// 		$GLOBALS['tmpl']->assign("user_id",$user_id);
// 		alert($user_id);die;
		$GLOBALS['tmpl']->display("page/find/mall_address.html");
	}
	public function mall_add_address(){
		
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			$res['status'] =0;
			$res['info'] ="未登陆";
			echo json_encode($res);exit;
		}
		$data = $_REQUEST;
		if(empty($data['area'])|| empty($data['phone'])||empty($data['user_name'])||empty($data['detail_address'])){
			$res['status'] =0;
			$res['info'] ="请填写完整";
			echo json_encode($res);exit;
		}
		if(!empty($data['address_id'])){
			$data['user_id'] = $user_id;
			$data['update_time']=TIME_UTC;
			$rows = $GLOBALS['db']->autoExecute(DB_PREFIX.'address',$data,'UPDATE','id='.$data['address_id']);
				if($rows){
					$res['status'] =1;
				}else{
					$res['status'] =0;
					$res['info'] ="请稍后再试";
				}
		}else{
			$data['user_id'] = $user_id;
			$data['add_time']=TIME_UTC;
			$data['update_time']=TIME_UTC;
			$id = $GLOBALS['db']->autoExecute(DB_PREFIX.'address',$data,'INSERT');
				if($id){
					$res['status'] =1;
				}else{
					$res['status'] =0;
					$res['info'] ="请稍后再试";
				}
		}
		echo json_encode($res);
	}
	public function mall_order(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			app_redirect(url("index","user#login"));
		}
		$order_all = $GLOBALS['db']->getAll('select gs.id,go.id,gs.name,gs.sub_name,go.is_delivery,go.order_status,go.ex_date from '.DB_PREFIX.'goods_order go inner join '.DB_PREFIX.'goods gs on gs.id=go.goods_id where go.user_id='.$user_id.' and go.order_status!=2 and go.order_status!=3 and go.order_status!=4');
		//待发货
		$order_none = $GLOBALS['db']->getAll('select gs.id,go.id,gs.name,gs.sub_name,go.is_delivery,go.order_status,go.ex_date from '.DB_PREFIX.'goods_order go inner join '.DB_PREFIX.'goods gs on gs.id=go.goods_id where go.user_id='.$user_id.' and  go.order_status=0');
		//已发货
		$order_done = $GLOBALS['db']->getAll('select gs.id,go.id,gs.name,gs.sub_name,go.is_delivery,go.ex_date from '.DB_PREFIX.'goods_order go inner join '.DB_PREFIX.'goods gs on gs.id=go.goods_id where go.user_id='.$user_id.' and go.order_status=1');
		$GLOBALS['tmpl']->assign("order_all",$order_all);
		$GLOBALS['tmpl']->assign("order_none",$order_none);
		$GLOBALS['tmpl']->assign("order_done",$order_done);
		$GLOBALS['tmpl']->assign("cate_title","我的订单");
		$GLOBALS['tmpl']->display("page/find/mall_order.html");
	}
	
	public function mall_order_details(){
		$user_id = $GLOBALS['user_info']['id'];
		if(!$user_id){
			app_redirect(url("index","user#login"));
		}
		$order_id = $_REQUEST['id'];
		$goods = $GLOBALS['db']->getRow('select gs.id as goods_id,gs.is_ground,gs.ground_time,go.id,gs.name,gs.sub_name,gs.img,go.order_sn,go.score,go.delivery_addr,go.delivery_name,go.delivery_tel,go.memo,go.number,go.is_delivery,go.order_status,go.ex_time,go.delivery_time,go.delivery_sn,gs.discount_score,go.delivery_express from '.DB_PREFIX.'goods_order go left join '.DB_PREFIX.'goods gs on gs.id=go.goods_id  where go.id='.$order_id);
		$goods['delivery_date'] = date('Y-m-d H:i:s',$goods['delivery_time']);
		$goods['ex_date'] = date('Y-m-d H:i:s',$goods['ex_time']);
		$address = $GLOBALS['db']->getRow('select user_name,area,phone,detail_address,memo from '.DB_PREFIX.'address where user_id='.$user_id);
		$conf = $GLOBALS['db']->getOne('select order_details from '.DB_PREFIX.'goods_conf');
		$GLOBALS['tmpl']->assign("conf",$conf);
		$GLOBALS['tmpl']->assign("goods",$goods);
		$GLOBALS['tmpl']->assign("address",$address);
		$GLOBALS['tmpl']->assign("cate_title","订单详情");
		$GLOBALS['tmpl']->display("page/find/mall_order_details.html");
	}


  public function  W647(){
    $GLOBALS['tmpl']->display("page/activity/W647.html");
  }

//庆国庆活动
  public function W648(){
    
    
    $user_id = $GLOBALS['user_info']['id'];
    if(!$user_id){
      
      app_redirect(url("index","user#login"));
    }
    //判断活动是否开始
    if(intval(date("Ymd",TIME_UTC))>20171009){//已经过了10月9号 不让访问
      
      die('活动已经结束，谢谢您参与！');
    }else if(intval(date("Ymd",TIME_UTC))<20170929){
      
      die('活动尚未开始，请9月29号来参与活动！');
    }

  $award=array(
    array('id'=>'29','prizename'=>'月模',  'time'=>'2017/09/29', 'date'=>'09月29日'),
    array('id'=>'30','prizename'=>'烤箱',  'time'=>'2017/09/30', 'date'=>'09月30日'),
    array('id'=>'1','prizename'=>'面粉',  'time'=>'2017/10/01', 'date'=>'10月01日'),
    array('id'=>'2','prizename'=>'鸡蛋',  'time'=>'2017/10/02', 'date'=>'10月02日'),
    array('id'=>'3','prizename'=>'植物油','time'=>'2017/10/03', 'date'=>'10月03日'),
    array('id'=>'4','prizename'=>'酒',    'time'=>'2017/10/04', 'date'=>'10月04日'),
    array('id'=>'5','prizename'=>'莲蓉',  'time'=>'2017/10/05', 'date'=>'10月05日'),
    array('id'=>'6','prizename'=>'鲜肉',  'time'=>'2017/10/06', 'date'=>'10月06日'),
    array('id'=>'7','prizename'=>'栗蓉',  'time'=>'2017/10/07', 'date'=>'10月07日'),
    array('id'=>'8','prizename'=>'五仁',  'time'=>'2017/10/08', 'date'=>'10月08日'),
    array('id'=>'9','prizename'=>'豆沙',  'time'=>'2017/10/09', 'date'=>'10月09日'),
  );

    
    $nowdate = date('Y/m/d',TIME_UTC);
    //$nowdate = '2017/09/09';
    foreach ($award as $key => $value) {
            if($nowdate==$value['time']){
              $day_sign = $key;//根据日期判断是几号
              $now_award= $value;//今日获得的奖品
              $now_award['num']=$key+1;
            }
    }
    

    //查询用户是否签到过
    $signinfo = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'guoqing_sign where user_id='.$user_id);
    
    if($signinfo){//非首次签到
       $signdata = $signinfo['updatetime'];

       if($signdata==$nowdate){//说明今天签到过了

         //查询今天签到的奖品
         // $prize = $GLOBALS['db']->getRow('select * from '.DB_PREFIX.'guoqing_award where user_id='.$user_id .' and is_buqian!=1');
         
         $issign=1;//判断今天是否签到标志
         $arr  = unserialize($signinfo['day_sign']);
         //$forgetsign = $signinfo['forgetsign_days'];
         
       }else{//今天签到
         
         $issign=0;
         //处理签到天数数据
         //签到插入的数组
         
         $arr=unserialize($signinfo['day_sign']);//查询的结果
         array_push($arr,$day_sign);
         sort($arr);
         $signs=serialize($arr);
         //如果最后天签到且无漏签 跟新completetime iscomplete
         if(array_sum($arr)==55){
            $updata['iscomplete']=1;
            $updata['completetime']=TIME_UTC;
         }

         //求漏签的次数
         //$alldays=array(0,1,2,3,4,5,6,7,8,9,10);
         //$arr=array(1,2,5);//查询的结果
         //$arr_louqian=array_slice($alldays,0,$arr[count($arr)-1]+1);//目标比较数组

         //$res=array_diff($arr_louqian,$arr);
         //$forgetsign = count($res);//求漏签的天数
         //$forgetsign = $signinfo['forgetsign_days'];
         $updata['updatetime']=date('Y/m/d',TIME_UTC);
         $updata['day_sign']  =$signs;
         $GLOBALS['db']->autoExecute(DB_PREFIX.'guoqing_sign',$updata,'UPDATE','user_id='.$user_id);
         
         
       }
        //求漏签的次数
        $alldays=array(0,1,2,3,4,5,6,7,8,9,10);
        $arr=unserialize($signinfo['day_sign']);//查询的结果
        $arr_louqian=array_slice($alldays,0,$arr[count($arr)-1]+1);//目标比较数组

        $res=array_diff($arr_louqian,$arr);//漏签的天数
        $forgetsign = count($res);//求漏签的天数
        //判断今天是否已经补签
        if($nowdate == $signinfo['buqian_time']||$forgetsign==0){
          $todaybuqian=1;
        }else{
          $todaybuqian=0;
        }

    }else{//首次签到

         
         $issign=0;
         //求漏签的次数
         $alldays=array(0,1,2,3,4,5,6,7,8,9,10);
         $arr=array($day_sign);//查询的结果
         $arr_louqian=array_slice($alldays,0,$arr[count($arr)-1]+1);//目标比较数组

         $res=array_diff($arr_louqian,$arr);
         $forgetsign = count($res);//求漏签的天数
         if($forgetsign==0){
           $todaybuqian=1;
         }else{
           $todaybuqian=0;
         }
         

         $data['user_id'] = $user_id;
         $data['mobile']  =$GLOBALS['user_info']['mobile'];
         $data['day_sign'] = serialize( array($day_sign));
         $data['updatetime'] = date('Y/m/d',TIME_UTC);
         
         $GLOBALS['db']->autoExecute(DB_PREFIX."guoqing_sign",$data,"INSERT");

         
    }
    //今日签到奖励
    // $paihan =$GLOBALS['db']->getAll("select mobile from ".DB_PREFIX."guoqing_sign where updatetime='".$nowdate."' order by id desc limit 0,100");
    
    // foreach ($paihan as $key => $value) {
    //      $paihan[$key]['mobile'] =substr($value['mobile'], 0, 3).'****'.substr($value['mobile'], 7);
    //      $paihan[$key]['date']   =$now_award['date'];
    //      $paihan[$key]['prizename']=$now_award['prizename'];
    // }
    //我的签到奖励
    // $myprize  = array();
    // foreach ($award as $key => $value) {
    //      if(in_array($key,$arr)){
    //         $myprize[]=$value;
    //      }
    // }

    //今日签到人数
    $num = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."guoqing_sign where updatetime  = '".$nowdate."'" );

    //移动端分享设置
    $GLOBALS['tmpl']->assign('mobile',$GLOBALS['user_info']['mobile']);
    
    
    foreach ($award as $key => $value) {
          $award[$key]['num']=$key+1;
      if(in_array($key,$arr)){//已经签到
          $award[$key]['class']='select';
      }else if(in_array($key,$res)){//漏签的
          $award[$key]['class']='miss';
      }else{
          $award[$key]['class']='';
      }
    }
    
    //移动端跳转交互
    $jump = machineInfo();

    $GLOBALS['tmpl']->assign("jump",$jump);
    $GLOBALS['tmpl']->assign("todaybuqian",$todaybuqian);//今天是否补签
    $GLOBALS['tmpl']->assign("issign",$issign);//提示签到成功
    $GLOBALS['tmpl']->assign("forgetsign",$forgetsign);//忘记签到的数据
    $GLOBALS['tmpl']->assign("sign_days",$arr);//已经签到的数据
    $GLOBALS['tmpl']->assign("now_award",$now_award);//今天获得的奖品
    $GLOBALS['tmpl']->assign("num",$num); //今日签到人数
    $GLOBALS['tmpl']->assign("award",$award);
    // $GLOBALS['tmpl']->assign("paihan",$paihan); //今日签到排行
    // $GLOBALS['tmpl']->assign("myprize",$myprize); //我的签到奖励

    $GLOBALS['tmpl']->display("page/activity/W648.html");

  }

    public function  W649(){
        $GLOBALS['tmpl']->display("page/activity/W649.html");
    }


    public function  W650(){
        
        $user_id = $GLOBALS['user_info']['id'];
        if(!$user_id){
          
          app_redirect(url("index","user#login"));
        }
        
        //获取步数以及距离
        $today_step = $_GET['today_step'];

        $today_distance = $_GET['today_distance'];

        $today = strtotime(date("Y-m-d",TIME_UTC));
        //查询今天领取情况 一天只有2次
        //$award = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."health_activity where user_id=".$user_id." and time>=".$today);
        $award = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."health_activity_new where (time>=".$today."  or  time2>=".$today." ) and user_id=".$user_id);

        if($today_step < 0){
            
            $on_off = 0; 
            
        }else{
            //步数大于0 关闭弹窗
            $on_off = 1; 
     
        }
        $todaysteps = $GLOBALS['db']->getRow("select step_count,id from ".DB_PREFIX."step_counter where user_id=".$user_id." and update_time>=".$today);

         // ======taocejun======
        $MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
        $device = $MachineInfo[0];
        
        //========taocejun ======
        //判断如果是Android设备就跟新加上步数
        // if($device =='Android'){
        //   if($today_step&&$todaysteps){
        //      $data['step_count'] = $today_step+$todaysteps['step_count'];
        //      $data['update_time'] =TIME_UTC;
        //      $GLOBALS['db']->autoExecute(DB_PREFIX."step_counter",$data,"UPDATE","id=".$todaysteps['id']);
        //      $today_step = $today_step+$todaysteps['step_count'];
        //   }else{

        //     $data['step_count'] = $today_step;
        //     $data['upload_time'] =TIME_UTC;
        //     $data['update_time'] =TIME_UTC;
        //     $data['user_id'] = $user_id;
        //     $GLOBALS['db']->autoExecute(DB_PREFIX."step_counter",$data,"INSERT");
        //   }
        // }else{

          if($today_step&&$today_step>0){
            //获取当日上传记录
            
            if(!$todaysteps){
              $data['step_count'] = $today_step;
              $data['upload_time'] =TIME_UTC;
              $data['update_time'] =TIME_UTC;
              $data['user_id'] = $user_id;
              $GLOBALS['db']->autoExecute(DB_PREFIX."step_counter",$data,"INSERT");
            }else if($todaysteps&&$todaysteps['step_count']<$today_step){
              $data['step_count'] = $today_step;
              $data['update_time'] =TIME_UTC;
              $GLOBALS['db']->autoExecute(DB_PREFIX."step_counter",$data,"UPDATE","id=".$todaysteps['id']);
            }

            if($todaysteps['step_count']>$today_step){
              $today_step=$todaysteps['step_count'];
            }
          }else{
            if($todaysteps){
              $today_step=$todaysteps['step_count'];
            }else{
              $today_step=0;
            }
            
          }
        // }
        $calorie =intval($today_step/20);
      
        
        //===========================taocejun=========================
        //移动端跳转交互
        $jump = machineInfo();
        $GLOBALS['tmpl']->assign("award",$award);
        $GLOBALS['tmpl']->assign("jump",$jump);
        $GLOBALS['tmpl']->assign("today_step",$today_step);
        $GLOBALS['tmpl']->assign("calorie",$calorie);
        $GLOBALS['tmpl']->assign("on_off",$on_off);
        $GLOBALS['tmpl']->assign("device",$device);
        
       
        $GLOBALS['tmpl']->display("page/activity/W650.html");
    }
    //积分抽积分转盘活动
    public function  W651(){

        $user_id = $GLOBALS['user_info']['id'];
        if(!$user_id){
          
          app_redirect(url("index","user#login"));
        }

        $score = $GLOBALS['db']->getOne("SELECT score FROM ".DB_PREFIX."user WHERE id ='".$user_id."'");
        $nowdate =date('Ymd',TIME_UTC);
        //今日抽奖次数
        $num = $GLOBALS['db']->getOne("SELECT count(id) FROM ".DB_PREFIX."turntable_activity WHERE user_id ='".$user_id."' and type != 11 and create_time_ymd=".$nowdate);
        //今日中奖名单
        //$prizesinfo1 = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."turntable_activity  where prizename='3积分' order by id desc limit 20");
        //$prizesinfo2 = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."turntable_activity  where prizename='90积分' order by id desc limit 30");
        //$prizeinfo =$this->shuffleMergeArray($prizesinfo1,$prizesinfo2);

        $prizesinfo = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."turntable_activity  where type !=11  order by id desc limit 50");
        // $sum = count($prizesinfo1) + count($prizesinfo2);
        // for ($k = $sum; $k > 0; $k--) {
        //   $number = mt_rand(1, 2);
        //   if ($number == 1) {
        //     $prizesinfo[] = $prizesinfo2 ? array_shift($prizesinfo2) : array_shift($prizesinfo1);
        //   } else {
        //     $prizesinfo[] = $prizesinfo1 ? array_shift($prizesinfo1) : array_shift($prizesinfo2);
        //   }
        // }
        
        foreach ($prizesinfo as $key => $value) {
          $prizesinfo[$key]['mobile'] = substr_replace($value['mobile'],'****',3,4);
        }
        //今日参与次数
        $todaynum = $GLOBALS['db']->getOne("SELECT count(id) FROM ".DB_PREFIX."turntable_activity WHERE   create_time_ymd=".$nowdate);
        //总参与次数
        $allnum = $GLOBALS['db']->getOne("SELECT count(id) FROM ".DB_PREFIX."turntable_activity ");
        
        // $redis = new Redis();
        // $redis->connect(REDIS_HOST, REDIS_PORT);
        // $redis->auth(REDIS_PWD);
        // $redis->select(8);
        // if($redis->get(REDIS_PREFIX.$uid_id)){
        //   $allnum = $redis->get(REDIS_PREFIX.$uid_id);
        // }else{
        //   $allnum = $GLOBALS['db']->getOne("SELECT count(id) FROM ".DB_PREFIX."turntable_activity ");
        //   $redis->set(REDIS_PREFIX.$uid_id,$allnum,3600*24);
        // }
        

        
        $jump = machineInfo();
        $GLOBALS['tmpl']->assign("prizesinfo",$prizesinfo);
        $GLOBALS['tmpl']->assign("jump",$jump);
        $GLOBALS['tmpl']->assign("score",$score);
        $GLOBALS['tmpl']->assign("num",$num);
        $GLOBALS['tmpl']->assign("todaynum",$todaynum);
        $GLOBALS['tmpl']->assign("allnum",$allnum);
        
        $GLOBALS['tmpl']->display("page/activity/W651.html");
    }
    
    public function weekday($time) 

    { 
         if(is_numeric($time)) 

         { 

             $weekday = array('星期日','星期一','星期二','星期三','星期四','星期五','星期六'); 

             return $weekday[date('w', $time)]; 

         } 

         return false; 
    }

   
   
    //积分抽积分转盘活动抽奖记录
    public function  W651_record(){
        $user_id = $GLOBALS['user_info']['id'];
        if(!$user_id){
          
          app_redirect(url("index","user#login"));
        }
        
        
        //今日中奖名单
        // $prizesinfo = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."turntable_activity  order by id desc limit 50");

        //我的中奖记录
        $myprizesinfo = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."turntable_activity WHERE user_id ='".$user_id."' order by id desc limit 20");

        foreach ($myprizesinfo as $key => $value) {
          $myprizesinfo[$key]['zhou'] =$this->weekday($value['create_time']);
          $myprizesinfo[$key]['hi'] =date("H:i",$value['create_time']);
          $myprizesinfo[$key]['date'] =date("Y-m-d",$value['create_time']);
          if($value['prizename']=='再来一次'){
                 $myprizesinfo[$key]['prizename'] ='免费再来一次';
          }
        }
        if(empty($myprizesinfo)){
           $myprizesinfo=0;
        }
        //print_r($myprizesinfo);die;
        //$GLOBALS['tmpl']->assign("prizesinfo",$prizesinfo);
        $GLOBALS['tmpl']->assign("myprizesinfo",$myprizesinfo);
        $GLOBALS['tmpl']->display("page/activity/W651_record.html");
    }

    //积分抽积分转盘活动
    public function  W653(){
       $GLOBALS['tmpl']->display("page/activity/W653.html");
    }

    //警告页
    public function warning(){
      $GLOBALS['tmpl']->display("page/activity/warning.html");
    }

    //抢红包分享页面改版

    public  function W654(){

        $user_code=$_REQUEST['code'];
//        $user_code='18822220003';
        $randstr =  es_session::get("randstr");
        if(!$randstr){
            $randstr =md5($this->make_randstr(5));
            es_session::set("randstr",$randstr,300);

        }

        $GLOBALS['tmpl']->assign("randcode",$randstr);

        if ($user_code != "") {
            $numstr = substr_replace($user_code,'****',3,4);
            $GLOBALS['tmpl']->assign("numstr",$numstr);
            $GLOBALS['tmpl']->assign("user_code",$user_code);
        }else{
            $user_code = 0;
            $GLOBALS['tmpl']->assign("user_code",$user_code);
        }
        // 抢红包记录
        $moring_time =strtotime(date("Y-m-d")." 00:00:00");
        $night_time =strtotime(date("Y-m-d")." 23:59:59");
        $red_packet_log = $GLOBALS['db']->getAll("select user_id,sum(rob_red_money) as money from ".DB_PREFIX."red_packet_rob where rob_time >=$moring_time and rob_time <=$night_time group by user_id order by rob_time desc limit 50");
        foreach($red_packet_log as $k=>$v){
           $name_info = $GLOBALS['db']->getRow("select  real_name,mobile from ".DB_PREFIX."user where id=".$v['user_id']);
                if($name_info['real_name']){
                    $red_packet_log[$k]['realname'] = $this->set_real_name($name_info['real_name']);
                }else{
                    $red_packet_log[$k]['realname'] = substr_replace($name_info['mobile'],"****",3,6);
                }

        }

        $GLOBALS['tmpl']->assign('red_packet_log',$red_packet_log);
        $GLOBALS['tmpl']->assign('log_num',count($red_packet_log));


//
        // 分享人的用户名（带星号的）
        $user_name ="w".$user_code;
        $userRow = $GLOBALS['db']->getRow("select id,real_name,mobile from ".DB_PREFIX."user where user_name='".$user_name."'");
        if($userRow['real_name']){
            $frealname =$this->set_real_name($userRow['real_name']);
        }else{
            $frealname =substr_replace($userRow['mobile'],"****",3,4);
        }

        $GLOBALS['tmpl']->assign('frealname',$frealname);
        $GLOBALS['tmpl']->assign('fpid',$userRow['id']);
        //分享人的头像
        $head_url =$GLOBALS['db']->getOne("select header_url from ".DB_PREFIX."user where user_name='".$user_name."'");
        $GLOBALS['tmpl']->assign('header',$head_url);
        // 分享人的现金红包
//        $user_rob = $GLOBALS['db']->getOne('select sum(rob_red_money) from '.DB_PREFIX.'red_packet_rob where user_id='.$userRow['id']);
        $exchange = $GLOBALS['db']->getOne("select new_red_money from ".DB_PREFIX."user where id=".$userRow['id']);
        $exchangeMoney = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."red_packet where user_id=".$userRow['id']." and red_type_id=12");
        $user_rob =floatval($exchange)+ floatval($exchangeMoney);

        if(!$user_rob){
            $user_rob=0.00;
        }
        $GLOBALS['tmpl']->assign('user_rob',$user_rob);

        // 第二部分 当前用户获取随机红包
        $rand_red_packet = es_session::get("rand_red_packet");

        if(empty($rand_red_packet)){
            $rand_red_packet =$this->randomFloat(1,2); // 用户随机金额
            // 获取session 信息 把 红包金额存入session 中
            es_session::set('rand_red_packet',$rand_red_packet);
        }
        $GLOBALS['tmpl']->assign('rand_red_packet',$rand_red_packet);
        $GLOBALS['tmpl']->display("page/activity/W654.html");


    }

    public  function W654_rule(){
        $GLOBALS['tmpl']->display("page/activity/W654_rule.html");
    }

    //方法用途：用户名中间用星号代替
    public function set_real_name($str){

        if(preg_match("/[\x{4e00}-\x{9fa5}]+/u", $str)) {
            //按照中文字符计算长度
            $len = mb_strlen($str, 'UTF-8');
            //echo '中文';
            if($len >= 3){
                //三个字符或三个字符以上掐头取尾，中间用*代替
                $str = mb_substr($str, 0, 1, 'UTF-8') . '*' . mb_substr($str, -1, 1, 'UTF-8');
            } elseif($len == 2) {
                //两个字符
                $str = mb_substr($str, 0, 1, 'UTF-8') . '*';
            }
            return $str;
        } else {
            //按照英文字串计算长度
            $len = strlen($str);
            //echo 'English';
            if($len >= 3) {
                //三个字符或三个字符以上掐头取尾，中间用*代替
                $str = substr($str, 0, 1) . '*' . substr($str, -1);
            } elseif($len == 2) {
                //两个字符
                $str = substr($str, 0, 1) . '*';
            }
            return $str;
        }

    }

    // 返回随机红包1.0 -2.0 之间
   public function randomFloat($min = 0, $max = 1) {
        $float_money = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return round($float_money,1);
    }

    // 验证用户输入信息
    public function ajax_phone_verify(){
        $mobile =$_POST['mobile'];  //手机号
        $code =$_POST['imgcode'];   //图形验证码
        $phonecode =$_POST['phoneCode']; // 手机验证码
//        $code =$_POST['login_pwd'];
        if(empty($mobile)){
            $data['status'] = 0;
            $data['info'] = "请输入手机号";
            $data['input_name'] = "#phone_number";
            ajax_return($data);

        }else{

            if(!preg_match("/^1[34578]\d{9}$/",$mobile)){
                $data['status'] = 0;
                $data['info'] = "请正确输入手机号";
                $data['input_name'] = "#phone_number";
                ajax_return($data);
            }
            //验证手机格式
            $info = $GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."user WHERE mobile =$mobile");
            if($info  > 0) {
                $data['status'] = 0;
                $data['info'] = "手机号码已被注册";
                $data['input_name'] = "#phone_number";
                ajax_return($data);
            }

            if($code==''){
                $data['status'] = 0;
                $data['info'] = "请输入图形验证码";
                $data['input_name'] = "#img_code";
                ajax_return($data);
            }


            if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".$phonecode."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ")==0){
                $return['status'] = 0;
                $return['info'] = "手机验证码出错,或已过期";
                $return['input_name'] = "#phone_code";
                $return['sql'] = "SELECT count(*) FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$mobile."' AND verify_code='".$phonecode."' AND create_time + ".SMS_EXPIRESPAN." > ".TIME_UTC." ";
                ajax_return($return);
                //showErr("手机验证码出错,或已过期");
            }

            $data['status'] = 1;
            $data['info'] = "验证成功";
            ajax_return($data);


        }


    }

    // 分享抢红包
    public function ajax_senduser_redpacket(){

        $userData =$_POST;

        $randcode =$userData['randcode'];
        if($randcode==es_session::get('randstr')){

            es_session::set('randstr',null);
        }else{
            $data['status'] = 0;
            $data['info'] = "您的操作不合法";
            ajax_return($data);

        }

        if(empty($userData['mobile'])){
            $data['status'] = 0;
            $data['info'] = "请输入手机号";
            ajax_return($data);

        }else {

            if (!preg_match("/^1[34578]\d{9}$/", $userData['mobile'])) {
                $data['status'] = 0;
                $data['info'] = "请正确输入手机号";
                ajax_return($data);
            }
            //验证手机格式
            $info = $GLOBALS['db']->getRow("SELECT id FROM " . DB_PREFIX . "user WHERE mobile =" .trim($userData['mobile']));
            if ($info > 0) {
                $data['status'] = 0;
                $data['info'] = "手机号码已被注册";
                ajax_return($data);
            }

            if ($userData['imgcode'] == '') {
                $data['status'] = 0;
                $data['info'] = "请输入图形验证码";
                ajax_return($data);
            }


            if($userData['login_pwd']=='')
            {
                $return['status'] = 0;
                $return['info'] = "密码不能为空";
                ajax_return($return);
            }
            if(!preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/",$userData['login_pwd'])){
                $data['status'] = 0;
                $data['info'] = "请输入6-16位数字和字母组合";
                ajax_return($data);
            }

             //验证邀请人
            $pidInfo = $GLOBALS['db']->getOne("SELECT id FROM " .DB_PREFIX."user WHERE id =".intval($userData['pid']));
            if (empty($pidInfo)) {
                $data['status'] = 0;
                $data['info'] = "邀请人不存在！";
                ajax_return($data);
            }



        }

        //注册操作（一）
        $user_data['mobile']=$userData['mobile'];
        $user_data['user_name']='w'.$userData['mobile'];
        $user_data['user_pwd']=$userData['login_pwd'];
        $user_data['is_effect'] = 1;
        $user_data['cunguan_tag'] = 1;
        $user_data['mobilepassed'] = 1;
        $user_data['pid'] = $userData['pid'];
        $user_data['referer'] =$GLOBALS['db']->getOne("SELECT mobile FROM " .DB_PREFIX."user WHERE id =".$userData['pid']);
        $user_data['referer_time'] =time();
        $user_data["mobile_encrypt"] = " AES_ENCRYPT('".$user_data['mobile']."','".AES_DECRYPT_KEY."') ";
        $res =save_user($user_data);
        //记录红包操作（二）
        if($res['status']==1){
            $user_id=$GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."user WHERE user_name="."'".$user_data['user_name']."'");
            if(es_session::get('rand_red_packet')){
                $order_data['new_red_money'] =es_session::get('rand_red_packet');
                 $status = $GLOBALS['db']->autoExecute(DB_PREFIX."user",$order_data,"UPDATE","id=".$user_id);//更新用户红包金额
//                    var_dump($GLOBALS['db']);
                    $red_package_log['user_id']=$user_id;
                    $red_package_log['red_money']=$order_data['new_red_money'];
                    $red_package_log['new_red_money']=$order_data['new_red_money'];
                    $red_package_log['addtime']=date("Y-m-d H:i:s",time());
                    $red_package_log['remark']='邀请好友红包';
                    $red_package_log['type']=1;
                    $red_package_log['action']=5;
                    $GLOBALS['db']->autoExecute(DB_PREFIX."red_packet_log",$red_package_log,"INSERT");
                    if($status){
                        $data['status'] = 1;
                        $data['info'] = "领取成功";
                        ajax_return($data);

                    }else{
                        $data['status'] = 0;
                        $data['info'] = "领取失败";
//                        $data['sql']=$order_data;
                        ajax_return($data);
                    }

            }else{
                $return['status'] = 0;
                $return['info'] = "抱歉,红包已过期";
                ajax_return($return);
            }

        }else{

            $return['status'] = 0;
            $return['info'] = "注册失败";
            ajax_return($return);

        }

    }

    public function send_phone_verifycode_seven(){

        $user_mobile = strim($_REQUEST['mobile']);
        $verify_code =  strim($_REQUEST['imgcode']);

        //判断手机号是否注册
        if($user_mobile==''){
            $data['status'] = 0;
            $data['info'] = "手机号不能为空";
            $data['input_name'] = "#phone_number";
            ajax_return($data);
        }
        if(!preg_match("/^1[34578]\d{9}$/",$user_mobile)){
            $data['status'] = 0;
            $data['info'] = "请正确输入手机号";
            $data['input_name'] = "#phone_number";
            ajax_return($data);
        }
        $info = $GLOBALS['db']->getRow("SELECT id FROM ".DB_PREFIX."user WHERE mobile ='".$user_mobile."'");
        if($info  > 0){
            $data['status'] = 0;
            $data['info'] = "手机号码已被注册";
            $data['input_name'] = "#phone_number";
            ajax_return($data);
        }

      if ($verify_code == '') {
            $data['status'] = 0;
            $data['info'] = "请输入图形验证码";
            $data['input_name'] = "#img_code";
            ajax_return($data);
        }

        if ($verify_code) {
            if (!checkVeifyCode($verify_code)) {
                $data['status'] = 0;
                $data['info'] = "图形验证码错误";
                $data['input_name'] = "#img_code";
                ajax_return($data);
            }
        }


            $shortmessage = 1 + es_session::get('shortmessage');
            es_session::set("shortmessage",$shortmessage);
            if(es_session::get('shortmessage') <= 200){
                //开始生成手机验证
                $t = time();
                $begin_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)); 	//今天开始时间
                $result = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time);
                if(!check_ipop_limit(CLIENT_IP,"register_mobile_verify",60,0))
                {
                    $data['status'] = 0;
                    $data['info'] = $GLOBALS['lang']['VERIFY_CODE_SEND_FAST'];
                    $data['input_name']="#phone_code";
                    ajax_return($data);
                }
                if($GLOBALS['db']->getOne("select send_count from ".DB_PREFIX."mobile_verify_code where mobile =".$user_mobile." and create_time >".$begin_time) >= SEND_VERIFYSMS_LIMIT){
                    $data['status'] = 0;
                    $data['info'] = "你今天已经不能再发验证码了";
                    $data['input_name']="#phone_code";
                    ajax_return($data);
                }
                $verify_data['verify_code'] = rand(111111,999999);
                $verify_data['mobile'] = $user_mobile;
                $verify_data['create_time'] = TIME_UTC;
                $verify_data['client_ip'] = CLIENT_IP;
                if($vid = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."mobile_verify_code WHERE mobile='".$user_mobile."'"))
                    $GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"UPDATE","id=".$vid);
                else
                    $GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",$verify_data,"INSERT");
                send_verify_sms($user_mobile,$verify_data['verify_code'],$users,true);
                $data['status'] = 1;
                $data['info'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
                $data['input_name']="#phone_code";
                ajax_return($data);
            }
            if (es_session::get('shortmessage') > 200) {
                $data['status'] = 2;
                $data['info'] = '请重新获取短信验证码';
                $data['input_name']="#phone_code";
                $data['codeinfo'] = es_session::get('shortmessage');
                ajax_return($data);
            }

    }

    function make_randstr( $length = 8 )
    {

// 密码字符集，可任意添加你需要的字符
        $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

// 在 $chars 中随机取 $length 个数组元素键名

        $password = "";
        for($i = 0; $i < $length; $i++)
        {
// 将 $length 个数组元素连接成字符串
            $keys = mt_rand($i,count($chars)-1);
            $password .= $chars[$keys];

        }

        return $password;
    }


   public function  W655(){
       $GLOBALS['tmpl']->display("page/activity/W655.html");
    }

    public function  W656(){
       $GLOBALS['tmpl']->display("page/activity/W656.html");
    }

    public function  wxvip(){
       $GLOBALS['tmpl']->display("page/activity/wxvip.html");
    }

}
?>