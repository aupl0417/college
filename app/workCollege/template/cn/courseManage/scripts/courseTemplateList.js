/*=============================================================================
#     FileName: courseTemplateList.js
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-14 17:40:20
#      History:
#      Paramer:
=============================================================================*/
var list = function() {
	var dataGrid = new Datatable();
  function delCourseTemp(id, name) {
		if ( !! id && !! name) {
			bootbox.confirm(`确定要删除【${name}】模板吗?`, function(res) {
				if (res) {
					$.ajax({
						'url': `/courseManage/delCourseTemp.json?id=${id}`,
						'dataType': 'JSON',
						'success': function(res) {
							if (res.id == '1001') {
								bootbox.alert(res.msg, function() {
									dataGrid.getDataTable().ajax.reload(null, false);
								});
							} else {
								var msg = res.info || res.msg;
								bootbox.alert(msg);
							}
						}
					});
				}
			});
		}
	}

	return {
		initList: function() {
			var table = $("#detail");
			var columns = [];
			$('thead > tr > th', table).each(function() { !! $(this).data('dt') && columns.push({
					"data": $(this).data('dt'),
					"sortable": !! $(this).data('sort'),
					"visible": ! $(this).data('hide')
				});
			});
			dataGrid.init({
				src: table,
				dataTable: {
					"ajax": {
						"type": "POST",
						"url": "/courseManage/courseTemplate.json"
					},
					"columns": columns,
					"order": [[0, "desc"]]
				}
			});

      table.on('draw.dt', function() {
        $(".delCourseTemp").each(function() {
          $(this).click(function(){
            delCourseTemp($(this).data('id'),$(this).data('name'));
          });
        });
      });

			return dataGrid;
		}
	};
} ();

var grid = list.initList();


var courseTemplate = function() {
  var validate = {
		rules: {
			'name': {
				required: true,
			},
			'state': {
				required: true,
			},
		},
		messages: {
			'name': {
				required: "请填写模板名",
			},
			'state': {
				required: "请选择状态",
			},
		},
		closest: '.form-group',
	};

	var modalOptions = {
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

	function getCourseInfo(el) {
		var val = el.val();
		if (val != '' || val != 0) {
			$.post('courseManage/getCourseInfo.json', {
				'id': val
			},
			function(res) {
				if (res.id == '1001') {
					$(el).closest('div.form-group').find("[name='hour[]']").val(`${res.info.co_hour} 课时`);
					$(el).closest('div.form-group').find("[name='credit[]']").val(`${res.info.co_credit} 学分`);
				}
			},
			'json');
		}
	}

	function addRow(el) {
		var newRow = $(el).closest('.form-group').clone();
		newRow.find('.del-upload-row').click(function() {
			$(this).closest('div.form-group').remove();
		});
		$('a.del-upload-row', newRow).show();
		$('a.add-upload-row', newRow).hide();
		$(el).closest('.form-group').after(newRow);
		initCourse();
	}

	function initCourse() {
		$("[name='course[]']").each(
		function() {
			$(this).change(
			function() {
				getCourseInfo($(this));
			});
		});
	}

	return {
		'editTemplate': function() {
			$('#formCourse').handleForm(validate, function(data, statusText) {
				modalOptions.message = data.info || data.msg;
				if (data.id != '1001') {
					Global.alert(modalOptions);
				} else {
					bootbox.alert(data.msg, function() {
						$('#formModal').modal('hide');
						grid.getDataTable().ajax.reload(null, false);
					});
				}
			});
		},
		'addCourse': function(el) {
			$(el).handleForm({
				rules: {},
				messages: {},
				closest: 'td',
				confirm: '确认修改课程模板',
				ass: {}
			},
			function(data, statusText) {
				if (data.id != '1001') {
					Global.alert(modalOptions);
				} else {
					bootbox.alert(data.msg, function() {
						$('#formModal').modal('hide');
						grid.getDataTable().ajax.reload();
					});
				}
			});
		},
		'addCourseTemplate': function(el) {
			var validate = {
				rules: {
					'name': {
						required: true,
					},
					'gradeID': {
						required: true,
						min: 1
					},
					'describe': {
						required: true,
					},
					'logo': {
						required: true,
					},
				},
				messages: {
					'name': {
						required: "请填写模板名",
					},
					'gradeID': {
						required: "请填写课程级别",
						mini: '请选择课程级别'
					},
					'describe': {
						required: "请填写描述",
					},
					'logo': {
						required: "请上传LOGO",
					},
				},
				closest: '.form-group',
			};
			$(el).handleForm(validate, function(data, statusText) {
				modalOptions.message = data.info || data.msg;
				if (data.id != '1001') {
					Global.alert(modalOptions);
				} else {
					bootbox.alert(data.msg, function() {
						Global.ajaxify('/courseManage/courseTemplate');
					});
				}
			});
		},
		'modalUpload': function() {
			$('.modalUpload').each(function(i) {
				$(this).handleUpload(function(data) {
					if (data.status == 'success') {
						var fileinput = $(".modalUpload:eq(" + i + ")").prev('.fileinput');
						$("input[type='hidden']", fileinput).val(data.savename);
						$('.thumbnail img', fileinput).prop('src', data.filename + '?r=' + Math.random());
						$('.thumbnail a.fancybox-button', fileinput).prop('href', data.filename + '?r=' + Math.random());
					} else {
						return false;
					}
				});
			});
		},
		'initAddCourseTemplate': function() {
			$("a.add-upload-row").each(
			function() {
				$(this).click(
				function() {
					addRow($(this));
				});
			});

			$("a.del-upload-row").each(
			function() {
				$(this).click(
				function() {
					$(this).closest('div.form-group').remove();
				});
			});

		},
		'initCourse': function() {
			initCourse();
		}
	};
} ();

