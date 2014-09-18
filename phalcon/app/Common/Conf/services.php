<?php
$di = new \Phalcon\DI\FactoryDefault();

$di->set('router', function () {

	$router = new \Phalcon\Mvc\Router();
	$router->removeExtraSlashes(true);
	$router->add('/',array(
			'module' => 'Home',
	));
	$router->add('/:module',array(
			'module' => 1,
	));
	$router->add('/:module/:controller/:action', array(
			'module' => 1,
			'controller' => 2,
			'action' => 3
	));
	$router->notFound(array(
			'module'     => 'Home',
			'controller' => 'Index',
			'action' => 'route404'
	));
	return $router;
},true);

$di->set('url', function(){
	$url = new \Phalcon\Mvc\Url();
	$url->setBaseUri(__APP__);
	return $url;
},true);

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function() use ($di){
	
	$connect=new \Phalcon\Db\Adapter\Pdo\Mysql(array(
			"host" => C('DB_HOST'),
			"username" => C('DB_USER'),
			"password" => C('DB_PWD'),
			"dbname" => C('DB_NAME')
	));
	
	$eventsManager = new Phalcon\Events\Manager();
	if(C('LOG_RECORD')===true){
		$logger = new \Phalcon\Logger\Adapter\File(LOG_PATH.'/sql'.date('Y-m-d').'.log');
		$dispatcher=$di->get('dispatcher');
		$eventsManager->attach('db:beforeQuery', function($event, $connection) use ($logger,$dispatcher) {
			$logger->log('SQL:'.$connection->getSQLStatement(), Phalcon\Logger::INFO);
			if(C('LOG_RECORD_DETAIL')===true){
				$logger = new \Phalcon\Logger\Adapter\File(LOG_PATH.'/debug'.date('Y-m-d').'.log');
				$str='';
				foreach (debug_backtrace () as $vv){
					isset($vv['file']) && $str.='
file:'.$vv['file'].' ,line:'.$vv['line'];
					$str.='
	function:'.$vv['function'].' ,class:'.$vv['class'].' ,type:'.$vv['type'];
				}
				$logger->log('detail:'.$str, Phalcon\Logger::DEBUG);
			}
		});
		$connect->setEventsManager($eventsManager);
	}
	return $connect;
},true);

$di->set('modelsManager', function() {
	return new \Phalcon\Mvc\Model\Manager();
},true);
/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
// $di->set('modelsMetadata', function () {
//     return new MetaDataAdapter();
// });


