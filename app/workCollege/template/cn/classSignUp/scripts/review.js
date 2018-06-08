$('#transfer_review').handleForm({
	rules: {
		'state': {
			required: true,
		},
		'reason': {
			required: true,
		}
	},
	messages: {
		'state': {
			required: "请填选择审核落地",
		},
		'reason': { 
			required: "请填选择审核落地",
		}
	},
	closest: '.form-group',
},
function(data, statusText) {
	var modalOptions = {
		"container": "#transfer_review",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};
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

$(function(){
	var ei = $("#large");
	ei.hide();
	$("#img1, img").mousemove(function(e){
		ei.css({top:e.pageY,left:e.pageX}).html('<img style="border:1px solid gray;" width="400%" height="400%" src="' + this.src + '" />').show();
    }).mouseout( function(){
        ei.hide();
    })
});