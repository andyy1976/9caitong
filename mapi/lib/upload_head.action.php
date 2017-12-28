<?php
require_once APP_ROOT_PATH.'system/alioss/aliyun-oss-php-sdk.phar';
use OSS\OssClient;
use OSS\Core\OssException;
//上传头像接口

class upload_head{
    
    public function index(){
        $oss_img_path = "Img";
        $oss_domain = "https://oss.9caitong.com";
        //文件保存目录路径
        $save_path = APP_ROOT_PATH."public/attachment/";
        //文件保存目录URL
        $save_url = WAP_SITE_DOMAIN."/public/attachment/";
        $root = get_baseroot();
        $user = $GLOBALS['user_info'];
        $root['session_id'] = es_session::id();

        if ($user['id'] > 0) {

            //定义允许上传的文件扩展名
            $ext_arr = array(
                'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            );
            //最大文件大小
            $max_size = 2000000;

            $save_path = realpath($save_path) . '/';

            //PHP上传失败
            if (!empty($_FILES['file']['error'])) {
                switch($_FILES['file']['error']){
                    case '1':
                        $root['show_err'] = '超过php.ini允许的大小。';
                        break;
                    case '2':
                        $root['show_err'] = '超过表单允许的大小。';
                        break;
                    case '3':
                        $root['show_err'] = '图片只有部分被上传。';
                        break;
                    case '4':
                        $root['show_err'] = '请选择图片。';
                        break;
                    case '6':
                        $root['show_err'] = '找不到临时目录。';
                        break;
                    case '7':
                        $root['show_err'] = '写文件到硬盘出错。';
                        break;
                    case '8':
                        $root['show_err'] = 'File upload stopped by extension。';
                        break;
                    case '999':
                    default:
                        $root['show_err'] = '未知错误。';
                }
                $root['response_code']=0;
                output($root);
            }
            //有上传文件时
            if (empty($_FILES) === false) {
                //原文件名
                $file_name = $_FILES['file']['name'];
                //服务器上临时文件名
                $tmp_name = $_FILES['file']['tmp_name'];
                //文件大小
                $file_size = $_FILES['file']['size'];
                //检查文件名
                if (!$file_name) {
                    $root['show_err']='请选择文件。';
                    $root['response_code']=0;
                    output($root);
                }
                //检查目录
                if (@is_dir($save_path) === false) {
                    $root['show_err']= '上传目录不存在。';
                    $root['response_code']=0;
                    output($root);
                }
                //检查目录写权限
                if (@is_writable($save_path) === false) {
                    $root['show_err'] = '上传目录没有写权限。';
                    $root['response_code']=0;
                    output($root);
                }
                //检查是否已上传
                if (@is_uploaded_file($tmp_name) === false) {
                    $root['show_err']='上传失败。';
                    $root['response_code']=0;
                    output($root);
                }
                //检查文件大小
                if ($file_size > $max_size) {
                    $root['show_err']='上传文件大小超过限制。';
                    $root['response_code']=0;
                    output($root);
                }
                //检查目录名
                $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
                if (empty($ext_arr[$dir_name])) {
                    $root['show_err']='目录名不正确。';
                    $root['response_code']=0;
                    output($root);
                }
                //获得文件扩展名
                $temp_arr = explode(".", $file_name);
                $file_ext = array_pop($temp_arr);
                $file_ext = trim($file_ext);
                $file_ext = strtolower($file_ext);
                //检查扩展名
                if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
                    $root['show_err']="上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。";
                    $root['response_code']=0;
                    output($root);
                }
                //创建文件夹
                if ($dir_name !== '') {
                    $save_path .= "Img/";
                    $save_url .= "Img/";
                    if (!file_exists($save_path)) {
                        mkdir($save_path);
                    }
                }
                $y = date("Y");
                $m = date("m");
                $d = date("d");
                $save_path .= $y . "/";
                $save_url .= $y . "/";
                if (!file_exists($save_path)) {
                    mkdir($save_path);
                }
                $save_path .= $m . "/";
                $save_url .= $m . "/";
                if (!file_exists($save_path)) {
                    mkdir($save_path);
                }
                $save_path .= $d . "/";
                $save_url .= $d . "/";
                if (!file_exists($save_path)) {
                    mkdir($save_path);
                }
                //新文件名
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
                //移动文件
                $file_path = $save_path . $new_file_name;

                /*if (move_uploaded_file($tmp_name, $file_path) === false) {
                    $root['show_err'] = '上传文件失败。';
                    $root['response_code']=0;
                    output($root);
                }*/
                @chmod($file_path, 0644);
                $file_url = $save_url . $new_file_name;

                $object = $oss_img_path."/".$y."/".$m."/".$d."/".$new_file_name;

                //上传oss完成返回
                $endpoint = HOSTNAME;  // http://oss-cn-hangzhou.aliyuncs.com
                $accessKeyId = ACCESS_ID;
                $accessKeySecret = ACCESS_KEY;
                $bucket = BUCKET;
                try {
                    $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                    $ossClient->uploadFile($bucket, $object, $tmp_name);
                    $file_url = $oss_domain."/".$oss_img_path."/".$y."/".$m."/".$d."/".$new_file_name;
                    $root['file_url'] = $file_url;
                    $GLOBALS['db']->query("update ".DB_PREFIX."user set header_url='".$file_url."' where id = ".$user['id']);
                    $root['show_err'] = '上传成功';
                    $root['user_login_status'] = 1;
                    $root['response_code'] = 1;
                    output($root);
                } catch (OssException $e) {
                    $rs=$e->getMessage() . "\n";
                    $root['response_code'] = 0;
                    $root['show_err'] = "上传失败";
                    $root['res'] = $rs;
                    $root['user_login_status'] = 1;
                    output($root);
                }
            }
        } else {
            $root['response_code'] = 0;
            $root['show_err'] = "未登录";
            $root['user_login_status'] = 0;
            output($root);
        }

    }

    /*
    * 上传图片到oss
    *$data string   要上传的图片
    */
    function upload_file_to_alioss11($object,$upload_file_options)
    {
        $access_id=ACCESS_ID;
        $access_key=ACCESS_KEY;
        $hostname=HOSTNAME;
        $bucket=BUCKET;
        require_once APP_ROOT_PATH."/alioss/alioss.class.php";
        $oss = new ALIOSS($access_id,$access_key, $hostname, $security_token = NULL);
        //要上传的文件服务器地址
        $postArrayString='';
        $res = $oss->upload_file_by_content($bucket,$object, $upload_file_options);
		foreach($res as $key=>$value){
			$postArrayString .= $key."=>".$value."\n"; 
		}
		file_put_contents("log2222.txt", $postArrayString, FILE_APPEND ); //接收日志
        $array=(array)$res;

        switch ($array['status']) {
            case '200':
                return true;
                break;

            default:
                return $array;
                break;
        }
    }


}

?>