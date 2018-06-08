
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
						"url": "/branch/index.json"
					},
					"columns": columns,
					"order": [[1, "desc"]]
				}
			});
			return dataGrid;
		}
	};
} ();

var grid = list.initList();

var getSelectList = function(selectId, appendId){
	$('#' + selectId).change(function(){
		var pid = $(this).val();
		$.ajax({
			data:{
				'id': pid,
			},
			type:'post',
			url:'/branch/getArea.json',
			dataType:'json',
			success:function(data){
				if(data.id == '1001'){
					$('#' + appendId).html('');
					var html = '<option value="">--请选择--</option>';
					var info = data.info;
					var length = info.length;
					for(var i=0; i<length; i++){
						html += '<option value="' + info[i].id +'">' + info[i].name + '</option>';
					}
					$('#' + appendId).append(html);
				}
			}
		});
	});
}

getSelectList('province', 'cityCode');

getSelectList('cityCode', 'conty');

function delBranch(id){
    bootbox.confirm("点击确认将删除该分院，该操作不可撤销，请确认无误后进行操作！", function(result) {
    	if(result){
        //提交删除
		$.ajax({
			data:{
				'id': id,
			},
			type:'get',
			url:'/branch/del.json',
			dataType:'json',
			success:function(result){
				if(result.id == '1001'){
					bootbox.alert(result.info, function() {
						grid.getDataTable().ajax.reload();//重新加载
					});
				}else {
					bootbox.alert(result.info);
				}
			}
		});
    	}
    });
}


