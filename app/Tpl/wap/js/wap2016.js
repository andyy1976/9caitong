//wap2016.js
$(function(){
	//banner轮播
    TouchSlide({ 
        slideCell:"#banner",
        titCell:".ban-btn ul",
        mainCell:".ban-img ul", 
        effect:"leftLoop", 
        interTime:"4000",
        delayTime:"500",
        autoPage:true,
        autoPlay:true
    });
    //公告滚动
    $("#noticeList").kxbdMarquee({isEqual:false});
    //数据滚动
    var registUser = deleteSign($("#registUser0").html(),"");
    var totalInvest = deleteSign($("#registUser1").html(),"");
    var dataRegistUser = new CountUp("registUser0", 0, Number(registUser), 0, 1.5);
    var dataTtotalInvest = new CountUp("registUser1", 0, Number(totalInvest), 0, 1.5);
    $(window).scroll(function(){
        if($(window).scrollTop()>=140){
            dataRegistUser.start();
            dataTtotalInvest.start();
        }
    })
    //弧形进度
    $(".arch-progress").gaugeMeter();
});
//通过正则去掉数据中的“,”
function deleteSign(str,signB){
    return str.replace(/,/g,signB);
}
//活动弹出层
function popBox(){
    var popBox = document.getElementById('popBox');
    var popClose = document.getElementById('popClose');
    var popBg = document.getElementById('popBg');
    //弹出层
    popBg.style.display = popBox.style.display = 'block';
    popBoxPos();
    popBgStyle();
    //当页面滚动的时候，弹出框跟随滚动
    window.onscroll = function() {
        popBoxPos();
        popBgStyle();
    }
    //当浏览器窗口大小改变的时候，弹出框的位置随着变化
    window.onresize = function() {
        popBoxPos();
        popBgStyle();
    }
    //设置弹出框的位置
    function popBoxPos(){
        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        var scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
        popBox.style.left = ( document.documentElement.clientWidth - popBox.offsetWidth ) / 2 + scrollLeft + 'px';
        popBox.style.top = ( document.documentElement.clientHeight - popBox.offsetHeight ) / 2 + scrollTop + 'px';
    }
    //设置灰色背景的宽高
    function popBgStyle(){
        popBg.style.width = Math.max(document.documentElement.scrollWidth, document.body.scrollWidth) + "px";
        popBg.style.height = Math.max(document.documentElement.clientHeight, document.body.scrollHeight) + "px";
    }
    //关闭弹出框
    popClose.onclick = function(){
        popBg.style.display = popBox.style.display = 'none';
    }
}
