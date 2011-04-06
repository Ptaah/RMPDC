<?php
if(!version_compare(PHP_VERSION, '5.3.0', '>='))
{
    die('Sorry, Shozu needs at least PHP5.3.');
}
if(get_magic_quotes_gpc())
{
    die('You should turn magic_quotes off.');
}

require(__DIR__.'/Shozu.php');
Shozu::getInstance()->handle(array(
        // These are the default settings and closures. You may modify them or add your own.
        'project_root'          => __DIR__ . '/',
        'document_root'         => __DIR__ . '/docroot/',
        //'benchmark'             => false,
        'url_rewriting'         => false,
        'default_application'   => 'choonz',
        //'default_controller'    => 'index',
        //'default_action'        => 'index',
        'db_dsn'                => 'mysql:host=your_host;dbname=your_schema',
        'db_user'               => 'your_user_name',
        'db_pass'               => 'your_password',
        'redbean_start'         => false,
        //'redbean_freeze'        => false
        //'base_url'            => 'http://' . $_SERVER['HTTP_HOST'] . (dirname($_SERVER['SCRIPT_NAME']) != '/' ? dirname($_SERVER['SCRIPT_NAME']) . '/' : '/') ,
        //'debug'               => false
        //'routes'              => array(),
        //'obstart'             => true,
        //'include_path'        => explode(PATH_SEPARATOR, get_include_path()),
        //'error_handler'       => '',
        //'timezone'            => 'Europe/Paris',
        //'session_name'        => 'app_session',
        //'session'             => function(){return Session::getInstance(Shozu::getInstance()->session_name);}
        //'use_i18n'            => false,
));
