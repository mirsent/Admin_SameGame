<?php
return array(
    'LOAD_EXT_CONFIG' => 'db,web_config',
    'TMPL_PARSE_STRING' => array(
        '__PUBLIC__'    => __ROOT__.'/Public',
        '__BOWER__'     => __ROOT__.'/Public/bower_components',
        '__STATICS__'   => __ROOT__.'/Public/statics',
        '__ADMIN_CSS__' => __ROOT__.trim(TMPL_PATH,'.').'/Admin/Public/css',
        '__ADMIN_JS__'  => __ROOT__.trim(TMPL_PATH,'.').'/Admin/Public/js',
        '__ADMIN_IMG__' => __ROOT__.trim(TMPL_PATH,'.').'/Admin/Public/img',
        '__HOME_CSS__'  => __ROOT__.trim(TMPL_PATH,'.').'/Home/Public/css',
        '__HOME_JS__'   => __ROOT__.trim(TMPL_PATH,'.').'/Home/Public/js',
        '__HOME_IMG__'  => __ROOT__.trim(TMPL_PATH,'.').'/Home/Public/img',
    ),
    'AUTH_CONFIG' => array(
        'AUTH_USER' => 'user'
    ),
);
