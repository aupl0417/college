<?php
return array(
    'base'    => array(
        /**
         * TODO:请改为实际的调用环境。测试环境：test，沙盒环境：shandbox，生产环境：product
         * 当前请求环境
         */
        'env'        => 'test',

        /**
         * TODO:推荐生产环境关闭日志，设置此项值为：FALSE
         * 是否记录日志
         */
        'isLog'      => TRUE,

        /**
         * TODO:请修改为实际申请到的开发者ID
         * 申请成为开发者之后，分配给应用的partner id
         */
        'partner_id' => '08b918547dbc09ec018b',

        /**
         * TODO:请修改为实际申请到的 app id
         * 申请成为开发者之后，分配给应用的partner id
         */
        'app_id'     => '7F00610BE0AF038E',

        /**
         * TODO:请修改为实际申请到的 secret key
         * 申请成为开发者之后，分配给应用的secret key
         */
        'secret_key' => '86E343067F00610BE0AF038EB1FEA5F8',

        /**
         * TODO:请修改为您实际需要的权限列表
         * 请求用户授权时向用户显示的可进行授权的列表
         */
        'scope'      => 'user.getUser',

        //用于展示的样式。一般不需要修改
        'view'       => 'pc',
    ),

    /*测试环境*/
    'test' => array(
        //授权登陆
        'oauth' => array(
            //登录授权地址
            'access_code_url'   => 'http://oauth.dterptest.com/authorize',

            //换取access token地址
            'access_token_url'  => 'http://oauth.dterptest.com/token',

            //刷新access token地址
            'refresh_token_url' => 'http://oauth.dterptest.com/refresh',

            //获取open_id地址
            'get_openid_url'    => 'http://oauth.dterptest.com/me/openid',

            /**
             * TODO:请修改为您网站的实际的回调地址
             * 成功授权后的回调地址，建议设置为网站首页或网站的用户中心
             */
            'redirect_uri'      => 'http://www.localhost.com/sdk/open_php_utf8/demo/oauth/callback.php',
        ),

        //开放平台
        'open'  => array(
            'api_url' => 'http://api.dterptest.com/',
        ),
    ),

    /*沙盒环境*/
    'sandbox' => array(
        //授权登陆
        'oauth' => array(
            //登录授权地址
            'access_code_url'   => 'http://oauth.af888.com/authorize',

            //换取access token地址
            'access_token_url'  => 'http://oauth.af888.com/token',

            //刷新access token地址
            'refresh_token_url' => 'http://oauth.af888.com/refresh',

            //获取open_id地址
            'get_openid_url'    => 'http://oauth.af888.com/me/openid',

            /**
             * TODO:请修改为您网站的实际的回调地址
             * 成功授权后的回调地址，建议设置为网站首页或网站的用户中心
             */
            'redirect_uri'      => 'http://www.localhost.com/sdk/open_php_utf8/demo/oauth/callback.php',
        ),

        //开放平台
        'open'  => array(
            'api_url' => 'http://api.af888.com/',
        ),
    ),

    /*生产环境*/
    'product' => array(
        //授权登陆
        'oauth' => array(
            //登录授权地址
            'access_code_url'   => 'https://oauth.dttx.com/authorize',

            //换取access token地址
            'access_token_url'  => 'https://oauth.dttx.com/token',

            //刷新access token地址
            'refresh_token_url' => 'https://oauth.dttx.com/refresh',

            'get_openid_url' => 'https://oauth.dttx.com/me/openid',

            /**
             * TODO:请修改为您网站的实际的回调地址
             * 成功授权后的回调地址，建议设置为网站首页或网站的用户中心
             */
            'redirect_uri'   => 'http://www.erp.com/sdk/open_php_utf8/demo/oauth/callback.php',
        ),

        //开放平台
        'open'  => array(
            'api_url' => 'https://api.dttx.com/',
        ),
    ),
);
//end of file config.php