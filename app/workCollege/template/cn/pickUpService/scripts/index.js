var list = function() {
	var dataGrid = new Datatable();
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
						"url": "/pickUpService/index.json"
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

			$('#download').click(function() {
				bootbox.confirm('你确定要导出接站信息表格吗?', function(res) {
					if (res) {
						if ( !! $("[name='tse_classId']").val() == false) {
              bootbox.alert('请选择班级', function() {return true;});
						} else {
              $('#filter-detail').prop("action", 'enrollManage/exl.json').submit();
						}
					}
				});
			});

			return dataGrid;
		}
	};
} ();

var grid = list.initList();

