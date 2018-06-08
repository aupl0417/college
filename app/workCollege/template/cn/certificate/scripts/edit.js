$('#transfer_detail').handleForm({
	rules: {
		'name': {
			required: true,
		},
		'username': {
			required: true,
		},
		'userType': {
			required: true,
		},
		'certType': {
			required: true,
		},
		'condition': {
			required: true,
		}
		
	},
	messages: {
		'name': {
			required: "请填写证书名称",
		},
		'username': {
			required: "请用户名",
		},
		'userType': {
			required: "请选择用户类型",
		},
		'certType': {
			required: "请选择证书类型",
		},
		'condition': {
			required: "请选择获得条件",
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
		bootbox.alert(data.info, function() {});
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

