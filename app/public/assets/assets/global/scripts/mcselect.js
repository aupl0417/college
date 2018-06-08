(function($){
	
	$.mcSelect = function (el, options, callback) {
		var select = this;		
				
		if(typeof options === 'function'){
			callback = options;
			options = {};
		}
		select.callback = null || callback;
        
		//获取唯一编码,用于给select分组
		var getUniqueID = function(prefix){
			var prefix = prefix || 'C';
			return prefix + '_' + Math.floor(Math.random() * (new Date()).getTime());
		}		
				
		var defaults = {
			'type': '',
			'val':  '',
			'firstValue': '',
			'firstText': '请选择',
			'selectParent': false,
			'selectName': '',
			'selectClass': 'form-control input-sm',
			'isFilter': '',//主要是用于datatable表单的检索,如果只能搜索最后一个select,那么将这个class加上
			'url': '/public/select.json',
			'group': getUniqueID('select'),
			'maxLevel': 0,//联动最大级数,0为不限制
			'wrap':'' ,//用于包含select的element
			'wrapClass': '',//wrap的样式
			'disabled': false,
			'callbackTimes': 1
        }
		
        options = $.extend( defaults, $(el).data(), options );
		
		//预设option，如果firstValue和firstText都包含分隔符“|”，那么代表预设的option有多个
		var firstValue = options.firstValue+'';
		var firstText = options.firstText;
		console.log(firstValue, firstText);
		if(firstValue.indexOf('|') == -1 && firstText.indexOf('|') == -1){
			var firstOption = $('<option />')
							.val(options.firstValue)
							.text(options.firstText);
		}
		else{
			var firstValues = firstValue.split('|');
			var firstTexts = firstText.split('|');
			if(firstValues.length == firstTexts.length){
				var firstOption = '';
				for(var i = 0, len = firstValues.length;i < len; i++){					
					firstOption += '<option value="'+firstValues[i]+'">'+firstTexts[i]+'</option>';					
				}
				firstOption = $(firstOption);
			}else{
				//数组长度不一样，错误处理
			}
		}
		console.log(firstOption);
									
		//ajax获取select列表值
		var ajaxSelect = function(parent){
			var parent = parent || null;
			var val = parent ? parent.val() : options.val;
			var isParent = !!parent - 0;
			
			$.ajax({
				'url': '/public/select.json',
				'dataType': 'json',
				'data': {type: options.type, val: val, selected: !!val-0, isParent: isParent},
				'success': function(res){
					if(res.id == '1001'){
						res.info && createSelect(parent, res.info);
					}else{
						
					}
				}
			});			
		}
		
		var createSelect = function(parent, data){	
			if(parent){
				var sel = $('<select />');
					
				if(options.wrap){
					var wrap = $('<'+options.wrap+'>');
					$(el).append(wrap);
					wrap
					.addClass(options.wrapClass)
					.append(sel);
				}else{
					$(el).append(sel);
				}
				initSelect(sel, data[0]);
			}
			else{
				//当前已有select			
				var selectElement = $('select', $(el));
				for(var i=0,len=data.length;i<len;i++){
					if(selectElement.length > i){//如果已有select,
						var sel = selectElement.eq(i);
					}else{//如果没有现成的select选择框,那么添加一个
						var sel = $('<select />');						
						if(options.wrap){
							var wrap = $('<'+options.wrap+'>');
							$(el).append(wrap);
							wrap
							.addClass(options.wrapClass)
							.append(sel);
						}else{
							$(el).append(sel);
						}
						
						
					}
					initSelect(sel, data[i]);
				}
			}
			
			
			
			var groupSelects = groupSelect();//处理这一组select
			select.callback && select.callback(groupSelects);
		}
		
		var initSelect = function(sel, opts){
			sel
			.html('')
			.append(firstOption.clone())
			.append(opts)
			.addClass(options.selectClass)
			.addClass(options.group)
			.on('change', function(){
				changeSelect(this);
			})
			.prop('name', function(){//当前select如果没有name,那么name=options.selectName
				return (sel.prop('name') == '') ? options.selectName : sel.prop('name');
			})
			.prop('disabled', !!options.disabled);
			//console.log(sel.prop('name'));
		}
		
		var groupSelect = function(){
			$("select:not('."+options.group+"')", $(el)).remove();//删除多余的select
			//处理select的name			
			if(options.selectParent){//如果可以选择父select,给
				options.isFilter && $('select.'+options.group).addClass(options.isFilter);				
			}else{//如果不可以选择父select,那么只要给最后一个select定义name就行了
				options.isFilter && $('select.'+options.group+':last').addClass(options.isFilter).siblings().removeClass(options.isFilter);//给最后一个加上检索样式
				$('select.'+options.group).prop('name', '');//置空所有select的name
			}			
			$('select.'+options.group+':last').prop('name', options.selectName);
			return $('select.'+options.group+'');
			
		}
		
		var changeSelect = function(sel){
			if(options.wrap){
				$(sel).closest(options.wrap).nextAll(options.wrap).remove();
			}else{
				$(sel).nextAll('.'+options.group).remove();
			}
			if(options.maxLevel > 0 && $('.'+options.group).size() >= options.maxLevel){
				return false;
			}
			
			var groupSelects = groupSelect();
			ajaxSelect($(sel));			
			
		}
		
		return ajaxSelect();
			
	}
	$.fn.mcSelect = function (options, callback) {
		return this.each(function (i) {
			(new $.mcSelect(this, options, callback));
		});
	};	
}(jQuery));
