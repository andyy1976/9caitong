<?php

require APP_ROOT_PATH.'app/Lib/deal.php';
class deals
{
    public function index()
    {

        $page = intval(base64_decode($GLOBALS['request']['page']));
        if ($page == 0)
            $page = 1;
        $keywords = trim(htmlspecialchars(base64_decode($GLOBALS['request']['keywords'])));
        $level = intval(base64_decode($GLOBALS['request']['level']));
        $interest = intval(base64_decode($GLOBALS['request']['interest']));
        $months = intval(base64_decode($GLOBALS['request']['months']));
        $lefttime = intval(base64_decode($GLOBALS['request']['lefttime']));
        $deal_status = intval(base64_decode($GLOBALS['request']['deal_status']));
        $type_id = intval(base64_decode($GLOBALS['request']['type_id']));

        $limit = (($page - 1) * app_conf("DEAL_PAGE_SIZE")) . "," . app_conf("DEAL_PAGE_SIZE");
        $level_list = load_auto_cache("level");
        $cate_id = intval(base64_decode($GLOBALS['request']['cid']));
        $information_status = $GLOBALS['db']->getOne("select information_status from " . DB_PREFIX . "deal_loan_type where id=" . $type_id);

        //新加理财计划
        if ($type_id == 12) {

            $n_cate_id = 0;
            $condition = " publish_wait = 0 and is_effect = 1 and is_delete = 0 and cunguan_tag =1 and is_hidden = 0 AND (if(deal_status = 1, start_time + enddate*24*3600 > " . TIME_UTC . ",1=1))";
            $orderby = "deal_status ASC , sort DESC , id DESC";;
            if ($cate_id > 0) {
                $n_cate_id = $cate_id;
                //$condition .= "AND deal_status in(0,1)";
                $orderby = "deal_status ASC , sort DESC , id DESC";
            } else {
                $n_cate_id = 0;
                $orderby = "deal_status ASC , sort DESC , id DESC";
            }
            if ($keywords) {
                $kw_unicode = str_to_unicode_string($keywords);
                $condition .= " and (match(name_match,deal_cate_match,tag_match,type_match) against('" . $kw_unicode . "' IN BOOLEAN MODE))";
            }
            if ($level > 0) {
                $point = $level_list['point'][$level];
                $condition .= " AND user_id in(SELECT u.id FROM " . DB_PREFIX . "user u LEFT JOIN " . DB_PREFIX . "user_level ul ON ul.id=u.level_id WHERE ul.point >= $point)";
            }
            if ($interest > 0) {
                $condition .= " AND rate >= " . $interest;
            }
            if ($months > 0) {
                $condition .= " AND repay_time = " . $months;
            }
            if ($type_id > 0 && $type_id < 12) {
                $condition .= " AND type_id = " . $type_id;
            }
            if ($lefttime > 0) {
                $condition .= " AND (start_time + enddate*24*3600 - " . TIME_UTC . ") <= " . $lefttime * 24 * 3600;
            }
            if ($deal_status > 0) {
                $condition .= " AND deal_status = " . $deal_status;
            }
            if ((int)app_conf("SHOW_EXPRIE_DEAL") == 0) {
                $condition .= " AND (if(deal_status = 1, start_time + enddate*24*3600 > " . TIME_UTC . ",1=1)) ";
            }


            //$result = get_deal_list($limit, $n_cate_id, $condition, $orderby);
            $result = get_plandeal_list($limit, $n_cate_id, $condition, $orderby);

            /*******出借列表icon图标*******/
            foreach ($result ['list'] as $k => $v) {
                //取出符合理财计划的标的
                $all_deals = $GLOBALS['db']->getAll("select id from " . DB_PREFIX . "deal where plan_id =" . $v['id']);
                $sy_money = 0;//投资金额
                foreach ($all_deals as $key => $value) {
                    if ($all_deals) {
                        $sy_money += $GLOBALS['db']->getOne("select SUM(total_money) from " . DB_PREFIX . "deal_load where deal_id=" . $value['id']);
                    }
                }
                $need_money_re = $v['borrow_amount'] - $sy_money;//如果表里有记录，可以换个方法

                $group = intval($GLOBALS['db']->getOne(" select id from " . DB_PREFIX . "icon_group where name = '投资' "));
                $icon = $GLOBALS['db']->getOne(" select img from " . DB_PREFIX . "icon where is_effect = 1 and group_id =" . $group);
                /*******出借列表icon图标结束*******/
                // $result ['list'][$k]['img'] = get_abs_img_root(get_spec_image($icon, 0, 0, 1));
                $result ['list'][$k]['rate'] = sprintf("%.1f", $v["rate"]);//利率+加息利率
                if (!empty($v['interest_rate']) && $v['interest_rate'] != 0) {
                    $result ['list'][$k]['interest_rate'] = sprintf("%.1f", $v["interest_rate"]);//利率+加息利率
                } else {
                    unset($result ['list'][$k]['interest_rate']);
                }
                /* $cate_info_icon = get_abs_wap_url_root(get_abs_img_root($result ['list'][$k]['cate_info']['icon']));
                 $result ['list'][$k]['cate_info']['icon'] = $cate_info_icon;*/
                $progress_point = ($sy_money / $v['borrow_amount'] * 100);
                if ($progress_point >= 100) {
                    $result['list'][$k]['progress_point'] = '100';
                } else {
                    $result['list'][$k]['progress_point'] = substr_replace($progress_point, '', strpos($progress_point, '.') + 2);            //借款进度
                }

                //$result ['list'][$k]['need_money'] = FormatMoney(intval($v['need_money']));
                if ($need_money_re >= 10000) {
                    $result ['list'][$k]['need_money'] = is_float($need_money_re / 10000) ? substr_replace($need_money_re / 10000, '', strpos($need_money_re / 10000, '.') + 3) . '万' : $need_money_re / 10000 . '万';
                } elseif ($need_money_re < 0) {
                    $result ['list'][$k]['need_money'] = '0';
                } else {
                    $result ['list'][$k]['need_money'] = intval($need_money_re);
                }
                $result ['list'][$k]['name'] = strval($v['name']);
                $result ['list'][$k]['information_status'] = 1;
                $result ['list'][$k]['id'] = $v['id'];
            }

            $root = get_baseroot();
            $root['response_code'] = 1;
            $root['now_time'] = time();
            //$root['item'] = $result['list'];


            if ($months > 0) {
                $root['item'] = $result['list'];
            } else {
                $root['item'] = $result['list'];
            }

            $root['rs_count'] = $result['count'];
            $root['page'] = array("page" => $page, "page_total" => ceil($result['count'] / app_conf("DEAL_PAGE_SIZE")), "page_size" => app_conf("DEAL_PAGE_SIZE"));
            $root['webview'][] = $GLOBALS['db']->getRow("select name,height,is_effect,url from " . DB_PREFIX . "app_web_view where name='投资'");
            $root['program_title'] = "出借列表";
            output($root);
        } else if($type_id == 15) {
            $n_cate_id = 0;
            $condition = " publish_wait = 0 and is_effect = 1  and is_delete = 0 and cunguan_tag =1 and is_hidden = 0 AND (if(deal_status = 1, start_time + enddate*24*3600 > " . TIME_UTC . ",1=1))";
            $orderby = "";
            if ($cate_id > 0) {
                $n_cate_id = $cate_id;
                //$condition .= "AND deal_status in(0,1)";
                $orderby = "deal_status ASC , sort DESC , id DESC";
            } else {
                $n_cate_id = 0;
                $orderby = "deal_status ASC , sort DESC , id DESC";
            }
            if ($keywords) {
                $kw_unicode = str_to_unicode_string($keywords);
                $condition .= " and (match(name_match,deal_cate_match,tag_match,type_match) against('" . $kw_unicode . "' IN BOOLEAN MODE))";
            }
            if ($level > 0) {
                $point = $level_list['point'][$level];
                $condition .= " AND user_id in(SELECT u.id FROM " . DB_PREFIX . "user u LEFT JOIN " . DB_PREFIX . "user_level ul ON ul.id=u.level_id WHERE ul.point >= $point)";
            }
            if ($interest > 0) {
                $condition .= " AND rate >= " . $interest;
            }
            if ($months > 0) {
                $condition .= " AND repay_time = " . $months;
            }
            if ($type_id > 0 && $type_id < 12) {
                $condition .= " AND type_id = " . $type_id;
            }
            if ($lefttime > 0) {
                $condition .= " AND (start_time + enddate*24*3600 - " . TIME_UTC . ") <= " . $lefttime * 24 * 3600;
            }
            if ($deal_status > 0) {
                $condition .= " AND deal_status = " . $deal_status;
            }
            if ((int)app_conf("SHOW_EXPRIE_DEAL") == 0) {
                $condition .= " AND (if(deal_status = 1, start_time + enddate*24*3600 > " . TIME_UTC . ",1=1)) ";
            }


            $result = get_deal_listfood($limit, $n_cate_id, $condition, $orderby);
          if($result){
              $root['response_code']=1;
          }
            /*******出借列表icon图标*******/
            foreach ($result ['list'] as $k => $v) {

                if ($v['is_new'] == 1 && $v['is_hot'] == 0) {
                    if ($v['need_money'] == 0) {
                        $sort = 2;
                    } else {
                        $sort = 1;
                    }
                } else if ($v['is_new'] == 0 && $v['is_hot'] == 1) {
                    if ($v['need_money'] == 0) {
                        $sort = 4;
                    } else {
                        $sort = 3;
                    }
                } else if ($v['is_new'] == 0 && $v['is_hot'] == 0) {
                    $sort = 0;
                } else if ($v['is_new'] == 0 && $v['publish_wait'] == 1) {
                    if ($v['need_money'] == 0) {
                        $sort = 17;
                    } else {
                        $sort = 17;
                    }
                } else if ($v['is_new'] == 0 && $v['is_advance'] == 1) {
                    if ($v['need_money'] == 0) {
                        $sort = 18;
                    } else {
                        $sort = 18;
                    }
                }

                if ($v['is_advance'] == 1) {
                    if ($v['need_money'] == 0) {
                        $sort = 19;
                    } else {
                        $sort = 18;
                    }

                }
                $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from " . DB_PREFIX . "deal_load where deal_id=" . $v['id']);
                $need_money_re = $v['borrow_amount'] - $need_money;
                $group = intval($GLOBALS['db']->getOne(" select id from " . DB_PREFIX . "icon_group where name = '投资' "));
                $icon = $GLOBALS['db']->getOne(" select img from " . DB_PREFIX . "icon where is_effect = 1 and group_id =" . $group . " and sort=" . $sort);
                /*******出借列表icon图标结束*******/
                $result ['list'][$k]['img'] = get_abs_img_root(get_spec_image($icon, 0, 0, 1));
                $result ['list'][$k]['rate'] = sprintf("%.1f", $v["rate"]);//利率+加息利率
                if (!empty($v['interest_rate']) && $v['interest_rate'] != 0) {
                    $result ['list'][$k]['interest_rate'] = sprintf("%.1f", $v["interest_rate"]);//利率+加息利率
                } else {
                    unset($result ['list'][$k]['interest_rate']);
                }
                $cate_info_icon = get_abs_wap_url_root(get_abs_img_root($result ['list'][$k]['cate_info']['icon']));
                $result ['list'][$k]['cate_info']['icon'] = $cate_info_icon;
                $progress_point = ($need_money / $v['borrow_amount'] * 100);
                if ($progress_point >= 100) {
                    $result['list'][$k]['progress_point'] = '100';
                } else {
                    $result['list'][$k]['progress_point'] = substr_replace($progress_point, '', strpos($progress_point, '.') + 2);            //借款进度
                }

                //$result ['list'][$k]['need_money'] = FormatMoney(intval($v['need_money']));
                if ($need_money_re >= 10000) {
                    $result ['list'][$k]['need_money'] = is_float($need_money_re / 10000) ? substr_replace($need_money_re / 10000, '', strpos($need_money_re / 10000, '.') + 3) . '万' : $need_money_re / 10000 . '万';
                } elseif ($need_money_re < 0) {
                    $result ['list'][$k]['need_money'] = '0';
                } else {
                    $result ['list'][$k]['need_money'] = intval($need_money_re);
                }

                /*if(mb_strlen($v['name'],'utf8')>10){
                    $result ['list'][$k]['name'] = cut_str(strval($v['name']),15).'...';
                    $result ['list'][$k]['sub_name'] = cut_str(strval($v['sub_name']),15).'...';
                }else{*/
                $result ['list'][$k]['name'] = strval($v['name']);
                $result ['list'][$k]['information_status'] = $information_status;
                $result ['list'][$k]['sub_name'] = strval($v['sub_name']);
                //}

                $result ['list'][$k]['id'] = $v['id'];
                if ($v['debts'] == 1) {
                    $last_repay_time = $GLOBALS['db']->getOne("select repay_time from " . DB_PREFIX . "deal_repay where deal_id =" . $v['old_deal_id'] . " order by repay_time desc limit 1");
                    $result['list'][$k]['repay_time'] = ((strtotime(date("Y-m-d", $last_repay_time)) - strtotime(date("Y-m-d", time()))) / 3600 / 24) + 1;
                }
            }

        if ($months > 0) {
            $root['item'] = $result['list'];

        } else {
            $root['item'] = $result['list'];
        }

        $root['rs_count'] = $result['count'];
        $root['page'] = array("page" => $page, "page_total" => ceil($result['count'] / app_conf("DEAL_PAGE_SIZE")), "page_size" => app_conf("DEAL_PAGE_SIZE"));
        $root['webview'][] = $GLOBALS['db']->getRow("select name,height,is_effect,url from " . DB_PREFIX . "app_web_view where name='投资'");
        $root['program_title'] = "出借列表";
        output($root);
     }else{
            $n_cate_id = 0;
            $condition = " publish_wait = 0 and is_effect = 1 and type_id <>12 and is_delete = 0 and cunguan_tag =1 and is_hidden = 0 AND (if(deal_status = 1, start_time + enddate*24*3600 > " . TIME_UTC . ",1=1))";
            $orderby = "";
            if ($cate_id > 0) {
                $n_cate_id = $cate_id;
                //$condition .= "AND deal_status in(0,1)";
                $orderby = "deal_status ASC , sort DESC , id DESC";
            } else {
                $n_cate_id = 0;
                $orderby = "deal_status ASC , sort DESC , id DESC";
            }
            if ($keywords) {
                $kw_unicode = str_to_unicode_string($keywords);
                $condition .= " and (match(name_match,deal_cate_match,tag_match,type_match) against('" . $kw_unicode . "' IN BOOLEAN MODE))";
            }
            if ($level > 0) {
                $point = $level_list['point'][$level];
                $condition .= " AND user_id in(SELECT u.id FROM " . DB_PREFIX . "user u LEFT JOIN " . DB_PREFIX . "user_level ul ON ul.id=u.level_id WHERE ul.point >= $point)";
            }
            if ($interest > 0) {
                $condition .= " AND rate >= " . $interest;
            }
            if ($months > 0) {
                $condition .= " AND repay_time = " . $months;
            }
            if ($type_id > 0 && $type_id < 12) {
                $condition .= " AND type_id = " . $type_id;
            }
            if ($lefttime > 0) {
                $condition .= " AND (start_time + enddate*24*3600 - " . TIME_UTC . ") <= " . $lefttime * 24 * 3600;
            }
            if ($deal_status > 0) {
                $condition .= " AND deal_status = " . $deal_status;
            }
            if ((int)app_conf("SHOW_EXPRIE_DEAL") == 0) {
                $condition .= " AND (if(deal_status = 1, start_time + enddate*24*3600 > " . TIME_UTC . ",1=1)) ";
            }


            $result = get_deal_list($limit, $n_cate_id, $condition, $orderby);

            /*******出借列表icon图标*******/
            foreach ($result ['list'] as $k => $v) {

                if ($v['is_new'] == 1 && $v['is_hot'] == 0) {
                    if ($v['need_money'] == 0) {
                        $sort = 2;
                    } else {
                        $sort = 1;
                    }
                } else if ($v['is_new'] == 0 && $v['is_hot'] == 1) {
                    if ($v['need_money'] == 0) {
                        $sort = 4;
                    } else {
                        $sort = 3;
                    }
                } else if ($v['is_new'] == 0 && $v['is_hot'] == 0) {
                    $sort = 0;
                } else if ($v['is_new'] == 0 && $v['publish_wait'] == 1) {
                    if ($v['need_money'] == 0) {
                        $sort = 17;
                    } else {
                        $sort = 17;
                    }
                } else if ($v['is_new'] == 0 && $v['is_advance'] == 1) {
                    if ($v['need_money'] == 0) {
                        $sort = 18;
                    } else {
                        $sort = 18;
                    }
                }

                if ($v['is_advance'] == 1) {
                    if ($v['need_money'] == 0) {
                        $sort = 19;
                    } else {
                        $sort = 18;
                    }

                }
                $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from " . DB_PREFIX . "deal_load where deal_id=" . $v['id']);
                $need_money_re = $v['borrow_amount'] - $need_money;
                $group = intval($GLOBALS['db']->getOne(" select id from " . DB_PREFIX . "icon_group where name = '投资' "));
                $icon = $GLOBALS['db']->getOne(" select img from " . DB_PREFIX . "icon where is_effect = 1 and group_id =" . $group . " and sort=" . $sort);
                /*******出借列表icon图标结束*******/
                $result ['list'][$k]['img'] = get_abs_img_root(get_spec_image($icon, 0, 0, 1));
                $result ['list'][$k]['rate'] = sprintf("%.1f", $v["rate"]);//利率+加息利率
                if (!empty($v['interest_rate']) && $v['interest_rate'] != 0) {
                    $result ['list'][$k]['interest_rate'] = sprintf("%.1f", $v["interest_rate"]);//利率+加息利率
                } else {
                    unset($result ['list'][$k]['interest_rate']);
                }
                $cate_info_icon = get_abs_wap_url_root(get_abs_img_root($result ['list'][$k]['cate_info']['icon']));
                $result ['list'][$k]['cate_info']['icon'] = $cate_info_icon;
                $progress_point = ($need_money / $v['borrow_amount'] * 100);
                if ($progress_point >= 100) {
                    $result['list'][$k]['progress_point'] = '100';
                } else {
                    $result['list'][$k]['progress_point'] = substr_replace($progress_point, '', strpos($progress_point, '.') + 2);            //借款进度
                }

                //$result ['list'][$k]['need_money'] = FormatMoney(intval($v['need_money']));
                if ($need_money_re >= 10000) {
                    $result ['list'][$k]['need_money'] = is_float($need_money_re / 10000) ? substr_replace($need_money_re / 10000, '', strpos($need_money_re / 10000, '.') + 3) . '万' : $need_money_re / 10000 . '万';
                } elseif ($need_money_re < 0) {
                    $result ['list'][$k]['need_money'] = '0';
                } else {
                    $result ['list'][$k]['need_money'] = intval($need_money_re);
                }

                /*if(mb_strlen($v['name'],'utf8')>10){
                    $result ['list'][$k]['name'] = cut_str(strval($v['name']),15).'...';
                    $result ['list'][$k]['sub_name'] = cut_str(strval($v['sub_name']),15).'...';
                }else{*/
                $result ['list'][$k]['name'] = strval($v['name']);
                $result ['list'][$k]['information_status'] = $information_status;
                $result ['list'][$k]['sub_name'] = strval($v['sub_name']);
                //}

                $result ['list'][$k]['id'] = $v['id'];
                if ($v['debts'] == 1) {
                    $last_repay_time = $GLOBALS['db']->getOne("select repay_time from " . DB_PREFIX . "deal_repay where deal_id =" . $v['old_deal_id'] . " order by repay_time desc limit 1");
                    $result['list'][$k]['repay_time'] = ((strtotime(date("Y-m-d", $last_repay_time)) - strtotime(date("Y-m-d", time()))) / 3600 / 24) + 1;
                }
            }
        }


        //体验金的标
        $conditis = '';
        $conditis .= " cunguan_tag =1 and publish_wait=1 and deal_status in (1)";
        $result_treetop = experience_treetop($limit, $n_cate_id, $conditis, $orderby);

        // $root['item']= $result_treetop;
        // output($root);
        /*******出借列表icon图标*******/
        foreach ($result_treetop ['list'] as $k => $v) {

            /*
            if($v['is_new'] == 1 && $v['is_hot'] == 0){
                if($v['need_money'] == 0){
                    $sort = 2;
                }else{
                    $sort = 1;
                }
            }else if($v['is_new'] == 0 && $v['is_hot'] == 1){
                if($v['need_money'] == 0){
                    $sort = 4;
                }else{
                    $sort = 3;
                }
            }else if($v['is_new'] == 0 && $v['is_hot'] == 0){
                $sort = 0;
            }*/
            if ($v['need_money'] == 0) {
                $sort = 20;
            } else {
                $sort = 17;
            }

            $need_money = $GLOBALS['db']->getOne("select SUM(total_money) from " . DB_PREFIX . "experience_deal_load where deal_id=" . $v['id']);
            $need_money_re = $v['borrow_amount'] - $need_money;
            $group = intval($GLOBALS['db']->getOne(" select id from " . DB_PREFIX . "icon_group where name = '投资' "));
            $icon = $GLOBALS['db']->getOne(" select img from " . DB_PREFIX . "icon where is_effect = 1 and group_id =" . $group . " and sort=" . $sort);
            /*******出借列表icon图标结束*******/
            $result_treetop ['list'][$k]['img'] = get_abs_img_root(get_spec_image($icon, 0, 0, 1));
            $result_treetop ['list'][$k]['rate'] = sprintf("%.1f", $v["rate"]);//利率+加息利率
            if ($v['interest_rate'] > 0) {
                $result_treetop ['list'][$k]['interest_rate'] = sprintf("%.1f", $v["interest_rate"]);//利率+加息利率
            }
            $cate_info_icon = get_abs_wap_url_root(get_abs_img_root($result ['list'][$k]['cate_info']['icon']));
            $result_treetop ['list'][$k]['cate_info']['icon'] = $cate_info_icon;
            $progress_point = ($need_money / $v['borrow_amount'] * 100);
            if ($progress_point >= 100) {
                $result_treetop['list'][$k]['progress_point'] = '100';
            } else {
                $result_treetop['list'][$k]['progress_point'] = substr_replace($progress_point, '', strpos($progress_point, '.') + 2);            //借款进度
            }

            //$result ['list'][$k]['need_money'] = FormatMoney(intval($v['need_money']));
            if ($need_money_re >= 10000) {
                $result_treetop ['list'][$k]['need_money'] = is_float($need_money_re / 10000) ? substr_replace($need_money_re / 10000, '', strpos($need_money_re / 10000, '.') + 3) . '万' : $need_money_re / 10000 . '万';
            } elseif ($need_money_re < 0) {
                $result_treetop ['list'][$k]['need_money'] = '0';
            } else {
                $result_treetop ['list'][$k]['need_money'] = intval($need_money_re);
            }

            /*if(mb_strlen($v['name'],'utf8')>10){
                $result ['list'][$k]['name'] = cut_str(strval($v['name']),15).'...';
                $result ['list'][$k]['sub_name'] = cut_str(strval($v['sub_name']),15).'...';
            }else{*/
            $result_treetop ['list'][$k]['name'] = strval($v['name']);
            $result_treetop ['list'][$k]['sub_name'] = strval($v['sub_name']);
            //}

            $result_treetop ['list'][$k]['id'] = $v['id'];
        }


        $root = get_baseroot();
        $root['response_code'] = 1;
        $root['now_time'] = time();
        //$root['item'] = $result['list'];

        if ($months > 0) {
            $root['item'] = $result['list'];
        } else {
            $root['item'] = array_merge_recursive($result_treetop['list'], $result['list']);
        }

        $root['rs_count'] = $result['count'];
        $root['page'] = array("page" => $page, "page_total" => ceil($result['count'] / app_conf("DEAL_PAGE_SIZE")), "page_size" => app_conf("DEAL_PAGE_SIZE"));
        $root['webview'][] = $GLOBALS['db']->getRow("select name,height,is_effect,url from " . DB_PREFIX . "app_web_view where name='投资'");
        $root['program_title'] = "出借列表";
        output($root);


    }

}
?>
