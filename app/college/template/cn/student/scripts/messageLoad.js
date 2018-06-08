var workOrderManager = function() {
	  
		grid = new Datatable();
		return {
			initWorkOrderList:function(num){
				if(num==1 || num==null){
					num=1;
				   	var table=$('#myMsglist1');
				}else if(num==2){
					var table=$('#myMsglist2');
				}else if(num==3){
					var table=$('#myMsglist3');
				}else if(num==4){
					var table=$('#myMsglist4');
				}
				$("thead > tr > th", table).each(
					function() { !!$(this).data('dt') && columns.push({
						"data": $(this).data('dt'),
						"sortable": !! $(this).data('sort'),
						"visible": ! $(this).data('hide')
					});
				});
				grid.init({
					src : table,
					loadingMessage : 'Loading...',
					dataTable : {
						fixedHeader : {
							header : true,
							headerOffset : 46
						},
						'bPaginate': true, //翻页功能
						'bFilter': true, //过滤功能
						'bLengthChange' : true,//改变每页显示数据数量
						"aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
						"iDisplayLength": 10,
						"iDisplayStart": 0,
						"dom" : "<'row'r>t<'row table-footer margin-bottom-10'<'col-md-6 col-sm-12'<'table-group-actions'>><'col-md-6 col-sm-12 text-right'pli>>",
						"bStateSave" : false,
						"bAutoWidth" : false,
						"bRetrieve": true,
						"sPaginationType": "full_numbers",
						"ajax" : {
							//"type" : "GET",
							"url" : "/message/index.json?type="+type+"&num="+ num,
						},
						"columns" : [//这些是表格列对应读取的数据,数量一定要对应上
			             	{"data" : "ids"},
							{
								"data" : "title",
								"mRender": function ( data, type, row, k ) {
									if(row.readed == '1'){
										return '<a class="font-grey-gallery" href="javascript:void(0);" onclick="javascript:Global.viewSiteMsg('+row.msgid+', \''+row.DT_RowId+'\');"><i class="fa fa-folder-open-o"></i> '+data+'</a>';
									}else{
										return '<a class="font-blue" href="javascript:void(0);" onclick="javascript:view('+row.msgid+', \''+row.DT_RowId+'\');"><i class="fa fa-envelope-o"></i> '+data+'</a>';
									}
								}
							},
							{"data" : "ctime"},
							{
								"data" : "op",
								"orderable" : false,
								"mRender": function ( data, type, row, k ) {
									if(row.readed == '1'){
										return '<a href="javascript:;" onclick="javascript:Global.viewSiteMsg('+row.msgid+');get_unrear_msg();" class="btn btn-xs blue"><i class="fa fa-search"></i> 查看</a>&nbsp;<a onclick="javascript:delMessage('+row.msgid+');" class="btn btn-xs blue"><i class="fa fa-search"></i> 删除</a>';
									}else{
										return '<a href="javascript:;" onclick="javascript:view('+row.msgid+', \''+row.DT_RowId+'\');get_unrear_msg();" class="btn btn-xs blue"><i class="fa fa-search"></i> 查看</a>&nbsp;<a onclick="javascript:delMessage('+row.msgid+');" class="btn btn-xs blue"><i class="fa fa-search"></i> 删除</a>';
									}
								}
							},
							{"data" : "readed","visible" : false},
							{"data" : "deleted","visible" : false},
						],
						"aaSorting": [[ 1, "desc" ]],
						"order" : [
							[5, "asc"],
							[2, "desc"]
						]
					}
				});

				$('.search').click(function(){
					var title=$("#listState").val();
					if(title!=''){
						$('tbody > tr', grid.getTable()).remove(); //清空table
						grid.getDataTable().ajax.url().load(); //重新加载
					}
				});

				grid.getTableWrapper().on('click', '.table-action-buttons', function (e) {
					e.preventDefault();
					var ids=grid.getSelectedRows();
					var count=grid.getSelectedRowsCount();
					var action=$(this).val();
					if (action!="" && count>0) {
						$.ajax({
							data:{
								'id': ids,
								'type':action,
							},
							type:'get',
							url:'/message/myInbox.json',
							dataType:'json',
							success:function(result){
								if(result.code == '1001'){
									grid.getDataTable().ajax.reload();
									var number = $('#header_notification_bar').find("span.badge-success").html();
									if(number == ''){
										number = 0;
									}else {
										number = parseInt(number);
									}
									if(action == 0){
										sum = number + count;
									}else {
										sum = number - count;
									}
									if(sum<=0){
										$('#header_notification_bar').find("span.badge-success").html('');
									}else {
										$('#header_notification_bar').find("span.badge-success").html(sum);
									}
									for(var i=0;i<count;i++){
										get_unrear_msg();
									}
								}
							}
						});
					} else if (action=="") {
						Global.alert({
							type:'danger',
							icon:'warning',
							message:'请选择一个有效的操作',
							container:grid.getTableWrapper(),
							//place: 'prepend',
							closeInSeconds:5
						});
					} else if (count===0) {
						Global.alert({
							type:'danger',
							icon:'warning',
							message:'没有选中记录',
							container:grid.getTableWrapper(),
							//place: 'prepend',
							closeInSeconds:5
						});
					}
					//get_unrear_msg();
				});

				var i=0;
				grid.getTableWrapper().on('click', '.group-checkable', function(e){
					i++;
					var cls=$(this).parent().attr('class');
					if(cls=='checked' && i>2){
						$('tbody > tr', grid.getTable()).each(function(){
							$(this).find('input').click();
						});
					}
				});
				
				return grid;
			},
		};
	} ();

	function view(id, row){
		Global.viewSiteMsg(id, function(el){
			var table=grid.getDataTable();
			var rowData=table.row('#'+row).data();
			rowData.readed=1;
			get_unrear_msg();
			table.row('#'+row).data(rowData);
			//table.draw();
		});
	}
	function delMessage(id){
        bootbox.confirm("确定删除信息吗?", function(result) {
            if(result){
                //提交删除
        		$.ajax({
        			data:{
        				'id': id,
        				'type':type,
        			},
        			type:'get',
        			url:'/message/delete.json',
        			dataType:'json',
        			success:function(result){
        				if(result.id == '1001'){
							bootbox.alert(result.msg, function() {
								grid.getDataTable().ajax.reload();//重新加载
								get_unrear_msg();
							});
        				}
        			}
        		});
            }
        });
	}