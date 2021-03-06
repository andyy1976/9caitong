$(function(){
    show_tip();
    total_price(".plan_table");
    bind_project_form();

    $(".plan_table").live('blur',function(){
        total_price(".plan_table");
    });

    // 添加下一个阶段
    $("#add_new_plan").bind("click",function(){
        var num=parseInt($("input[name='plan_step']").val())+1;
        $("input[name='plan_step']").val(num);
        $.ajax({
            'url':APP_ROOT+"/index.php?ctl=project_new&act=add_investor_item&num="+num+"&html=add_new_plan",
            'type':'POST',
            'dataType':'json',
            success:function(data){
                if(data.status==1){
                    $("#add_new_plan_tr").before(data.html);
                    bindKindeditor();
                }
            }
        });
    });

    // 删除阶段
    $(".plan_del").live('click',function(){
        var num=$(".plan_del").index($(this));
        $(".xm-content:eq("+num+")").remove();
        total_price(".plan_table");
    });
});

//选择日期控件
$("input.jcDate_1").jcDate({
    IcoClass : "jcDateIco",
    Event : "click",
    Speed : 100,
    Left :-125,
    Top : 28,
    format : "-",
    Timeout : 100,
    Oldyearall : 0,  // 配置过去多少年
    Newyearall : 3  // 配置未来多少年
});

function bind_project_form(){   
    $("#agencyAdd_stepfour_form").bind("submit",function(){
        var ajaxurl = $(this).attr("action");
        var query = $(this).serialize();
        $.ajax({ 
                url: ajaxurl,
                dataType: "json",
                data:query,
                type: "POST",
                success: function(ajaxobj){
                    if(ajaxobj.status)
                    {
                        $.showSuccess(ajaxobj.info,function(){
                             location.href = ajaxobj.jump;
                        });
                    }
                    else
                    {
                        if(ajaxobj.jump!="")
                            location.href = ajaxobj.jump;
                        else
                        $.showErr(ajaxobj.info);
                    }                       
                },
                error:function(ajaxobj)
                {
                    if(ajaxobj.responseText!='')
                    alert(ajaxobj.responseText);
                }
        });
        return false;
    });
    $("#ui-button").live("click",function(){
        if(!checkErrortime(".time_box","input[rel='begin_time']","input[rel='end_time']")){
            $("#agencyAdd_stepfour_form").submit();
        }
    });
}

/*
阶段开始结束时间控制
	time_box:外围盒子
	begin_time:开始时间元素
	end_time:结束时间元素
*/
function checkErrortime(time_box,begin_time,end_time){
    var is_errortime=false;
    function isErrortime(){
        $(time_box).each(function(i){
            var $obj=$(this) ,
    			begin_time_val=$obj.find(begin_time).val() ,
            	end_time_val=$obj.find(end_time).val() ,
            	begin_time_arr = begin_time_val.split("-") ,
            	new_begin_time = new Date(begin_time_arr[0], begin_time_arr[1], begin_time_arr[2]) ,
            	new_begin_times = new_begin_time.getTime() ,
            	end_time_arr = end_time_val.split("-") ,
            	new_end_time = new Date(end_time_arr[0], end_time_arr[1], end_time_arr[2]) ,
            	new_end_times = new_end_time.getTime();
            if (new_begin_times >= new_end_times) {
                $.showErr("开始时间不能大于结束时间");
                is_errortime=true;
                $(begin_time).eq(i).focus();
                return false;
            }
            else{
                is_errortime=false;
                return true;
            }
        });
    }
    isErrortime();
    return is_errortime;
}