//加载模板
(function($){
    $.loadTemplate = function(options, data){
		if(options.file !== ''){
			var defaults = {
				'suffix': 'html',
				'callback': null,
				'id': null
				//''
			};
			var file = options.file;
			var data = data || {};
			var options = options || {};
			options = $.extend({}, defaults, options);
			file = file + '.' + options.suffix;
			var $_scriptId = 'script_' + Math.floor(Math.random() * (new Date()).getTime());
			Global.blockUI({zIndex: 99999});
			$.ajax(file, {
				async : false,
				error : function(data){
					Global.unblockUI();
					console.log(data);
				},
				success : function (html) {
					$('body').append(						
							$('<script>')
							.prop({
									'type': 'text/html',
									'id': $_scriptId
							})						
							.html(html)
					);
					var $_temp = template($_scriptId, data);					
					$('body').find('#'+$_scriptId).remove();
					Global.unblockUI();
					if(options.callback){
						options.callback($_temp);
					}else{
						if(options.id){
							$('#'+options.id).html($_temp);
						}else{
							$('body').append($_temp);
						}	
					}
					
				}
			});			
		}else{
			return false;
		}
	}
})(jQuery);

