
	//上传文件
	/* 初始化上传插件 */
	$("#upload-file").uploadify({
		"height"          : 30,
		"swf"             : "{_TEMP_PUBLIC_}/plugins/uploadify/uploadify.swf",
		"fileObjName"     : "qiniu_file",
		"buttonText"      : "上传文件",
		"uploader"        : "/courseResource/upload",
		"width"           : 120,
		'removeTimeout'   : 1,
		'onInit'		  : init,
		'multi'			  : false,
		"onUploadSuccess" : uploadSuccess,
		'onFallback' : function() {
            alert('未检测到兼容版本的Flash.');
        }
	});
	function init(){
		$('#upload-file, #upload-file-queue').css('display','inline-block');
	}

	/* 文件上传成功回调函数 */
	function uploadSuccess(file, data){
		console.log(data);
		var data = $.parseJSON(data);
		if(data.status){
			updateAlert('上传成功', 'alert-success');
			setTimeout(function(){
				location.reload(true);
			},1500);
		} else {
			console.log(data.data);
			updateAlert('上传失败');
		}
	}

	

	//批量删除
	$('#batchDelBtn').click(function(){
		var $checked = $('#file_list input[name="key"]:checked');
		if($checked.length != 0){
			if(confirm('您确认删除吗？')){
				$.ajax({
					url : '{:U('batchDel')}',
					data : { key : $checked.serializeArray()},
					success: function(data){
						if(data.status){
							updateAlert('删除成功','alert-success');
							location.reload(true);
						}else{
							updateAlert('批量删除失败');
						}
					}
				});
			}
		}else{
			updateAlert('请先选择一项');
		}
		return false;
	});

	//重命名


	$('.rename').click(function(){
		var action = $.trim($(this).data('href'));
		var html = $($("#hooktpl").html());
		html.find("input[name=new_name]").val(this.title);
		html.find("input[name=new_name]").parents('form').attr('action', action);
		//ajaxForm 公共函数
        function ajaxForm(element,callback,dataType){
            var form = $(element).closest('form');
            var dataType = dataType || 'json';
            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: form.serialize(),
                async: false,
                dataType:dataType,
                success: function(data) {
                    if($.isFunction(callback)){
                        callback(data,form);
                    }
                }
            });
        }

		option = {
			title:'文件名更改',
			actions:['close'],
			drag:true,
			tools:true,
			buttons:{"ok":['保存', 'blue',function(){
				var _this = this;
				ajaxForm(this.find('.input-large'),function(data){
					if (data.status){
						_this.hide();
						updateAlert(data.info,'alert-success');
						setTimeout(function(){
                        	location.reload(true);
                        },1000);
		            }else{
		            	updateAlert(data.info);
		            }
				})
			}]}
		}
		$.thinkbox(html,option);
	});