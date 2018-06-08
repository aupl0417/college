/*=============================================================================
#     FileName: index.js
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-15 10:20:08
#      History:
#      Paramer:
=============================================================================*/
var flowModalUpload = function() {
	$('.modalUpload').each(function(i) {
		$(this).handleUpload(function(data) {
			if (data.status == 'success') {
				let fileinput = $(".modalUpload:eq(" + i + ")").prev('.fileinput');
				$("input[type='hidden']", fileinput).val(data.savename);
				$('.thumbnail img', fileinput).prop('src', data.filename + '?r=' + Math.random());
				$('.thumbnail a.fancybox-button', fileinput).prop('href', data.filename + '?r=' + Math.random());
			} else {
				return false;
			}
		});
	});
};

var list = function() {
	let dataGrid = new Datatable();

	function delSchedule(id, name) {
		if ( !! id && !! name) {
			bootbox.confirm(`确定要删除【${name}】排课记录?`, function(res) {
				if (res) {
					$.ajax({
						'url': `/classSchedule/delSchedule.json?id=${id}`,
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
						"url": "/classSchedule/index.json"
					},
					"columns": columns,
					"order": [[5, "desc"]]
				}
			});

			table.on('draw.dt', function() {
				$("a.delSchedule").each(function() {
					$(this).click(function() {
						let data = $(this).data();
						delSchedule(data.id, data.name);
					});
				});
			});
			return dataGrid;
		}
	};
} ();
var grid = list.initList();

