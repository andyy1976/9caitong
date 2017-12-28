<?php
class agreementModule extends SiteBaseModule{

    public function service() {    	
        
        $pid = intval($_REQUEST['id']);
        $data = $GLOBALS['db']->getRow("select debts,type_id,contract_id from ".DB_PREFIX."deal where id=".$pid);
//         $contract_id = $is_debts ? 13 : 11 ;
//         $title = $is_debts ? "债权转让及受让协议" : "出借协议";
        if($data['contract_id'] == 16){
            $contract_id = 16;
            $title = "餐饮贷出借协议";
        }elseif($data['is_debts'] == 1){
            $contract_id = 13;
            $title = "债权转让及受让协议";
        }elseif($data['type_id'] == 14){//上线后更改
            $contract_id = 14;
            $title = "房贷出借协议";
        }else{
            $contract_id = 11;
            $title = "出借协议";
        }
        $contract = $GLOBALS['tmpl']->fetch("str:".get_contract($contract_id));
        require APP_ROOT_PATH.'app/Lib/contract.php';
        $pdf = new contract();
        $file_name = $title.".pdf";
        $pdf->contractOutputByHtml($contract,$file_name,'I',$title);
        
    }
    public function warning() {     
        $GLOBALS['tmpl']->assign("cate_title","风险提示书");
        $GLOBALS['tmpl']->display("page/agreement_warning.html");
    }
    public function payment() {     
        $GLOBALS['tmpl']->assign("cate_title","移动支付协议");
        $GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_agreement_payment.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }
    public function questionnaire(){
        $user = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."wenjuan_user_answer_record where user_id=".$GLOBALS['user_info']['id']);
        if(!$user){
            $user_id = $GLOBALS['user_info']['id'];
            $time=time();
            $data['answer']="";
            $data['score']=30;
            $data['test_time'] = $time;
            $data['user_id']=$user_id;
            $data['status']='2';
            $request=$GLOBALS['db']->getOne("select * from ".DB_PREFIX."wenjuan_user_answer_record where user_id =".$user_id);
            if ($request) {
                $res = $GLOBALS['db']->autoExecute(DB_PREFIX."wenjuan_user_answer_record",$data,"UPDATE","user_id=".$user_id);
            }else{
                $res = $GLOBALS['db']->autoExecute(DB_PREFIX."wenjuan_user_answer_record",$data);
            }
        }
        $user_status = $GLOBALS['db']->getOne("select status from ".DB_PREFIX."wenjuan_user_answer_record where user_id=".$GLOBALS['user_info']['id']);
        $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."wenjuan_user_answer_record where (status = 1 or status = 2) and user_id=".$GLOBALS['user_info']['id']);        
        $GLOBALS['tmpl']->assign("user_info",$user_info);
        $GLOBALS['tmpl']->assign("user_status",$user_status);
        $GLOBALS['tmpl']->assign("cate_title","风险评估");
        $GLOBALS['tmpl']->display("page/questionnaire_index.html");
    }
    public function riskassessment(){
        $user_id = $GLOBALS['user_info']['id'];
        $time=time();
        if ($user_id) {
            if ($_POST) {
                $data['user_id'] = $user_id;
                $score1= $_POST[score1];
                $score2=$_POST[score2];
                $score3=$_POST[score3];
                $score4=$_POST[score4];
                $score5=$_POST[score5];
                $score6=$_POST[score6];
                $score7=$_POST[score7];
                $score8=$_POST[score8];
                $score9=$_POST[score9];
                $score10=$_POST[score10];
                $score11=$_POST[score11];
                $score12=$_POST[score12];
                $data['answer']=$score1.",".$score2.",".$score3.",".$score4.",".$score5.",".$score6.",".$score7.",".$score8.",".$score9.",".$score10.",".$score11.",".$score12;
                $data['score'] = $score1+$score2+$score3+$score4+$score5+$score6+$score7+$score8+$score9+$score10+$score11+$score12;
                $data['test_time'] = $time;
                $data['status']='1';
                $request=$GLOBALS['db']->getOne("select * from ".DB_PREFIX."wenjuan_user_answer_record where user_id =".$user_id);
                if ($request) {
                    $res = $GLOBALS['db']->autoExecute(DB_PREFIX."wenjuan_user_answer_record",$data,"UPDATE","user_id=".$user_id);
                }else{
                    $res = $GLOBALS['db']->autoExecute(DB_PREFIX."wenjuan_user_answer_record",$data);
                }
                if($res){
                    // 风险评估奖励成长值
                    $is_get=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_grow_point where user_id=".$user_id." and task_type=5");
                    if(!$is_get){
                        require_once APP_ROOT_PATH."system/user_level/Level.php";
                        $level=new Level();
                        $level->get_grow_point(5);
                    }
                    if(12 <= $data['score'] && $data['score'] <=24){
                        $json['status'] = 1;
                        $json['info1'] = "保守型";
                        $json['info2'] = "此类出借人风险偏好较低，愿意用较小的风险来获得确定的收益。此类出借人愿意承受或能承受少许本金的损失和波动。";
                        echo json_encode($json);
                    }else if(25 <= $data['score'] && $data['score'] <=36){
                       $json['status'] = 2;
                       $json['info1'] = "稳健型";
                       $json['info2'] = "此类出借人愿意承担一定程度的风险，强调出借风险和资产升值之间的平衡，主要出借目标是资产的升值，为实现目标往往愿意承担相当程度的风险，比较适合组合出借";
                       echo json_encode($json);
                    }else if(37 <= $data['score'] && $data['score'] <=60){
                       $json['status'] = 3;
                       $json['info1'] = "积极型";
                       $json['info2'] = "此类出借人为了获得高回报的出借收益，能够承受出借产品价格的显著波动，主要出借目标是实现资产升值，为实现目标往往愿意承担相当程度的风险，此类出借人可以承受一定的资产波动风险和本金亏损风险。";
                       echo json_encode($json);              
                    }
                }
            }
       }
    }
    //重新评估
    public function updateriskassessment(){
        $user_id = $GLOBALS['user_info']['id'];
        $data['status']='0';
        $res = $GLOBALS['db']->autoExecute(DB_PREFIX."wenjuan_user_answer_record",$data,"UPDATE","user_id=".$user_id);
        if($res){
          $json['status1'] = 1;
          echo json_encode($json);
        }else{
          $json['status1'] = 0;
          echo json_encode($json);
        }
    }
    //跳过评估
    public function skipriskassessment(){
        $user_id = $GLOBALS['user_info']['id'];
        $time=time();
        $data['answer']="";
        $data['score']=30;
        $data['test_time'] = $time;
        $data['user_id']=$user_id;
        $data['status']='1';
        $request=$GLOBALS['db']->getOne("select * from ".DB_PREFIX."wenjuan_user_answer_record where user_id =".$user_id);
        if ($request) {
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX."wenjuan_user_answer_record",$data,"UPDATE","user_id=".$user_id);
        }else{
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX."wenjuan_user_answer_record",$data);
        }
        if($res){
            $json['status'] = 1;
            $json['msg'] = "稳健型";
            $json['info'] = "此类出借人愿意承担一定程度的风险，强调出借风险和资产升值之间的平衡，主要出借目标是资产的升值，为实现目标往往愿意承担相当程度的风险，比较适合组合出借";
         }else{
             $json['status'] = 0;
             $json['msg'] = "评估失败";
         }
         echo json_encode($json);
    }
    public function app_download(){
        $GLOBALS['tmpl']->display("app_download.html");
    }
    public function wap_download(){
		//添加渠道来源
		$source_id = es_session::get("source_id");
		$device    = es_session::get("device");
		if(!empty($source_id)){
			$source_id = $source_id."_";
		}
		$GLOBALS['tmpl']->assign('source_id',$source_id);
        $GLOBALS['tmpl']->display("wap_download.html");
    }
    public function xiaojiuketang(){
        $GLOBALS['tmpl']->display("xiaojiuketang.html");
    }
}
?>