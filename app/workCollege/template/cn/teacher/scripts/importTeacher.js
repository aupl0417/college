/*=============================================================================
#     FileName: importTeacher.js
#         Desc: 导入讲师
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-21 09:11:34
#      History:
#      Paramer:
=============================================================================*/
var importTeacher = function() {
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

	function importTeacherInfo() {
		let validate = {
			rules: {
				'schValue': {
					required: "true",
				},
				'source': {
					required: true,
					min: 1
				},
				'userName': {
					required: true,
				},
				'sex': {
					required: true,
				},
				'mobile': {
					required: true,
				},
				'IDNum': {
					required: true,
					isID: true
				},
				'teacherLevel': {
					required: true,
					min: 1
				},
				'description': {
					required: true,
				},
				'workExperience': {
					required: true,
				},
				'courseReward': {
					required: true,
				},
				'teachGrade': {
					required: true,
					min: 1
				},
				'branchId': {
					required: true,
					min: 1
				},
				'eduLevel': {
					required: true,
					min: 1
				}
			},
			messages: {
				'schValue': {
					required: "请填写用户名或手机号",
				},
				'source': {
					required: '请选择来源',
					min: 1
				},
				'userName': {
					required: '请填写姓名',
				},
				'sex': {
					required: '请选择性别',
				},
				'mobile': {
					required: '请填写手机号',
				},
				'IDNum': {
					required: '请填写身份证号',
					isID: '请填写正确的身份证号'
				},
				'teacherLevel': {
					required: '请选择讲师等级',
					min: 1
				},
				'description': {
					required: '请填写简介',
				},
				'workExperience': {
					required: '请填写工作经历',
				},
				'courseReward': {
					required: '请填写课酬',
				},
				'teachGrade': {
					required: '授课类型',
					min: 1
				},
				'branchId': {
					required: '请选择所属分院',
					min: 1
				},
				'eduLevel': {
					required: '请选择讲师学历',
					min: 1
				}
			},
			closest: 'td',
		};
		$('#form').handleForm(validate, function(data, statusText) {
			modalOptions.message = data.info || data.msg;
			if (data.id != '1001') {
				Global.alert(modalOptions);
			} else {
				bootbox.alert(data.msg, function() {
					Global.ajaxify('/teacher/index');
				});
			}
		});
	}

	function searchTeacher() {
		$(".btn-search").each(function() {
			$(this).click(function() {
				$.post('teacher/searchTeacher.json', {
					'type': $("#fromType").val(),
					'value': $('#schValue').val()
				},
				function(res) {
					if (1001 != res.id) {
						modalOptions.message = res.info || res.msg;
						Global.alert(modalOptions);
					} else {
						bootbox.alert(res.msg, function() {
							$("[name='userName']").val(res.info.nick);
							$("[name='mobile']").val(res.info.tel);
							$("[name='trueName']").val(res.info.name);
							$("[name='userId']").val(res.info.id);
						});

					}
				},
				'json');
			});
		});
	};
	return {
		'init': function() {
			searchTeacher();
			importTeacherInfo();
		}
	};
} ();

