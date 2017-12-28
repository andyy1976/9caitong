<?php
require_once APP_ROOT_PATH."system/utils/Depository/Require.php";
require_once APP_ROOT_PATH."system/libs/user.php";
class uc_autoinvestModule extends SiteBaseModule{
	
    public function index(){
        //判断是否是企业用户
        if($GLOBALS['user_info']['user_type']){
            $jump = url("index","uc_center#index");
            if(WAP==1) app_redirect(url("index","uc_center#index"));
            showErr("企业用户暂不可使用",0,$jump);
        }
        //判断是否登录
        $user = $GLOBALS['user_info'];
        if(empty($user['id'])) app_redirect(url("index","user#login"));
        //判断是否开通存管 GJQ
        if(!$GLOBALS['user_info']['cunguan_tag']){
            $jump = url("index","uc_depository_account#index");
            if(WAP==1) app_redirect(url("index","uc_depository_account#index"));
            showErr("请您先开通银行存管账户",0,$jump);
        }
        //判断是否设置存管交易密码
        if(!$GLOBALS['user_info']['cunguan_pwd']){
            $jump = url("index","uc_account#security");
            if(WAP==1) app_redirect(url("index","uc_account#security"));
            showErr("请您先设置存管系统交易密码",0,$jump);
        }
        //判断存管是否绑卡
        $cg_bank = $GLOBALS['db']->getRow("select ub.id,ub.user_id,ub.bank_id,ub.bankcard,ub.status,b.name,b.icon from " . DB_PREFIX . "user_bank as ub join " .DB_PREFIX."bank as b on ub.bank_id=b.bankid where ub.user_id=".$user['id']." and ub.status=1 and ub.cunguan_tag=1");
        if(empty($cg_bank)){
            $jump = url("index","uc_money#bank");
            if(WAP==1) app_redirect(url("index","uc_money#bank"));
            showErr("请您先绑定存管系统的银行卡",0,$jump);
        }
        
        //取当前账户的配置 
        $list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."auto_invest_config where user_id=".$user['id']." and is_delete=0");
        //格式化数据
        foreach ($list as $k=>$v){
            $list[$k]['del_str'] = $this->getDelStr($list[$k]['is_ordinary'], $list[$k]['is_advance'], $list[$k]['is_debts']);
            $list[$k]['deadline_str'] = $this->getDeadLineStr($list[$k]['deadline_start'], $list[$k]['deadline_end']);
            $list[$k]['dealtype_str'] = $this->getDealType($list[$k]['is_ordinary'], $list[$k]['is_advance'], $list[$k]['is_debts']);
            if(!$list[$k]['is_long']){
                $list[$k]['start_time_str'] = date("Y-m-d",$list[$k]['start_time']);
                $list[$k]['end_time_str'] = date("Y-m-d",$list[$k]['end_time']);
            }
            $list[$k]['money'] = strval(intval($list[$k]['money']));
        }
        //开启人数
        $count  = $GLOBALS['db']->getAll("select user_id from ".DB_PREFIX."auto_invest_config where status=1 group by user_id");
        $start_num = strval(count($count));
        //当前排名
        $time = $GLOBALS['db']->getOne("select update_time from ".DB_PREFIX."auto_invest_config where user_id=".$user['id']." and is_delete=0 and status=1 order by id asc limit 1");
        if($time){
//             $a = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."auto_invest_config where update_time<".$time." and is_delete=0 and status=1");
            $result = $GLOBALS['db']->getAll("select user_id,update_time from ".DB_PREFIX."auto_invest_config where status=1 group by user_id order by update_time asc");
            foreach ($result as $k=>$v){
                if($result[$k]['user_id'] == $user['id']){
                    $rank = $k;
                }
            }
            $rank = strval($rank + 1);
        }else{
            $rank = 0;
        }
        if(WAP == 1){
            $count = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."auto_invest_config where user_id=".$user['id']." and is_delete=0");
            $GLOBALS['tmpl']->assign("count",$count);
        }
        $GLOBALS['tmpl']->assign("list",$list);
        $GLOBALS['tmpl']->assign("start_num",$start_num);
        $GLOBALS['tmpl']->assign("rank",$rank);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_autoinvest_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
    }
    
    public function add(){
        $user = $GLOBALS['user_info'];
        if($user['id'] == null) {
            $result['status'] = 0;
            $result['info'] = "未登录";
            app_redirect(url("index","user#login"));
            ajax_return($result);
        }
        $id = intval($_REQUEST['id']);
        if($id){
            $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."auto_invest_config where id=".$id);
            if(!$data['is_long']){
                $data['start_time_str'] = date("Y-m-d",$data['start_time']);
                $data['end_time_str'] = date("Y-m-d",$data['end_time']);
                $data['wap_star_time'] = date("Y/m/d",$data['start_time']);
                $data['wap_end_time'] = date("Y/m/d",$data['end_time']);
            }
            $data['wap_deal_type_str'] = $this->getDealType($data['is_ordinary'], $data['is_advance'], $data['is_debts']);
            $data['money'] = intval($data['money']);
            $GLOBALS['tmpl']->assign("data",$data);
            $GLOBALS['tmpl']->assign("id",$id);
        }
        if(WAP == 1){
            $GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_autoinvest_add.html");
            $GLOBALS['tmpl']->display("page/uc.html");
        }else{
            $html = $GLOBALS['tmpl']->fetch("inc/uc/uc_autoinvest_add.html");
            ajax_return(array('page'=>$html,'status'=>1));
        }
        
    }
    
    public function addconfig(){
        $user = $GLOBALS['user_info'];
        if($user['id'] == null) {
            $result['status'] = 0;
            $result['info'] = "未登录";
            ajax_return($result);
        }
        $count = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."auto_invest_config where user_id=".$user['id']." and is_delete=0");
        $id = intval(strim($_POST['id']));
        if(!$id && $count >= 3){
            $result['status'] = 0;
            $result['info'] = '每人只能添加3条规则';
            ajax_return($result);
        }
        $update_time = $GLOBALS['db']->getOne("select update_time from ".DB_PREFIX."auto_invest_config where user_id=".$user['id']." and status=1 and is_delete=0 order by update_time asc limit 1");
        $data['cid'] = $id;
        $data['user_id'] = $user['id'];
        $data['deadline_start'] = intval(strim($_POST['deadline_start']));
        $data['deadline_end'] = intval(strim($_POST['deadline_end']));
        $data['money'] = intval(strim($_POST['money']));
        $data['set_money'] = intval(strim($_POST['money']));
        $data['is_new'] = intval(strim($_POST['is_new']));
        $data['is_debts'] = intval(strim($_POST['is_debts']));
        $data['is_advance'] = intval(strim($_POST['is_advance']));
        $data['is_ordinary'] = intval(strim($_POST['is_ordinary']));
        $data['is_long'] = intval(strim($_POST['is_long']));
        if($data['is_long'] != 1){
            if(WAP == 1){
                $data['start_time'] = strtotime($_POST['start_time']);
                $data['end_time'] = strtotime($_POST['end_time']." 23:59:59");
            }else{
                $data['start_time'] = strtotime(substr($_POST['valid_date'],0,10));
                $data['end_time'] = strtotime(substr($_POST['valid_date'],14,10)." 23:59:59");
            }
        }
        $data['is_part_load'] = intval(strim($_POST['is_part_load']));
        $data['create_time'] = time();
        $data['update_time'] = $update_time ? $update_time : time();
        $data['status'] = 1;
        if($data['deadline_start'] == 0 || $data['deadline_end'] == 0){
            $data['deadline_start'] = 0;
            $data['deadline_end'] = 0;
        }
        if(empty($data['money'])){
            $result['status'] = 0;
            $result['info'] = "请填写金额";
            ajax_return($result);
        }
        $cunguan_money = $GLOBALS['db']->getOne("select AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as money from ".DB_PREFIX."user where id=".$user['id']);
        if($data['money'] > $cunguan_money){
            $result['status'] = 0;
            $result['info'] = "余额不足";
            ajax_return($result);
        }
        if($data['money'] < 100){
            $result['status'] = 0;
            $result['info'] = "最低100元起投";
            ajax_return($result);
        }
        if($data['deadline_start'] > $data['deadline_end']){
            $result['status'] = 0;
            $result['info'] = "投标期限错误";
            ajax_return($result);
        }
        if(!$data['is_debts'] && !$data['is_advance'] && !$data['is_ordinary']){
            $result['status'] = 0;
            $result['info'] = "请选择投标类型";
            ajax_return($result);
        }
        if($data['is_long'] != 1){
            if(empty($data['start_time']) || empty($data['end_time'])){
                $result['status'] = 0;
                $result['info'] = "请填写有效期";
                ajax_return($result);
            }
            if($data['start_time'] > $data['end_time']){
                $result['status'] = 0;
                $result['info'] = "结束日期必须大于开始日期";
                ajax_return($result);
            }
            if($data['end_time'] < time()){
                $result['status'] = 0;
                $result['info'] = "结束日期已经过期";
                ajax_return($result);
            }
            if(!$id){
                if($data['start_time'] < strtotime(date("Y-m-d",time()))){
                    $result['status'] = 0;
                    $result['info'] = "开始日期最小为当天";
                    ajax_return($result);
                }
            }
            
        }
         
        $rs = $GLOBALS['db']->autoExecute(DB_PREFIX."auto_invest_config_log",$data,"INSERT");
        $insert_id = intval($GLOBALS['db']->insert_id());
        
        if($rs){
            $result['status'] = 1;
            $result['jump'] = "https://" . $_SERVER['HTTP_HOST'] . "/index.php?ctl=uc_autoinvest&act=check_pwd&id=".$insert_id;
            ajax_return($result);
        }else{
            $result['status'] = 0;
            $result['info'] = "系统繁忙请稍后再试";
            ajax_return($result);
        }
        
    }
    
    //开关
    public function autoinvest_switch(){
        $user = $GLOBALS['user_info'];
        if($user['id'] == null) {
            $result['status'] = 0;
            $result['info'] = "未登录";
            ajax_return($result);
        }
        $id = intval($_POST['id']);
        $status = intval($_POST['isopen']);
        if(empty($id)) {
            $result['status'] = 0;
            $result['info'] = "参数错误";
            ajax_return($result);
        }
        if($status == 1){
            $data = $GLOBALS['db']->getRow("select money,end_time,is_long from ".DB_PREFIX."auto_invest_config where id=".$id);
            if(empty($data['is_long']) && $data['end_time'] < time()){
                    $result['status'] = 0;
                    $result['info'] = "有效期已过期，请重新设置";
                    ajax_return($result);
            }
            $cunguan_money = $GLOBALS['db']->getOne("select AES_DECRYPT(cunguan_money_encrypt,'".AES_DECRYPT_KEY."') as money from ".DB_PREFIX."user where id=".$user['id']);
            if($data['money'] > $cunguan_money){
                $result['status'] = 0;
                $result['info'] = "余额不足";
                output($result);
            }
            $result['status'] = 1;
            $result['jump'] = "https://" . $_SERVER['HTTP_HOST'] . "/index.php?ctl=uc_autoinvest&act=switch_check_pwd&id=".$id;
            ajax_return($result);
        }else{
            $old_money = $GLOBALS['db']->getOne("select money from ".DB_PREFIX."auto_invest_config where id=".$id." and status=1");
            if(empty($old_money)) {
                $result['status'] = 0;
                $result['info'] = "请求频繁，请稍后再试";
                ajax_return($result);
            }
            //将之前设置金额解冻
            modify_account(array('cunguan_money'=>$old_money,'cunguan_lock_money'=>-$old_money),$user['id'],"自动投标返还",48,"自动投标返还",1);
            $data['status'] = 0;
//             $data['update_time'] = time();
            $rs = $GLOBALS['db']->autoExecute(DB_PREFIX."auto_invest_config",$data,"UPDATE","id=".$id." and user_id=".$user['id']);
        }
        if($rs){
            $result['id'] = $id;
            $result['status'] = 2;
            $result['info'] = '关闭成功';
            ajax_return($result);
        }else{
            $result['status'] = 0;
            $result['info'] = '系统繁忙，请稍后再试';
            ajax_return($result);
        }
        
    }
    
    //删除
    public function autoinvest_delete(){
        $user = $GLOBALS['user_info'];
        if($user['id'] == null) {
            $result['status'] = 0;
            $result['info'] = "未登录";
            ajax_return($result);
        }
        $id = intval($_POST['id']);
        if(empty($id)) {
            $result['status'] = 0;
            $result['info'] = "参数错误";
            ajax_return($result);
        }
        $data = $GLOBALS['db']->getRow("select money,status from ".DB_PREFIX."auto_invest_config where id=".$id." and user_id=".$user['id']);
        if($data['status'] == 1){
            //将之前设置金额解冻
            modify_account(array('cunguan_money'=>$data['money'],'cunguan_lock_money'=>-$data['money']),$user['id'],"自动投标返还",48,"自动投标返还",1);
        }
        $time = time();
        $rs = $GLOBALS['db']->query("update ".DB_PREFIX."auto_invest_config set is_delete=1,status=0,update_time=".$time." where id = ".$id." and user_id = ".$user['id']);
        if($rs){
            $result['status'] = 1;
            $result['info'] = "删除成功!";
            ajax_return($result);
        }else{
            $result['status'] = 0;
            $result['info'] = "删除失败!";
            ajax_return($result);
        }
        
    }
    
    //校验交易密码
    public function check_pwd(){
        $userid = $GLOBALS['user_info']['id'];
        $id = intval($_GET['id']);
        if($userid < 0) app_redirect(url("index","user#login"));
        if(empty($id) || $id < 0) showErr("参数错误");
        
        $Publics = new Publics();
        $SeqNo = $Publics->seqno();
        //记录流水号
        $rs = $GLOBALS['db']->query("UPDATE ".DB_PREFIX."auto_invest_config_log SET seqno="."'$SeqNo'"." where id=".$id);
        if($rs){
            $html = $Publics->verify_trans_password('uc_autoinvest',"confirm_autoinvest",$userid,'4',$SeqNo,"_self");
            echo $html;
        } else{
            showErr("系统繁忙请稍后再试");
        }
    }
    
    //校验密码成功回调
    public function confirm_autoinvest(){
        $result = $_REQUEST;
        $user = $GLOBALS['user_info'];
        $seqno = $result['businessSeqNo'];
        if($result['flag'] == 1){
            
            //更改校验密码状态
            $rs = $GLOBALS['db']->query("UPDATE ".DB_PREFIX."auto_invest_config_log SET is_paypwd=1 where seqno="."'$seqno'"." and user_id=".$result['userId']);
            $rs = $GLOBALS['db']->affected_rows();
            $MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
            if($rs){
                //判断是否开通委托协议
                if($user['is_entrust'] != 1){
                    //开通委托协议
                    $reg = new Register();
                    $res = $reg->delegate($seqno,$user['id'],'B04','T01',$seqno);
//                     $res = $reg->delegate($seqno,$user['id'],'B05','T01',"JCT2017922702005524980");die;
                    if($res['respHeader']['respCode'] == "P2P0000"){
                        //更改user表委托协议状态
                        $result = $GLOBALS['db']->query("UPDATE ".DB_PREFIX."user SET is_entrust=1,entrustNo="."'$seqno'"." where id=".$result['userId']);
                        
                    }else{
                        $jump = url("index","uc_autoinvest#index");
                        showErr("系统繁忙，请稍后再试",0,$jump);die;
                    }
                }
                //将配置插入表中
                $field = "seqno,user_id,cid,money,set_money,deadline_start,deadline_end,is_new,is_debts,is_advance,is_ordinary,is_long,start_time,end_time,create_time,update_time,status,is_delete,is_part_load";
                $data = $GLOBALS['db']->getRow("select ".$field." from ".DB_PREFIX."auto_invest_config_log where seqno="."'$seqno'");
                if($data['cid']){//判断是否是修改
                    $old_data = $GLOBALS['db']->getRow("select money,status from ".DB_PREFIX."auto_invest_config where id=".$data['cid']);
                    //将之前设置金额解冻
                    if($old_data['status'] == 1){
                        modify_account(array('cunguan_money'=>$old_data['money'],'cunguan_lock_money'=>-$old_data['money']),$data['user_id'],"自动投标返还",48,"修改自动投标返还",1);
                    }
                    unset($data['create_time']);
                    $rs1 = $GLOBALS['db']->autoExecute(DB_PREFIX."auto_invest_config",$data,"UPDATE","id=".$data['cid']." and user_id=".$user['id']);
                }else{
                    $insertId = $GLOBALS['db']->autoExecute(DB_PREFIX."auto_invest_config",$data,"INSERT");
                    $insertId = $GLOBALS['db']->insert_id();
                }
                
                //冻结用户存管资金
                modify_account(array('cunguan_money'=>-$data['money'],'cunguan_lock_money'=>$data['money']),$data['user_id'],"自动投标冻结",48,"自动投标冻结",1);
                
                if($MachineInfo[0] == 'iOS'){
                    echo "<script>window.webkit.messageHandlers.kInputFromiOS.postMessage({'idf':'jumpToAutoInvest'});</script>";die;
                }elseif ($MachineInfo[0] == 'Android'){
                    echo "<script>window.jiucaitong.FromH5ToJump('jumpToAutoInvest','');</script>";die;
                }else{
                    app_redirect(url("index","uc_autoinvest#index"));
                }
                
            }else{
                $jump = url("index","uc_autoinvest#index");
                showErr("系统繁忙，请稍后再试",0,$jump);
            }
        }
    }
    
    
    //开关校验密码 分开处理 以防后期业务扩展
    public function switch_check_pwd(){
        
        $userid = $GLOBALS['user_info']['id'];
        $id = intval($_GET['id']);
        if($userid < 0) app_redirect(url("index","user#login"));
        if(empty($id) || $id < 0) showErr("参数错误");
        
        $Publics = new Publics();
        $SeqNo = $Publics->seqno();
        $html = $Publics->verify_trans_password('uc_autoinvest',"switch_confirm_autoinvest&id=".$id,$userid,4,$SeqNo,"_self");
        echo $html;
        
    }
    
    public function switch_confirm_autoinvest(){
        $result = $_REQUEST;
        $id = intval($_GET['id']);
        $user = $GLOBALS['user_info'];
        $seqno = $result['businessSeqNo'];
        if($result['flag'] == 1){
            $update_time = $GLOBALS['db']->getOne("select update_time from ".DB_PREFIX."auto_invest_config where user_id=".$user['id']." and status=1 and is_delete=0 order by update_time asc limit 1");
            $time = $update_time ? $update_time : time();
            $MachineInfo = explode("|||",es_session::get('MachineInfo')); //设备信息
            $old_money = $GLOBALS['db']->getOne("select money from ".DB_PREFIX."auto_invest_config where id=".$id);
            //冻结用户存管资金
            modify_account(array('cunguan_money'=>-$old_money,'cunguan_lock_money'=>$old_money),$user['id'],"自动投标冻结",48,"开启自动投标",1);
            $rs = $GLOBALS['db']->query("update ".DB_PREFIX."auto_invest_config set status=1,update_time=".$time." where id = ".$id." and user_id = ".$user['id']);
            
            if($MachineInfo[0] == 'iOS'){
                echo "<script>window.webkit.messageHandlers.kInputFromiOS.postMessage({'idf':'jumpToAutoInvest'});</script>";die;
            }elseif ($MachineInfo[0] == 'Android'){
                echo "<script>window.jiucaitong.FromH5ToJump('jumpToAutoInvest','');</script>";die;
            }else{
                app_redirect(url('member','uc_autoinvest#index'));
            }
        }else{
            $jump = url("index","uc_autoinvest#index");
            showErr("系统繁忙，请稍后再试",0,$jump);
        }
    }
    
    /**
     * 规则
     */
    public function rule(){
        $GLOBALS['tmpl']->assign("cate_title","自动投标规则");
        $GLOBALS['tmpl']->display("inc/uc/uc_autoinvest_rule.html");
    }
    /**
     * 自动投标协议
     */
    public function protocol(){
        $GLOBALS['tmpl']->assign("cate_title","自动投标授权协议");
        $GLOBALS['tmpl']->display("inc/uc/uc_autoinvest_protocol.html");
    }
    
    /*
     * $is_ordinary 是否普通标
     * $is_advance 是否预售标
     * $is_debts 是否转让标
     */
    public function getDelStr($is_ordinary,$is_advance,$is_debts){
    
        if(($is_ordinary && $is_advance && $is_advance) || ($is_ordinary && $is_advance) || ($is_ordinary && $is_debts)){
            $str = "普通标等";
        }elseif ($is_advance && $is_debts){
            $str = "预售标等";
        }elseif ($is_ordinary){
            $str = "普通标";
        }elseif ($is_advance){
            $str = "预售标";
        }elseif($is_debts){
            $str = "转让标";
        }
        return $str;
    }
    
    public function getDeadLineStr($deadline_start,$deadline_end){
    
        if($deadline_start == 0 || $deadline_end == 0){
            $str = "不限";
        }elseif($deadline_start == $deadline_end){
            $str = $deadline_start."个月";
        }else{
            $str = $deadline_start.'-'.$deadline_end.'个月';
        }
        return $str;
    }
    
    public function getDealType($is_ordinary,$is_advance,$is_debts){
        $str = '';
        if($is_ordinary){
            $str .= '普通标、';
        }
        if($is_advance){
            $str .= '预售标、';
        }
        if($is_debts){
            $str .= '转让标、';
        }
        $str = rtrim($str, '、');
        return $str;
    }
}
?>