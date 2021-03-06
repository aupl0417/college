
var teachRecord = function() {
	var dataGrid = new Datatable();
	return {
		initList: function() {
			var table = $('#teachRecord');
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
					"bDestroy":true,
					"ajax": {
						"type": "post",
						"url": `/teacher/detail.json`
		},
			"columns": columns,
					"order": [[0, "desc"]]
		}
		});

			table.on('draw.dt', function() {
				$("a.delClass").each(function() {
					$(this).click(function() {
						var data = $(this).data();
						delClass(data.id, data.name);
					});
				});
			});

			return dataGrid;
		}
	};
} ();

var certificate = function() {
	var dataGrid = new Datatable();
	return {
		initList: function() {
			var table = $('#certificate');
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
					"bDestroy":true,
					"ajax": {
						"type": "post",
						"url": `/public/getCertificate.json?userType=1`
		},
			"columns": columns,
					"order": [[0, "desc"]]
		}
		});

			table.on('draw.dt', function() {
				$("a.delClass").each(function() {
					$(this).click(function() {
						var data = $(this).data();
						delClass(data.id, data.name);
					});
				});
			});

			return dataGrid;
		}
	};
} ();
