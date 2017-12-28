window.onload = function(){
	  var oTop = document.getElementById("topLiOne");
	  var screenw = document.documentElement.clientWidth || document.body.clientWidth;
	  var screenh = document.documentElement.clientHeight || document.body.clientHeight;
	  oTop.style.left = screenw - oTop.offsetWidth +"px";
	  oTop.style.top = screenh - oTop.offsetHeight + "px";
	  window.onscroll = function(){
		var scrolltop = document.documentElement.scrollTop || document.body.scrollTop;
		oTop.style.top = screenh - oTop.offsetHeight + scrolltop +"px";
	  }
	  oTop.onclick = function(){
		document.documentElement.scrollTop = document.body.scrollTop =0;
	  }
	
   
}  



$(function($){
      
	  function sys (){
			       var moneyCount = parseInt ( $(".sys_inp").val() );
				   //alert(moneyCount);
			       var jct = (moneyCount*0.065/12).toFixed(2);
				   var meb = (moneyCount*0.0364/12).toFixed(2);
				   var hqck = (moneyCount*0.0035/12).toFixed(2);
				   $('.jctyl').html(jct);
				   $(".mebyl").html(meb);
				   $(".hqtz").html(hqck);
				   $(".jctget").css('width','0px');
				   $(".mebget").css('width','0px');
				   $(".hqget").css('width','0px');
				   $(".jctget").animate({width:225});
				   $(".mebget").animate({width:140});
				   $(".hqget").animate({width:20});
			
		}
	  
	   $(".jibtn").click(function(){
		   var oLogin =$("<div id='alertBox'><p class='boxtitle'>收益计算器</p><span class='close_btn'><img src='new/activity/P_banner_register/images/close.png'/></span><div class='boxtop'><p class='jct'>玖财通</p><div class='jctget'><span class='jctyl'>54.17</span></div><p >某额宝</p><div class='mebget'><span class='mebyl'>30.33</span></div><p>银行活期</p><div class='hqget'><span class='hqtz'>2.92</span></div></div><p class='get-money'>投资金额（元）<input class='sys_inp' value='10000' type='text' /></p><p class='sys-btn'><input class='sys_btn' value='算一算' type='button' /></p></div>");
		   
		   
		    $("body").append(oLogin);
		    $(".sys_inp").OnlyNum();
			   oLogin.css("left" , ($(window).width() - oLogin.outerWidth() ) / 2);
			   oLogin.css("top" , ($(window).height() - oLogin.outerHeight() ) / 2);
			   
			    $(".close_btn").click(function yichu (){
			         $(oLogin).remove();
			   });
			 $("#ordinary").OnlyNum();   
		   $(".sys_btn").click(function(){
			   var moneyCount = parseInt ( $(".sys_inp").val() );
			   //alert(moneyCount);
			   if( 100 <= $('.sys_inp').val() && $('.sys_inp').val() <= 50000  ){
				   sys (); 
			    }else if(  $('.sys_inp').val()<100 ){
					$('.sys_inp').val(100);
					sys (); 	
					alert("最低投资金额为100元！");
				}else {
					$('.sys_inp').val(50000);
					sys (); 	
					alert("最高投资金额为50000元！");
				}

		  } );  
			   
			  $(window).scroll(function(){
			       
			       oLogin.css('top' , ($(window).height() - oLogin.outerHeight())/2 + $(window).scrollTop() );
		        });
		      $(window).ready(function(){
			       
			       oLogin.css('top' , ($(window).height() - oLogin.outerHeight())/2 + $(window).scrollTop() );
		        }); 
		   
	   } )
	  
	 
	  var $BtnLi = $(".problemclass");
	  var $BtnOl = $(".problem ol");
	  
		  $BtnLi.click(function(){
			 $BtnLi.children(".jiajian").html("+"); 
			var display =$(this).next().css('display');
			//alert(display);
			if(display == 'none'){
				$BtnOl.slideUp();
				
				
				$(this).next().slideDown();
				
				
				if( $(this).children(".jiajian").html()=='+' ){
					$(this).children(".jiajian").html("-");
				}else{
					$(this).children(".jiajian").html("+");
				}
			}else{
				$BtnOl.slideUp();
				
				$(this).children(".jiajian").html("+");
			}
					 
	 
	  })
	  
	  
	/* $("#topLiOne").click(function(){
		 alert(11);
		 alert($(".newTop").offset().bottom);
	    $(".newTop").animate({top:1000});
	 } )
	  */
	  
	  
	  
	  
	  
	  
	  
} );