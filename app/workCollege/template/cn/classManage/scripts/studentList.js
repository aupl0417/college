/*=============================================================================
#     FileName: studentList.js
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-14 17:24:05
#      History:
#      Paramer:
=============================================================================*/
var studentList = function() {
	let grid = new Datatable();
	let table = $("#studentListTable");

	function delStudent(id, name, clid) {
		if ( !! id && !! name && !! clid) {
			bootbox.confirm(`确定要删除【${name}】学员吗?`, function(res) {
				if (res) {
					$.ajax({
						'url': `/classManage/delStudent.json?id=${id}&clID=${clid}`,
						'dataType': 'JSON',
						'success': function(res) {
							if (res.id == '1001') {
								bootbox.alert(res.msg, function() {
									grid.getDataTable().ajax.reload(null, false);
								});
							} else {
								let msg = res.info || res.msg;
								bootbox.alert(msg);
							}
						}
					});
				}
			});
		}
	}

	return {
		init: function(clID) {
			let columns = [];
			$("thead > tr > th", table).each(
			function() { !! $(this).data('dt') && columns.push({
					"data": $(this).data('dt'),
					"sortable": !! $(this).data('sort'),
					"visible": ! $(this).data('hide')
				});
			});
			grid.init({
				src: table,
				dataTable: {
					"ajax": {
						"url": `/classManage/studentList.json?clID=${clID}`
					},
					"columns": columns,
					"order": [[0, "desc"]]
				}
			});

			table.on('draw.dt', function() {
				$("a.delStudent").each(function() {
					$(this).click(function() {
						let data = $(this).data();
						delStudent(data.id, data.name, data.clid);
					});
				});
			});

			return grid;
		},
	};
} ();

