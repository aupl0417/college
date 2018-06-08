/*=============================================================================
#     FileName: schedule.js
#         Desc: 临时调课
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-15 10:19:59
#      History:
#      Paramer: 
=============================================================================*/
var classSchedule = function() {
	let modalOptions = {
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
	let validate = {
		rules: {
			'trainingsiteId': {
				required: true,
				min: 1,
			},
			'teacherId': {
				required: true,
				//min: 1,
			},
			'startTime': {
				required: true,
			},
			'endTime': {
				required: true,
			},
		},
		messages: {
			'trainingsiteId': {
				required: "请选择培训地点",
				min: "请选择培训地点",
			},
			'teacherId': {
				required: "请选择讲师",
				min: "请选择讲师",
			},
			'startTime': {
				required: "请填写上课开始时间",
			},
			'endTime': {
				required: "请填写上课结束时间",
			},
		},
		closest: '.form-group',
	};
	return {
		'init': function() {
			$('#formCourse').handleForm(validate, function(data, statusText) {
				modalOptions.message = data.info || data.msg;
				if (data.id == '1001') {
					bootbox.alert(data.msg, function() {
            		$('#formModal').modal('hide');
					Global.ajaxify('/classSchedule/index');
					});
				} else {
					Global.alert(modalOptions);
				}
			});
		}
	};
} ();

