<?php
define(ACTION_NAME,"memberlevel");
define(MODULE_NAMEN,"index");
require_once APP_ROOT_PATH."system/user_level/Level.php";
class memberLevelModule extends SiteBaseModule
{
	function __construct(){
		parent::__construct();
		
		$user_info=$GLOBALS['user_info'];
		if(!$user_info){
			app_redirect(url("index","user#login"));
		}
	}
	public function index()
	{
		
        //移动端跳转交互
        $jump = machineInfo();
        $GLOBALS['tmpl']->assign('jump',$jump);
		
		$level = new Level();
        $user_img=$GLOBALS['user_info']['header_url'];
		$data=$level->get_user_vip_level($GLOBALS['user_info']['id']);
		$grow_point =$GLOBALS['user_info']['grow_point'];
        $goods = $GLOBALS['db']->getAll("select id,score,name,img,discount_score,Consumer_integration from ".DB_PREFIX."goods where is_vip=1 and is_ground=1 and max_bought>0 order by ground_time desc limit 4");

        $GLOBALS['tmpl']->assign("goods",$goods);
        $GLOBALS['tmpl']->assign("user_img",$user_img);
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("grow_point",$grow_point);
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$GLOBALS['tmpl']->assign("page_title","会员等级");
		$GLOBALS['tmpl']->assign("page_keyword","会员等级,");
		$GLOBALS['tmpl']->assign("page_description","会员等级,");
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		$GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
		$GLOBALS['tmpl']->display("page/memberLevel.html");
	}
    // 成长任务
	public function GrowthMission()
	{
        $newbie_task1=$this->task_info(1); 
        $newbie_task2=$this->task_info(9);
        $newbie_task3=$this->task_info(12);
        $newbie_task4=$this->task_info(19);
        $GLOBALS['tmpl']->assign("newbie_task1",$newbie_task1);
        $GLOBALS['tmpl']->assign("newbie_task2",$newbie_task2);
        $GLOBALS['tmpl']->assign("newbie_task3",$newbie_task3);
        $GLOBALS['tmpl']->assign("newbie_task4",$newbie_task4);
		$GLOBALS['tmpl']->assign("page_title","成长任务");
	    $GLOBALS['tmpl']->display("page/memberLevel_GrowthMission.html");

	}
    //PC 我的特权
	public function privilege()
	{
        require_once APP_ROOT_PATH.'app/Lib/page.php';
        $page = intval($_REQUEST['p']);
        if($page==0)
            $page = 1;
        
        $limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
        $result['list'] = $GLOBALS['db']->getAll("select grow_point,account_point,intro,create_date,create_time from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['id']." order by id desc limit ".$limit);
        $result['count'] = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['id']);
        $GLOBALS['tmpl']->assign("list",$result['list']);
        $page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象       
        $p  =  $page->show();
        $GLOBALS['tmpl']->assign('pages',$p);
		$user_img=$GLOBALS['user_info']['header_url'];
		$level = new Level();
		$data=$level->get_user_vip_level($GLOBALS['user_info']['id']);
		$grow_point =$GLOBALS['user_info']['grow_point'];
		$level_privilege = $GLOBALS['db']->getAll('select privilege_name,privil,rate_val,rule_content,privilege_content,type from '.DB_PREFIX.'level_privilege where is_delete=0');
		foreach($level_privilege as $k=>$v){
			$arr = explode(",",$v['privil']);
			$rate_arr = explode(",",$v['rate_val']);
            $type = explode(",",$v['type']);
			$level_privilege[$k]['privilege'] =$arr;
            $level_privilege[$k]['rate'] =$rate_arr;
			$level_privilege[$k]['type'] =$type;
            foreach($type as $key =>$val){
                if($val==5 && $rate_arr[$key]==1){
                    $level_privilege[$k]['rate'][$key] = "<img src='/new/images/memberLevel/have.png' />";
                }elseif($val==5 && $rate_arr[$key]==0){
                    $level_privilege[$k]['rate'][$key] = '';
                }
            }
		}

		$GLOBALS['tmpl']->assign("level_privilege",$level_privilege);
		$GLOBALS['tmpl']->assign("user_img",$user_img);
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("grow_point",$grow_point);
		$GLOBALS['tmpl']->assign("page_title","会员特权");
	    $GLOBALS['tmpl']->display("page/memberLevel_privilege.html");
	}
	// WAP成长值记录
    public function member_record(){
    	require APP_ROOT_PATH.'app/Lib/uc.php';
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
    	$member_record=$GLOBALS['db']->getAll("select grow_point,account_point,intro,create_time from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['id']." order by id desc limit ".$limit);
    	if(count($member_record)>0){
    		foreach ($member_record as $k => $v) {
    			if(date('Y-m-d',$v['create_time'])==date('Y-m-d',time())){
    				$member_record[$k]['day'] = '今天';
    			}else{
    				$member_record[$k]['day'] = '';
    			}
    			if($v['grow_point']>0){
    				$member_record[$k]['grow_point'] = '+'.$v['grow_point'];
    			}
    			$member_record[$k]['create_time']=date('H:i',$v['create_time']);
    			$member_record[$k]['create_date']=date('Y-m-d',$v['create_time']);
    		}
    		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
    		$GLOBALS['tmpl']->assign('member_record',$member_record);
    		$GLOBALS['tmpl']->display("page/member_record.html");
    	}else{
    		// 无记录
    		$GLOBALS['tmpl']->display("page/member_record1.html");
    	}
    }
    // 会员特权
    public function member_privilege(){
    	
    	$GLOBALS['tmpl']->display("page/member_privilege.html");

    }
    //  特权详情
    public function member_privilege_info(){
    	$type=strim($_REQUEST['type']);
    	$GLOBALS['tmpl']->display("page/privilege/privilege".$type.".html");
    }
    // 如何提升
    public function member_introduce(){
		$GLOBALS['tmpl']->display("page/member_introduce.html");
    }
    // 任务详情(新手任务)
    public function member_details1(){
    	$newbie_task=$this->task_info(1);
    	$GLOBALS['tmpl']->assign("newbie_task",$newbie_task);
		$GLOBALS['tmpl']->display("page/member_details1.html");
    }
    // 充值任务
    public function member_details2(){
    	$newbie_task=$this->task_info(9);
    	$GLOBALS['tmpl']->assign("newbie_task",$newbie_task);
		$GLOBALS['tmpl']->display("page/member_details2.html");
    }
    // 成长任务
    public function member_details3(){
    	$newbie_task=$this->task_info(12);
    	// // 是否邀请一位好友
    	// $invite=$GLOBALS['db']->getOne('select id from '.DB_PREFIX.'user where pid='.$GLOBALS['user_info']['id']);
    	// if(isset($invite) && $invite>0){
    	// 	$data['is_invite']=1;
    	// }
    	// // 使用玖财通时长
    	// $use_days=ceil((time()-$GLOBALS['user_info']['create_time'])/3600/24);
    	// if($use_days>=7){
    	// 	$data['is_seven']=1;
    	// }
    	$GLOBALS['tmpl']->assign("newbie_task",$newbie_task);
		$GLOBALS['tmpl']->display("page/member_details3.html");
    }
    // 出借任务
    public function member_details4(){
    	$newbie_task=$this->task_info(19);
    	$GLOBALS['tmpl']->assign("newbie_task",$newbie_task);
		$GLOBALS['tmpl']->display("page/member_details4.html");
    }



    public function ajax_record(){        
        echo $GLOBALS['db']->getOne("SELECT count(id) FROM ".DB_PREFIX."user_grow_point WHERE user_id=".$GLOBALS['user_info']['id']);
    }
    // wap 下拉分页
    public function recordList(){
    	require APP_ROOT_PATH.'app/Lib/uc.php';
    	$page = $_REQUEST['page'];
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$member_record = $GLOBALS['db']->getAll("select grow_point,account_point,intro,create_time from ".DB_PREFIX."user_grow_point where user_id=".$GLOBALS['user_info']['id']." limit ".$limit);
		foreach ($member_record as $k => $v) {
			if(date('Y-m-d',$v['create_time'])==date('Y-m-d',time())){
    				$member_record[$k]['day'] = '今天';
    			}else{
    				$member_record[$k]['day'] = '';
    			}
    			if($v['grow_point']>0){
    				$member_record[$k]['grow_point'] = '+'.$v['grow_point'];
    			}
    			$member_record[$k]['create_time']=date('H:i',$v['create_time']);
    			$member_record[$k]['create_date']=date('Y-m-d',$v['create_time']);
		}
		$GLOBALS['tmpl']->assign('member_record',$member_record);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign("page",app_conf("PAGE_SIZE"));
		if (empty($member_record)) {
                echo 'false';
        }else{
        	$str='';
        	foreach ($member_record as $kk=>$vv){
        		$str.='<li>';
				$str.="<p class='record-li-p1'>".$vv['day']."<br>".$vv['create_time']."</p>
					<p class='record-li-p2'><i class='record-ico'></i></p>
					<p class='record-li-p3'>".$vv['grow_point']."<br>".$vv['create_date']."</p>
					<p class='record-li-p4'>".$vv['intro']."</p>";
				$str.="</li>";
        	}
				
           	// $GLOBALS['tmpl']->assign("member_record",$member_record);
            // $info = $GLOBALS['tmpl']->fetch("page/member_record.html");
             echo $str;
        }
		
    }
    // 任务详情
    private function task_info($pid){
    	//移动端的交互
    	$MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
        if(isset($pid) && $pid>0){
            $newbie_task=$GLOBALS['db']->getAll('select id,short_name,task_type,grow_point,`desc` from '.DB_PREFIX.'grow_point_config where pid='.$pid.' and is_delete=0 order by sort asc');
        }else{
            $newbie_task=$GLOBALS['db']->getAll('select id,short_name,task_type,grow_point,`desc` from '.DB_PREFIX.'grow_point_config where pid>0 and is_delete=0 order by pid asc, sort asc');
        }
        
        $user_id=$GLOBALS['user_info']['id'];
        $pay_count=$GLOBALS['db']->getOne('select count(id) from '.DB_PREFIX.'user_money_log where type=1 and cunguan_tag=1 and user_id='.$user_id);
        // 使用玖财通时长
        $use_jct_days=ceil((time()-$GLOBALS['user_info']['create_time'])/86400);
        $load_count=$GLOBALS['db']->getOne('select count(id) from '.DB_PREFIX.'deal_load where user_id='.$user_id);
        foreach($newbie_task as $k=>$v){
            if($v['task_type'] == 1){
                $newbie_task[$k]['is_finished']=1;
            }
            if($v['task_type'] == 2){
                // 是否存管开户
                $newbie_task[$k]['url']=url("index","uc_depository_account");
                if($GLOBALS['user_info']['cunguan_tag'] == 1){  
                    $newbie_task[$k]['is_finished']=1;
                }
            }
            if($v['task_type'] == 3){
                // 是否绑定存管银行卡
                $newbie_task[$k]['url']=url("index","uc_account#security");
                $bank_id=$GLOBALS['db']->getOne('select id from '.DB_PREFIX.'user_bank where user_id='.$user_id.' and cunguan_tag=1 and status=1');
                if(isset($bank_id) && $bank_id>0){
                    $newbie_task[$k]['is_finished']=1;
                }
                
            }
            if($v['task_type'] == 4){
                // 是否设置存管交易密码
                $newbie_task[$k]['url']=url("index","uc_account#security");
                if($GLOBALS['user_info']['cunguan_pwd'] == 1){
                    $newbie_task[$k]['is_finished']=1;
                }
            }
            if($v['task_type'] == 5){
                // 是否风险评估
                $assess=$GLOBALS['db']->getOne('select id from '.DB_PREFIX.'wenjuan_user_answer_record where user_id='.$user_id);
                $newbie_task[$k]['url']=url("index","uc_account");
                if(isset($assess) && $assess>0){
                    $newbie_task[$k]['is_finished']=1;
                }
            }
            if($v['task_type'] == 6){
                // 完善个人信息
                $newbie_task[$k]['url']=url("index","uc_account");
                $newbie_task[$k]['is_finished']=0;
            }
            if($v['task_type'] == 7){
                // 微信绑定
                if(isset($GLOBALS['user_info']['wx_openid']) && !empty($GLOBALS['user_info']['wx_openid'])){
                    $newbie_task[$k]['is_finished']=1;
                }
            }
            if($v['task_type'] == 8){
                // 首次充值
                $newbie_task[$k]['url']=url("index","uc_money#incharge");
                if(isset($pay_count) && $pay_count>0){
                    $newbie_task[$k]['is_finished']=1;
                }
            }
            if($v['task_type'] == 9){
                // 任意充值
                $newbie_task[$k]['url']=url("index","uc_money#incharge");
                $newbie_task[$k]['grow_point']='M';
                if(isset($pay_count) && $pay_count>0){
                    $newbie_task[$k]['is_finished']=0;
                }
            }
            if($v['task_type'] == 10){
                // 邀请一个好友
                if(WAP == 1){
                    $newbie_task[$k]['url']=url("index","invite#invete_repair");
                }else{
                    $newbie_task[$k]['url']=url("index","uc_invite");
                }
                if(isset($invite_count) && $invite_count>0){
                    $newbie_task[$k]['is_finished']=0;
                }
                
            }
            if($v['task_type'] == 11){
                // 使用玖财通满7天
                if(isset($use_jct_days) && $use_jct_days>=7){
                    $newbie_task[$k]['is_finished']=1;
                }else{
                    $newbie_task[$k]['is_finished']=2;
                }
            }
            if($v['task_type'] == 12){
                // 使用玖财通满30天
                if(isset($use_jct_days) && $use_jct_days>=30){
                    $newbie_task[$k]['is_finished']=1;
                }else{
                    $newbie_task[$k]['is_finished']=2;
                }
            }
            if($v['task_type'] == 13){
                // 使用玖财通满60天
                if(isset($use_jct_days) && $use_jct_days>=60){
                    $newbie_task[$k]['is_finished']=1;
                }else{
                    $newbie_task[$k]['is_finished']=2;
                }
            }
            if($v['task_type'] == 14){
                // 使用玖财通满100天
                if(isset($use_jct_days) && $use_jct_days>=100){
                    $newbie_task[$k]['is_finished']=1;
                }else{
                    $newbie_task[$k]['is_finished']=2;
                }
            }
            if($v['task_type'] == 15){
                // 出借满10次
                if(isset($load_count) && $load_count>=10){
                    $newbie_task[$k]['is_finished']=1;
                }else{
                    if($MachineInfo[0] == "iOS"){
                        $newbie_task[$k]['url']="jumpToProductList";
                        $newbie_task[$k]['MachineInfo']="iOS";
                    }elseif($MachineInfo[0] == "Android"){
                        $newbie_task[$k]['url']="jumpToProductList";
                        $newbie_task[$k]['MachineInfo']="Android";
                    }else{
                        $newbie_task[$k]['url']=url("index","deals");
                        $newbie_task[$k]['MachineInfo']="Wap";
                    }
                }
            }
            if($v['task_type'] == 22){
                // 首次出借
                if(isset($load_count) && $load_count>0){
                    $newbie_task[$k]['is_finished']=1;
                }else{
                    if($MachineInfo[0] == "iOS"){
                        $newbie_task[$k]['url']="jumpToProductList";
                        $newbie_task[$k]['MachineInfo']="iOS";
                    }elseif($MachineInfo[0] == "Android"){
                        $newbie_task[$k]['url']="jumpToProductList";
                        $newbie_task[$k]['MachineInfo']="Android";
                    }else{
                        $newbie_task[$k]['url']=url("index","deals");
                        $newbie_task[$k]['MachineInfo']="Wap";
                    }
                }
            }
            if($v['task_type'] == 16){
            	if($MachineInfo[0] == "iOS"){
            		$newbie_task[$k]['url']="jumpToProductList";
            		$newbie_task[$k]['MachineInfo']="iOS";
            	}elseif($MachineInfo[0] == "Android"){
            		$newbie_task[$k]['url']="jumpToProductList";
            		$newbie_task[$k]['MachineInfo']="Android";
            	}else{
            		$newbie_task[$k]['url']=url("index","deals");
            		$newbie_task[$k]['MachineInfo']="Wap";
            	}
                // 出借任意金额               
                $newbie_task[$k]['is_finished']=0;
                $newbie_task[$k]['grow_point']='A';
            }
            if($v['task_type'] == 17){
                // 当天注册并出借
                $load_register=$GLOBALS['db']->getOne('select count(id) from '.DB_PREFIX.'deal_load where user_id='.$user_id.' and create_date='.$GLOBALS['user_info']['create_date']);
                if(isset($load_register) && $load_register>0){
                    $newbie_task[$k]['is_finished']=1;
                    }else{
                        if($MachineInfo[0] == "iOS"){
                            $newbie_task[$k]['url']="jumpToProductList";
                            $newbie_task[$k]['MachineInfo']="iOS";
                        }elseif($MachineInfo[0] == "Android"){
                            $newbie_task[$k]['url']="jumpToProductList";
                            $newbie_task[$k]['MachineInfo']="Android";
                        }else{
                            $newbie_task[$k]['url']=url("index","deals");
                            $newbie_task[$k]['MachineInfo']="Wap";
                        }
                }
                // $newbie_task[$k]['url']=url("index","deals");
                $newbie_task[$k]['grow_point']='B';
            }
            if($v['task_type'] == 18){
                // 当月本金复投
                if($MachineInfo[0] == "iOS"){
                    $newbie_task[$k]['url']="jumpToProductList";
                    $newbie_task[$k]['MachineInfo']="iOS";
                }elseif($MachineInfo[0] == "Android"){
                    $newbie_task[$k]['url']="jumpToProductList";
                    $newbie_task[$k]['MachineInfo']="Android";
                }else{
                    $newbie_task[$k]['url']=url("index","deals");
                    $newbie_task[$k]['MachineInfo']="Wap";
                }
                $newbie_task[$k]['is_finished']=0;
                $newbie_task[$k]['grow_point']='C';
            }
            
        }
        return $newbie_task;
    }
}
?>