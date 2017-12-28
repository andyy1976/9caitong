<?php
//define(ACTION_NAME,"user");
//define(ACTN,"login_reg");
//define(MODULE_NAMEN,"index");
class dealloadrepayModule extends SiteBaseModule
{
	//红包余额
	public function updatedealloadrepaylkey(){
		$page = $_GET['page']?$_GET['page']:0; //取当前页数
		$nextpage = $page + 1; //下一页
		$num = 5;
		
		$deallist = $GLOBALS['db']->getAll("select id,repay_time from ".DB_PREFIX."deal order by id asc limit ".$page*$num.",".$num); //获取当前需要处理的标的
		foreach($deallist as $key=>$value){
			$repay_times = $value['repay_time'];//标的期数
			$deal_load_list = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."deal_load where deal_id= ".$value['id']." order by id asc"); //获取当前需要处理的投资记录
			if(empty($deal_load_list)){
				die;
			}
			//print_r($deal_load_list);
			foreach($deal_load_list as $loadkey=>$loadvalue){
				$deal_load_repay_list = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."deal_load_repay where deal_id= ".$value['id']." and load_id=".$loadvalue['id']." order by repay_date asc"); 
				
				//获取当前需要处理的还款记录
				if(empty($deal_load_repay_list)){
					die;
				}
				foreach($deal_load_repay_list as $repaykey=>$repayvalue){
					$data['l_key'] = $repaykey;
					//DB_PREFIX."deal_load_repay",$data,'UPDATE'," id= ".$repayvalue['id']."<br>";
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$data,'UPDATE'," id= ".$repayvalue['id']); //更新数据
				}
			}
		}
		
		echo "<script>window.location.href='http://jihe.9caitong.com/index.php?ctl=dealloadrepay&act=updatedealloadrepaylkey&page=".$nextpage."'</script>";
		
		
		
		
	}
	 
}
?>