//图片上传
(function ($) {
    $.handleUpload = function (el, options, callback) {		
        
		//获取唯一编码,用于分组
		var getUniqueID = function(prefix){
			var prefix = prefix || 'Upload_';
			return prefix + '_' + Math.floor(Math.random() * (new Date()).getTime());
		}
		
		

    }

    $.fn.handleUpload = function (options, callback) {
        return this.each(function (i) {
            (new $.handleUpload(this, options, callback));
        });
    };


})(jQuery);