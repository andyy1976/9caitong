<?php
class taste_cash
{
    /**
     * 个人中心我的体验金---2016-8-16
     */
    public function index(){
        $root = get_baseroot();
        $user =  $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();
        $user_id  = intval($user['id']);

        $user_data = $this->m_user;
        $my_taste_info=$this->getTasteInfo($user_data['user_id']);
        $usable_taste=$this->getUsableTaste($user_data['user_id']);

        $taste_uearn=$this->getTasteUEarn($user_data['user_id']);
        $taste_earning=$this->getTasteEarning($user_data['user_id']);
        $taste_list=$this->getTasteList($user_data['user_id']);
        $data["total"]=$my_taste_info["taste"];
        $data["avaliable_money"]=$usable_taste["taste"];
        $data["will_interest"]=$taste_uearn['taste2'];
        $data["getted_interest"]=$taste_earning["tasteearn"]+$taste_uearn['taste1'];

        output($root);
    } 
    /*
     * 领取体验金
     * */
    public function setTasteCash(){
        $arr = base64_decode($_REQUEST);
//        $cashList=CALL_API(SERVICE_CASH_POST_CASHLIST,make_sign($params),'post');
//        $arr = $_POST;
        // $arr['trigger_position'] = '1';
        // $arr['device'] = 'ios';
        $user_data = $this->m_user;
        $arr['user_id']=$this->m_uid;

        $data=$this->getTaste($arr);

        //var_dump($data);exit;
        if($data){
            $qq= CALL_API(SERVICE_SEND_FIRST_LOGIN, array('user_id'=>$this->m_uid));
            $this->successReturn($data);
        }else{
            $this->errorReturn();
        }
    }
    /*
     * 领取体验金
     * */
    public function setTasteCashWap(){
        $arr = base64_decode($_REQUEST);
        $data=$this->getTaste($arr);
        $callback = isset($_GET['callback']) ? trim($_GET['callback']) : ''; //jsonp回调参数，必需

        $tmp= json_encode($data); //json 数据
        echo $callback . '(' . $tmp .')';  //返回格式，必需
    }
    /*
    * 体验金立即使用
    *
    * */
    public function toUseTaste(){
        $arr = base64_decode($_REQUEST);
        // $arr['taste_id'] = '63';
        // $arr['device'] = 'ios';
        $user_info = $this->m_user;
        $arr['user_id']=$this->m_uid;
        $data=$this->toUseTasteCash($arr);
        if($data){
            $this->successReturn($data);
        }else{
            $this->errorReturn();
        }
    }
    /*
     * 体验金领取收益
     *
     * */
    public function useTasteCashDetail(){
        $arr = base64_decode($_REQUEST);
        //$arr['taste_id'] = '63';
        //$arr['device'] = 'ios';
        $arr['user_id']=$this->m_uid;
        $data=$this->useTaste($arr);
        if($data){
            $this->successReturn($data);
        }else{
            $this->errorReturn();
        }
    }
    /*
     * 首次出借
     *
     * */
    public function firstTrade(){

    }

    /*
     * 体验金使用明细
     *
     * */
    public function showTasteCashList(){
        // $arr = $_REQUEST;
        $user_info = $this->m_user;
        $data=$this->tasteDetail($user_info['user_id']);
        foreach($data as $key=>$val){
            $rec[$key]['time']=$val['create_time'];
            $rec[$key]['amount']=$val['amount'];
            $rec[$key]['title']=$val['detail'];
            $rec[$key]['date']=$val['timeString'];
            $rec[$key]['img_url']=$val['img_url'];
        }

        if($rec){
            $this->successReturn($rec);
        }else{
            $this->errorReturn();
        }
    }


    /**
     * 获取该账户的体验金总额
     * $user_id---当前用户id
     */
    public function getTasteInfo($user_id){

        $my_taste_info = CALL_API(SERVICE_GET_MY_TASTE,array('user_id'=>$user_id));
        return $my_taste_info['data'];
    }

    /**
     *  获取该账户的可用体验金
     * $user_id---当前用户id
     */
    public function getUsableTaste($user_id){
        $usable_taste = CALL_API(SERVICE_GET_MY_USABLE_TASTE, array('user_id'=>$user_id));
        return $usable_taste['data'];

    }

    /**
     * 体验金已收收益---
     * $user_id---当前用户id
     */
    public function getTasteEarning($user_id){
        $taste_earning= CALL_API(SERVICE_GET_MY_TASTE_EARNING,array('user_id'=>$user_id));

        return $taste_earning['data'];
    }

    /**
     * 体验金待收收益---
     * $user_id---当前用户id
     */
    public function getTasteUEarn($user_id){
        $taste_uearn= CALL_API(SERVICE_GET_MY_TASTE_UEARN,array('user_id'=>$user_id));
        //var_dump($taste_uearn);
        return $taste_uearn['data'];
    }

    /**
     * 体验金列表--
     * $user_id---当前用户id
     */
    public function getTasteList($user_id){
        $taste_list= CALL_API(SERVICE_GET_MY_TASTE_LIST,array('user_id'=>$user_id));
        return $taste_list['data'];
    }

    /*
     * 领取体验金
     * */
    public function getTaste($arr){
        $tasteNum=CALL_API(SERVICE_GET_TASTE_NUM,$arr,'post');
        return $tasteNum['data'];
    }
    /*
    * 使用体验金
     *
     */
    public function toUseTasteCash($arr){
        $tasteNum=CALL_API(SERVICE_GET_TO_USE_TASTE,$arr,'post');

        return $tasteNum['data'];
    }

    /*
     * 体验金领取收益
     * */
    public function useTaste($arr){
        $tasteNum=CALL_API(SERVICE_GET_USE_TASTE,$arr,'post');

        return $tasteNum['data'];
    }
    /*
    * 体验金明细
    * */
    public function tasteDetail($user_id){
        $tasteDetail=CALL_API(SERVICE_GET_USE_TASTE_DETAIL,array('user_id'=>$user_id));

        return $tasteDetail['data'];
    }
}
?>