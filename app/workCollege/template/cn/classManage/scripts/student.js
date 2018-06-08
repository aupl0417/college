/*=============================================================================
#     FileName: student.js
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-14 17:24:11
#      History:
#      Paramer:
=============================================================================*/
var student = function() {
	let modalOptions = {
		"container": "#formStudent",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};

	function searchStudent() {
		$(".btn-search").each(function() {
			$(this).click(function() {
				if ($("#formStudent").valid()) {
					$.post('classManage/searchStudent.json', {
						'type': 1,
						'value': $('#schValue').val()
					},
					function(res) {
						if (1001 != res.id) {
							modalOptions.message = res.info || res.msg;
							Global.alert(modalOptions);
						} else {
							$("#userName").val(res.info.username);
							$("#mobile").val(res.info.mobile);
							$("#btn-submit").attr('disabled', false);
						}
					},
					'json');
				}
			});
		});
	};

	function addStudent() {
		let validate = {
			rules: {
				'schValue': {
					required: true,
				},
			},
			messages: {
				'schValue': {
					required: "请填写用户名或手机号",
				},
			},
			closest: 'td',
		};
		$('#formStudent').handleForm(validate, function(data, statusText) {
			modalOptions.message = data.info || data.msg;
			if (data.id != '1001') {
				Global.alert(modalOptions);
			} else {
				bootbox.alert(data.msg, function() {
					//$('#formModal').modal('hide');
				});
			}
		});
	}

	return {
		'init': function() {
			searchStudent();
			addStudent();
		},
	};
} ();

