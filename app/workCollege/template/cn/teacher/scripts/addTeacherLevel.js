$('#formCourse').handleForm({
	rules: {
		'name': {
			required: true,
		},
		'badgeName': {
			required: true,
		},
		// 'logo': {
			// required: true,
		// },
		'courseLevel': {
			required: true,
		},
		'courseType': {
			required: true,
		},
		'condition': {
			required: true,
		}
	},
	messages: {
		'name': {
			required: "请填写教师等级名称",
		},
		'badgeName': {
			required: "请填写徽章名称",
		},
		// 'logo': {
			// required: "请上传徽章图片",
		// },
		'courseLevel': {
			required: "请选择可授课等级",
		},
		'courseType': {
			required: "请选择授课类型",
		},
		'condition': {
			required: "请填写晋升条件",
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
		bootbox.alert(data.msg, function() {
			$('#temp-modal-power').modal('hide');
			Grid.getDataTable().ajax.reload(null, false);
		});
	} else {
		Global.alert(modalOptions);
	}
});

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

flowModalUpload();
