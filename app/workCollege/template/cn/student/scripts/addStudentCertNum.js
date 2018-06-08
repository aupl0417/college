var studentCertNum = function() {
	let modalOptions = {
		"container": "#studentInfoDetail",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};
	let validate = {
		rules: {
			'certNum': {
				required: true,
				isID: true,
			},
			'trueName': {
				required: true,
			},
		},
		messages: {
			'certNum': {
				required: "请填写身份证号",
				isID: "请填写身份证号",
			},
			'trueName': {
				required: "请填写真实姓名",
			},
		},
		closest: 'td',
	};

	return {
		init: function() {
			$('#studentInfoDetail').handleForm(validate, function(data, statusText) {
				modalOptions.message = data.info || data.msg;
				if (data.id == '1001') {
					bootbox.alert(data.msg, function() {
						$('#formModal').modal('hide');
						if (studentGrid) {
							studentGrid.getDataTable().ajax.reload();
						}
					});
				} else {
					Global.alert(modalOptions);
				}
			});
		}
	};
} ();

