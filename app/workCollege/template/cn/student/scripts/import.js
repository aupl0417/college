function importStudent(nickName){
    bootbox.confirm("确定删除信息吗?", function(result) {
        if(result){
            //提交删除
    		$.ajax({
    			data:{
    				'id': nickName,
    			},
    			type:'get',
    			url:'/student/import.json',
    			dataType:'json',
    			success:function(result){
    				if(result.id == '1001'){
						bootbox.alert(result.msg, function() {
							StudentGrid.getDataTable().ajax.reload();//重新加载
						});
    				}
    			}
    		});
        }
    });
}

$('#submit').click(function(){
	 var url = "/student/getUser.json";
	 var username = $('input[name=username]').val();
	 var mobile   = $('input[name=mobile]').val();
	 $.ajax({
			data:{
				'username': username,
				'mobile'  : mobile
			},
			type: 'post',
			url : url,
			dataType:'json',
			success:function(data){
				console.log(data);
				var info = data.info;
				if(info.email == null){
					info.email = '';
				}
				if(info.name == null){
					info.name = '';
				}
				if(info.tel == null){
					info.tel = '';
				} 
//				<a href="/branch/importStudent/?_ajax=1&username=" data-target="#temp-modal-power" data-toggle="modal" class="btn btn-sm btn-success table-group-action-submit deal_btn"><i class="fa fa-edit"></i> 导入</a>
				if(data.id == '1001' && info != ''){
					var html = '<tr>';
					$('.studentList').html('');
					html += '<td>' + info.nick + '</td>';
					html += '<td>' + info.name + '</td>';
					html += '<td>' + info.email + '</td>';
					html += '<td>' + info.tel + '</td>';
				    html += '<td><a href="/student/importStudent/?_ajax=1&username=' + info.nick + '" data-target="#temp-modal-power" data-toggle="modal" class="btn-xs blue"><i class="fa fa-edit"></i> 导入</a></td>';
					html += '</tr>';
				    $('.studentList').append(html);
				}else {
					$('.studentList').html('');
				}
			}
		});
});