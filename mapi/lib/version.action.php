<?php
class version
{
    public function index()
    {

        $site_url = str_replace("/mapi", "", WAP_SITE_DOMAIN.APP_ROOT)."/";//站点域名;

        //客服端手机类型dev_type=android
        $dev_type = base64_decode($GLOBALS['request']['dev_type']);
        $version = base64_decode($GLOBALS['request']['version']);//
        $uid = $GLOBALS['user_info']['id'];
        //更新判断用户登录标识

        $root = get_baseroot();
        if($uid){
            $root['login_status'] = 1;
        }else{
            $root['login_status'] = 0;
        }
        if ($dev_type == 'android'){
            $root['title'] = $GLOBALS['version']['program_title'];
            $root['serverVersion'] = $GLOBALS['version']['android_version'];//android版本号
            $root['serverName'] = $GLOBALS['version']['android_serverName'];//android版本名称
            if ($version < $root['serverVersion']){
                $root['filename'] = $GLOBALS['version']['android_filename'];//android下载包名
                $root['android_upgrade'] = $GLOBALS['version']['android_upgrade'];//android版本升级内容
                if($GLOBALS['version']['android_filename'])
                {
                    $root['hasfile'] = 1;
                    $root['filesize'] = filesize($GLOBALS['version']['android_filename']);
                    $root['has_upgrade'] = 1;//1:可升级;0:不可升级
                    $root['forced_upgrade'] = intval($GLOBALS['version']['android_forced_upgrade']);//0:非强制升级;1:强制升级
                }
                else
                {
                    $root['hasfile'] = 0;
                    $root['filesize'] = 0;
                    $root['has_upgrade'] = 0;//1:可升级;0:不可升级
                }

            }else{
                $root['hasfile'] = 0;
                $root['has_upgrade'] = 0;//1:可升级;0:不可升级
            }
            $root['response_code'] = 1;
        }else if ($dev_type == 'ios'){
            $root['title'] = $GLOBALS['version']['program_title'];
            $root['serverVersion'] = $GLOBALS['version']['ios_version'];//ios版本号

            if ($version < $root['serverVersion']){
                $root['ios_down_url'] = $GLOBALS['version']['ios_down_url'];//ios下载地址
                $root['ios_upgrade'] = $GLOBALS['version']['ios_upgrade'];//ios版本升级内容
                $root['has_upgrade'] = 1;//1:可升级;0:不可升级
                $root['forced_upgrade'] = intval($GLOBALS['version']['ios_forced_upgrade']);//0:非强制升级;1:强制升级
            }else{
                $root['has_upgrade'] = 0;//1:可升级;0:不可升级
                //$root['ios_down_url'] = $GLOBALS['version']['ios_down_url'];//ios下载地址
            }

            $root['response_code'] = 1;
        }else{
            $root['response_code'] = 0;
        }

        output($root);
    }
}
?>