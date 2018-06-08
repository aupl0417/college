var trainingSite = function() {
	let dataGrid = new Datatable();

	function delSite(id, name) {
		if ( !! id && !! name) {
			bootbox.confirm(`确定要删除【${name}】 ? `, function(res) {
				if (res) {
					$.ajax({
						'url': `classRoomManage/delSite.json?id=${id}`,
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

  function initTalbeList(){
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
						"url": "/classRoomManage/index.json"
					},
					"columns": columns,
					"order": [[0, "desc"]]
				}
			});

			table.on('draw.dt', function() {
				$("a.delSite").each(function() {
					$(this).click(function() {
						let data = $(this).data();
						delSite(data.id, data.name);
					});
				});
			});
			return dataGrid;
  }

  let validate = {
	rules: {
		'name': {
			required: true,
		},
		'area': {
			required: true,
		},
		'address': {
			required: true,
		},
		'property': {
			required: true,
		},
		'type': {
			required: true,
		},
	},
	messages: {
		'name': {
			required: "请选择课室名称",
		},
		'area': {
			required: "请选择所在区域",
		},
		'address': {
			required: "请填写课室详细地址",
		},
		'property': {
			required: "请选择产权类型",
		},
		'type': {
			required: "请选择课室类型",
		},
	},
	closest: '.form-group',
  };
  
  let modalOptions = {
		"container": "#form",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};

	return {
		init: function() {
      return initTalbeList();
		},
    trainingSite:function(){
      $('#form').handleForm(validate,function(data){
        modalOptions.message = data.info || data.msg;
        if (data.id == '1001') {
          bootbox.alert(data.msg, function() {
            $('#formModal').modal('hide');
            grid.getDataTable().ajax.reload(null, false);
          });
        } else {
            Global.alert(modalOptions);
          }
      });
    }
	};
} ();

