var Login = function () {

    var handleLogin = function () {

        $('#form_login').handleForm(
                {
                    rules: {
                        'username': {
                            required: true,
                            isworkname: true,
                            maxlength: 20
                        },
                        'password': {
                            required: true,
                            minlength: 6,
                            maxlength: 20
                        },
                        'code': {
                            required: true
                        }
                    },
                    messages: {
                        'username': {
                            required: "请填写员工号",
                            isusername: "员工编码不对",
                            maxlength: "用户名为9位字符",
                        },
                        'password': {
                            required: "请填写密码",
                            minlength: "密码为6-20位字符",
                            maxlength: "密码为6-20位字符"
                        },
                        'code': {
                            required: "请输入验证码图片中算术题的计算结果"
                        }
                    },
                    closest: 'div.form-group',
                    ass: {
                    }
                },
        function (data, statusText) {
            
            if (data.id == '2100') {
                msg = '<p>' + data.msg + '</p>';
/*                 for (e in data.info) {
                    msg += '<p>' + e + ': ' + data.info[e] + '</p>';
                } */
                bootbox.alert(msg, function () {
                    if (data.info.url) {
                        document.location.href = data.info.url;
                    }
                });
            } else {
                Global.alert({
                    "container": "#form_login",
                    "place": "prepend",
                    "type": "warning",
                    "message": data.msg,
                    "close": true,
                    "reset": true,
                    "focus": true,
                    "closeInSeconds": "0",
                    "icon": "warning"
                });
            }
        }
        );

        $('#form_login input').keypress(function (e) {
            if (e.which == 13) {
                if ($('#form_login').validate().form()) {
                    $('#form_login').submit();
                }
                return false;
            }
        });

        //验证码

        var showPopover = function () {
            $(this).popover('show').prop('placeholder', '请输入验证码图片中算术题的计算结果,点击可刷新');
            refreshCode();
        }
        , hidePopover = function () {
            $(this).popover('hide').prop('placeholder', '点击显示验证码');
        }
        , refreshCode = function () {
            $('.popover #img_verification_code').attr('src', '/validate/?type=code&code_len=4&font_size=16&width=100&height=30&font_color=&background=&refresh=1&time=' + Math.random());
        };

        $('input[name=code]').popover({
            html: true,
            placement: 'bottom',
            content: function () {
                return $('#html_verification_code').html();
            }, //$('<img id="img_verification_code" alt="" src="">'),
            trigger: 'manual'
        })
                .focus(showPopover)
                .blur(hidePopover)
                .click(refreshCode);
    }

    var handleForgetPassword = function () {
        $('.forget-form').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",
            rules: {
                email: {
                    required: true,
                    email: true
                }
            },
            messages: {
                email: {
                    required: "请输入Email"
                }
            },
            invalidHandler: function (event, validator) { //display error alert on form submit   

            },
            highlight: function (element) { // hightlight error inputs
                $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group
            },
            success: function (label) {
                label.closest('.form-group').removeClass('has-error');
                label.remove();
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element.closest('.input-icon'));
            },
            submitHandler: function (form) {
                form.submit();
            }
        });

        $('.forget-form input').keypress(function (e) {
            if (e.which == 13) {
                if ($('.forget-form').validate().form()) {
                    $('.forget-form').submit();
                }
                return false;
            }
        });

        jQuery('#forget-password').click(function () {
            jQuery('.login-form').hide();
            jQuery('.forget-form').show();
        });

        jQuery('#back-btn').click(function () {
            jQuery('.login-form').show();
            jQuery('.forget-form').hide();
        });

    }

    return {
        //main function to initiate the module
        init: function () {

            handleLogin();
            handleForgetPassword();

        }

    };

}();


