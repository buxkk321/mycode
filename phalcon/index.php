<?php

require './app/Common/Conf/head.php';
require './app/Common/Function/public.php';
require './app/Common/Function/phalcon.php';
require './app/Common/Function/thinkphp.php';
try{
	C(include './app/Common/Conf/config.php');
	include './app/Common/Conf/services.php';

// 	$eventsManager = new Phalcon\Events\Manager();
// 	$eventsManager->attach(
// 			'application:beforeStartModule',
// 			function($event, $application) {
// 				if ($application->router->getModuleName() == 'Admin'){
// 					$auth=$application->session->get('user_auth');
// 					$application->persistent->IS_ROOT=$auth['id']===1?true:false;
// 				}
// 			}
// 	);
	$loader = new \Phalcon\Loader();
	$loader->registerNamespaces(
			array(
					'Think' => './ThinkPhp/',
			)
	)->register();
	$app = new \Phalcon\Mvc\Application($di);
// 	$app->setEventsManager($eventsManager);
	$app->registerModules(
			array(
					'Admin' => array(
							'className' => 'Modules\Admin\Module',
							'path'      => './app/Admin/Module.php',
					),
					'Home'  => array(
							'className' => 'Modules\Home\Module',
							'path'      => './app/Home/Module.php',
					),
					'Port'  => array(
							'className' => 'Modules\Port\Module',
							'path'      => './app/Port/Module.php',
					)
			)
	);
	
	echo $app->handle()->getContent();
	
} catch(\Phalcon\Exception $e) {
	if(APP_DEBUG===true){
		dump($e->__toString());
	}else{
		echo "PhalconException: ", $e->getMessage();
	}
} catch (\PDOException $e){
	if(APP_DEBUG===true){
		dump($e->__toString());
	}else{
		echo "PhalconException: ", $e->getMessage();
	}
}

?>