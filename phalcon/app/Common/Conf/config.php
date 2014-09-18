<?php
return array(
	'DB_HOST'   => '114.215.134.108',
	'DB_PORT'   => 3306,
	'DB_TYPE'	=>	'Mysqli',
	'DB_NAME'   => DEFAULT_DB_NAME,
	'DB_USER'   => 'root',
	'DB_PWD'    => 'weiyun@1401',
		
		'PICTURE_UPLOAD' => array(
				'mimes'    => '', //允许上传的文件MiMe类型
				'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
				'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
				'autoSub'  => true, //自动子目录保存文件
				'subName'  => array('date', 'd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
				'rootPath' => '.'.UPLOAD_DIR.'/Picture/', //保存根路径
				'savePath' => '', //保存路径
				'saveName' => array('uniqid_ex', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
				'saveExt'  => '', //文件保存后缀，空则使用原后缀
				'replace'  => false, //存在同名是否覆盖
				'hash'     => true, //是否生成hash编码
				'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
		), //图片上传相关配置（文件上传类配置）
	
	'LOG_RECORD' => true,
	'LOG_RECORD_DETAIL'=>true,//记录详细信息
);
