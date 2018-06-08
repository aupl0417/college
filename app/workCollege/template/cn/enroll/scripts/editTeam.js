$('#formTeam').handleForm({
	rules: {
		'team': {
			required: true,
      min:1
		},
	},
	messages: {
		'team': {
			required: "请填分组",
      min:"请填分组",
		},
	},
	closest: 'td',
	ass: {}
},
function(data, statusText) {
	if (data.id == '1001') {
		bootbox.alert(data.msg, function() {
			grid.getDataTable().draw(false);
			$('#formModal').modal('hide');
		});
	} else {
		var msg = data.info || data.msg;
		Global.alert({
			"container": "#formTeam",
			"place": "prepend",
			"type": "warning",
			"message": msg,
			"close": true,
			"reset": true,
			"focus": true,
			"closeInSeconds": "0",
			"icon": "warning"
		});
	}
});

