<?php
namespace Modules\Port;

class Module implements \Phalcon\Mvc\ModuleDefinitionInterface{

	/**
	 * Register a specific autoloader for the module
	 */
	public function registerAutoloaders(){
		$loader = new \Phalcon\Loader();
		
		$loader->registerNamespaces(
				array(
						'Modules\Port\Controller' => './app/Port/Controller/',
						'Modules\Port\Model'      => './app/Port/Model/',
				)
		)->register();
	}

	/**
	 * Register specific services for the module
	 */
	public function registerServices($di){
		
		//Registering a dispatcher
		$di->set('dispatcher', function() {
			$dispatcher = new \Phalcon\Mvc\Dispatcher();
			$dispatcher->setDefaultNamespace('Modules\Port\Controller');
			$dispatcher->setActionSuffix('');
			return $dispatcher;
		},true);
		
		$di->set('session', function () {
			$session = new \Phalcon\Session\Adapter\Files(array('uniqueId' => 'Port_'));
			$session->start();
			return $session;
		},true);
		
		$di->set('acl', function() {
			$acl = new \Phalcon\Acl\Adapter\Memory();
			$acl->setDefaultAction(\Phalcon\Acl::DENY);
// 			$customersResource = new \Phalcon\Acl\Resource('admin');
			return $acl;
		},true);
			
		//Registering the view component
		$di->set('view', function() {
			$view = new \Phalcon\Mvc\View();
			return $view;
		});
			
	}

}