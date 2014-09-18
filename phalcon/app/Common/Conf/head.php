<?php


define('DEFAULT_DB_NAME','wyof');
define('APP_DEBUG', true );

// 系统常量定义
defined('APP_PATH') 	or define('APP_PATH',       dirname($_SERVER['SCRIPT_FILENAME']));

defined('UPLOAD_DIR')	or define('UPLOAD_DIR', '/Uploads');
defined('RUNTIME_PATH') or define('RUNTIME_PATH',   APP_PATH.'/Runtime');  // 系统运行时目录

defined('LOG_PATH')     or define('LOG_PATH',       RUNTIME_PATH.'/Logs'); // 应用日志目录
defined('DATA_PATH')    or define('DATA_PATH',      RUNTIME_PATH.'/Data'); // 应用数据目录
defined('CACHE_PATH')   or define('CACHE_PATH',     RUNTIME_PATH.'/Cache'); // 应用模板缓存目录

is_dir(APP_PATH.UPLOAD_DIR) 	or mkdir(APP_PATH.UPLOAD_DIR);
is_dir(RUNTIME_PATH) 	or mkdir(RUNTIME_PATH);

is_dir(LOG_PATH) 	or mkdir(LOG_PATH);
is_dir(DATA_PATH) 	or mkdir(DATA_PATH);
is_dir(CACHE_PATH)	or mkdir(CACHE_PATH);

define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);

if(!IS_CLI) {
	
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',    rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        $_root  =   rtrim(dirname(_PHP_FILE_),'/');
        define('__ROOT__',  (($_root=='/' || $_root=='\\')?'':$_root));
    }
}

define('__APP__',str_replace('index.php','',strip_tags(_PHP_FILE_)));

define('__PUBLIC__',	__ROOT__.'/Public');

