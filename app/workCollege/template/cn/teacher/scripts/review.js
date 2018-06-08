$('#teacher_promotion').handleForm({
	rules: {
		'state': {
			required: true,
		},
		'reason': {
			required: true,
		}
	},
	messages: {
		'state': {
			required: "请填选择审核落地",
		},
		'reason': {
			required: "请填写原因",
		}
	},
	closest: '.form-group',
},
function(data, statusText) {
	var modalOptions = {
		"container": "#teacher_promotion",
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

