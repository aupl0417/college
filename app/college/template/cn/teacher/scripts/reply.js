$('#replyForm').handleForm({
	rules: {
		'content': {
			required: true,
		}
	},
	messages: {
		'content': {
			required: "请填写回复内容2",
		}
	},
	closest: '.form-group',
},
function(data, statusText) { 
	var modalOptions = {
		"container": "#replyForm",
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
			$('#temp-modal-power').modal('hide');
			grid.getDataTable().ajax.reload(null, false);
		});
	} else {
		Global.alert(modalOptions);
	}
});

$('.reply').click(function(){
	var pid = $(this).attr('index');
	var obj = $('#reply_' + pid);
	var action = obj.attr('index');
	
	//点击当前回复时，隐藏其它打开的回复列表
	$('.replylist').each(function(){
		var id = $(this).attr('id');
		if(id != 'reply_' + pid){
			var o_pid = id.split('_')[1];
			$('#reply_' + o_pid).attr('index', 'hide');
			$('.replyList').hide();
			$('#replyLabel_' + o_pid).addClass('hide');
			$('#replyContent_' + o_pid).addClass('hide');
		}
	});
	
	if(action == 'hide'){
		$.ajax({
			url: '/teacher/getReply.json',
			dataType: 'json',
			data: {pid:pid},
			success: function(res) {
				if (1001 == res.id) {
					obj.attr('index', 'show');
					if(res.info.length > 0){
						showReplyList(res.info, obj);
					}
					$('#replyLabel_' + pid).removeClass('hide');
	      			$('#replyContent_' + pid).removeClass('hide');
				}
			}
		});
	}else if(action == 'show'){
		obj.attr('index', 'hide');
		$('.replyList').hide();
		$('#replyLabel_' + pid).addClass('hide');
		$('#replyContent_' + pid).addClass('hide');
	}
});

$('.submit').click(function(){
	var $obj = $(this).parent().parent();
	var pid = $obj.find($("input[name='pid']")).val();
	var classId = $obj.find($("input[name='classId']")).val();
	var courseId = $obj.find($("input[name='courseId']")).val();
	var content = $obj.parent().find($("textarea[name='reply']")).val();
	console.log(content);
	
	if(content == ''){
		layer.alert('请输入评论内容');
		return false;
	}
	
	if(!pid || !classId || !courseId){
		layer.alert('参数非法');
		return false;
	}
	
	$.ajax({
		url: '/teacher/reply.json',
		dataType: 'json',
		data: {pid:pid,classId:classId,courseId:courseId,content:content},
		success: function(res) {
			if (1001 == res.id) {
				$obj.parent().find($("textarea[name='reply']")).val('');
				$('#reply_' + pid).attr('index', 'hide');
				$('.replyList').hide();
				$('#replyLabel_' + pid).addClass('hide');
				$('#replyContent_' + pid).addClass('hide');
				layer.alert(res.info);
				var html = '';
				html += "<label class='control-label replyList col-md-2'></label>";
		      	html += "<div class='col-md-10 replyList' style='width:600px;height:auto;'>";
				html += "<div class='row' style='margin-top:20px;margin-left:50px;border:1px solid #eee;display: block;'>";
				html += "	<div class='col-md-10' style='margin-top:20px;'>";
				html += "		" + data[i].content;
				html += "	</div>";
				html += "	<div class='col-md-10' style='margin-top:10px;margin-bottom:20px;'>"
				html += "		<label></label>" + data[i].user + "</label><label style='margin-left:50px;'><font style='font-size:10px;'>" + data[i].createTime + "</font></label>";
				html += "	</div>";
				html += "</div>";
				html += "</div>";
				$('#reply_' + pid).prepend(html);
			}else {
				layer.alert(res.info);
			}
		}
	});
});

//点击取消按钮，隐藏当前的回复列表
$('.closeList').click(function(){
	var $obj = $(this).parent().parent();
	var pid = $obj.find($("input[name='pid']")).val();
	$('#reply_' + pid).attr('index', 'hide');
	$('.replyList').hide();
	$('#replyLabel_' + pid).addClass('hide');
	$('#replyContent_' + pid).addClass('hide');
});

//显示当前点击的回复列表
function showReplyList(data, obj){
	var html = '';
	var length = data.length;
	for(var i=0; i<length; i++){
		html += "<label class='control-label replyList col-md-2'></label>";
      	html += "<div class='col-md-10 replyList' style='width:600px;height:auto;'>";
		html += "<div class='row' style='margin-top:20px;margin-left:50px;border:1px solid #eee;display: block;'>";
		html += "	<div class='col-md-10' style='margin-top:20px;'>";
		html += "		" + data[i].content;
		html += "	</div>";
		html += "	<div class='col-md-10' style='margin-top:10px;margin-bottom:20px;'>"
		html += "		<label></label>" + data[i].user + "</label><label style='margin-left:50px;'><font style='font-size:10px;'>" + data[i].createTime + "</font></label>";
		html += "	</div>";
		html += "</div>";
		html += "</div>";
	}
	obj.append(html);
}

