	var Grid = new Datatable();
	var table = $("#field-detail");
	var columns = [];
	$("thead > tr > th", table).each(
			function() { !! $(this).data('dt') && columns.push({
				"data": $(this).data('dt'),
				"sortable": !! $(this).data('sort'),
				"visible": ! $(this).data('hide')
			});
			});
	Grid.init({
		src: table,
		dataTable: {
			"ajax": {
				"url": "/itemPool/index.json"
			},
			"columns": columns,
			"order": [[0, "desc"]]
		}
	});
	function delPaper(id){
        bootbox.confirm("点击确认将删除该课件，该操作不可撤销，请确认无误后进行操作！", function(result) {
            if(result){
                //提交删除
        		$.ajax({
        			data:{
        				'id': id,
        			},
        			type:'get',
        			url:'/itemPool/delPaper.json',
        			dataType:'json',
        			success:function(result){
        				if(result.id == '1001'){
							bootbox.alert(result.info, function() {
								Grid.getDataTable().ajax.reload();//重新加载
							});
        				}else {
        					bootbox.alert(result.info);
        				}
        			}
        		});
            }
        });
	}
	
	function shareHandle(id, type, action){
                //提交删除
 		$.ajax({
 			data:{
 				'id': id,
 				'type' : type,
 				'action' : action
 			},
 			type:'get',
 			url:'/itemPool/share.json',
 			dataType:'json',
 			success:function(result){
 				if(result.id == '1001'){
					bootbox.alert(result.info, function() {
						Grid.getDataTable().ajax.reload();//重新加载
					});
   				}
   			}
   		});
            
	}


