/*=============================================================================
#     FileName: attendanceList.js
#         Desc: 学员签到记录
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-14 17:24:29
#      History:
#      Paramer: 
=============================================================================*/
var attendanceList = function(clID) {

  let table = $("#attendanceListTable");
	let grid  = new Datatable();

	return {
		'init': function(clID) {
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
						"url": `/classManage/attendanceList.json?clID=${clID}`
					},
					"columns": columns,
					"order": [[0, "desc"]]
				}
			});

      return grid;
		}
	};
} ();

