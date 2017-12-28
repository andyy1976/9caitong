<?php
define(ACTION_NAME,"loan");
define(MODULE_NAMEN,"index");
class loanModule extends SiteBaseModule
{
    public function index()
    {
        $GLOBALS['tmpl']->assign("page_title","我要借款");
        $GLOBALS['tmpl']->assign("page_keyword","我要借款,");
        $GLOBALS['tmpl']->assign("page_description","我要借款,");
        $GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
        $GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
        $GLOBALS['tmpl']->display("page/loan.html");
    }
    public function borrow_register(){

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
        //避免手机重复注册
        $info = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."borrow money WHERE phone ='".$user_data['phone']."'");
        if($info  > 0){
            $data['info'] = "手机号码已被注册";
            ajax_return($data);
        }else if($user_data['phone']==''){
            $return['info'] = "手机号不能为空";
            ajax_return($return);
        }
        $user_data['create_time']=time();

        $borrow_money= $GLOBALS['db']->autoExecute(DB_PREFIX."borrow money",$user_data); //插入
        if($borrow_money) {
            $root['status'] = true;
            $root['info'] = "恭喜您，提交成功！";
        }else{
            $root['status']=false;
            $root['info']='提交失败！';
        }
        ajax_return($root);
    }


}
?>