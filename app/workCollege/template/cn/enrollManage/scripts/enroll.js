/*=============================================================================
#     FileName: enroll.js
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 21:56:31
#      History:
#      Paramer:
=============================================================================*/
var enroll = function() {
	var modalOptions = {
		"container": "#formCourse",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};
	var validate = {
		rules: {
			'name': {
				required: true,
			},
			'number': {
				required: true,
			},
			'logo': {
				required: true,
			},
			'allowableNumber': {
				required: true,
			},
			'cost': {
				required: true,
			},
			'headmasterId': {
				required: true,
				min: 1,
			},
			'enrollStartTime': {
				required: true,
			},
			'enrollEndTime': {
				required: true,
			},
			'startTime': {
				required: true,
			},
			'endTime': {
				required: true,
			},
			'catering': {
				required: true,
				min: 1,
			},
			'hostel': {
				required: true,
				min: 1,
			},
			'tangCollege': {
				required: true,
				min: 1,
			},
			'description': {
				required: true,
			},
		},
		messages: {
			'name': {
				required: "请选择课程名",
			},
			'number': {
				required: "请填写班级自定义编号",
			},
			'logo': {
				required: "请上传班级logo",
			},
			'allowableNumber': {
				required: "请填写学员总数",
			},
			'cost': {
				required: "请填写学费",
			},
			'headmasterId': {
				required: '请选择班主任',
				min: '请选择班主任',
			},
			'enrollStartTime': {
				required: "请填写报名开始时间",
			},
			'enrollEndTime': {
				required: "请填写报名结束时间",
			},
			'startTime': {
				required: "请填写课程开始时间",
			},
			'catering': {
				required: "请选择餐饮方式",
				min: "请选择餐饮方式",
			},
			'tangCollege': {
				required: "请选择校区",
				min: "请选择校区",
			},
			'hostel': {
				required: "请选择住宿方式",
				min: "请选择住宿方式",
			},
			'description': {
				required: "请填写内容",
			},
		},
		closest: '.form-group',
	};
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

	function getCourseInfo(el) {
		var val = el.val();
		if (val != '' || val != 0) {
			$.post('/courseManage/getCourseInfo.json', {
				'id': val
			},
			function(res) {
				if (res.id == '1001') {
					$(el).closest('div.form-group').find("[name='hour[]']").val(`${res.info.co_hour}课时`);
					$(el).closest('div.form-group').find("[name='credit[]']").val(`${res.info.co_credit}学分`);
				}
			},
			'json');
		}
	}

	function addRow(el) {
		var newRow = $(el).closest('.form-group').clone();
		newRow.find('.del-upload-row').click(function() {
			$(this).closest('div.form-group').remove();
		});

		$('a.del-upload-row', newRow).show();
		$('a.add-upload-row', newRow).hide();
		$(el).closest('.form-group').after(newRow);
		initCourse();
	}

	function initCourseRow() {
		$("a.add-upload-row").each(
		function() {
			$(this).click(
			function() {
				addRow($(this));
			});
		});

		$("a.del-upload-row").each(function() {
			$(this).click(function() {
				$(this).closest('div.form-group').remove();
			});
		});
	}

	function initCourse() {
		$("[name='course[]']").each(
		function() {
			$(this).change(
			function() {
				getCourseInfo($(this));
			});
		});
	}

	//获取模板信息
	function getCourseTemplateInfo() {
		$("[name='templateID']").change(function() {
			if ( !! $(this).val() == false) {
				return false;
			}
			$.post('/courseManage/getCourseTemplateInfo.json', {
				'id': $(this).val()
			},
			function(res) {
				if (1001 != res.id) {
					modalOptions.message = res.msg;
					Global.alert(modalOptions);
				}
				$("[name='description']").html(res.info.TempDescribe);
				var html = template('enrollTempCourseList', res.info);
				$("#courseList").html(html);
				initCourseRow();
				//getCourseTemplateInfo();
				initCourse();
			},
			'json');
		});
	}

	function initEiditDescription() {
		if (typeof ueditors === 'undefined') {
			ueditors = {};
		}
		var descriptionEdit = UE.getEditor('description',
      {
			  UEDITOR_HOME_URL: "/app/public/assets/plugins/ueditor/",
        serverUrl: "/app/public/assets/plugins/ueditor/php/controller.php",
        //toolbars:[["bold","italic","undo","redo"]]
        toolbars: [[
            'source', '|', 'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
            'directionalityltr', 'directionalityrtl', 'indent', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'pagebreak', 'template', 'background', '|',
            'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
            'preview', 'searchreplace'
        ]]
		  }
    );
	}

	return {
		'init': function() {
			flowModalUpload();
			initCourse();
			initCourseRow();
			getCourseTemplateInfo();
		},
		'addEnroll': function() {
      initEiditDescription();
			$('#formCourse').handleForm(validate, function(data, statusText) {
				modalOptions.message = data.info || data.msg;
				if (data.id == '1001') {
					bootbox.alert(data.msg, function() {
						Global.ajaxify('/enrollManage/index');
					});
				} else {
					Global.alert(modalOptions);
				}
			});
		}
	};
} ();

