/*=============================================================================
#     FileName: course.js
#         Desc: 课程管理 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-14 17:40:28
#      History:
#      Paramer: 
=============================================================================*/
$('#formCourse').handleForm({
	rules: {
		'name': {
			required: true,
		},
		'description': {
			required: true,
		},
		'content': {
			required: true,
		},
		'hour': {
			required: true,
		},
		'credit': {
			required: true,
		},
		'studyDirectionId': {
			required: true,
			min: 1,
		},
		'gradeID': {
			required: true,
			min: 1,
		},
		'courseLogo': {
			required: true,
		}
	},
	messages: {
		'name': {
			required: "请选择课程名",
		},
		'description': {
			required: "请填写课程描述",
		},
		'content': {
			required: "请填写课程说明",
		},
		'credit': {
			required: "请填写学分",
		},
		'hour': {
			required: "请填写学时",
		},
		'studyDirectionId': {
			required: "请选择课程类别",
			min: "请选择课程类别",
		},
		'gradeID': {
			required: "请选择课程级别",
			min: "请选择课程级别",
		},
		'courseLogo': {
			required: "请上传Logo",
		}
	},
	closest: '.form-group',
},
function(data, statusText) {
	let modalOptions = {
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
	modalOptions.message = data.info || data.msg;
	if (data.id == '1001') {
		bootbox.alert(data.msg, function() {
			$('#formModal').modal('hide');
			grid.getDataTable().ajax.reload(null, false);
		});
	} else {
		Global.alert(modalOptions);
	}
});

flowModalUpload();

