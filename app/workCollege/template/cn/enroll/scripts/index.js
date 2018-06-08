var list = function() {
	var dataGrid = new Datatable();

	function closeOrder(id) {
		if ( !! id) {
			bootbox.confirm('关闭订单，用户将不能付款，您确定操作吗？', function(res) {
				if (res) {
					$.ajax({
						'url': '/enroll/closeEnroll.json?id=' + id,
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

	function delOrder(id) {
		if ( !! id) {
			bootbox.confirm('删除订单，您确定操作吗？', function(res) {
				if (res) {
					$.ajax({
						'url': '/enroll/delEnroll.json?id=' + id,
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
	
	//批量审核
	$('.review').click(function(){
		bootbox.confirm('你确定要批量审核通过吗？该操作确定后不可逆?', function(res) {
			if (res) {
				var result = new Array();
				$("input[name='ids[]']:checked").each(function(){
					var value = $(this).val();
					result.push(value);
				});
				$.ajax({
					'url': '/enroll/batchReview.json',
					'type' : 'post',
					'data' : {ids : result},
					'dataType': 'JSON',
					'success': function(data) {
						if (data.id == '1001') {
							bootbox.alert(data.msg, function() {
								dataGrid.getDataTable().draw(false);
							});
						} else {
							var msg = data.info || data.msg;
							bootbox.alert(msg);
						}
					}
				});
			}
		});
	});

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
						"url": "/enroll/index.json"
					},
					"columns": columns,
					"order": [[0, "desc"]]
				}
			});

			table.on('draw.dt', function() {
				$("a.closeOrder").each(function() {
					$(this).click(function() {
						var id = $(this).data('id');
						closeOrder(id);
					});
				});
			});

			table.on('draw.dt', function() {
				$("a.delOrder").each(function() {
					$(this).click(function() {
						var id = $(this).data('id');
						delOrder(id);
					});
				});
			});

			return dataGrid;
		}
	};
} ();

var grid = list.initList();

$('#download').click(function() {
	bootbox.confirm('你确定要导出学员报名信息表格吗?', function(res) {
		if (res) {
			if ( !! $("[name='cl_number']").val() == false && !! $("[name='cl_name']").val() == false) {
				bootbox.alert('请选择填写班级编号或班级名称', function() {});
			} else {
				$("[name='act']").val('downloadEnrollInfo');
				$('#filter-detail').prop("action", 'enrollManage/exl.json').submit();
			}
		}
	});
	return false;
});



