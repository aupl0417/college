/*=============================================================================
#     FileName: certainStudent.js
#         Desc:  
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-15 20:42:11
#      History:
#      Paramer: 
=============================================================================*/
$('#formEmployeeCheckIn').handleForm({
	rules: {
		'trueName': {
			required: true
		},
		'certNum': {
			required: true,
			isID: true,
		}
	},
	messages: {
		'trueName': {
			required: '真实姓名不能为空',
		},
		'certNum': {
			required: '身份证不能为空',
			isID: '身份证格式不正确'
		}
	},
	closest: 'td',
	ass: {}
},
function(data, statusText) {
	var alertMsg = {
		"container": "#formEmployeeCheckIn",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};
	if (data.id == '1001') {
		bootbox.alert(data.msg, function() {
			$('#formModal').modal('hide');
			studentGrid.getDataTable().ajax.reload();
		});
	} else {
		if ( !! data.info.black) {
			var log = data.info.log;
      $("#blackLogDiv tbody").html('');
			for (var i in log.enrollNotArrival) {
				var html = '<td>' + log.enrollNotArrival[i].cl_name + '</td><td>' + log.enrollNotArrival[i].tse_createTime + '</td><td>'+log.enrollNotArrival[i].result+'</td>';
				$('<tr></tr>').html(html).appendTo("#blackLogDiv tbody");
			}
			for (var i in log.studyLog) {
				var html = '<td>' + log.studyLog[i].cl_name + '</td><td>' + log.studyLog[i].cs_createTime + '</td><td>'+log.studyLog[i].result+'</td>';
				$('<tr></tr>').html(html).appendTo("#blackLogDiv tbody");
			}
      $("#blackLogDiv").css('display','block');
      $("#formEmployeeCheckIn [type=submit]").attr('disabled',true);
		} else {
			alertMsg.message = data.info || data.msg;
			Global.alert(alertMsg);
		}
	}
});

