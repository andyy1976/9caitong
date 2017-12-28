<?php 
define("IS_DEBUG",1);
define("SHOW_DEBUG",0);
define("SHOW_LOG",0);
define("LAI_LAI",0); 
define("MAX_DYNAMIC_CACHE_SIZE",1000);  //动态缓存最数量
define("SMS_TIMESPAN",60);  //短信验证码发送的时间间隔
define("SMS_EXPIRESPAN",300);  //短信验证码失效时间
define("TIME_UTC",get_gmtime());   //当前UTC时间戳
define("CLIENT_IP",get_client_ip());  //当前客户端IP
define("SITE_DOMAIN",get_domain());   //站点域名
define("WAP_SITE_DOMAIN","https://wapjct.9caitong.com");   //wap端站点域名,测试。
define("MAX_LOGIN_TIME",1200);  //登录的过期时间
define("SESSION_TIME",3600); //session超时时间
define("AES_DECRYPT_KEY","__JIUCAITONGAESKEY__");//数据库加密
define("ACCOUNT_PAGE_SIZE",10); //一页显示条数
define("SEND_VERIFYSMS_LIMIT",100); //注册短信发送条数限制
define("REDIS_HOST","r-2ze9bb16ac00f324.redis.rds.aliyuncs.com");  //redis host
define("REDIS_PORT",6379); //redis 端口
define("REDIS_USER","2ze9bb16ac00f324");  //redis host
define("REDIS_PWD","o7r3xau4X31UDPW6SpddfjSRnyFH"); //redis 端口
define("REDIS_PREFIX","test_");//redis键前缀 区分测试环境和正式环境

/***OSS相关配置***/
/*define("ACCESS_ID","7L4ceDYcNtrjiusM");
define("ACCESS_KEY","Wnb9gPI6ikWAzekID7SKe6j8kCsVUK");
define("HOSTNAME","oss-cn-beijing.aliyuncs.com");
define("BUCKET","jxdai");*/

define("ACCESS_ID","XWtY8H1mvyfaUbpl");
define("ACCESS_KEY","C1KUrFJOMmESdGQwkXMt3w34QTJtVL");
define("HOSTNAME","http://oss-cn-beijing.aliyuncs.com");
define("BUCKET","newjcjr2");






?>