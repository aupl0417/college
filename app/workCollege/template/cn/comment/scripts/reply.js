$('#replyForm').handleForm({
	rules: {
		'reply': {
			required: true,
		}
	},
	messages: {
		'reply': {
			required: "请填写回复内容",
		}
	},
	closest: '.form-group',
},
function(data, statusText) { 
	var modalOptions = {
		"container": "#replyForm",
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
