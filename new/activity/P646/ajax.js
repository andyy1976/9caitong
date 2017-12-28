function if_login()
{
	var query = new Object();
	var ajax_url="";
	$.ajax({
		type:"post",
		url:ajax_url,
		dataType:"json",
		data:query,
		async:true,
		success:function (obj) {
				is_login=obj.status;
				answer=[1,0,0,0,0,0,0];
				if (is_login) {
					$('.success').each(function(index, el) {
						if(answer[index])
						{
							if($(this).hasClass('hidden'))
							{$(this).removeClass("hidden").siblings().addClass('show');}
						}
					});
				}
			},
		error:function (obj) {			  
			  return false;
		}
	});
}
