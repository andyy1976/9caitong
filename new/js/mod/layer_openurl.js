define("new/js/mod/layer_openurl",["new/css/layer.css","$","layer"],function(e,t,n){function s(e,t){this.url=e,this.title=t}e("new/css/layer.css");var i=(e("$"),e("layer"));n.exports=s,s.prototype.open=function(){i.open({type:2,area:["700px","530px"],fix:!1,maxmin:!0,title:this.title,content:this.url})}});