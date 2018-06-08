var fileImport = function() {
	var validate = {
		rules: {
			'name': {
				required: true,
				min: 1
			},
			'upload': {
				required: true,
			},
		},
		messages: {
			'name': {
				required: "请选择班级名",
				min: 1
			},
			'upload': {
				required: "请填写课程描述",
			},
		},
		closest: '.form-group',
	};

	var modalOptions = {
		"container": "#fileImportForm",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};

	return {
		init: function() {
			$('#fileImportForm').handleForm(validate, function(data, statusText) {
				modalOptions.message = data.info || data.msg;
				if (data.id == '1001') {
					bootbox.alert(data.msg, function() {
						var info = data.info;
						var tr = {};
						for (var i in info) {
							tr = $('<tr></tr>');
							tr.html('<td>' + info[i].mobile + '</td><td>' + info[i].msg + '</td>');
              tr.appendTo("#resContent tbody");
						}
					});
				} else {
					Global.alert(modalOptions);
				}
			});
		}
	};
} ();

