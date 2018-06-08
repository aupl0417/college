$('#transfer_detail').handleForm({
	rules: {
		'name': {
			required: true,
		},
		'courseId': {
			required: true,
		},
		'description': {
			required: true,
		},
		'cre_a': {
			required: true,
		},
		'cre_b': {
			required: true,
		},
		'cre_c': {
			required: true,
		},
		'cre_d': {
			required: true,
		},
		'answer': {
			required: true,
		}
	},
	messages: {
		'name': {
			required: "请填写题目问题",
		},
		'courseId': {
			required: "请选择所属课程",
		},
		'description': {
			required: "请填写题目描述",
		},
		'cre_a': {
			required: '请填写选项A',
		},
		'cre_b': {
			required: '请填写选项B',
		},
		'cre_c': {
			required: '请填写选项C',
		},
		'cre_d': {
			required: '请填写选项D',
		},
		'answer': {
			required: '请填写答案',
		}
	},
	closest: '.form-group',
},
function(data, statusText) {
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
	modalOptions.message = data.info || data.msg;
	if (data.id == '1001') {
		bootbox.alert(data.info, function() {
			$('#temp-modal-power').modal('hide');
			Grid.getDataTable().ajax.reload(null, false);
		});
	} else {
		Global.alert(modalOptions);
	}
});

