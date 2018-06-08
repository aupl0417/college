
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
						"url": "/teacher/interactionManage.json?id=" + courseId + '&cId=' + classId
					},
					"columns": columns,
					"order": [[0, "desc"]]
				}
			});
			return dataGrid;
		}
	};
} ();

var grid = list.initList();