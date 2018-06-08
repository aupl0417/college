/*=============================================================================
#     FileName: courseDirection.js
#         Desc: 课程分类管理 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 20:42:06
#      History:
#      Paramer: 
=============================================================================*/
var courseDirection = function() {
	let dataGrid = new Datatable();
	let validate = {
		rules: {
			'name': {
				required: true,
			},
			'description': {
				required: true,
			},
			'content': {
				required: true,
			},
		},
		messages: {
			'name': {
				required: "请填写课程分类名",
			},
			'description': {
				required: "请填写课程分类描述",
			},
		},
		closest: '.form-group',
	};

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

	function initTable() {
		let table = $("#detail");
		let columns = [];
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
					"url": "/courseManage/courseDirection.json"
				},
				"columns": columns,
				"order": [[0, "desc"]]
			}
		});
	}

	function courseDirectionForm() {
		$('#formCourse').handleForm(validate, function(data) {
			modalOptions.message = data.info || data.msg;
			if (data.id == '1001') {
				bootbox.alert(data.msg, function() {
					$('#formModal').modal('hide');
					dataGrid.getDataTable().ajax.reload(null, false);
				});
			} else {
				Global.alert(modalOptions);
			}
		});
	}

	return {
		init: function() {
			initTable();
		},
		courseDirectionForm: function() {
			courseDirectionForm();
		}
	};
} ();
