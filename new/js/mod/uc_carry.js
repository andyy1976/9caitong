define("new/js/mod/uc_carry",["new/css/layer.css","$","layer","new/js/mod/util/FW_Password","new/js/mod/util/lang"],function(a){function r(){var a=0,r=n("#ips_carry_form :checked").val();if("1"==r)var t=parseFloat(n("#Jcarry_old_money").val())-parseFloat(n("#Jcarry_nmc_amount").val());else if("2"==r)var t=parseFloat(n("#Jcarry_recharge_money").val())-parseFloat(n("#Jcarry_nmc_amount").val());if(n.trim(n("#carry_money").val()).length>0&&(isNaN(n("#carry_money").val())?(n("#carry_money").val(0),a=0):a=parseFloat(n("#carry_money").val())),a=parseFloat(a),0>a)return n("#r_carry_tips").html("请输入正确金额"),!1;if(a>o)return n("#r_carry_tips").html("您的账户余额不足"),!1;n("#r_carry_tips").html("");var l=0,s=0;json_fee.length>0&&(a>=json_fee[json_fee.length-1].max_price?(l=json_fee[json_fee.length-1].fee,s=json_fee[json_fee.length-1].fee_type):n.each(json_fee,function(r,e){a>=e.min_price&&a<=e.max_price&&(l=e.fee,s=e.fee_type)})),l=parseFloat(l),1==s&&(l=a*l*.01),a+l>t&&n("#r_carry_tips").html("您的账户余额不足"),n("#carry_fee").html(e(l,2)+" 元");var c=t-a-l;n("#Jcarry_acount_balance_res").val(e(c,2))}function e(a,r){r=r>0&&20>=r?r:2,a=parseFloat((a+"").replace(/[^\d\.-]/g,"")).toFixed(r)+"";var e=a.split(".")[0].split("").reverse(),o=a.split(".")[1];for(t="",i=0;i<e.length;i++)t+=e[i]+((i+1)%3==0&&i+1!=e.length?",":"");var n=t.split("").reverse().join("")+"."+o;return n.replace("-,","-")}a("new/css/layer.css");var o,n=a("$"),l=a("layer"),s=a("new/js/mod/util/FW_Password").FW_Password,c=a("new/js/mod/util/lang").lang,_=0;n.ajax({url:"/index.php?ctl=uc_money&act=ajaxwithdrawalamount",type:"POST",dataType:"json",success:function(a){a.withdrawalamount?(_=parseFloat(a.withdrawalamount),0==n("#carry_A_1").length?(n("#carry_money").attr({placeholder:"可提现金额"+_+"元"}),o=_):o=parseFloat(n("#oldMoney").val())):_=0}}),n(document).ready(function(){n("#carry_money").val(""),n("#ips_carry_form :radio").click(function(){n("#carry_money").val(""),"1"==n(this).val()?(n("#carray_paypwd").show(),n("#ordinary_bank").show(),n("#dep_bank").hide(),n("#carry_money").attr({placeholder:"可提现金额"+n("#oldMoney").val()+"元"}),o=parseFloat(n("#oldMoney").val())):(n("#carray_paypwd").hide(),n("#ordinary_bank").hide(),n("#dep_bank").show(),n("#carry_money").attr({placeholder:"可提现金额"+_+"元"}),o=_)})}),n("#carry_money").keyup(function(){r()}),n("#carry_money").blur(function(){n(this).val(isNaN(n(this).val())?"0":parseFloat(n(this).val()).toFixed(2)),r()}),n("#carry_money").focus(function(){0==n(this).val()&&n(this).val("")}),n("#carry_done").click(function(){if(n(this).hasClass("btn_disable"))return!1;if(""==n("#carry_money").val()||isNaN(n("#carry_money").val())||!(n("#carry_money").val()>0))return l.tips("请输入正确的提现金额","#carry_money",{tips:1}),n("#carry_money").focus(),!1;if(parseFloat(n.trim(n("#carry_money").val()))>o)return l.tips("可提现金额不足","#carry_money",{tips:1}),n("#carry_money").focus(),!1;if("1"==n("#ips_carry_form :checked").val()){if(""==n.trim(n("#J_PAYPASSWORD").val()))return l.tips(c.EMPTY_PAY_PWD,"#J_PAYPASSWORD",{tips:1}),n("#J_PAYPASSWORD").focus(),!1;carry_mode="1"}else carry_mode="2";n(this).addClass("btn_disable"),l.load(1);var a=n("#ips_carry_form").attr("ajax_url"),r=new Object;r.carry_money=n("#carry_money").val(),r.amount=n("#carry_money").val(),paypassword=n("#J_PAYPASSWORD").val(),r.paypassword=s(paypassword,n("#LOGIN_KEY").val()),r.withdraw_acc=carry_mode,r.bid=n("#list_id").val(),r.bid="1"==carry_mode?n("#list_id").val():n("#cg_list_id").val(),n.ajax({url:a,data:r,type:"post",dataType:"json",success:function(a){l.closeAll("loading"),1==a.status?l.alert(a.info,{title:"成功",icon:1,closeBtn:0},function(){n("#carry_done").removeClass("btn_disable"),window.location.href=a.jump}):2==a.status?window.location.href=a.jump:l.alert(a.info,{title:"提示",icon:5,closeBtn:0},function(){n("#carry_done").removeClass("btn_disable"),l.closeAll()})},error:function(){l.closeAll("loading"),l.alert(c.REQUEST_DATA_FAILED,{title:"错误",icon:2,closeBtn:0}),n("#carry_done").removeClass("btn_disable")}})})});