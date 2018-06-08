/*=============================================================================
#     FileName: delayClass.js
#         Desc: 延迟开课
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-14 17:24:25
#      History:
#      Paramer:
=============================================================================*/
var delayClass = function() {
	let modalOptions = {
		"container": "#form",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};

	function delayClass() {
		let validate = {
			rules: {
				'startTime': {
					required: true,
				},
				'endTime': {
					required: true,
				},
			},
			messages: {
				'startTime': {
					required: "请填写课程开始时间",
				},
				'endTime': {
					required: "请填写课程结束时间",
				},
			},
			closest: '.form-group',
		};
		$('#form').handleForm(validate, function(data, statusText) {
			modalOptions.message = data.info || data.msg;
			if (data.id != '1001') {
				Global.alert(modalOptions);
			} else {
				bootbox.alert(data.msg, function() {
					$('#formModal').modal('hide');
					grid.getDataTable().ajax.reload();
				});
			}
		});
	}
	return {
		'init': function() {
			delayClass();
		},
	};
} ();

