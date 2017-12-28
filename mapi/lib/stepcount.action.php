<?php
class stepcount{
//app计步统计
	public function index(){
		$MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
		if($MachineInfo[0]!=='iOS' && $MachineInfo[0]!=='Android'){//判断是否移动端
			$res['status']=0;
			output($res);
		}
		$user = $GLOBALS['user_info'];
		if(!$user){//是否登陆
			$res['status']=2;
			output($res);
		}
		//上传的步数
		$today_count = intval(base64_decode($_REQUEST['today']));
		if(!$today_count){//当日步数是否为空或0
			$res['status']=5;
			output($res);
		}
		//当日时间戳
		$today = strtotime(date("Y-m-d",time()));
		//获取最后一次记录
		/*$last_step = $GLOBALS['db']->getRow("select update_time,step_count from ".DB_PREFIX."step_counter where user_id=".$user['id']."  order by update_time desc limit 1");
		 if($last_step){
			//将时间转换成日期
			$last_date = strtotime(date("Y-m-d",$last_step['update_time']));
			$i = ($today-$last_date)/86400;//相差天数
			if($i >1){//如果最后一次更新时间减去当日时间大于1 
				$init = 1; //初始化数据
				while($init<$i){//循环补齐数据
					$datas['user_id'] =$user['id'];
					$datas['step_count']=$last_step['step_count'];
					$datas['update_time']=$last_date+86400*$init;
					$datas['upload_time']=TIME_UTC;
					$init++;
					$GLOBALS['db']->autoExecute(DB_PREFIX."step_counter",$datas,"INSERT");
				}
				
			}
		} */
		
		//获取当日上传记录
		$today_step = $GLOBALS['db']->getRow("select step_count,id from ".DB_PREFIX."step_counter where user_id=".$user['id']." and update_time>=".$today);
		if(!$today_step){
			$data['step_count'] = $today_count;
			$data['upload_time'] =TIME_UTC;
			$data['update_time'] =TIME_UTC;
			$data['user_id'] = $user['id'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."step_counter",$data,"INSERT");
		}else{
			$data['step_count'] = $today_count;
			$data['update_time'] =TIME_UTC;
			$GLOBALS['db']->autoExecute(DB_PREFIX."step_counter",$data,"UPDATE","id=".$today_step['id']);
		}
		$res['status']=1;
		output($res);
		
	}




}