  function myscroll(obj){
        var speed=50;
        var tab=$('#'+obj).find('.demo');
        var tab1=$('#'+obj).find('.demo1');
        var tab2=$('#'+obj).find(".demo2");
        var flag=true;
        if(tab1.children().length<8){
            flag=false;
        }
		if(flag){ tab2.html(tab1.html());}
        function Marquee(){
            
                if(tab2.get(0).offsetHeight-tab.get(0).scrollTop<=0)
                    tab.get(0).scrollTop-=tab1.get(0).offsetHeight;
                else{
                    tab.get(0).scrollTop++;
                }
        }
        var MyMar=setInterval(Marquee,speed);
        tab.onmouseover=function() {clearInterval(MyMar)};
        tab.onmouseout=function() {MyMar=setInterval(Marquee,speed)};
   }
   $(document).ready(function(e) {
     	myscroll('tab1-con');
		myscroll('tab2-con');
	});
	$('.tab-head').find('a').each(function(index, element) {
         $(this).click(function(){
			if(!$(this).hasClass('tab-select')){
				var now=index+1;
				var old;
				if(now==1){
					old=2;
				}else{
					old=1;
				}
				var show='tab'+now+'-con';
				var hidden='tab'+old+'-con';
				$('#'+'tab'+old).removeClass('tab-select');
				$(this).addClass('tab-select');
				$('#'+show).css('display','block');
				$('#'+hidden).css('display','none');
				
			}
		});
    });
  