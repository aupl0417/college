/*=============================================================================
#     FileName: studentReport.js
#         Desc: 学员报到
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 21:56:37
#      History:
#      Paramer: 
=============================================================================*/
var studentReport = function() {
	var studentGrid = {};

	function initClassInfo() {
		var table = $("#classInfo");
		initTable(table, 'enrollManage/schClass.json', 0);
		table.on('draw.dt', function() {
			$("#classInfo i.fa-search").each(function() {
				var data = $(this).data();
				$("[name='clID']").val(data.clid);
				$("#detail tbody").html('');
			});
		});
	}

	function delStudent(id, name, clid) {
		if ( !! id && !! name && !! clid) {
			bootbox.confirm(`确定要删除【${name}】学员吗 ? `, function(res) {
				if (res) {
					$.ajax({
						'url': `/classManage/delStudent.json?id=${id}&clID=${clid}`,
						'dataType': 'JSON',
						'success': function(res) {
							if (res.id == '1001') {
								bootbox.alert(res.msg, function() {
									studentGrid.getDataTable().ajax.reload(null, false);
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

	function certainStudent(id, name, clid) {
		if ( !! id && !! name && !! clid) {
			bootbox.confirm(`确定【${name}】学员已经报到吗 ? `, function(res) {
				if (res) {
					$.ajax({
						'url': `/classManage/certainStudent.json?id=${id}&clID=${clid}`,
						'dataType': 'JSON',
						'success': function(res) {
							if (res.id == '1001') {
								bootbox.alert(res.msg, function() {
									studentGrid.getDataTable().ajax.reload(null, false);
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

	function downdTable() {
		$('#download').click(function() {
			bootbox.confirm('你确定要导出学员报到表格吗?', function(res) {
				if (res) {
					if ( !! $("[name='clID']").val() == false && !! $("[name='tse_classId']").val() == false) {
						bootbox.alert('请选择需要导出学员报名信息的班级', function() {
							$("[name='classId']").focus();
						});
					} else {
						$("[name='act']").val('downloadStudentInfo');
						$('#filter-detail').prop("action", 'enrollManage/exl.json?eeee=11').submit();
					}
				}
			});
			return false;
		});
	}

	function initStudentList() {
		studentGrid = initTable($("#detail"), 'enrollManage/reportStudent.json', 4);
	}

	function initTable(table, url, order) {
		var dataGrid = new Datatable();
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
					"url": url
				},
				"columns": columns,
				"order": [[order, "desc"]]
			}
		});

		table.on('draw.dt', function() {
			$("a.delStudent").each(function() {
				$(this).click(function() {
					var data = $(this).data();
					delStudent(data.id, data.name, data.clid);
				});
			});

			$("a.certainStudent").each(function() {
				$(this).click(function() {
					var data = $(this).data();
					certainStudent(data.id, data.name, data.clid);
				});
			});
		});

		return dataGrid;
	}

	return {
		init: function() {
			initClassInfo();
			downdTable();
		},
		initStudentList: function() {
			initStudentList();
			return studentGrid;
		}
	};
} ();

//查询学员报到信息
function inTimeSchStudent() {
	var alertMsg = {
		"container": "#formCheckIn",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};

  if(1 != $("#studentReportInTimeSet").val()){
    clearInterval(scanStudentInterver);
    return false;
  }

	clid = $("[name='tse_classId']").val();
	if ( !! clid) {
		//$("[name='mobile']").val((new Date()).toString());  
		var modalObj = $("#formModal");
		if ('block' != modalObj.css('display') ) {
			$.post('enrollManage/getStudentInfoInTime', {
				'id': clid,
			},
			function(res) {
				if (res.id == '1001') {
					$("[name=studentInfoInTime]").attr('id', res.info.tempId);
					var html = template(res.info.tempId, res.info);
					$(".modal-body", modalObj).html(html);
					modalObj.modal('show');

					$('#formCheckIn').handleForm({
						rules: {
							'trueName': {
								required: true
							},
							'certNum': {
								required: true
							}
						},
						messages: {
							'trueName': {
								required: '真实姓名不能为空',
							},
							'certNum': {
								required: '身份证不能为空',
							}
						},
						closest: 'td',
						ass: {}
					},
					function(data, statusText) {
						if (data.id == '1001') {
							bootbox.alert(data.msg, function() {
								studentGrid.getDataTable().ajax.reload();
								$('#formModal').modal('hide');
							});
						} else {
							alertMsg.message = data.info || data.msg;
							Global.alert(alertMsg);
						}
					});

					$("#delayDeal").on('click', function() {
						var clID = $("#formCheckIn [name='clID']").val();
						bootbox.confirm('确定要延迟处理', function(result) {
              if (result) {
                $.get('enrollManage/delayDeal.json?clID=' + clID, function(res) {
								alertMsg.message = res.msg || res.info;
                if (1001 == res.id) {
								  $('#formModal').modal('hide');
                }else{
								  Global.alert(alertMsg);
                }
							},
							'JSON');
              }
						});
					});
				} else {
					//bootbox.alert(res.info);
				}
			},
			'JSON');
		}
	}
}

