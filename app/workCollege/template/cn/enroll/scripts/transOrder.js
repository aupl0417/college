$('#formOrder').handleForm({
	rules: {
		'oriUser': {
			required: true,
		},
		'oriMobile': {
			required: true,
		},
		'purUser': {
			required: true,
		},
		'purMobile': {
			required: true,
		},
		'classId': {
			required: true,
		},
	},
	messages: {
		'oriUser': {
			required: "请填写报名人用户名",
		},
		'oriMobile': {
			required: "请填写报名人手机号",
		},
		'purUser': {
			required: "请填写报到用户名",
		},
		'purMobile': {
			required: "请填写报到人手机号",
		},
		'classId': {
			required: "请选择班级",
		},
	},
	closest: '.form-group',
},
function(data, statusText) {
	var modalOptions = {
		"container": "#formOrder",
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

$('input[name=purMobile]').blur(function() {
	if (false == !! $(this).val()) {
		return false;
	}

	$.ajax({
		data: {
			'mobile': $(this).val(),
		},
		type: 'post',
		url: '/enroll/checkArrivalUser.json',
		dataType: 'json',
		success: function(data) {
			if (data.id == '1001') {
				var msg = data.info.username;
				$('#arraivalUserName').val(msg);
				$('#info').html(data.info.msg);
				if(data.info.state == 0){
					$('button[type=submit]').attr('disabled', true);
				}
			} else {
				alertInfo(data.info);
			}
		}
	});
});

function getUserInfo() {
	var username = $('input[name=oriUser]').val();
	var oriMobile = $('input[name=oriMobile]').val();

	if (username != '' || oriMobile != '') {
		$.ajax({
			data: {
				'username': username,
				'mobile': oriMobile,
			},
			type: 'post',
			url: '/enroll/getClass.json',
			dataType: 'json',
			success: function(data) {
				$('.classId').html('');
				var html = '';
				if (data.id == '1001') {
          var data = data.info;
          $('input[name=oriUser]').val(data.userInfo.username);
          $('input[name=oriMobile]').val(data.userInfo.mobile);
          $('#trueName').val(data.userInfo.trueName);
					var length = data.classList.length;
					if (length < 1) {
						html += '<option value="">学员没有报名班级</option>'
					} else {
						for (var i = 0; i < length; i++) {
							html += '<option value="' + data.classList[i].id + '">' + data.classList[i].className + '</option>'
						}
					}

					$('.classId').html(html);
				} else {
					$('.classId').html(html);
					alertInfo(data.info);
				}
			}
		});
	}
}

$('input[name=oriMobile]').bind('input propertychange', function(){ getUserInfo();});
$('input[name=oriUser]').bind('input propertychange', function(){ getUserInfo();});

var alertInfo = function(msg) {
	var modalOptions = {
		"container": "#formOrder",
		"place": "prepend",
		"type": "warning",
		"message": msg,
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};
	Global.alert(modalOptions);
}

