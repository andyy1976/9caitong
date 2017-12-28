<?php
define(ACTION_NAME,"activity");
define(MODULE_NAMEN,"index");
require APP_ROOT_PATH.'system/libs/user.php';
class activityModule extends SiteBaseModule
{
    
	public function inte($where){
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."activity where cunguan_tag =1 and ".$where."");
		
		foreach($list as $k=>$v){
			$list[$k]['start_time'] = date('Y.m.d',$v['start_time']);
			$list[$k]['end_time'] = date('Y.m.d',$v['end_time']);
		}
		$GLOBALS['tmpl']->assign('list',$list);
	}
	public function index()
	{
		$where = "disable = 1 and is_effect = 1 and is_delete = 1 and use_way in(1,2) and end_time >= '".TIME_UTC."' order by activity_id desc";
		$this->inte($where);
		$GLOBALS['tmpl']->assign("page_title","热门活动");
		$GLOBALS['tmpl']->assign("page_keyword","热门活动");
		$GLOBALS['tmpl']->assign("page_description","热门活动");
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
		$GLOBALS['tmpl']->display("page/activity.html");
	}
	public function past()
	{
		$where = "disable = 1 and is_effect = 1 and is_delete = 1 and use_way in(1,2) and end_time <= '".TIME_UTC."' order by activity_id desc";
		$this->inte($where);
		$GLOBALS['tmpl']->assign("page_title","往期活动");
		$GLOBALS['tmpl']->assign("page_keyword","热门活动");
		$GLOBALS['tmpl']->assign("page_description","热门活动");
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
		$GLOBALS['tmpl']->display("page/activity_past.html");
	}
	public function P1(){
		$activity_id = $_REQUEST['id'];
		$user_list = get_referer_list($activity_id,1);
		$data = $this->happy_zuo($user_list,$activity_id);
		$GLOBALS['tmpl']->assign("data",$data);
		$data_y = $this->happy_you($user_list,$activity_id);
		$GLOBALS['tmpl']->assign("data_y",$data_y);
		$GLOBALS['tmpl']->assign("user_list",$user_list);
		$GLOBALS['tmpl']->assign("activity_id",$activity_id);
		$GLOBALS['tmpl']->assign("user_id",$GLOBALS['user_info']['id']);
		if(file_exists("./app/Tpl/wap/page/activity/P1.html")){
			$GLOBALS['tmpl']->display("page/activity/P1.html");
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
	function P632()
	{
		$GLOBALS['tmpl']->display("page/activity/P632.html");
	}
	
	
	/**
	 * 专注首尾标，指缝留红包
	 */
	function P633()
	{
		$GLOBALS['tmpl']->display("page/activity/P633.html");
	}
	
	/**
	 * 高质用户，红包任性拿
	 */
	function P636()
	{
	    $GLOBALS['tmpl']->display("page/activity/P636.html");
	}

	
	
	/**
	 * 首页banner   注册就送84442体验金+50元代金券
	 */
	function banner_register(){
		if($GLOBALS["user_info"]){
			$GLOBALS['tmpl']->assign("user_msg",$GLOBALS["user_info"]);
		}
		$GLOBALS['tmpl']->display("page/activity/banner_register.html");
	}

    /*
    *  签约存管
     * */
    public function contract_depository(){
        $GLOBALS['tmpl']->display("page/activity/Contract_depository.html");
    }

    /*
     *  注册送体验金
     * */
    public function registered_experience(){
        $GLOBALS['tmpl']->display("page/activity/Registered_experience.html");
    }
        	/*
	*前端-新手福利-2017.07.11_PC
	*/
	function P640()
	{
		//添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
	    $GLOBALS['tmpl']->display("page/activity/P640.html");
	}
	    	/*
	*前端-推广页面-2017.07.11_PC
	*/
	function P641()
	{
		//添加渠道来源
        $source_id = es_session::get("source_id");
        $device    = es_session::get("device");
        if(!empty($source_id)){
            es_session::set("add_time",TIME_UTC);
        }
	    $GLOBALS['tmpl']->display("page/activity/P641.html");
	}
	//新手引导页
	function Contract_depository_cgzy(){
		$GLOBALS['tmpl']->display("page/activity/Contract_depository_cgzy.html");
	}
    // 庆存管
    function happy_cg(){
    	$jump = machineInfo();
    	$GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->display("page/activity/happy_cg.html");
    }
    //七夕活动
    function P646(){
        $data['user_id'] =$GLOBALS["user_info"]['id'];
        $all = $GLOBALS['db']->getAll("select sign from ".DB_PREFIX."send_qixi where user_id=".$data['user_id']);
        foreach($all as $k=>$v){
            $number[]=$v['sign'];
        }
        $number=count($number);
        //计算任务五
        $invite_time=strtotime("2017-08-28 0:0:0");
        $invited_time=strtotime("2017-08-30 23:59:59");
        $conditon1 = " pid=".$data['user_id']." and create_time between " .$invite_time. " and ".$invited_time;
        $invited_count = count($GLOBALS['db']->getAll("select pid,id from jctp2p_user where " .$conditon1." and id in(select DISTINCT(user_id) from jctp2p_deal_load where cunguan_tag=1) and cunguan_tag=1"));
        //全部奖励
        $all_Award = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."send_qixi where type<>7 order by id desc limit 0,100");
      
        $my_Award = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."send_qixi where type<>7  and user_id =".$data['user_id']);
        foreach ($all_Award as $k=>$v){
            $v["user_name"] =substr($v["user_name"],1,11);
            $all_Award[$k]['user_name']= substr_replace($v["user_name"],'***',3,4);
            $all_Award[$k]['add_time']= date('m月d日',$v['addtime']);
        }
        foreach ($my_Award as $k=>$v){
            $v["user_name"] =substr($v["user_name"],1,11);
            $my_Award[$k]['user_name']= substr_replace($v["user_name"],'***',3,4);
            $my_Award[$k]['add_time']= date('m月d日',$v['addtime']);
        }
        
        //搭建鹊桥排行榜
        $paihang = $GLOBALS['db']->getAll("select user_id from ".DB_PREFIX."send_qixi  group by user_id");
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
                    $name =substr($name,1,11);
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
        //计算投资金额
        //活动期间玖财通存管版出借普通标的10000元及以上的用户（折标后）
        //活动期间玖财通存管版出借普通标的50000元及以上的用户（折标后）
        $zhebiao_money= $this-> zhebiao($data['user_id']);
        
        $GLOBALS['tmpl']->assign("zhebiao_money",$zhebiao_money);
        $GLOBALS['tmpl']->assign("invited_count",$invited_count);
        $GLOBALS['tmpl']->assign("user_id",$data['user_id']);
        $GLOBALS['tmpl']->assign("user_infos",$number);
        $GLOBALS['tmpl']->assign("num",count($number));
        $GLOBALS['tmpl']->assign("all_Award",$all_Award);
        $GLOBALS['tmpl']->assign("my_Award",$my_Award);
        $GLOBALS['tmpl']->assign("ranking",$ranks);
        $GLOBALS['tmpl']->display("page/activity/P646.html");
    }
    // 折标方法
    public function zhebiao($user_id){
        $begin_time=strtotime("2017-08-28 0:0:0");
        $end_time=strtotime("2017-08-30 23:59:59");
        
        $conditon = " d.is_effect=1 and d.is_delete=0 and d.publish_wait = 0 and dl.user_id = ".$user_id." and dl.create_time between ".$begin_time ." and " .$end_time. " and d.cunguan_tag=1";
        $investInfo = $GLOBALS['db']->getAll("SELECT dl.money,d.repay_time FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON d.id =dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id where " .$conditon);        
        $zhebiao_money = 0;   
        foreach($investInfo as $k => $v){
            $zhebiao_money += round($v['money'] * $v['repay_time']/12,1);
        }
        return $zhebiao_money;
    }
    
    public function P647(){
  
        $GLOBALS['tmpl']->display("page/activity/P647.html");
    }

    /**
     * 抢红包活动 wap
     * 
     */
    public function W649(){

        $resultArr = $this->dataFormRedis();

        $GLOBALS['tmpl']->assign('datas',$resultArr);

        $GLOBALS['tmpl']->display("page/activity/W649.html");
    }

    /**
     * 抢红包活动 微信专用链接
     *
     */
    public function WX649(){

        $resultArr = $this->dataFormRedis();

        $GLOBALS['tmpl']->assign('datas',$resultArr);

        $GLOBALS['tmpl']->display("page/activity/WX649.html");
    }
    /**
     * 抢红包活动 pc
     * 
     */
    public function P649(){
        $resultArr = $this->dataFormRedis();

       $GLOBALS['tmpl']->assign('datas',$resultArr);

        $GLOBALS['tmpl']->display("page/activity/P649.html");

        
    }

    public function P650(){

        $GLOBALS['tmpl']->display("page/activity/P650.html");
    }
    
    public function P652(){
    
        $GLOBALS['tmpl']->display("page/activity/P652.html");
    }
    
    public function W652(){
        $GLOBALS['tmpl']->display("page/activity/W652.html");
    }

    /**
     * 抢红包活动 app
     * 
     */
    public function A649(){

    	// $resultArr = $this->getDataFor469();
         $resultArr = $this->dataFormRedis();

    	$GLOBALS['tmpl']->assign('datas',$resultArr);
        //添加渠道来源
        $MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息

        switch ($MachineInfo[0]) {
            case 'iOS':
                $jump['jumpToFindPage'] = 'iosjumpToFindPage';
                break;
            case 'Android':
                $jump['jumpToFindPage'] = 'androidjumpToFindPage';
                break;
            default:
                break;
        }
        $GLOBALS['tmpl']->assign('jump',$jump); 
        $GLOBALS['tmpl']->display("page/activity/A649.html");
    }

    function dataFormRedis(){

        $redis = new Redis();
        //$redis->connect('127.0.0.1',REDIS_PORT);
        $redis->connect(REDIS_HOST,REDIS_PORT);
        $redis->auth(REDIS_PWD);
        $redis->select(8);
        $user_list = json_decode($redis->hGet(REDIS_PREFIX.'lists_for_red_activity','ranksInfo'),true);

        if (!empty($user_list) && is_array($user_list)) {

            return $user_list;
           
        } else {

            $user_list = $this->getDataFor469();
            $redis->hSet(REDIS_PREFIX.'lists_for_red_activity','ranksInfo',json_encode($user_list));
            $redis->expire(REDIS_PREFIX.'lists_for_red_activity',600);
            return $user_list;
        }
        

        // $user_list = $this->getDataFor469();
        
    }

    //获取轮播展示数据
    public function getDataFor469(){
    	//获取活动期间参与抢红包的userid 和 钱数
    	
    	$start_time = strtotime("2017-09-26 12:00:00");

    	$end_time = strtotime("2017-10-11 11:59:59");

    	$where = "`type` = 1 and rob_time >= $start_time and rob_time <= $end_time and rob_red_money > 0";
    	//数据格式为:Array ( [0] => Array ( [user_id] => 1123430 [rob_red_money] => 0.10 ) [1] => Array ( [user_id] => 1123431 [rob_red_money] => 0.00 ).....)
    	$info_during_activity = $GLOBALS['db']->getAll("select user_id,rob_red_money from ".DB_PREFIX."red_packet_rob where " .$where);
    	//用户id数组
    	$userid_array;

        //详细信息//["用户id"=>["个人抢红包次数"=>"22","抢红包总次数"=>"323","手机号"=>"***","下线个数"=>"2222"]]
        /*$dattaInfo = array(
            "1154057"=> array("totalmoney_for_self"=> "0.44", "totaltimes_for_self"=> "1", "xiaxian_for_self"=>5  ,"totaltimes"=>  "2049", "totalmoney"=>  "0.44" ),
            "1151589"=> array("totalmoney_for_self"=> "0.44", "totaltimes_for_self"=> "1", "xiaxian_for_self"=>5  ,"totaltimes"=>  "1425", "totalmoney"=>  "0.44" )
        );*/
        $dattaInfo = array();
    	foreach ($info_during_activity as $uids => $infoArr) {

            if($infoArr['user_id']=="1154057" || $infoArr['user_id'] == "1151589"){
                unset($info_during_activity[$uids]);
                continue;
            }

            $user_id = $infoArr["user_id"];

    		$userid_array[] = $user_id;

    		//去重
    		$userid_array = array_unique($userid_array);

            //活动期间,每个用户的自己抢了多少钱
            if (!isset($dattaInfo[$user_id]["totalmoney_for_self"])) {

                $dattaInfo[$user_id]["totalmoney_for_self"] = $infoArr["rob_red_money"];

            } else {

                 $dattaInfo[$user_id]["totalmoney_for_self"] = $dattaInfo[$user_id]["totalmoney_for_self"] + $infoArr["rob_red_money"];
            }

            //活动期间,每个用户自己抢了多少次红包
            if (!isset($dattaInfo[$user_id]["totaltimes_for_self"])) {

                 $dattaInfo[$user_id]["totaltimes_for_self"] = "1";

            } else {

                 $dattaInfo[$user_id]["totaltimes_for_self"] = $dattaInfo[$user_id]["totaltimes_for_self"] + '1';
            }

    	}

    	//拿到每个用户的下线用户
    	
    	foreach ($userid_array as $value) {
    		
    		$condition_for_select_pid = "pid = $value and create_time >= $start_time and create_time <= $end_time";

    		//获取活动期间的的下线用户
    		$downids = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."user where " .$condition_for_select_pid);

            //用户下线个数
            $dattaInfo[$value]["xiaxian_for_self"] = count($downids);

    		//二维数组转一维数组
    		$downidArr;
    		foreach ($downids as $key => $value1) {

    			$downidArr[] = $value1["id"];
    		}

    		//下线用户数组与参与抢红包的用户数组取交集
    		$joinActivity_users = array_intersect($downidArr, $userid_array);

            unset($downidArr);

            //用户自己+下线抢红包的次数
            $dattaInfo[$value]["totaltimes"] = $dattaInfo[$value]['totaltimes_for_self'];

            //用户自己+下线抢的钱总数
            $dattaInfo[$value]["totalmoney"] = $dattaInfo[$value]['totalmoney_for_self'];

            //下面是加上下线的,上面只是加上自己的
    		foreach ($joinActivity_users as $value2) {

                 $dattaInfo[$value]["totaltimes"] = $dattaInfo[$value]["totaltimes"] + $dattaInfo[$value2]['totaltimes_for_self'];

                 
                 $dattaInfo[$value]["totalmoney"] = $dattaInfo[$value]["totalmoney"] + $dattaInfo[$value2]["totalmoney_for_self"];

    		}

    	}

       //然后根据totaltimes(抢红包总次数) xiaxian_for_self (下线个数) 以及 totalmoney(抢的红包总钱数) 综合排序
       //自定义降序排列函数
       function compare($x,$y){

            if ($x['totaltimes'] == $y['totaltimes']) {

                if ($x['xiaxian_for_self'] == $y['xiaxian_for_self']) {
                    
                    return $x['totalmoney'] < $y['totalmoney'] ? 1 : 0;
                   
                } else {
                    
                    return $x['xiaxian_for_self'] < $y['xiaxian_for_self'] ? 1 : 0;
                }
                
            } else {
                return $x['totaltimes'] < $y['totaltimes'] ? 1 : -1;
            }
            
       }
       //排序
       uasort($dattaInfo,"compare");

       
       //将20个人的数组改为索引数组
       $index = 1;
       foreach ($dattaInfo as $uid => $valuexxx) {

            //取前20个人
            if($index > 20){

                break;
            }
            //将原来的键名添加到数组中
            $valuexxx['userid'] = $uid ;

            //更具userid取出手机号
            $conditon_for_search_phpone = "id = $uid";

            $phone_number = $GLOBALS['db']->getAll("select mobile from ".DB_PREFIX."user where " .$conditon_for_search_phpone);

            //格式化****
            if (!empty($phone_number)) {

                //格式化手机号  加****
                $fomated_phone = substr_replace($phone_number[0]["mobile"],'****',3,4);

                $valuexxx['phone'] = $fomated_phone;
            } 

            $resultInfo[$index] = $valuexxx;

            $index += 1;
       }
    	return $resultInfo;

    }

    public function W650(){
        // $step = $_GET['today_step'];
        $step = '1000';
        $GLOBALS['tmpl']->assign('step',$step); 
        $GLOBALS['tmpl']->display("page/activity/W650.html");
    }

    public function saveStepFor650($step){
        // $step = $_GET['today_step'];
        



    }

    public function P655(){
        
        
        $GLOBALS['tmpl']->display("page/activity/P655.html");
    }

    public function P656(){
        
        
        $GLOBALS['tmpl']->display("page/activity/P656.html");
    }

}
?>