
	(function($){
	    // 输入框格式化 
	    $.fn.OnlyNum = function(func){
	        var obj = $(this);
	        if (obj.val() != '' && obj.val() != undefined){
	        	obj.val(obj.val().replace(/\s/g, ''));
	        }
	        obj.live('keyup', function(event){
                if (!(event.keyCode >= 48 && event.keyCode <= 57)) {
                    this.value = this.value.replace(/\D/g, '');
                }
	            this.value = this.value.replace(/\s/g, '');
	            if (func != null) {
					func.call(this);
				}
	        }).live('dragenter', function(){
	            return false;
	        }).live('onpaste', function(){
	            return !clipboardData.getData('text').match(/\D/);
	            if (func != null) {
					func.call(this);
				}
	        }).live('blur', function(){
	            this.value = this.value.replace(/\s/g, '');
	            this.rvalue = this.value.replace(/\s/g, '');
	            if (func != null) {
					func.call(this);
				}
	        });
	    }
	})(jQuery);