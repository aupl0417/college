(function($){
	
	$.orgSelect = function (el, options, callback) {
		var select = this;		
				
		if(typeof options === 'function'){
			callback = options;
			options = {};
		}
		select.callback = null || callback;
        
		//获取唯一编码,用于给select赋值id
		var getUniqueID = function(prefix){
			var prefix = prefix || 'C';
			return prefix + '_' + Math.floor(Math.random() * (new Date()).getTime());
		}		
				
		var defaults = {
			'val':  '',
			'placeholder': '请选择部门...',
			'selectParent': false,
			'selectName': '',
			'selectClass': 'form-control input-sm',
			'url': '/organization/tree/?_ajax=1&ids=',
			'selecId': getUniqueID('org'),
			'selectEmployee': false,
			'elEmployee': '',//雇员select的id
			'urlAjax': '/public/select.json',//json文件
			'wrap':'' ,//用于包含select的element
			'wrapClass': '',//wrap的样式
			'disabled': false,
			'callback': null,
			'multiple': true,
			'size': 1,
			'hasSelect': 0
        }
		
        options = $.extend( defaults, $(el).data(), options );
			
		/* 用于选择部门的modal */
		var modalOrgSelect = $('#modal-temp-select-orgs');
		var createModal = function(url){
			if(modalOrgSelect.length == 0){
				modalOrgSelect = '<div class="modal fade modal-scroll bs-modal-lg" id="modal-temp-select-orgs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-replace="false" data-url="'+url+'">';				
				modalOrgSelect += '	<div class="modal-dialog modal-lg">';
				modalOrgSelect += '		<div class="modal-content">';
				modalOrgSelect += '			<div class="modal-header">';
				modalOrgSelect += '				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
				modalOrgSelect += '				<h4 class="modal-title">选择部门</h4>';
				modalOrgSelect += '			</div>';
				modalOrgSelect += '			<div class="modal-body"></div>';
				modalOrgSelect += '			<div class="modal-footer display-hide"></div>';
				modalOrgSelect += '		</div>';			
				modalOrgSelect += '	</div>';
				modalOrgSelect += '</div>';
				modalOrgSelect = $(modalOrgSelect);			
				$('body').append(modalOrgSelect);
			}else{
				modalOrgSelect.data('url', url);
			}
			
			!!modalOrgSelect.data('url') && modalOrgSelect.on('show.bs.modal', function(){
				$('.modal-body', this).load($(this).data('url'), function(){
					$('#btnyes', this).on('click', function(e){
						e.preventDefault();
						$('#orgsListTree').jstree('open_all');
						var selected = [], orgs = [];
						$('.jstree-wholerow.jstree-wholerow-clicked').each(function(){
							var li = $(this).closest('li');
							var val = li.attr('id');
							var text = $('.jstree-clicked:first', li).text();
/* 							var opt = '<option selected="selected" value="'+ val +'">'+ text +'</option>';
							select2.append(opt).select2({allowClear: true}); */
							selected.push({val: val, text: text});//
							orgs.push(val);
						});
						/* 添加部门已选择tags */
						select2.empty();
						add2Tags(selected, select2);
						
						/* 根据已选择部门读取所属员工 */
						employeeSelected(null, orgs);
						$('#modal-temp-select-orgs').modal('hide');
						
					});					
				});				
			}).modal('show');
			return modalOrgSelect;
		}
		
		/* 选中options添加到类似tags */
		var add2Tags = function(selected, sel){			
			var optionsSelected = '';
			
			for(var i = 0, len = selected.length; i < len; i++){
				optionsSelected += '<option selected="selected" value="'+ selected[i].val +'">'+ selected[i].text +'</option>';
				
			}
			sel.append(optionsSelected);
			//console.log(optionsSelected);
			//console.log(selected, $('option', sel));
			sel.select2({allowClear: true});
		}
		
		/* 创建select2 */
		var select2 = $('select', el);
		var createSelect = function(parent, data){
		
			if(select2.length == 0 && !$(el).data('hasSelect')){//
				select2 = $('<select />');
				select2.addClass(options.selectClass)
					   .prop({
							'name': options.selectName,
							'id': options.selecId,
							'size': options.size,
							'multiple':options.multiple
						});
				if(options.wrap){
					var wrap = $('<'+options.wrap+'>');
					$(el).append(wrap);
					wrap
					.addClass(options.wrapClass)
					.append(select2);
				}else{
					$(el).append(select2);
				}
				$(el).data('hasSelect', 1);
			}
			//console.log(options.val, $('option:selected', $(select2)));
			//$('option:selected', $(select2)).prop('selected', false);
			$(select2).empty().select2({
				placeholder: options.placeholder,
				allowClear: true,
				formatNoMatches: null
			})
			.on("select2-open", function(e) {	
				$(this).select2('close');
				var orgs = $('option:selected', this).map(function(){
					return $(this).val();
				}).get().join(',');
				
				createModal(options.url+orgs);				
			})
			.on("change", function(e)
			{
				if(e && e.removed)
				{
					var orgs = $('option:selected', this).map(function(){
						return $(this).val();
					}).get().join(',');
				   employeeSelected(null, orgs);
				}
			});	
			//console.log(options.val);
			/* 添加部门已选择tags */			
			if(options.val){
				$.ajax({
					'type': 'GET',
					'url': options.urlAjax,
					'dataType': 'json',
					'data': {type: 'orgselected', val: options.val},
					'success': function(res){
						if(res.id == '1001'){
							//console.log(res.info);
							select2.empty();
							add2Tags(res.info, select2);							
						}else{
							
						}
					}
				});				
			}
			
			/* 已选择员工 */
			$(options.elEmployee).each(function(i, e){
				var opts = $('option:selected', $(this)).map(function(){
					return $(this).val();
				}).get().join(',');
				eVals = opts || $(this).data('val');
				employeeSelected($(this), options.val, eVals);
			});
			
			return select2;
		}
		
		/* 如果自动筛选已选择部门的员工 */
		var employeeSelected = function(elEmployee, orgs, val){
			//console.log(orgs);
			elEmployee = elEmployee || $(options.elEmployee);
			orgs = typeof orgs == 'object' ? orgs.join(',') : orgs;
			val = val || '';
			if(!!options.selectEmployee && elEmployee.length > 0){ 
				$.ajax({
					'type': 'POST',
					'url': options.urlAjax,
					'dataType': 'json',
					'data': {type: 'employee', orgs: orgs, val: val},
					'success': function(res){
						if(res.id == '1001'){
							elEmployee.html(res.info);
							var optionSelected = $('option:selected', elEmployee).map(function(){
								return {val:$(this).val(), text:$(this).text()}
							}).get();
							add2Tags(optionSelected, elEmployee);
						}else{
							
						}
					}
				});
			}			
		}
		
	
		/* <select class="form-control  selectOrgs" multiple name="orgs[]" id="orgs" data-error-container="targets-error-info"></select> */
		
		return createSelect();		
			
	}
	$.fn.orgSelect = function (options, callback) {
		return this.each(function (i) {
			(new $.orgSelect(this, options, callback));
		});
	};	
}(jQuery));
