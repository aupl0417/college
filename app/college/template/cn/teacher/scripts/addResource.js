 
    var flowModalUpload = function() {
		$('.modalUpload').each(function(i) {
			$(this).handleUpload(function(data) {
				if (data.status == 'success') {
					var fileinput = $(".modalUpload:eq(" + i + ")").prev('.fileinput');
					$("input[type='hidden']", fileinput).val(data.savename);
					$('.thumbnail img', fileinput).prop('src', data.filename + '?r=' + Math.random());
					$('.thumbnail a.fancybox-button', fileinput).prop('href', data.filename + '?r=' + Math.random());
				} else {
					return false;
				}
			});
		});
	};

    $(function () {
        $('#form_edit').handleForm(
                {
                    rules: {
                        'name': {
                            required: true
                        },
                        'courseId': {
                            required: true
                        },
                        'type': {
                            required: true
                        }
                    },
                    messages: {
                        'name': {
                            required: "请填写课件名称"
                        },
                        'courseId': {
                            required: "请选择所属课程"
                        },
                        'type': {
                            required: "请选择文件类型"
                        }
                    },
                    closest: 'div.form-group',
                    ass: {}
                },
                function (data, statusText) {
                    if (data.id == '1001') {
                        bootbox.alert(data.msg, function () {
                            console.log(data.info);
                            Global.ajaxclick('31101');
                            $('#formModal').modal('hide');
                        });
                    } else {
                        Global.alert({
                        	"container": "#form_edit",
                			"place": "prepend",
                			"type": "warning",
                			"message": data.info,
                			"close": true,
                			"reset": true,
                			"focus": true,
                			"closeInSeconds": "0",
                			"icon": "warning"
                        });
                        $('#showfile').val('');
                        $('#files').val('');
                        $('#keys').val('');
                    }

                }
        );
        $('#msg_annex').handleUpload(function (data) {
            console.log(data);

        });
    });
    
    flowModalUpload();
    
  //上传图片前缩略图预览
    function PreviewImage(obj){
    	console.log(obj);
    	// Get a reference to the fileList
    	var files = !!obj.files ? obj.files : [];
    	// If no files were selected, or no FileReader support, return
    	if (!files.length || !window.FileReader) return;

    	// Only proceed if the selected file is an image
    	if (/^image/.test( files[0].type)){

    		// Create a new instance of the FileReader
    		var reader = new FileReader();

    		// Read the local file as a DataURL
    		reader.readAsDataURL(files[0]);
    		var _obj = obj;

    		// When loaded, set image data as background of div
    		reader.onloadend = function(){

    			$(_obj).closest('div.fileinput').addClass('input-medium thumbnail').find('a').attr('href',this.result);
    			$(_obj).closest('div.fileinput').find('a').find('img').attr('src',this.result);
    		}
    	}
    }