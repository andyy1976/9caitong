define("new/js/mod/login",["new/css/layer.css","new/css/userForm.css","$","layer","new/js/page/page_login"],function(e){e("new/css/layer.css"),e("new/css/userForm.css");var n=e("$"),s=e("layer");e("new/js/page/page_login"),n(document).ready(function(){n("#layer_login,#layer_logins").click(function(){s.open({type:1,title:"登录玖财通",closeBtn:1,shadeClose:!1,area:["402px"],move:!1,content:n("#open_login")})})})});