
    $(function () {
        $('#form_edit').handleForm(
                {
                    rules: {
                        'logo': {
                            required: true
                        }
                    },
                    messages: {
                        'logo': {
                            required: "请上传封面图"
                        }
                    },
                    closest: 'div.form-group',
                    ass: {}
                },
                function (data, statusText) {
                    if (data.id == '1001') {
                        bootbox.alert(data.msg, function () {
                            console.log(data.info);
                            Global.ajaxclick('31101');
                            $('#formModal').modal('hide');
                            var domain   = window.location.host;
    						var protocol = window.location.protocol
    						var url = protocol + '//' + domain + '/?return=/classManage/glimpse?clID=' + classId + '&root=1';
    	                    window.location.href = url;
                        });
                    } else {
                        Global.alert({
                        	"container": "#form_edit",
                			"place": "prepend",
                			"type": "warning",
                			"message": data.info,
                			"close": true,
                			"reset": true,
                			"focus": true,
                			"closeInSeconds": "0",
                			"icon": "warning"
                        });
                    }

                }
        );
        $('#msg_annex').handleUpload(function (data) {
            console.log(data);

        });
    });
    