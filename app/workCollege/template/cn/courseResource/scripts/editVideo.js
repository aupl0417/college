$('#transfer_detail').handleForm({
	rules: {
		'name': {
			required: true,
		},
		'courseId': {
			required: true,
		}
	},
	messages: {
		'name': {
			required: "请填写课件名称",
		},
		'courseId': {
			required: "请选择所属课程",
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
			$('#formModal').modal('hide');
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
				console.log(data.filename);
				$('#video1').attr('poster', data.filename);
			} else {
				return false;
			}
		});
	});
};

flowModalUpload();
