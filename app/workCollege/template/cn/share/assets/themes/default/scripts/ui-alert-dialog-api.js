var UIAlertDialogApi = function () {

    var handleDialogs = function() {

        $('#demo_1').click(function(){
                bootbox.alert("Hello world!");    
            });
            //end #demo_1

            $('#demo_2').click(function(){
                bootbox.alert("Hello world!", function() {
                    alert("点击确定后的回调事件");//点击确定后的回调事件
                });  
            });
            //end #demo_2
        
            $('#demo_3').click(function(){
                bootbox.confirm("确定吗?", function(result) {
                   alert("结果: "+result);
				   /* if(result){//根据返回结果可扩展
					
				   }else{
					   
				   } */
                }); 
            });
            //end #demo_3

            $('#demo_4').click(function(){
                bootbox.prompt("你是谁?", function(result) {
                    if (result === null) {
                        alert("关闭");
                    } else {
                        alert(result+",你好!");
                    }
                });
            });
            //end #demo_6

            $('#demo_5').click(function(){
                bootbox.dialog({
                    title: "自定义标题",
                    message: "我是自定义消息内容,可自定义编辑,<p style=\"font-size:18px;color:#ff0000;\">支持html</p><p style=\"font-size:14px;color:#ff6600;\">高度自动</p>",
                    buttons: {
                      success: {
                        label: "成功!",
                        className: "btn-success",//参见按钮样式页面
                        callback: function() {//点击该按钮触发的事件
                          alert("成功了!!!");
                        }
                      },
                      danger: {
                        label: "危险!",
                        className: "btn-danger",
                        callback: function() {
                          alert("危险!!!");
                        }
                      },
                      main: {
                        label: "点我!",
                        className: "blue",
                        callback: function() {
                          alert("点我了!!!");
                        }
                      }
                    }
                });
            });
            //end #demo_7

    }

    var handleAlerts = function() {
        
        $('#alert_show').click(function(){
			var alertOptions= {
                container: $('#alert_container').val(), // alerts parent container(by default placed after the page breadcrumbs)
                place: $('#alert_place').val(), // append or prepent in container 
                type: $('#alert_type').val(),  // alert's type
                message: $('#alert_message').val(),  // alert's message
                close: $('#alert_close').is(":checked"), // make alert closable
                reset: $('#alert_reset').is(":checked"), // close all previouse alerts first
                focus: $('#alert_focus').is(":checked"), // auto scroll to the alert after shown
                closeInSeconds: $('#alert_close_in_seconds').val(), // auto close after defined seconds
                icon: $('#alert_icon').val() // put icon before the message
            }
            Global.alert(alertOptions);
			
			$("#alertsOptions").text("使用方法:\n \n 在js中执行以下代码(以下参数如果是默认值那么缺省及格):\n\nGlobal.alert( " + JSON.stringify(alertOptions, null, 2)) +"\n );\n";

        });

    }

    return {

        //main function to initiate the module
        init: function () {
            handleDialogs();
            handleAlerts();
        }
    };

}();