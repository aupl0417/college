/*=============================================================================
#     FileName: editTeam.js
#         Desc: 分组 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-17 17:50:17
#      History:
#      Paramer: 
=============================================================================*/

var teamManage = function() {
	var alertMsg = {
		"container": "#formTeam",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};
	function handelTeamForm() {
		$('#formTeam').handleForm({
			rules: {
				'allowableNumber': {
					required: true,
				}
			},
			messages: {
				'allowableNumber': {
					required: '允许报名人数不能为空',
				}
			},
			closest: 'td',
			ass: {}
		},
		function(data, statusText) {
			if (data.id == '1001') {
				bootbox.alert(data.msg, function() {
					grid.getDataTable().ajax.reload();
					$('#formModal').modal('hide');
				});
			} else {
				alertMsg.message = data.info || data.msg;
				Global.alert(alertMsg);
			}
		});
	}

	function reCalc(el) {
    if ('allowableNumber' == el.attr('name')) {
     var allowNum = el.val();
     var num = $('[name=team]').val();
    }else{
     var allowNum = $('[name=allowableNumber]').val();
     var num = el.val();
    }

    var teamNum = studentNum = 0;

    if (allowNum < 1 || num < 1) {
      return false;
    }

    //if (allowNum % num != 0) {
    //  alertMsg.message = '分组信息不能平均，请检查后再试';
    //  Global.alert(alertMsg);
    //}

    //var arrangeNum = + (allowNum / num);

    //if ('studentNum' != el.attr('name')) {
    //  $('[name=studentNum]').val(arrangeNum);
    //} else if ($('[name=studentNum]').val() > 0) {
    //  $('[name=team]').val(arrangeNum);
    //}
  }

  return {
    'init': function() {
      handelTeamForm();
      $('[name=team]').blur(function() {
        reCalc($(this));
      });
      //$('[name=studentNum]').blur(function() {
      //  reCalc($(this));
      //});
      $('[name=allowableNumber]').blur(function() {
        reCalc($(this));
      });
    },
  };
} ();

