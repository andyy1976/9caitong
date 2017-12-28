<?php


class bill_listModule  extends SiteBaseModule{

    public function index() {
        $file_path = "new/cunguan_log/JCT_TRADECHECK_20170623_001.txt";
        $error_data_path="new/cunguan_log/error_data.txt";
        $error_data = fopen($error_data_path,'w+');
        //文件总行数
        $count_line=$this->count_line($file_path);
        //逐行读取
        for($i=40;$i<=$count_line;$i++){
            $content=$this->get_line($file_path,$i);
            $arr_content=explode('^|',$content);
//            print_r($arr_content);die;
            $seqno=$arr_content[1];  //业务流水号  对账文件提供
            $type=$arr_content[4];   // 交易类型   对账文件提供
            switch($type){
                case "R01":   //代扣充值       业务流水，状态，日期，订单流水，金额，用户台账(借方)，银行卡
                    $jct_bill=$GLOBALS['db']->getRow("select pn.seqno,pn.is_paid as status,FROM_UNIXTIME(pn.create_time,'%Y%m%d') as create_date,pn.notice_sn,pn.money,u.accno as borrow_accno,ub.bankcard from ".DB_PREFIX."payment_notice pn left join ".DB_PREFIX."user u on pn.user_id=u.id left join ".DB_PREFIX."user_bank ub on pn.user_id=ub.user_id where seqno='".$seqno."'");
                    break;
                case "R02":   //网易充值
                    break;
                case "R03":    //营销充值:
                    break;
                case "R04":   //代偿充值
                    break;
                case "R05":   //费用充值
                    break;
                case "R06":   //垫资充值
                    break;
                case "R07":   //线下充值
                    break;
                case "W01":   //客户提现   (没有订单流水号uc.notice_sn)      业务流水，状态，日期，金额，用户台账(借方)，银行卡
                    $jct_bill=$GLOBALS['db']->getRow("select uc.seqno,uc.status,FROM_UNIXTIME(uc.create_time,'%Y%m%d') as create_date,uc.money,u.accno as borrow_accno,ub.bankcard from ".DB_PREFIX."user_carry uc left join ".DB_PREFIX."user u on uc.user_id=u.id left join ".DB_PREFIX."user_bank ub on uc.user_id=ub.user_id where seqno='".$seqno."'");

                    break;
                case "W02":   //营销提现
                    break;
                case "W03":   //代偿提现
                    break;
                case "W04":   //费用提现
                    break;
                case "W05":   //垫资提现
                    break;
                case "T01":   //投标      业务流水，日期，金额，用户台账(借方)，标的台账（贷方），银行卡
                    $jct_bill=$GLOBALS['db']->getRow("select dl.load_seqno as seqno,dl.deal_id,FROM_UNIXTIME(dl.create_time,'%Y%m%d') as create_date,dl.total_money as money,u.accno as borrow_accno,d.objectaccno as lender_accno from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."user u on dl.user_id=u.id left join ".DB_PREFIX."deal d on dl.deal_id=d.id where load_seqno='".$seqno."'");
                    break;
                case "T02":   //取消投标
                    break;
                case "T03":   //放款      日期，金额，发标人台账（贷方），标的台账（借方）
                    $jct_bill=$GLOBALS['db']->getRow("select FROM_UNIXTIME(d.repay_start_time,'%Y%m%d') as create_date,d.borrow_amount as money,u.accno as lender_accno,d.objectaccno as borrow_accno  from ".DB_PREFIX."deal d left join ".DB_PREFIX."user u on d.user_id=u.id where d.objectaccno='".$arr_content[5]."'");
                    break;
                case "T04":   //还款      业务流水号，日期，金额，发标人台账（借方），标的台账（贷方）
                    $jct_bill=$GLOBALS['db']->getRow("select dr.seqno,FROM_UNIXTIME(dr.true_repay_time,'%Y%m%d') as create_date,dr.repay_money as money,u.accno as borrow_accno,d.objectaccno as lender_accno  from ".DB_PREFIX."deal_repay dr left join ".DB_PREFIX."user u on dr.user_id=u.id left join ".DB_PREFIX."deal d on dr.deal_id=d.id where dr.seqno='".$seqno."'");
                    break;
                case "T05":   //出款
                    break;
            }
            if(empty($jct_bill)||!isset($jct_bill)){
                fwrite($error_data, $content);
            }elseif(isset($jct_bill['create_date'])&&$jct_bill['create_date']!=$content[0]){
                fwrite($error_data, $content);
            }elseif(isset($jct_bill['seqno'])&&$jct_bill['seqno']!=$content[1]){
                fwrite($error_data, $content);
            }elseif(isset($jct_bill['borrow_accno'])&&$jct_bill['borrow_accno']!=$content[5]){
                fwrite($error_data, $content);
            }elseif(isset($jct_bill['lender_accno'])&&$jct_bill['lender_accno']!=$content[6]){
                fwrite($error_data, $content);
            }elseif(isset($jct_bill['money'])&&$jct_bill['money']!=$content[8]){
                fwrite($error_data, $content);
            }elseif(isset($jct_bill['status'])&&$jct_bill['status']!=$content[13]){
                fwrite($error_data, $content);
            }

        }
        echo "对账完成，细节没处理，自动生成文件什么的
            流程已通，具体错误数据没测
            这个方式可以的话，在改吧改吧就行了，还有出账没对，提现没有订单流水";
    }

    function duizhang(){
        $con = file_get_contents(APP_ROOT_PATH."new/cunguan_log/JCT_TRADECHECK_20170622_001.txt");
        $con_arr = explode("\n",$con);
        foreach($con_arr as $key=>$value) {
            $user_arr = explode("^|", trim($value));//切数据
            switch($user_arr[4]){
                case "R01":
                case "R02":
                case "R03":
                case "R04":
                case "R05":
                case "R06":
                case "R07":
                    $payment[] = $user_arr;
                    break;
                case "W01":
                case "W02":
                case "W03":
                case "W04":
                case "W05":
                    $withdraw[] = $user_arr;
                    break;
                case "T01":
                case "T02":
                case "T03":
                case "T04":
                case "T05":
                case "T06":
                case "T07":
                case "T08":
                case "T09":
                case "T10":
                case "T11":
                case "T12":
                    $load[] = $user_arr;
                    break;
                default:
                    $counts = $user_arr;
            }
        }
        foreach($payment as $ka=>$va){//充值
            $pay = $GLOBALS['db']->getRow("select user_id,money,bank_id,is_paid,FROM_UNIXTIME(create_time,'%Y%m%d') as create_time from ".DB_PREFIX."payment_notice where seqno='".$va[1]."'and notice_sn ='".$va[2]."'");
            $accno = $GLOBALS['db']->getRow("select accno from ".DB_PREFIX."user where id=".$pay['user_id']);//用户存管账户
            if($pay&&$accno){
                if($accno==$va[6]&&$pay['create_time']==$va[0]){
					if($pay['money']!=$va[8]){//如果金额不相等
						file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_03.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
					}
                }else{//用户不匹配
					file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_02.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
				}
            }else{//用户不匹配
				file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_01.txt',implode("^|",$va)."^|01^|1".PHP_EOL,FILE_APPEND);
			} 
        } 
		  foreach($load as $ka=>$va){
            $load = $GLOBALS['db']->getRow("select dl.deal_id as deal_id,dl.user_id, u.accno as accno,total_money from ".DB_PREFIX."deal_load dl left join user u on id = dl.user_id where dl.load_seqno=".$va[1]."");
			$deal= $GLOBALS['db']->getRow("select ojectaccno,borrow_amount,red_money,ecv_money from ".DB_PREFIX."deal where id=".$load['deal_id']."");//用户存管账户
			if($va[4]=='T01'){
				 if($load&&$load['accno']&&$deal['objectaccno']){
					if($load['accno']==$va[5]&&$deal['objectaccno']==$va[6]){
						if($load['total_money']!=$va[8]){//如果金额不相等
							file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624.txt',implode("^|",$va)."^|01^|3",FILE_APPEND);
						}
					}else{//用户不匹配
						file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_001.txt',implode("^|",$va)."^|01^|3",FILE_APPEND);
					}
				} 
			}elseif($va[4]=='T10'){//虚拟货币
				if($load&&$load['accno']){
					if($accno==$va[5]){
						$sales_money =$deal['red_money']+$deal['ecv_money'];//虚拟货币总额
						if($sales_money!=$va[8]){//如果金额不相等
							file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624.txt',implode("^|",$va)."^|01^|3",FILE_APPEND);
						}
					}else{//用户不匹配
						file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_001.txt',implode("^|",$va)."^|01^|3",FILE_APPEND);
					}
				} 
			}elseif($va[4]=='T03'){//放款
				$deal_info = $GLOBALS['db']->getRow("select dr.deal_id as deal_id,dr.user_id, u.accno as accno,total_money from ".DB_PREFIX."deal dr left join user u on id = dr.user_id where dr.objectaccno='".$va[5]."'");
				if($deal['objectaccno']){
					if($deal_info['accno']==$va[5]){
						if($deal['borrow_amount']!=$va[8]){//如果金额不相等
							file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
						}
					}else{//用户不匹配
						file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_001.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
					}
				} 
			}elseif($va[4]=='T04'){//还款
				$dec = $GLOBALS['db']->getRow("select user_id,objectaccno,accno,dealAmount from ".DB_PREFIX."decository where seqno=".$va[1]."");
				if($dec['accno']==$va[5]&&$dec['objectaccno']==$va[6]){
					if($dec['dealAmount']!=$va[8]){//如果金额不相等
						file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
					}
				}else{//用户不匹配
					file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_001.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
				}
			}elseif($va[4]=='T05'){//出款
				if($load&&$load['accno']&&$deal['objectaccno']){
					if($load['accno']==$va[5]&&$deal['objectaccno']==$va[6]){
						if($deal['borrow_amount']!=$va[8]){//如果金额不相等
							file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
						}
					}else{//用户不匹配
						file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_001.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
					}
				} 
			}elseif($va[4]=='T08'){//代偿回款
				if($load&&$load['accno']&&$deal['objectaccno']){
					if($load['accno']==$va[5]&&$deal['objectaccno']==$va[6]){
						if($deal['borrow_amount']!=$va[8]){//如果金额不相等
							file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
						}
					}else{//用户不匹配
						file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_001.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
					}
				}
			}elseif($va[4]=='T09'){//代偿还款
				if($load&&$load['accno']&&$deal['objectaccno']){
					if($load['accno']==$va[5]&&$deal['objectaccno']==$va[6]){
						if($deal['borrow_amount']!=$va[8]){//如果金额不相等
							file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
						}
					}else{//用户不匹配
						file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_001.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
					}
				}
			}
            
        }  
		 foreach($withdraw as $ka=>$va){
            $withdraw = $GLOBALS['db']->getRow("select u.accno as accno,uc.money as money,FROM_UNIXTIME(u.update_time,'%Y%m%d') as withdraw_time from ".DB_PREFIX."user_carry uc left join user u on u.id =uc.user_id where seqno=".$va[1]."");
            if($withdraw){
                if($withdraw['accno']==$va[6]&&$withdraw['withdraw_time']==$va[0]){
					if($withdraw['money']!=$va[8]){//如果金额不相等
						file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
					}
                }else{//用户不匹配
					file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_001.txt',implode("^|",$va)."^|01^|3".PHP_EOL,FILE_APPEND);
				}
            }else{//不存在
				file_put_contents(APP_ROOT_PATH.'new/cunguan_log/jct_20170624_001.txt',implode("^|",$va)."^|01^|1".PHP_EOL,FILE_APPEND);
			}
        } 
        $payment_count = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."payment_notice where notice_sn!='' and cunguan_tag=1 and create_time between 1498060800 and 1498147199");
        $withdraw_count = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user_carry where cunguan_tag=1 and cunguan_pwd=1 and create_time between 1498060800 and 1498147199");
        $load_count = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."deal_load where cunguan_tag=1 and create_time between 1498060800 and 1498147199");
        echo $payment_count."<br>";
        echo count($payment)."<br>";
        echo $withdraw_count."<br>";
        echo count($withdraw)."<br>";
        echo $load_count."<br>";
        echo count($load)."<br>";
        die;
    }






    // 读取指定行txt文件内容
    public function get_line($file,$line) {
        $fp = fopen($file,'r');
        $i = 0;
        while(!feof($fp)) {
            $i++;
            $c = fgets($fp);
            if($i==$line) {
                return $c;
            }
        }
    }
    /*
 * 高效率计算文件行数
 * @author axiang
*/
    function count_line($file){
        $fp=fopen($file, "r");
        $i=0;
        while(!feof($fp)){
            //每次读取2M
            if($data=fread($fp,1024*1024*2)){
                //计算读取到的行数
                $num=substr_count($data,"\n");
                $i+=$num;
            }
        }
        fclose($fp);
        return $i;
    }
}