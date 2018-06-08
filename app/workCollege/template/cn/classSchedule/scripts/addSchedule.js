/*=============================================================================
#     FileName: addSchedule.js
#         Desc: 添加排课 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-23 17:27:36
#      History:
#      Paramer: 
=============================================================================*/
var classSchedule = function() {
	let startDate = '';
	let endDate = '';

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

	let validate = {
		rules: {
			'classID': {
				required: true,
				min: 1
			},
			'classStartTime': {
				required: true,
			},
			'classEndTime': {
				required: true,
			},
		},
		messages: {
			'classID': {
				required: "请选择班级名",
			},
			'classStartTime': {
				required: "请选择课程开始时间",
			},
			'classEndTime': {
				required: "请选择课程结束日期",
			},
		},
		closest: '.form-group',
		confirm: function() {
			return '确定提交班级课程安排!';
		}
	};

	function addSchedule() {
		$('#form').handleForm(validate, function(data, statusText) {
			modalOptions.message = data.info || data.msg;
			if (data.id == '1001') {
				bootbox.alert(data.msg, function() {
					Global.ajaxify('/classSchedule/index');
				});
			} else {
				Global.alert(modalOptions);
			}
		});
	}

	function initDateTimePicker() {
		$(".form-datetime").each(function() {
			$(this).datetimepicker({
				format: 'HH:mm',
				useStrict: true,
			});
		});
	}

	function initDatePicker(start, end) {
		startDate = start;
		endDate = end;

    $("[name='classStartTime']").val(start);
    $("[name='classEndTime']").val(end);


		$("[name='scheduleDate[]']").each(function() {
			$(this).datepicker({
				format: "yyyy-mm-dd"
			});
			$(this).datepicker('setEndDate', new Date(endDate));
			$(this).datepicker('setStartDate', new Date(startDate));
			$(this).val(start);
		});
	}

	function addRow(el) {
		let newRow = $(el).closest('.form-group').clone();
		newRow.find('.del-upload-row').click(function() {
			$(this).closest('div.form-group').remove();
		});

		$('a.del-upload-row', newRow).show();
		$('a.add-upload-row', newRow).hide();
		$(el).closest('.form-group').after(newRow);
		initDateTimePicker();
		initDatePicker(startDate, endDate);
	}

	function initCourseRow() {
		$("a.add-upload-row").each(
		function() {
			$(this).click(
			function() {
				addRow($(this));
			});
		});

		$("a.del-upload-row").each(function() {
			$(this).click(function() {
				$(this).closest('div.form-group').remove();
			});
		});
	}

	function initClass() {
		$("[name='classID']").on('change', function() {
			let classID = $(this).val();
			if ('' != classID) {
				$.post('/classSchedule/searchClassCourse.json', {
					'classID': classID
				},
				function(res) {
					if (res.id == '1001') {
						$("[name='course[]']").each(function() {
							$(this).html(res.info.courseList);
						});

						initDatePicker(res.info.classInfo.startDate, res.info.classInfo.endDate);
					} else {
						modalOptions.message = res.info || res.msg;
						Global.alert(modalOptions);
					}
				},
				'JSON');
			}
		});
	}

	return {
		'init': function() {
			initDateTimePicker();
			initCourseRow();
			addSchedule();
			initClass();
		}
	};
} ();
