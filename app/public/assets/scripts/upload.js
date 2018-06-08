//图片上传
(function ($) {
    $.handleUpload = function (el, options, callback) {
        if (!plupload)
            return false;
        var upload = this;
        upload.$el = $(el);
        upload.el = el;
        upload.id = Global.getUniqueID("upload");
        upload.modal = '<div class="modal fade modal-scroll" id="' + upload.id + '" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">';
        upload.modal += '<div class="modal-dialog">';
        upload.modal += '  <div class="modal-content">';
        upload.modal += '    <div class="modal-header"> ';
        upload.modal += '      <h4 class="modal-title">文件上传</h4>';
        upload.modal += '    </div>';
        upload.modal += '    <div class="modal-body"> ';
        upload.modal += '		<div class="clearfix filelist margin-bottom-10"></div>';
        upload.modal += '		<div class="clearfix uploadinfo">';
        upload.modal += '			<div class="progress progress-striped active" style="display:none;">';
        upload.modal += '				<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">';
        upload.modal += '					<span class="sr-only"></span>';
        upload.modal += '				</div>';
        upload.modal += '			</div>';
        upload.modal += '		</div>';
        upload.modal += '		<div class="clearfix margin-top-10">';

        /*****************************Modal初始按钮*****************************************/
        upload.modal += '			<button id="' + upload.id + '_select" type="button" class="btn green select">';//1.2 select: 选择文件
        upload.modal += '				<i class="fa fa-plus"></i>';
        upload.modal += '				<span> 选择文件 </span>';
        upload.modal += '			</button>';
        upload.modal += '			<button id="' + upload.id + '_upload" type="button" class="btn blue upload disabled" disabled="disabled">';//1.2.2 upload: 点击上传
        upload.modal += '				<i class="fa fa-upload"></i>';
        upload.modal += '				<span> 上传文件 </span>';
        upload.modal += '			</button>';
        upload.modal += '			<button id="' + upload.id + '_closed" type="button" class="btn default closed" data-dismiss="modal">';//1.1 closed: 关闭窗口
        upload.modal += '				<i class="fa fa-trash-o"></i>';
        upload.modal += '				<span> 取消上传 </span>';
        upload.modal += '			</button>';
        /*****************************Modal初始按钮 end*************************************/

        /*****************************上传成功之后的按钮************************************/
        upload.modal += '			<button id="' + upload.id + '_success" type="button" class="btn blue success" style="display:none;">';//1.3.1.1 success: 确定 返回上传信息
        upload.modal += '				<i class="fa fa-check"></i>';
        upload.modal += '				<span> 确定 </span>';
        upload.modal += '			</button>';
        upload.modal += '			<button id="' + upload.id + '_drop" type="button" class="btn default drop" style="display:none;">';//1.3.1.2 drop: 放弃已上传文件 // reset + close
        upload.modal += '				<i class="fa fa-trash-o"></i>';
        upload.modal += '				<span> 取消上传 </span>';
        upload.modal += '			</button>';
        /*****************************上传成功之后的按钮 end********************************/

        /*****************************上传失败之后的按钮************************************/
        upload.modal += '			<button id="' + upload.id + '_reupload" type="button" class="btn blue reupload" style="display:none;">';//1.3.2.1 reupload: 重新上传 //先用reset方法重置当前Modal
        upload.modal += '				<i class="fa fa-refresh"></i>';
        upload.modal += '				<span> 重新上传 </span>';
        upload.modal += '			</button>';
        upload.modal += '			<button id="' + upload.id + '_error" type="button" class="btn default error" style="display:none;">';//1.3.2.2 error: 关闭窗口 返回错误信息
        upload.modal += '				<i class="fa fa-trash-o"></i>';
        upload.modal += '				<span> 取消上传 </span>';
        upload.modal += '			</button>';
        /*****************************上传成功之后的按钮 end********************************/

        /*****************************上传时的取消按钮**************************************/
        upload.modal += '			<button id="' + upload.id + '_cancel" type="button" class="btn red cancel" style="display:none;">';//1.3.3 cancel:取消上传 //重置当前Modal但不关闭
        upload.modal += '				<i class="fa fa-stop"></i>';
        upload.modal += '				<span> 停止上传 </span>';
        upload.modal += '			</button>';
        /*****************************上传时的取消按钮 end**********************************/

        upload.modal += '		</div>';
        upload.modal += '	</div>';
        upload.modal += '  </div>';
        upload.modal += '</div>';
        upload.modal += '</div>';
        upload.modal = $(upload.modal);
        
		var parentModal = upload.$el.closest('.modal');//判断是否是在modal中调用上传插件
		
		if(parentModal.size() == 0){
			upload.$el.append(upload.modal);
		}else{//如果是在modal中调用,那么当上传插件的modal加载到body;parentModal关闭的时候要销毁掉上传插件的modal
			$('body').append(upload.modal);
			parentModal.on('hide.bs.modal', function (e) {
				upload.modal.remove();
			});
		}
		//$('body').append(upload.modal);
        /* 
         *   按钮: step button|none action
         *	Upload Modal:{
         *		1.1 closed: 关闭窗口,
         *		1.2 select: 选择文件 {
         *			1.2.1 none: 自动上传,
         *			1.2.2 upload: 点击上传,
         *			1.2.3 closed: 关闭窗口 == 1.1
         *		},
         *		1.3 none 上传:{
         *			1.3.1 none: 上传成功 {
         *				1.3.1.1 success: 确定 返回上传信息,
         *				1.3.1.2 drop: 放弃已上传文件
         *			},
         *			1.3.2 none: 上传失败{
         *				1.3.2.1 reupload: 重新上传, //先用reset方法重置当前Modal
         *				1.3.2.2 error: 关闭窗口 返回错误信息
         *			},
         *			1.3.3 cancel:取消上传 //重置当前Modal但不关闭
         *		}
         *	}
         */

        if (typeof options === 'function') {
            callback = options;
            options = {};
        }
        upload.info = null;
        upload.callback = null || callback;
       
		
        upload.defaults = {
            url: '/upload/upload.json', //上传链接,			
            auto: false, //是否自动上传
            crop: false, //图片裁剪,auto失效
            type: 'jpg,gif,png,zip,rar,doc,docx,xls,xlsx,pdf,txt', // 上传文件类型
            size: "2048K", // 最大文件大小,
            show: 1, //是否显示上传图片的预览，0不显示，1显示
            max: 1, //最大上传数量
            multi: false,
            inputname: 'file',
            btnSelect: upload.id + '_select',
            btnUpload: upload.id + '_upload',
            btnClosed: upload.id + '_closed',
            btnSuccess: upload.id + '_success',
            btnDrop: upload.id + '_drop',
            btnReupload: upload.id + '_reupload',
            btnError: upload.id + '_error',
            btnCancel: upload.id + '_cancel',
            progress: $('.progress.active', upload.modal),
            success: $('.alert.alert-success', upload.modal),
            error: $('.alert.alert-danger', upload.modal),
            unique_names: true,
            savename: '',
        };

        upload.options = $.extend({multipart_params:null}, upload.defaults, upload.$el.data(), options);

        upload.handleButton = function (buttons, actions, callbacks) {//按钮处理			
            for (var i = 0, len = buttons.length; i < len; i++) {
                var button = $('#' + upload.options[buttons[i]]);
                for (var j = 0, l = actions.length; j < l; j++) {
                    switch (actions[j]) {
                        case 'disabled':
                            button.prop('disabled', true).addClass('disabled');
                            break;
                        case 'enabled':
                            button.prop('disabled', false).removeClass('disabled');
                            break;
                        case 'hide':
                            button.hide();
                            break;
                        case 'show':
                            button.show();
                            break;
                        case 'off':
                        default:
                            button.off()
                            break;
                    }
                }
            }
            ;
        }

        upload.handleProgressBar = function (percent) {//进度条设置
            var progressBar = $('.progress-bar.progress-bar-info', upload.options.progress);
            progressBar.attr('aria-valuenow', percent).css('width', percent + '%');
            switch (percent) {
                case 0:
                    upload.options.progress.hide();
                    break;
                case 100:
                    setTimeout(function () {
                        upload.options.progress.hide();
                        Global.alert({
                            container: $('.uploadinfo', upload.modal),
                            place: "prepend",
                            type: 'success',
                            message: "文件上传成功!",
                            closeInSeconds: 10
                        });
                    }, 1000);
                    break;
                default:
                    upload.options.progress.show();
                    break;
            }
        }

        upload.handleError = function (up, args) {//Error		
            upload.handleProgressBar(0);//隐藏滚动条			
            var info;
            switch (args.code) {
                case -600:
                    info = '上传文件大小超过' + upload.options.size;
                    break;
                case -601:
                    info = '上传文件类型错误!只允许上传' + upload.options.type + '格式文件';
                    break;
                default:
                    info = args.message;
                    break;
            }

            Global.alert({
                container: $('.uploadinfo', upload.modal),
                place: "prepend",
                type: 'danger',
                message: "错误:" + info,
                closeInSeconds: 10
            });
	
            upload.handleButton(['btnSelect', 'btnUpload', 'btnSuccess', 'btnDrop', 'btnError', 'btnCancel'], ['hide', 'disabled']);
            upload.handleButton(['btnReupload', 'btnClosed'], ['show', 'enabled']);

            //绑定事件
            $('#' + upload.options.btnReupload).on('click', function () {
                upload.handleReset();
                upload.init();
            });
            $('#' + upload.options.btnClosed).on('click', function () {//返回错误信息
																	
                upload.callback && upload.callback({
                    'status': 'error',
                    'info': info
                });
            });
        }

        upload.handleReset = function () {//reset		
            upload.handleButton(['btnSelect', 'btnUpload', 'btnClosed', 'btnSuccess', 'btnDrop', 'btnReupload', 'btnError', 'btnCancel'], ['hide', 'disabled', 'off']);//隐藏并disabled所有按钮
            upload.handleProgressBar(0);//重置并隐藏进度条
            $('.Global-alerts.alert', upload.$el).remove();//remove alert
            $('.filelist', upload.modal).html('');
            upload.Uploader.destroy();
        }

        upload.handlePreview = function (file, callback) {//file为plupload事件监听函数参数中的file对象,callback为预览图片准备完成的回调函数
            if (!file || !/image\//.test(file.type))
                callback && callback(false); //确保文件是图片
            if (file.type == 'image/gif') {//gif使用FileReader进行预览,因为mOxie.Image只支持jpg和png
                var fr = new mOxie.FileReader();
                fr.onload = function () {
                    callback(fr.result);
                    fr.destroy();
                    fr = null;
                }
                fr.readAsDataURL(file.getSource());
            } else {
                var preloader = new mOxie.Image();
                preloader.onload = function () {
                    //preloader.downsize(300, 300);//先压缩一下要预览的图片,宽300，高300
                    var imgsrc = preloader.type == 'image/jpeg' ? preloader.getAsDataURL('image/jpeg', 80) : preloader.getAsDataURL(); //得到图片src,实质为一个base64编码的数据
                    callback && callback(imgsrc); //callback传入的参数为预览图片的url
                    preloader.destroy();
                    preloader = null;
                };
                preloader.load(file.getSource());
            }
        }
        upload.cropInfo = null;
        upload.handleCrop = function (up, file) {
            console.log($('#' + upload.id + '_preview').width());
            var uploadPreview = $('#' + upload.id + '_preview');
            upload.cropInfo = {x: 0, y: 0, w: 0, h: 0, s_w: 0, o_w: 0};
            $("<img/>")
                    .attr("src", uploadPreview.attr("src"))
                    .load(function () {
                        upload.cropInfo.o_w = this.width;
                        upload.cropInfo.s_w = uploadPreview.width();
                    });
            cropOptions = $.extend({
                onSelect: function (c) {
                    upload.cropInfo.x = c.x;
                    upload.cropInfo.y = c.y;
                    upload.cropInfo.w = c.w;
                    upload.cropInfo.h = c.h;
                }
            }, upload.options.crop);

            uploadPreview.Jcrop(cropOptions);
        }

        upload.Uploader = null;
		
        upload.init = function () {
            upload.Uploader = new plupload.Uploader({
                runtimes: 'html5,flash,silverlight,html4',
                container: upload.modal[0],
                browse_button: upload.options.btnSelect,
                url: upload.options.url,
                flash_swf_url: 'assets/global/plugins/plupload/js/Moxie.swf',
                silverlight_xap_url: 'assets/global/plugins/plupload/js/Moxie.xap',
                multi_selection: upload.options.multi,
                unique_names: upload.options.unique_names,
                chunk_size: 0,
                file_data_name: upload.options.inputname, //<input type=file name=file_data_name
                filters: {
                    max_file_size: upload.options.size,
                    mime_types: [
                        {title: "files", extensions: upload.options.type},
                    ]
                },
                init: {
                    PostInit: function () {						   
                        upload.handleButton(['btnSelect', 'btnClosed'], ['show', 'enabled']);
                        upload.handleButton(['btnSuccess', 'btnDrop', 'btnReupload', 'btnError', 'btnCancel'], ['hide', 'disabled']);

                        if (upload.options.auto && !upload.options.crop) {//自动提交,那么隐藏掉上传按钮
                            upload.handleButton(['btnUpload'], ['hide']);
                        } else {//如果非自动提交,那么显示按钮并绑定按钮事件
                            upload.handleButton(['btnUpload'], ['show']);
                            $('#' + upload.options.btnUpload).on('click', function () {
                                upload.Uploader.start();
                                return false;
                            });
                        }
                    },
                                Refresh: function (up) {

                                },
                    FilesAdded: function (up, files) {//添加文件的操作
                        if ((!upload.options.auto || upload.options.crop) && up.files.length > 0) {
                            upload.handleButton(['btnUpload'], ['enabled']);
                        }

                        if (up.files.length > upload.options.max)
                        {
                            up.files.splice(upload.options.max);
                        }

                        plupload.each(files, function (file, i) {
                            if (upload.options.show) {
                                !function (i) {
                                    upload.handlePreview(files[i], function (imgsrc) {
                                        //$('#file-' + files[i].id).append('<img src="' + imgsrc + '" />');
                                        if (imgsrc) {
                                            upload.modal.find('.filelist').html('<div class="thumbnail"> <img id="' + upload.id + '_preview" src="' + imgsrc + '"><div class="caption"><h3>' + file.name + ' (' + plupload.formatSize(file.size) + ')</h3></div></div>');
                                            (upload.options.crop) && upload.handleCrop(up, file);
                                        } else {
                                            upload.modal.find('.filelist').html('<div id="file-' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') </div>');
                                        }
                                    });
                                }(i);
                            } else {
                                upload.modal.find('.filelist').html('<div id="file-' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') </div>');
                            }
                        });
                        if (upload.options.auto && !upload.options.crop) {//自动提交
                            upload.Uploader.start();
                        }
                    },
                    BeforeUpload: function (up, file) {//上传	   
                        upload.handleButton(['btnSelect', 'btnUpload', 'btnReupload', 'btnError', 'btnSuccess', 'btnDrop', 'btnClosed'], ['hide', 'disabled']);
                        upload.handleButton(['btnCancel'], ['show', 'enabled']);
                        //绑定事件
                        $('#' + upload.options.btnCancel).on('click', function () {
                            upload.Uploader.stop();
                            upload.handleReset();
                            upload.init();
                        });
                        if (upload.cropInfo) {//如果有裁剪信息
                            if (upload.cropInfo.o_w > upload.cropInfo.s_w > 0) {//如果取到了原大小,并且是缩放过,那么按比例放大裁剪信息
                                rat = upload.cropInfo.o_w / upload.cropInfo.s_w;
                                upload.cropInfo.x = upload.cropInfo.x * rat;
                                upload.cropInfo.y = upload.cropInfo.y * rat;
                                upload.cropInfo.w = upload.cropInfo.w * rat;
                                upload.cropInfo.h = upload.cropInfo.h * rat;
                            }
                            
							upload.options.multipart_params = $.extend(upload.options.multipart_params, upload.cropInfo);
                        }
						upload.options.multipart_params = $.extend(upload.options.multipart_params, {savename: upload.options.savename});
						up.settings.multipart_params = upload.options.multipart_params;

                    },
                    UploadProgress: function (up, file) {//进度条
                        file.percent < 100 && upload.handleProgressBar(file.percent);
                    },
                    UploadComplete: function (up, files) {//上传文件成功或者失败//由于现在都是单个上传,所以就不判断多个
                        var file = files[0];
                        if (file.status == '5') {
                        } else {//错误信息在Error中处理		

                        }                
                    },
                    FileUploaded: function (up, file, data) {//上传成功	
//                        console.log(data);
                        result = JSON.parse(data.response);
//                        console.log(result);
                        if (result.status == 'success') {
                            upload.handleProgressBar(100);//上传成功				   
                            upload.handleButton(['btnSelect', 'btnUpload', 'btnClosed', 'btnError', 'btnCancel'], ['hide', 'disabled']);//, 'btnReupload'
                            upload.handleButton(['btnSuccess', 'btnDrop', 'btnReupload'], ['show', 'enabled']);

							//绑定事件
							$('#' + upload.options.btnReupload).on('click', function () {
								upload.handleReset();
								upload.init();
							});
							
                            $('#' + upload.options.btnSuccess).on('click', function () {//返回成功信息
                                upload.callback && upload.callback(result);
                                upload.modal.modal('hide');
                            });

                            $('#' + upload.options.btnDrop).on('click', function () {//放弃已上传文件,删除已上传文件
																		
								__destroy = upload.options.destroy;
								if (__destroy && typeof __destroy === 'function') {
                                    __destroy();
                                }
                                upload.modal.modal('hide');
                            });
                        } else {
                            upload.handleError(up, result);
                            //return false;
                        }

                    },
                    Destroy: function (up) {//销毁

                    },
		 
                                Error: function (up, args) {//错误信息	
                        upload.handleError(up, args);
                                }
                }

            });
            upload.Uploader.init();
        }


        upload.modal.on('show.bs.modal', function (e) {
			//console.log('show');
            upload.init();

        }).on('hide.bs.modal', function (e) {			
            
        });
        upload.$el.on('click', '.start', function () {
            upload.modal.modal();//弹出modal			
        });
        upload.$el.on('click', '.closed, .drop, .cancel', function () {
            upload.handleReset();//reset
			//console.log('hide');
        });


    }

    $.fn.handleUpload = function (options, callback) {
        return this.each(function (i) {
            (new $.handleUpload(this, options, callback));
        });
    };


})(jQuery);