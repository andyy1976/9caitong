<?php

//抢红包邀请好友接口

class red_packet_invite{

    public function index(){
        $user = $GLOBALS['user_info'];
        $root = get_baseroot();
        $root['session_id'] = es_session::id();
        $phone_list = json_decode(base64_decode($GLOBALS['request']['phone_list']));
        if(empty($phone_list)){
            $root['response_code'] = 0;
            $root['msg_err'] = '参数错误';
            output($root);
        }
        if(empty($user['id'])){
            $root['response_code'] = 0;
            $root['user_login_status'] = 0;
            $root['show_err'] = '未登录';
            output($root);
        }
        //查询已注册的手机号 已注册的又分为 已添加好友的 和 未添加的
        foreach ($phone_list as $k=>$v){
            $user_name[$k] = 'w'.$v;
            $uname .= "'".$user_name[$k]."',";
        }
        $uname = substr($uname,0,strlen($uname)-1);
        $condition = "user_name in(".$uname.")";//根据user表联合索引unk_user_name查询user_name
        $rs = $GLOBALS['db']->getAll("select id as user_id,mobile from ".DB_PREFIX."user where ".$condition);
        
        $exist_phone = array_column($rs,'mobile');
        $exist_uid = array_column($rs,'user_id');
        foreach ($rs as $k=>$v){//将数据格式化方便后面区分 已添加和未添加
            $temp[$rs[$k]['user_id']] = $rs[$k]['mobile'];
        }
        
        //查询已添加的好友 --新建user_friend 联合索引
        foreach ($exist_uid as $k=>$v){//生成in查询条件
            $friend_ids .= "'".$exist_uid[$k]."',";
        }
        $friend_ids = substr($friend_ids,0,strlen($friend_ids)-1);
        $condition = "user_id=".$user['id']." and friend_id in(".$friend_ids.") and status=0";
        $my_friends = $GLOBALS['db']->getAll("select friend_id from ".DB_PREFIX."red_packet_friends where ".$condition);
        foreach ($my_friends as $k=>$v){
            $added[] = $temp[$my_friends[$k]['friend_id']];
            unset($temp[$my_friends[$k]['friend_id']]);//删除已添加的手机号
        }
        //格式化可以添加的数据
        foreach ($temp as $k=>$v){
            $add[] = $k.','.$v;
        }
        $root['added'] = $added;
        $root['add'] = $add;
        //未注册的手机号            可邀请
        foreach ($phone_list as $k=>$v){
            if(!in_array($phone_list[$k],$exist_phone)){
                $noexist_phone[] = $phone_list[$k];
            }
        }
        $root['response_code'] = 1;
        $root['invite'] = $noexist_phone;
        $url = WAP_SITE_DOMAIN . "/index.php?ctl=find&act=W645&code=" . $user['mobile'];
        $url = $this->getSinaShortUrl("2836808474",$url);
        $url = $url[0]['url_short'];
        $root['friend_msg'] = $GLOBALS['db']->getOne("select mobile_msg from ".DB_PREFIX."red_packet_config order by id desc limit 1") ."点击查看：". $url;
        output($root);   
    }
    
    
    /**
     * 调用新浪接口将长链接转为短链接
     * @param  string        $source    申请应用的AppKey
     * @param  array|string  $url_long  长链接，支持多个转换（需要先执行urlencode)
     * @return array
     */
    function getSinaShortUrl($source, $url_long){
        
        // 参数检查
        if(empty($source) || !$url_long){
            return false;
        }
    
        // 参数处理，字符串转为数组
        if(!is_array($url_long)){
            $url_long = array($url_long);
        }
    
        // 拼接url_long参数请求格式
        $url_param = array_map(function($value){
            return '&url_long='.urlencode($value);
        }, $url_long);
    
        $url_param = implode('', $url_param);

        // 新浪生成短链接接口
        $api = 'http://api.t.sina.com.cn/short_url/shorten.json';

        // 请求url
        $request_url = sprintf($api.'?source=%s%s', $source, $url_param);

        $result = array();

        // 执行请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $request_url);
        $data = curl_exec($ch);
        if($error=curl_errno($ch)){
            return false;
        }
        curl_close($ch);

        $result = json_decode($data, true);

        return $result;
    
    }
    
}