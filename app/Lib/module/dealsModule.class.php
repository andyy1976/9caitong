<?php
define(ACTION_NAME,"deals");
define(MODULE_NAMEN,"index");
require APP_ROOT_PATH.'app/Lib/deal.php';
class dealsModule extends SiteBaseModule
{
    public function index(){
        /*
        var_dump(time());
        var_dump(date('Z'));
        var_dump(time() - date('Z'));
        var_dump(get_gmtime());
        var_dump(get_gmtime());
        var_dump(TIME_UTC);EXIT;
        */
        $GLOBALS['tmpl']->caching = true;
        $GLOBALS['tmpl']->cache_lifetime = 60;  //首页缓存10分钟
        $field = es_cookie::get("shop_sort_field");

        $field_sort = es_cookie::get("shop_sort_type");

        $cache_id  = md5(MODULE_NAME.ACTION_NAME.$_SERVER['REQUEST_URI'].$field.$field_sort);
        if (!$GLOBALS['tmpl']->is_cached("page/deals.html", $cache_id))
        {
            require APP_ROOT_PATH.'app/Lib/page.php';
            $level_list = load_auto_cache("level");
            $GLOBALS['tmpl']->assign("level_list",$level_list['list']);

            if(trim($_REQUEST['cid'])=="last"){
                $cate_id = "-1";
                $page_title = $GLOBALS['lang']['LAST_SUCCESS_DEALS'];
            }
            else{
                $cate_id = intval($_REQUEST['cid']);
            }

            if($cate_id == 0){
                $page_title = $GLOBALS['lang']['ALL_DEALS'];
            }
            $keywords = trim(htmlspecialchars($_REQUEST['keywords']));
            $GLOBALS['tmpl']->assign("keywords",$keywords);

            $level = intval($_REQUEST['level']);
            $GLOBALS['tmpl']->assign("level",$level);

            $interest = intval($_REQUEST['interest']);
            $GLOBALS['tmpl']->assign("interest",$interest);

            $months = intval($_REQUEST['months']);
            $GLOBALS['tmpl']->assign("months",$months);

            $lefttime = intval($_REQUEST['lefttime']);
            $GLOBALS['tmpl']->assign("lefttime",$lefttime);

            $months_type = intval($_REQUEST['months_type']);
            $GLOBALS['tmpl']->assign("months_type",$months_type);

            $repay_time = intval($_REQUEST['repay_time']);
            $GLOBALS['tmpl']->assign("repay_time",$repay_time);

            $deal_status = intval($_REQUEST['deal_status']);
            $GLOBALS['tmpl']->assign("deal_status",$deal_status);

            $cates = intval($_REQUEST['cates']);
            $GLOBALS['tmpl']->assign("cates",$cates);

            $city = intval($_REQUEST['city']);
            $GLOBALS['tmpl']->assign("city_id",$city);

            $scity = intval($_REQUEST['scity']);
            $GLOBALS['tmpl']->assign("scity_id",$scity);

            $loantype = intval($_REQUEST['loantype']);
            $GLOBALS['tmpl']->assign("loantype",$loantype);
            //标的类型    车贷 房贷...
            $deal_type = intval($_REQUEST['loan_type']);
            $GLOBALS['tmpl']->assign("deal_type",$deal_type);
            $is_company = intval($_REQUEST['is_company']);
            $GLOBALS['tmpl']->assign("is_company",$is_company);
            if($_REQUEST['p']){
                $GLOBALS['tmpl']->assign("p",$_REQUEST['p']);
            }else{
                $GLOBALS['tmpl']->assign("p",1);
            }
            //输出分类
            $deal_cates_db = load_auto_cache("cache_deal_cate");
            $deal_cates = array();

            foreach($deal_cates_db as $k=>$v)
            {
                if($cate_id==$v['id']){
                    $v['current'] = 1;
                    $page_title = $v['name'];
                }
                $v['url'] = url("index","deals",array("cid"=>$v['id']));
                $deal_cates[] = $v;
            }
            unset($deal_cates_db);
            //输出投标列表
            $page = intval($_REQUEST['p']);
            if($page == "0" || $page=="1"){
                $page = 1;
                $limit = (($page-1)*7).",7";
            }else{
                $limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")-1).",".app_conf("DEAL_PAGE_SIZE");
            }
            $n_cate_id = 0;
            if(WAP == 1){
                $deal_type = intval($_REQUEST['deal_type']);
            }
            if($deal_type == 12){
                $condition = " publish_wait = 0 and is_effect = 1 and is_delete = 0 and cunguan_tag =1 and is_hidden = 0 AND (if(deal_status = 1, start_time + enddate*24*3600 > " . TIME_UTC . ",1=1))";
            }else{
                $condition = "publish_wait = 0 and is_effect = 1 and is_delete = 0 and cunguan_tag =1 and is_hidden = 0 and deal_status in(1,2,4,5)";
            }
            // $orderby = "deal_status asc ";
            if($cate_id > 0){
                $n_cate_id = $cate_id;
                if($field && $field_sort)
                    $orderby = "$field $field_sort ,deal_status desc , sort DESC,id DESC";
                else
                    $orderby = "sort DESC,id DESC";
                $total_money = $GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal WHERE cate_id=$cate_id AND deal_status in(4,5) AND is_effect = 1 and is_delete = 0 ");
            }
            /*elseif ($cate_id == 0){
                $n_cate_id = 0;
                if($field && $field_sort)
                    $orderby = "$field $field_sort ,sort DESC,id DESC";
                else
                    $orderby = "is_advance desc,deal_status ASC , sort DESC , id DESC";
                $total_money = $GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal WHERE deal_status in(4,5) AND is_effect = 1 and is_delete = 0");
            }*/
            elseif ($cate_id == "-1"){
                $n_cate_id = 0;
                $condition .= "AND deal_status in(2,4,5) ";
                $orderby = "deal_status ASC,success_time DESC,sort DESC,id DESC";
            }
            if($keywords){
                $kw_unicode = str_to_unicode_string($keywords);
                $condition .=" and (match(name_match,deal_cate_match,tag_match,type_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
            }

            if($level > 0){
                $point  = $level_list['point'][$level];
                $condition .= " AND user_id in(SELECT u.id FROM ".DB_PREFIX."user u LEFT JOIN ".DB_PREFIX."user_level ul ON ul.id=u.level_id WHERE ul.point >= $point)";
            }

            if($interest > 0){
                if($interest==6){
                    $condition .= " AND rate >= ".$interest." and rate <= 7";
                }
                if($interest==7){
                    $condition .= " AND rate > ".$interest." and rate <= 10";
                }
                if($interest==10){
                    $condition .= " AND rate > ".$interest;
                }
            }

            if($months > 0){
                if($months==12)
                    $condition .= " AND repay_time <= ".$months;
                elseif($months==18)
                    $condition .= " AND repay_time >= ".$months;
            }

            if($lefttime > 0){
                $condition .= " AND deal_status = 1  AND (start_time + enddate*24*3600 - ".TIME_UTC.") <= ".$lefttime*24*3600;
            }


            if ($deal_status == 19){
                $condition .= " AND deal_status = 1 AND start_time > ".TIME_UTC." ";
            }
            elseif($deal_status > 0){
                $condition .= " AND deal_status = ".$deal_status." AND start_time <= ".TIME_UTC." ";
            }

            if($is_company > 0){
                $condition .= " AND user_id in ( SELECT id FROM ".DB_PREFIX."user WHERE user_type =".($is_company - 1)." )";
            }


            // if ($months_type > 0){
            // 	if ($months_type == 1)
            // 		$condition .= " AND ((repay_time < 3  and repay_time_type = 1) or repay_time_type = 0) ";
            // 	else if ($months_type == 2)
            // 		$condition .= " AND repay_time in (3,4,5) and repay_time_type = 1 ";
            // 	else if ($months_type == 3)
            // 		$condition .= " AND repay_time in (6,7,8) and repay_time_type = 1 ";
            // 	else if ($months_type == 4)
            // 		$condition .= " AND repay_time in (9,10,11) and repay_time_type = 1 ";
            // 	else
            // 		$condition .= " AND repay_time >= 12 and repay_time_type = 1 ";
            // }
            if ($months_type > 0){
                if ($months_type == 1)
                    $condition .= " AND ((repay_time = 1  and repay_time_type = 1) or repay_time_type = 0) ";
                else if ($months_type == 2)
                    $condition .= " AND repay_time = 3 and repay_time_type = 1 ";
                else if ($months_type == 3)
                    $condition .= " AND repay_time = 6 and repay_time_type = 1 ";
                else if ($months_type == 4)
                    $condition .= " AND repay_time = 12 and repay_time_type = 1 ";
            }

            if ($repay_time > 0){
                $condition .= " AND repay_time =".$repay_time;
            }
            if ($city > 0){
                if($scity > 0){
                    $dealid_list = $GLOBALS['db']->getAll("SELECT deal_id FROM ".DB_PREFIX."deal_city_link where city_id = ".$scity);
                }
                else{
                    $dealid_list = $GLOBALS['db']->getAll("SELECT deal_id FROM ".DB_PREFIX."deal_city_link where city_id = ".$city);
                }

                $flatmap = array_map("array_pop",$dealid_list);
                $s2=implode(',',$flatmap);
                $condition .= " AND id in (".$s2.") ";
            }

            if($loantype > 0){
                $condition .= " AND loantype =  ".($loantype - 1)." ";
            }
            if($deal_type>0 && $deal_type<>12){
                $condition .= " AND type_id =  ".$deal_type." ";
            }
            //使用技巧
            $use_tech_list  = get_article_list(4,6);
            $GLOBALS['tmpl']->assign("use_tech_list",$use_tech_list);

            if((int)app_conf("SHOW_EXPRIE_DEAL") == 0){
                $condition .= " AND (if(deal_status = 1, start_time + enddate*24*3600 > ".TIME_UTC .",1=1)) ";
            }
            /*********wap2.0按时间筛选************/
            /*wap存管版 列表*/
            if(WAP == 1){
                $deal_type = intval($_REQUEST['deal_type']);
                if(!$deal_type)
                    $deal_type =11;
                $GLOBALS['tmpl']->assign("deal_type",$deal_type);
                $page_args['deal_type'] =  $deal_type;
                $deal_cate = $GLOBALS['db']->getAll("select id,name,sort from ".DB_PREFIX."deal_loan_type where is_effect = 1 and is_delete = 0 ");
                foreach($deal_cate as $k=>$v){

                    $tmp_args = $page_args;
                    $tmp_args['deal_type'] = $v['id'];
                    $deal_cate[$k]['url'] = url("index","deals",$tmp_args);
                    $sort[] = $deal_cate[$k]['sort'];
                }

                array_multisort($sort,SORT_ASC,$deal_cate);
                $GLOBALS['tmpl']->assign('deal_cate',$deal_cate);


                if($deal_type && $deal_type<>12)
                    $condition .= " and type_id = ".$deal_type." " ;

            }
            /*wap存管版 列表*/
            /*wap存管版 项目周期*/
            $repay_time = intval($_REQUEST['repay_time']);
            $GLOBALS['tmpl']->assign("repay_time",$repay_time);
            $page_args['repay_time'] =  $repay_time;
            $deal_time =array(
                array('id' => '0','month' => '全部'),
                array('id' => '1','month' => '1个月'),
                array('id' => '3','month' => '3个月'),
                array('id' => '6','month' => '6个月'),
                array('id' => '12','month' => '12个月'),
            );
            foreach($deal_time as $k=>$v){
                $tmp_args = $page_args;
                $tmp_args['repay_time'] = $v['id'];
                $deal_time[$k]['url'] = url("index","deals",$tmp_args);
            }
            $GLOBALS['tmpl']->assign('deal_time',$deal_time);

            /*wap存管版 项目周期*/
            if($deal_type == 12){
                $orderby = "deal_status asc,id desc";
                $result = get_plandeal_list($limit, $n_cate_id, $condition, $orderby);
            }else if($deal_type == 15){
                $orderby = "deal_status asc,id desc";
                $result = get_deal_listfood($limit, $n_cate_id, $condition, $orderby);
            }else{
                $orderby = "deal_status ASC,repay_time_type desc,is_new desc,is_advance desc, sort DESC , id DESC";
                $result = get_deal_list($limit,$n_cate_id,$condition,$orderby);
            }

            //体验标 wap
            if($deal_type ==11){
                $experience_deal = $GLOBALS['db']->getAll("select id,name,rate,borrow_amount,load_money,repay_time,FORMAT(load_money/borrow_amount*100,2) as progress_point from ".DB_PREFIX."experience_deal where deal_status = 1 and is_effect = 1 and cunguan_tag =1 and publish_wait=1 and deal_status in (1)");
            }


            if($experience_deal && !$repay_time){
                foreach($experience_deal as $k=>$v){
                    $experience_deal[$k]['url'] = url("index","experience_deal",array("id"=>$v['id']));
                    $experience_deal[$k]['rate'] = sprintf("%.1f",$v["rate"]);
                    $experience_deal[$k]['need_money'] = sprintf("%.2f",($v["borrow_amount"]-$v["load_money"]));
                    if($experience_deal[$k]['need_money'] > 10000){
                        $experience_deal[$k]['need_money'] = is_float($experience_deal[$k]['need_money']/10000) ? substr_replace($experience_deal[$k]['need_money']/10000, '', strpos($experience_deal[$k]['need_money']/10000, '.') + 3).'万' :$experience_deal[$k]['need_money']/10000 .'万';
                    }
                }
            }else{
                $experience_deal = 0;
            }
            $GLOBALS['tmpl']->assign("experience_deal",$experience_deal);


            /*体验标*/
            $conditis='';
            $conditis .= " cunguan_tag =1 and publish_wait=1 and deal_status in (1)";
            $result_treetop = experience_treetop($limit,$n_cate_id,$conditis,$orderby);
            if($result_treetop && !$months_type && !$deal_status && !$interest){
                $result_treetop = $result_treetop;
            }else{
                $result_treetop = 0;
            }
            $GLOBALS['tmpl']->assign("result_treetop",$result_treetop['list']);


            $page_total= ceil($result['count']/app_conf("DEAL_PAGE_SIZE"));
            $GLOBALS['tmpl']->assign("page_total",$page_total);


            foreach($result['list'] as $k=>$v){
                $result ['list'][$k]['rate']=sprintf("%.1f",$v['rate']);
                $result ['list'][$k]['timer']=$v['start_time'] - time();
                if($result ['list'][$k]['timer']<0){
                    $result['list'][$k]['timer'] = 0;
                }
                $hour = floor($result['list'][$k]['timer']/3600);
                if($hour<10){
                    $hour = "0".$hour;
                }
                $minutes = floor($result['list'][$k]['timer']/60%60);
                if($minutes<10){
                    $minutes = "0".$minutes;
                }
                $seconds = floor($result['list'][$k]['timer']%60);
                if($seconds<10){
                    $seconds = "0".$seconds;
                }
                $result['list'][$k]['initial_time'] = $hour.":".$minutes.":".$seconds;
                if($v['debts']==1){
                    $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$v['old_deal_id']." order by repay_time desc limit 1");
                    //$result['list'][$k]['debts_repay_time']= ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+2;
                    //$result['list'][$k]['debts_repay_time']= ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",$v['start_time'])))/3600/24)+1;
                    $remin = strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time()));
                    if($remin>0){
                        $result['list'][$k]['debts_repay_time']= ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time())))/3600/24)+1;
                    }else{
                        $result['list'][$k]['debts_repay_time']=0;
                    }
                }
                if ($months_type > 0){
                    if ($months_type == 1)
                        $condition .= " AND ((repay_time = 1  and repay_time_type = 1) or repay_time_type = 0) ";
                    else if ($months_type == 2)
                        $condition .= " AND repay_time = 3 and repay_time_type = 1 ";
                    else if ($months_type == 3)
                        $condition .= " AND repay_time = 6 and repay_time_type = 1 ";
                    else if ($months_type == 4)
                        $condition .= " AND repay_time = 12 and repay_time_type = 1 ";
                }
                /*
                $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."deal_load where deal_id=".$v['id']);
                $need_money_re = $v['borrow_amount'] - $need_money;
                $progress = floatval($need_money/$v['borrow_amount']*100);
                $progress = sprintf("%.2f",$progress);
                if($need_money_re<0){
                    $need_money_re = '0';
                }
                if($progress>100){
                    $progress = '100';
                }
                if($progress*100>=9999&&$need_money_re!=0){
                    $result['list'][$k]['progress_point']='99.99';
                }else{
                    $result['list'][$k]['progress_point'] = $progress;
                }

                $url_tmp_args['id'] = $v['id'];
                $result['list'][$k]['url'] = url("index","deal#index",$url_tmp_args); 	//入口 控制器模块 控制器 参数数组
                $result['list'][$k]['rate'] = sprintf("%.1f",$v["rate"]);				//统一预期年化收益格式
                //*************朱湘***********

                //$result['list'][$k]['need_money'] = intval($v['need_money']);
                if($need_money_re>=10000)
                    $result ['list'][$k]['need_money'] = is_float($need_money_re/10000) ? substr_replace($need_money_re/10000, '', strpos($need_money_re/10000, '.') + 3).'万' : $need_money_re/10000 .'万';
                else
                    $result ['list'][$k]['need_money']=intval($need_money_re);
                */
                if($v['need_money'] > 10000){
                    $result ['list'][$k]['need_money'] = is_float($v['need_money']/10000) ? substr_replace($v['need_money']/10000, '', strpos($v['need_money']/10000, '.') + 3).'万' :$v['need_money']/10000 .'万';
                }
                if($deal_type == 12){
                    $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."deal_load where plan_id=".$v['id']);
                }else{
                    $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."deal_load where deal_id=".$v['id']);
                }

                $progress = sprintf("%.2f",floatval($need_money/$v['borrow_amount']*100));
                //理财计划出借进度
                $result['list'][$k]['plan_progress'] = round($v['load_money']/$v['borrow_amount']*100,2);
                if($need_money>=$v['borrow_amount']){
                    $result['list'][$k]['progress_point'] = '100';
                }elseif($progress*100>=9999&&$need_money<$v['borrow_amount']){
                    $result['list'][$k]['progress_point'] = '99.99';
                }else{
                    $result['list'][$k]['progress_point'] = $progress;
                }
            }
            $GLOBALS['tmpl']->assign("cate_title","出借列表");
            $GLOBALS['tmpl']->assign("deal_list",$result['list']);
            $GLOBALS['tmpl']->assign("server_time",$result['server_time']);
            $GLOBALS['tmpl']->assign("total_money",$total_money);

            //输出公告
            $notice_list = get_notice(3);
            $GLOBALS['tmpl']->assign("notice_list",$notice_list);

            $page_args['cid'] =  $cate_id;
            $page_args['keywords'] =  $keywords;
            $page_args['level'] =  $level;
            $page_args['interest'] =  $interest;
            $page_args['months'] =  $months;
            $page_args['lefttime'] =  $lefttime;
            $page_args['loan_type'] =  $deal_type;


            $page_args['months_type'] =  $months_type;
            $page_args['deal_status'] =  $deal_status;
            $page_args['city'] =  $city;
            $page_args['is_company'] =  $is_company;

            //分类
            $cate_list_url = array();
            $tmp_args = $page_args;
            $tmp_args['cid'] = 0;
            $cate_list_url[0]['url'] = url("index","deals#index",$tmp_args);
            $cate_list_url[0]['name'] = "不限";
            $cate_list_url[0]['id'] = 0;
            foreach($deal_cates as $k=>$v){
                $cate_list_url[$k+1] = $v;
                $tmp_args = $page_args;
                $tmp_args['cid'] = $v['id'];
                $cate_list_url[$k+1]['url'] = url("index","deals#index",$tmp_args);
            }

            $GLOBALS['tmpl']->assign('cate_list_url',$cate_list_url);

            //利率
            $interest_url = array(
                array(
                    "interest"=>0,
                    "name" => "不限",
                ),
                array(
                    "interest"=>6,
                    "name" => "6-7%",
                ),
                array(
                    "interest"=>7,
                    "name" => "7-10%",
                ),
                array(
                    "interest"=>10,
                    "name" => "10%以上",
                ),
                // array(
                // 	"interest"=>10,
                // 	"name" => "10%",
                // ),
                // array(
                // 	"interest"=>12,
                // 	"name" => "12%",
                // ),
                // array(
                // 	"interest"=>15,
                // 	"name" => "15%",
                // ),
                // array(
                // 	"interest"=>18,
                // 	"name" => "18%",
                // ),
            );
            foreach($interest_url as $k=>$v){
                $tmp_args = $page_args;
                $tmp_args['interest'] = $v['interest'];
                $interest_url[$k]['url'] = url("index","deals#index",$tmp_args);
            }
            $GLOBALS['tmpl']->assign('interest_url',$interest_url);



            //几天内
            $lefttime_url = array(
                array(
                    "lefttime"=>0,
                    "name" => "不限",
                ),
                array(
                    "lefttime"=>1,
                    "name" => "1天",
                ),
                array(
                    "lefttime"=>3,
                    "name" => "3天",
                ),
                array(
                    "lefttime"=>6,
                    "name" => "6天",
                ),
                array(
                    "lefttime"=>9,
                    "name" => "9天",
                ),
                array(
                    "lefttime"=>12,
                    "name" => "12天",
                ),
            );

            foreach($lefttime_url as $k=>$v){
                $tmp_args = $page_args;
                $tmp_args['lefttime'] = $v['lefttime'];
                $lefttime_url[$k]['url'] = url("index","deals#index",$tmp_args);
            }
            $GLOBALS['tmpl']->assign('lefttime_url',$lefttime_url);

            //借款期限
            $months_type_url = array(
                array(
                    "name" => "不限",
                ),
                array(
                    "name" => "1 个月",
                ),
                array(
                    "name" => "3 个月",
                ),
                array(
                    "name" => "6 个月",
                ),
                // array(
                // 		"name" => "9 个月",
                // ),
                array(
                    "name" => "12 个月",
                ),
            );

            foreach($months_type_url as $k=>$v){
                $tmp_args = $page_args;
                $tmp_args['months_type'] = $k;
                $months_type_url[$k]['url'] = url("index","deals#index",$tmp_args);
            }

            $GLOBALS['tmpl']->assign('months_type_url',$months_type_url);

            //wap 借款期限分类
            //借款期限
            $months_type_deal = array(
                array(
                    "name" => "全部",
                ),
                array(
                    "name" => "3个月",
                ),
                array(
                    "name" => "6个月",
                ),
                array(
                    "name" => "12个月",
                ),
            );

            foreach($months_type_deal as $k=>$v){
                $tmp_args = $page_args;
                $tmp_args['repay_time'] = $k;
                $months_type_deal[$k]['url'] = url("index","deals#index",$tmp_args);
            }

            $GLOBALS['tmpl']->assign('months_type_deal',$months_type_deal);

            //标状态
            $deal_status_url = array(
                array(
                    "key"=>0,
                    "name" => "不限",
                ),
                // array(
                // 	"key"=>19,
                // 	"name" => "未开始",
                // ),
                array(
                    "key"=>1,
                    "name" => "募集中",
                ),
                // array(
                // 	"key"=>2,
                // 	"name" => "满标",
                // ),
                // array(
                // 	"key"=>3,
                // 	"name" => "流标",
                // ),
                array(
                    "key"=>4,
                    "name" => "还款中",
                ),
                array(
                    "key"=>5,
                    "name" => "已结束",
                ),
            );


            foreach($deal_status_url as $k=>$v){
                $tmp_args = $page_args;
                $tmp_args['deal_status'] = $v['key'];
                $deal_status_url[$k]['url'] = url("index","deals#index",$tmp_args);
            }
            $GLOBALS['tmpl']->assign('deal_status_url',$deal_status_url);


            //会员等级
            $level_list_url = array();
            $tmp_args = $page_args;
            $tmp_args['level'] = 0;
            $level_list_url[0]['url'] = url("index","deals#index",$tmp_args);
            $level_list_url[0]['name'] = "不限";
            foreach($level_list['list'] as $k=>$v){
                $tmp_args = $page_args;
                $tmp_args['level'] = $v['id'];
                $level_list_url[$k+1] = $v;
                $level_list_url[$k+1]['url'] = url("index","deals#index",$tmp_args);
            }
            $GLOBALS['tmpl']->assign('level_list_url',$level_list_url);

            //  标的类型   车贷、房贷。。。
            $loan_types=$GLOBALS['db']->getAll("SELECT id,name,sort  FROM ".DB_PREFIX."deal_loan_type where is_effect=1 and is_delete=0");
            array_unshift($loan_types,array('name'=>"不限"));

            foreach($loan_types as $k=>$v){
                $tmp_args = $page_args;
                $tmp_args['loan_type'] = $v['id'];
                $loan_types[$k]['url'] = url("index","deals#index",$tmp_args);
                $sort[] = $loan_types[$k]['sort'];
            }
            array_multisort($sort,SORT_ASC,$loan_types);
            $GLOBALS['tmpl']->assign('loan_type',$loan_types);
            //标状态
            $loantype_url = array();
            $loantypes= $GLOBALS['db']->getAll("SELECT distinct loantype FROM ".DB_PREFIX."deal ORDER BY loantype ASC ",1);
            $loantype_url[0]['url'] = url("index","deals#index",$tmp_args);
            $loantype_url[0]['name'] = "不限";
            foreach($loantypes as $k=>$v){
                $tmp_args = $page_args;
                $loantype_url[$v['loantype']+1]['name'] = loantypename($v['loantype'],0);
                $loantype_url[$v['loantype']+1]['loantype'] = $tmp_args['loantype'] = $v['loantype']+1;
                $loantype_url[$v['loantype']+1]['url'] = url("index","deals#index",$tmp_args);
            }
            $GLOBALS['tmpl']->assign('loantype_url',loantype_url);
            unset($loantypes);

            //城市
            $temp_city_urls =load_auto_cache("deal_city");
            $city_urls =array();
            if($temp_city_urls){
                $city_urls[0]['id'] = 0;
                $city_urls[0]['name'] = "不限";
                $tmp_args = $page_args;
                $tmp_args['city'] = 0;
                $city_urls[0]['url'] = url("index","deals#index",$tmp_args);

                foreach($temp_city_urls as $k=>$v){
                    if(isset($v['id'])){
                        $city_urls[$v['id']] = $v;
                        $tmp_args = $page_args;
                        $tmp_args['city'] = $v['id'];
                        $city_urls[$v['id']]['url'] = url("index","deals#index",$tmp_args);
                    }
                }
            }

            $GLOBALS['tmpl']->assign('city_urls',$city_urls);

            $sub_citys = $city_urls[$city]['child'];
            if($sub_citys){
                foreach($sub_citys as $k=>$v){
                    $tmp_args = $page_args;
                    $tmp_args['city'] = $v['pid'];
                    $tmp_args['scity'] = $v['id'];
                    $sub_citys[$k]['url'] = url("index","deals#index",$tmp_args);
                }
            }
            $GLOBALS['tmpl']->assign('sub_citys',$sub_citys);


            //企业标
            $user_type_urls = array(
                array(
                    "key"=>0,
                    "name"=>"不限",
                ),
                array(
                    "key"=>1,
                    "name"=>"个人借款",
                ),
                array(
                    "key"=>2,
                    "name"=>"企业借款",
                ),
            );
            foreach($user_type_urls as $k=>$v){
                $tmp_args = $page_args;
                $tmp_args['is_company'] = $v['key'];
                $user_type_urls[$k]['url'] = url("index","deals#index",$tmp_args);
            }
            $GLOBALS['tmpl']->assign('user_type_urls',$user_type_urls);



            $page_pram = "";
            foreach($page_args as $k=>$v){
                $page_pram .="&".$k."=".$v;
            }

            $page = new Page($result['count'],app_conf("DEAL_PAGE_SIZE"),$page_pram);   //初始化分页对象
            $p  =  $page->show();
            $GLOBALS['tmpl']->assign('pages',$p);

            $GLOBALS['tmpl']->assign("page_title",$page_title );

            $GLOBALS['tmpl']->assign("cate_id",$cate_id);
            $GLOBALS['tmpl']->assign("cid",strim($_REQUEST['cid']));
            $GLOBALS['tmpl']->assign("keywords",$keywords);
            $GLOBALS['tmpl']->assign("deal_cate_list",$deal_cates);
            $GLOBALS['tmpl']->assign("page_args",$page_args);
            $GLOBALS['tmpl']->assign("field",$field);
            $GLOBALS['tmpl']->assign("field_sort",$field_sort);

            $stats = site_statics();
            $GLOBALS['tmpl']->assign("stats",$stats);
        }
        $GLOBALS['tmpl']->assign("page",app_conf("DEAL_PAGE_SIZE"));
        $GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
        $GLOBALS['tmpl']->assign("MODULE_NAMEN",MODULE_NAMEN);
        $GLOBALS['tmpl']->display("page/deals.html",$cache_id);
    }

    public function about(){
        $GLOBALS['tmpl']->caching = true;
        $GLOBALS['tmpl']->cache_lifetime = 6000;  //首页缓存10分钟
        $name = trim($_REQUEST['u']) == "" ? "financing" : trim($_REQUEST['u']);
        $cache_id  = md5(MODULE_NAME.ACTION_NAME.$name);
        if (!$GLOBALS['tmpl']->is_cached("page/deals_about.html", $cache_id))
        {
            $info = get_article_buy_uname($name);
            $info['content']=$GLOBALS['tmpl']->fetch("str:".$info['content']);
            $GLOBALS['tmpl']->assign("info",$info);

            $about_list = get_article_list(20,7,"","id ASC",true);

            $GLOBALS['tmpl']->assign("about_list",$about_list['list']);

            $seo_title = $info['seo_title']!=''?$info['seo_title']:$info['title'];
            $GLOBALS['tmpl']->assign("page_title",$seo_title);
            $seo_keyword = $info['seo_keyword']!=''?$info['seo_keyword']:$info['title'];
            $GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
            $seo_description = $info['seo_description']!=''?$info['seo_description']:$info['title'];
            $GLOBALS['tmpl']->assign("page_description",$seo_description.",");
        }
        $GLOBALS['tmpl']->display("page/deals_about.html",$cache_id);
    }

    public function ajax_load(){

        $page_args['field'] =  $field = strim($_REQUEST['field']);

        $page_args['field_sort'] =  $field_sort = strim($_REQUEST['field_sort']);

        $page_args['page_size'] =  $page_size = intval($_REQUEST['page_size']);

        $page_args['cid'] =  $cate_id = intval($_REQUEST['cid']);

        $page_args['extcid'] =  $extcid = strim($_REQUEST['extcid']);

        $page_args['keywords'] = $keywords = strim($_REQUEST['keywords']);

        $page_args['level'] = $level = intval($_REQUEST['level']);

        $page_args['interest'] = $interest = intval($_REQUEST['interest']);

        $page_args['months'] = $months = intval($_REQUEST['months']);

        $page_args['lefttime'] = $lefttime = intval($_REQUEST['lefttime']);

        $page_args['months_type'] = $months_type = intval($_REQUEST['months_type']);

        $page_args['deal_status'] = $deal_status = intval($_REQUEST['deal_status']);

        $page_args['cates'] = $cates = intval($_REQUEST['cates']);

        $page_args['city'] = $city = intval($_REQUEST['city']);

        $page_args['scity'] = $scity = intval($_REQUEST['scity']);

        $page_args['typeid'] = $typeid = intval($_REQUEST['typeid']);

        $page_args['is_company'] = $is_company = intval($_REQUEST['is_company']);


        $page = intval($_REQUEST['p']);
        if($page==0)
            $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;

        $n_cate_id = 0;
        $condition = " publish_wait = 0 and is_hidden = 0 ";
        $orderby = "";


        if($cate_id > 0){
            $n_cate_id = $cate_id;
            if($field && $field_sort)
                $orderby = "$field $field_sort ,deal_status desc , sort DESC,id DESC";
            else
                $orderby = "sort DESC,id DESC";
            $total_money = $GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal WHERE cate_id=$cate_id AND deal_status in(4,5) AND is_effect = 1 and is_delete = 0 ");
        }
        elseif ($cate_id == 0){
            $n_cate_id = 0;
            if($field && $field_sort)
                $orderby = "$field $field_sort ,sort DESC,id DESC";
            else
                $orderby = "sort DESC , id DESC";
            $total_money = $GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal WHERE deal_status in(4,5) AND is_effect = 1 and is_delete = 0");
        }
        elseif ($cate_id == "-1"){
            $n_cate_id = 0;
            $condition .= "AND deal_status in(2,4,5) ";
            $orderby = "deal_status ASC,success_time DESC,sort DESC,id DESC";
        }

        if($extcid != ""){
            $condition .= "AND cate_id not in(".$extcid.") ";
        }


        if($keywords){
            $kw_unicode = str_to_unicode_string($keywords);
            $condition .=" and (match(name_match,deal_cate_match,tag_match,type_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
        }

        if($level > 0){
            $level_list = load_auto_cache("level");
            $point  = $level_list['point'][$level];
            $condition .= " AND user_id in(SELECT u.id FROM ".DB_PREFIX."user u LEFT JOIN ".DB_PREFIX."user_level ul ON ul.id=u.level_id WHERE ul.point >= $point)";
        }

        if($interest > 0){
            if($interest==6){
                $condition .= " AND rate >= ".$interest." and rate <= 7";
            }
            if($interest==7){
                $condition .= " AND rate > ".$interest." and rate <= 10";
            }
            if($interest==10){
                $condition .= " AND rate > ".$interest;
            }
        }

        if($months > 0){
            if($months==12)
                $condition .= " AND repay_time <= ".$months;
            elseif($months==18)
                $condition .= " AND repay_time >= ".$months;
        }

        if($lefttime > 0){
            $condition .= " AND deal_status = 1  AND (start_time + enddate*24*3600 - ".TIME_UTC.") <= ".$lefttime*24*3600;
        }


        if ($deal_status == 19){
            $condition .= " AND deal_status = 1 AND start_time > ".TIME_UTC." ";
        }
        elseif($deal_status > 0){
            $condition .= " AND deal_status = ".$deal_status." AND start_time <= ".TIME_UTC." ";
        }


        // if ($months_type > 0){
        // 	if ($months_type == 1)
        // 		$condition .= " AND ((repay_time = 1  and repay_time_type = 1) or repay_time_type = 0) ";
        // 	else if ($months_type == 2)
        // 		$condition .= " AND repay_time = 3 and repay_time_type = 1 ";
        // 	else if ($months_type == 3)
        // 		$condition .= " AND repay_time = 6 and repay_time_type = 1 ";
        // 	else if ($months_type == 4)
        // 		$condition .= " AND repay_time = 12 and repay_time_type = 1 ";
        // 	else
        // 		$condition .= " AND repay_time = 12 and repay_time_type = 1 ";
        // }

        if ($city > 0){
            if($scity > 0){
                $dealid_list = $GLOBALS['db']->getAll("SELECT deal_id FROM ".DB_PREFIX."deal_city_link where city_id = ".$scity);
            }
            else{
                $dealid_list = $GLOBALS['db']->getAll("SELECT deal_id FROM ".DB_PREFIX."deal_city_link where city_id = ".$city);
            }

            $flatmap = array_map("array_pop",$dealid_list);
            $s2=implode(',',$flatmap);
            $condition .= " AND id in (".$s2.") ";
        }


        if($typeid > 0){
            $condition .= " AND type_id = ".$typeid;
        }

        if($is_company > 0){
            $condition .= " AND user_id in ( SELECT id FROM ".DB_PREFIX."user WHERE user_type =".($is_company - 1)." )";
        }

        if((int)app_conf("SHOW_EXPRIE_DEAL") == 0){
            $condition .= " AND (if(deal_status = 1, start_time + enddate*24*3600 > ".TIME_UTC .",1=1)) ";
        }
        $result = get_deal_list($limit,$n_cate_id,$condition,$orderby);
        $GLOBALS['tmpl']->assign("deal_list",$result['list']);
        $GLOBALS['tmpl']->assign("total_money",$total_money);


        $page_pram = "";
        foreach($page_args as $k=>$v){
            $page_pram .="&".$k."=".$v;
        }

        require APP_ROOT_PATH.'app/Lib/page.php';

        $page = new Page($result['count'],$page_size,$page_pram);   //初始化分页对象
        $p  =  $page->show();
        $GLOBALS['tmpl']->assign('pages',$p);

        $GLOBALS['tmpl']->display('inc/deal/deals_item.html');
    }
    //返回数据条数
    public function ajaxProductLst(){
        $repay_time = $_REQUEST['repay_time'];
        $deal_type = intval($_REQUEST['deal_type']);
        $condition = " publish_wait = 0 and is_effect = 1 and is_delete = 0 and cunguan_tag =1 and is_hidden = 0 and deal_status in(1,2,4,5)" ;
        if($deal_type==12){
            $orderby = "deal_status ASC, sort DESC , id DESC";
        }else{
            $orderby = "deal_status ASC,is_new desc,is_advance desc, sort DESC , id DESC";
        }

        if($deal_type&&$deal_type!=12){
            $condition .= " and type_id = ".$deal_type ;
        }

        if ($repay_time > 0){
            $condition .= " AND repay_time =".$repay_time;
        }

        $condition .=" and cunguan_tag = 1 ";
        if($deal_type==12){
            $result = get_plandeal_list($limit,$n_cate_id,$condition,$orderby);
        }else if($deal_type==15){
            $result = get_deal_listfood($limit,$n_cate_id,$condition,$orderby);
        }else{
            $result = get_deal_list($limit,$n_cate_id,$condition,$orderby);
        }

        echo $result['count'];

    }
    //返回加载数据
    public function productList(){
        $page = $_REQUEST['page'];
        $repay_time = $_REQUEST['repay_time'];
        $deal_type = intval($_REQUEST['deal_type']);
        $condition = " publish_wait = 0 and is_effect = 1 and is_delete = 0 and cunguan_tag =1 and is_hidden = 0 and deal_status in(1,2,4,5)" ;
        if($deal_type==12){
            $orderby = "deal_status ASC, sort DESC , id DESC";
        }else{
            $orderby = "deal_status ASC,is_new desc,is_advance desc, sort DESC , id DESC";
        }
        if($page == "0" || $page=="1"){
            $page = 1;
            $limit = (($page-1)*7).",7";
        }else{
            $limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")-1).",".app_conf("DEAL_PAGE_SIZE");
        }
        if($deal_type&&$deal_type!=12){
            $condition .= " and type_id = ".$deal_type ;
        }

        if ($repay_time > 0){
            $condition .= " AND repay_time =".$repay_time;
        }
        $condition .=" and cunguan_tag = 1";

        if($deal_type==12){
            $result = get_plandeal_list($limit,$n_cate_id,$condition,$orderby);

        }else if($deal_type==15) {
            $result = get_deal_listfood($limit, $n_cate_id, $condition, $orderby);

        }else{
            $result = get_deal_list($limit,$n_cate_id,$condition,$orderby);
        }
        foreach($result['list'] as $k=>$v){
            $result ['list'][$k]['rate']=sprintf("%.1f",$v['rate']);
            $result ['list'][$k]['timer']=$v['start_time'] - time();
            if($result ['list'][$k]['timer']<0){
                $result['list'][$k]['timer'] = 0;
            }
            $hour = floor($result['list'][$k]['timer']/3600);
            if($hour<10){
                $hour = "0".$hour;
            }
            $minutes = floor($result['list'][$k]['timer']/60%60);
            if($minutes<10){
                $minutes = "0".$minutes;
            }
            $seconds = floor($result['list'][$k]['timer']%60);
            if($seconds<10){
                $seconds = "0".$seconds;
            }
            $result['list'][$k]['initial_time'] = $hour.":".$minutes.":".$seconds;
            $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from ".DB_PREFIX."deal_load where deal_id=".$v['id']);
            $need_money_re = $v['borrow_amount'] - $need_money;
            $progress = floatval($need_money/$v['borrow_amount']*100);
            if($need_money_re<0){
                $need_money_re = '0';
            }

            if($progress>100){
                $progress = '100';
            }
            if($progress*100>=9999&&$need_money_re!=0){
                $result['list'][$k]['progress_point']='99.99';
            }else{
                $result['list'][$k]['progress_point'] = $progress;
            }
            $url_tmp_args['id'] = $v['id'];
            if($deal_type==12){
                $result['list'][$k]['url'] = url("index","plandeal#index",$url_tmp_args); 	//入口 控制器模块 控制器 参数数组
            }else{
                $result['list'][$k]['url'] = url("index","deal#index",$url_tmp_args); 	//入口 控制器模块 控制器 参数数组
            }

            $result['list'][$k]['rate'] = sprintf("%.1f",$v["rate"]);				//统一预期年化收益格式
            /*************朱湘************/
            //$result['list'][$k]['need_money'] = intval($v['need_money']);
            if($v['need_money'] > 10000){
                $result ['list'][$k]['need_money'] = is_float($v['need_money']/10000) ? substr_replace($v['need_money']/10000, '', strpos($v['need_money']/10000, '.') + 3).'万' :$v['need_money']/10000 .'万';
            }
            if($v['debts']==1){
                $last_repay_time = $GLOBALS['db'] ->getOne("select repay_time from ".DB_PREFIX."deal_repay where deal_id =".$v['old_deal_id']." order by repay_time desc limit 1");
                //$result['list'][$k]['debts_repay_time']= ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",$v['start_time'])))/3600/24)+1;
                $remin = strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",time()));
                if($remin>0){
                    $result['list'][$k]['debts_repay_time']= ((strtotime(date("Y-m-d",$last_repay_time))-strtotime(date("Y-m-d",$v['start_time'])))/3600/24)+1;
                }else{
                    $result['list'][$k]['debts_repay_time']=0;
                }
            }

        }
        if (empty($result['list'])) {
            echo 'false';
        }else{
            $GLOBALS['tmpl']->assign('list', $result['list']);
            $info = $GLOBALS['tmpl']->fetch("page/ajaxProductLst.html");
            echo $info;
        }

    }
    function deal_fail(){
        $GLOBALS['tmpl']->display('page/deal_fail.html');
    }
}
?>
