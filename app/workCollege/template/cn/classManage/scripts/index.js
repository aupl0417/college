/*=============================================================================
#     FileName: index.js
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-14 17:24:21
#      History:
#      Paramer:
=============================================================================*/
var flowModalUpload = function() {
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
};

var list = function() {
	var dataGrid = new Datatable();
	var table = $("#detail");

	function changeClassState(id, name, state) {
		if ( !! id && !! name && !! state) {
			var alertInfo = '';
			switch (state) {
			case 2 : alertInfo = '点击确认班级课程已经全部结束，该操作不可撤销， 请确认无误后进行操作！ 确定结束【' + name + '】班级?'; break;
			case 1 : alertInfo = '点击确认该班级将正式开课，该操作不可撤销， 请确认无误后进行操作！ 确定要【' + name + '】班级开课?'; break;
			case -1 : alertInfo = '点击确认将取消发布该班级，该操作不可撤销， 请确认无误后进行操作！ 确定要撤销【' + name + '】班级?'; break;
			}
			bootbox.confirm(alertInfo, function(res) {
				if (res) {
					$.ajax({
						'url': '/classManage/changeClassState.json?id=' + id + '&state='+state,
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
						"url": "/classManage/index.json"
					},
					"columns": columns,
					"order": [[3, "desc"]]
				}
			});

			table.on('draw.dt', function() {
				$("a.changeClassState").each(function() {
					$(this).click(function() {
						var data = $(this).data();
						changeClassState(data.id, data.name, data.state);
					});
				});
				$("a.cancelClass").each(function() {
					$(this).click(function() {
						var data = $(this).data();
						cancelClass(data.id, data.name);
					});
				});
				$("a.beginClass").each(function() {
					$(this).click(function() {
						var data = $(this).data();
						beginClass(data.id, data.name);
					});
				});
			});

			return dataGrid;
		}
	};
} ();

var grid = list.initList();
