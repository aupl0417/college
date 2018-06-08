$('#formTransfer').handleForm({
	rules: {
		'classId': {
			required: true,
		}
	},
	messages: {
		'classId': {
			required: "请选择班级",
		}
	},
	closest: '.form-group',
},
function(data, statusText) {
	var modalOptions = { 
		"container": "#formTransfer",
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