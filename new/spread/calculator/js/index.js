$(function(){
	
	
	
	var $btn2 = $('.closeBtn');   //页面顶部关闭按钮
	var $btl = $('.top-box');         //页面顶部div
	$btn2.click(function(){            //移除页面顶部div
		$btl.remove();
		$btn2.remove();
	});
	
	
	$("#ordinary").OnlyNum();
    $("#ordinaryT").OnlyNum();
	
	

	
	//加减按钮
	var $cashMoney = parseInt( $('.cashMoney').val() );   //获取代金券得值 
	var $invMoney = parseInt( $('.invMoney').val() ) ;       //获取投资得值
	
	var $InvBtn = $('.Inv-btn');        //减号按钮
	var $InvBtnTwo = $('.Inv-btnTwo');  //加号按钮
	function btnColor() {                             //起投金额为1000得按钮颜色
		if(parseInt( $('.invMoney').val()) <= 1000 )
		{
			$('.jian').css({'display':'block'});
			$('.jian1').css({'display':'none'});
		}else{
			$('.jian').css({'display':'none'});
			$('.jian1').css({'display':'block'});
		}
	}
	function btnColor2() {                           //起投金额为100得按钮颜色
		if(parseInt( $('.invMoney').val()) <= 100 )
		{
			$('.jian').css({'display':'block'});
			$('.jian1').css({'display':'none'});
		}else{
			$('.jian').css({'display':'none'});
			$('.jian1').css({'display':'block'});
		}
	}
	
		

		//减按钮
		$InvBtn.click(function (){ 
		    if(  $('.monCunt').val() != 1 ){                          //判断是否是一月，不是一月减1000
        		if( parseInt( $('.invMoney').val() ) >= 2000 ){	
					$('.invMoney').val( parseInt($('.invMoney').val())-1000 );
					btnColor();
				}else if( ( parseInt( $('.invMoney').val() ) -1000 ) < 1000  || isNaN( parseInt( $('.invMoney').val() ) ) ){    //小于1000提示     
							    btnColor(); 
								$('.invMoney').val( 1000 ); 
								$("#Intme").show();
								setTimeout("$('#Intme').hide()",2000);
				}
				
		        $('#Profit').html( ( parseInt($('.invMoney').val())  * parseFloat($('#lvshu').html())/100/12* parseInt($('.monCunt').val() )).toFixed(2) );     //从新计算收获得金额
			 }  else {                                              //判断是否是一月，是一月减100
				 if( parseInt( $('.invMoney').val() ) >= 200 ){	
				
						$('.invMoney').val( parseInt($('.invMoney').val())-100 );
						btnColor2();
				   }else if( ( parseInt( $('.invMoney').val() ) -100 ) < 100  || isNaN( parseInt( $('.invMoney').val() ) ) ){        //小于100提示
								$('#Intme').html("起投金额为100元")
							    btnColor();
								$('.invMoney').val( 100 );
								$("#Intme").show();
								setTimeout("$('#Intme').hide()",2000);
				   }
		       $('#Profit').html( ( parseInt($('.invMoney').val())  * parseFloat($('#lvshu').html())/100/12* parseInt($('.monCunt').val() )).toFixed(2) );    //从新计算收获得金额
				 
			 }
		});	
			
		
		//加按钮
		$InvBtnTwo.click(function(){ 
		    if(  $('.monCunt').val() != 1 ){                           //判断是否是一月，是一月加1000
		        if(  isNaN( parseInt( $('.invMoney').val() ) ) ){       
					$('.invMoney').val( 1000 ); 
				
	            }else{
					$('.invMoney').val( parseInt($('.invMoney').val())+1000 );
				   
				}
			}else{                                                    //判断是否是一月，是一月加100 
				$('.invMoney').val( parseInt($('.invMoney').val())+100 );
				
			}
			$('.Profit').html( ( parseInt($('.invMoney').val())  * parseFloat($('#lvshu').html())/100/12* parseInt($('.monCunt').val() )).toFixed(2) );    //改变利益值
					btnColor();
		});	
	
	
		
	function noNumber(){
		var he =   parseInt( $('.invMoney').val() ) ;  //投资金额和代金券得和
		if( isNaN( parseInt( $('.invMoney').val() ) ) ){
			        
					$('#Profit').html( 0 );     
					$('.invMoney').val( 0 );  
	         }else{
				 if( $('.invMoney').val()<1000 ){     //判断投资金额是否小于一千，如果小于变成投资1000，代金券20
					
					 $('.invMoney').val(1000);
					 $('.Profit').html( ( 1000 *  parseFloat( $('#lvshu').html() ) /100 /12 * parseInt($('.monCunt').val() ) ).toFixed(2) );       //点击按钮后从新计算收获利益得值
				}else{                                //如果不小于，不改变投资金额得值
				     $('.Profit').html( ( he *  parseFloat( $('#lvshu').html() ) /100 /12 * parseInt($('.monCunt').val() ) ).toFixed(2) );       //点击按钮后从新计算收获利益得值	
				}
			 }
	}
	
	  
	
	//投资期限
	var $InvTrem = $('.Inv-trem input');  //获取月份按钮   点击改变投资月数  并改变年化率
	$InvTrem.click(function (){
		$InvTrem.attr('class','');
		$(this).attr('class','monCunt'); 
		//alert( $(this).val() );toFixed(2);
		if( $(this).val() == 1 ){                 //一月没有代金券，投资金额最低一百
				$('#lvshu').html('6.5');
				//$('.cashMoney').val(0);
				$('.invMoney').val(100);
				//$('#needM').html( "<p class='Investment'>不使用代金券<p/> ");  //改变需要投入得钱数
			    $('.Profit').html( ( parseInt( $('.invMoney').val() ) *  parseFloat( $('#lvshu').html() ) /100 /12 * parseInt($('.monCunt').val() ) ).toFixed(2) ); 
				btnColor2();
				$("#up").stop().animate({left:'-25%'});
		}else if( $(this).val() == 3 ) {
				$('#lvshu').html('6.1');
				$("#up").stop().animate({left:'-0.4%'});
				$('#Intme').html("起投金额为1000元")
				 noNumber();
				 btnColor();
		}else if( $(this).val() == 6 ) {
				$('#lvshu').html('7.3');
				$('#Intme').html("起投金额为1000元")
				 noNumber();
				 btnColor();
				 $("#up").stop().animate({left:'24.65%'});
		}else if( $(this).val()== 12 ) {
				$('#lvshu').html('9.8');
				$('#Intme').html("起投金额为1000元")
				 noNumber();
				 btnColor();
				 $("#up").stop().animate({left:'50.4%'});
		}
	} );
	

	
	
	//年化利率图片点击按钮
	var $details = $('.details');       //获取问号图片按钮
	$details.click(function (){
		$('.activeDetails').show();
		$('.redDetails').show();
	} )
    
	
	var $redBtn = $('.footBtnT');      //获取问号图片得取消按钮  点击取消问号图片隐藏
	    $redBtn.click(function(){
			$('.activeDetails').hide();
			$('.redDetails').hide();
		} )
	
	
	//底部客服电话点击弹出
	var $telephone = $('.telephone');       //获取客服电话  客服电话点击事件
	$telephone.click(function (){
		$('.CustomerBox').toggle();
				
	} );
	
	var $cancelBtn = $('.cancelBtn');      //获取客服电话得取消按钮  点击取消客服电话隐藏
	    $cancelBtn.click(function(){
			$('.CustomerBox').hide();
		} )
		
	
	    $telephone.click(function (){
			var oLogin1 =$(".CustomerBoxZ");
			var oLogin2 =$(".CustomerBox");
			oLogin1.css({'display':'block'})
			
			
			
			$(".cancelBtn").click(function(){
			    oLogin1.css({'display':'none'})
				
			});
			$(".CustomerBoxZ").click(function(e) {               //点击屏幕任意地方隐藏
                var drag = $(".CustomerBoxZ"),dragel = $(".CustomerBox")[0],target = e.target;
				if(  dragel !== target && !$.contains(dragel, target)  )
				{
					 oLogin1.css({'display':'none'});
					 oLogin2.css({'display':'none'});
				}
             });
			
	
	  } );
	

	  
	    var $WeChatPublic = $('.WeChat-public');       //微信图标
	    $WeChatPublic.click(function (){
			$("#mb").show();
			$("#zongDiv").show();
	
	    } );
	    $("#close").click(function(){
			    $("#zongDiv").hide();
				$("#mb").hide();
		});
	    $("#mb").click(function(e) {               //点击屏幕任意地方隐藏
                var drag = $("#mb"),dragel = $("#imgT")[0],target = e.target;
				if(  dragel !== target && !$.contains(dragel, target)  )
				{
					$("#zongDiv").hide();
					 $("#mb").hide();
				}
        });
		
		$( "#ordinaryT" ).blur(function() {                 //投资失去焦点事件，
				if( $('.monCunt').val() != 1  ){		     //判断是否是一月
						if( $("#ordinaryT").val() <1000 ){    //不是一月投资不能低于一千
							blurBtn();
							//$('#ordinary').val( 20 );          //不是一月代金券不能低于20
						}
				}else{
					//$('#needM').html( "<p class='Investment'>不使用代金券<p/> ");  //改变需要投入得钱数
					if( $("#ordinaryT").val() <100 ){
							blurBtn1();
							//$('#ordinary').val( 0 );
				        }
				}
						});
						
		
});

function blurBtn() {
		    $("#Intme").show();
			setTimeout("$('#Intme').hide()",2000);
            $('.needMoney').html( 1000  );
            $('#ordinaryT').val( 1000  );	
			$('#Profit').html( 15.25 );
	       
		
	}
function blurBtn1() {
		    $("#Intme").show();
			$('#Intme').html("起投金额为100元")
			setTimeout("$('#Intme').hide()",2000);
            $('.needMoney').html( 100  );
            $('#ordinaryT').val( 100  );	
			$('#Profit').html( 0.54 );
	       
		
	}
function OnInput(event){
	
	var numChange = parseInt(event.target.value);   //输入后的数
	var $xian = $("#ordinary").val()*50;
	var $touzi =  parseInt( numChange/50 );
	if(  $('.monCunt').val() != 1 ){                //判断是否是一月
	
		if( isNaN(numChange) || numChange==0 || numChange=='' ){                     //为空或者不是数字得时候
		   
			$('#Profit').html( 0 );     
			//$('.cashMoney').val( 0 );        
			//$('#needM').html( "<p class='Investment'>使用此代金券，需投资<span class='needMoney'>"+1000+"</span>元<p/> ");   //改变需要投入得钱数
		}else if( numChange >=1000 ){  
			
			var numId = (event.target.id);
			var lilv = parseFloat($('#lvshu').html());
			
			if( numChange < $xian ){
				
				$("#ordinary").val( $touzi );
				$('#needM').html( "<p class='Investment'>使用此代金券，需投资<span class='needMoney'>"+numChange+"</span>元<p/> ");   //改变需要投入得钱数
			}
				
			
			$('#Profit').html( ( numChange  * lilv / 100 / 12 * parseInt($('.monCunt').val() ) ).toFixed(2) );
		
		}
	}else{
		if( isNaN(numChange) ){                //为空或者不是数字得时候
		   
			$('#Profit').html( 0 );     
			$('.cashMoney').val( 0 );        
			
		}else if( numChange >= 100 ){  
			var numId = (event.target.id);
			var lilv = parseFloat($('#lvshu').html()); 
			$('#Profit').html( (  numChange  * lilv / 100 / 12 * parseInt($('.monCunt').val() ) ).toFixed(2) );
			$('#ordinary').val( 0 );
			$('.needMoney').html( 100  );    //改变需要投入得钱数
			
		}
		//$('#needM').html( "<p class='Investment'>不使用代金券！<p/> ");  //改变需要投入得钱数
	}
	
}


		



  



