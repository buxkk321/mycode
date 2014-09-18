<?php
namespace Modules\Admin;

class Module implements \Phalcon\Mvc\ModuleDefinitionInterface{

	/**
	 * Register a specific autoloader for the module
	 */
	public function registerAutoloaders(){
		$loader = new \Phalcon\Loader();
		
		$loader->registerNamespaces(
				array(
						'Modules\Admin\Controller' => './app/Admin/Controller/',
						'Modules\Admin\Model'      => './app/Admin/Model/',
						'Modules\Admin\Plugin'      => './app/Admin/Plugin/',
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
			$dispatcher->setDefaultNamespace('Modules\Admin\Controller');
			$dispatcher->setActionSuffix('');
			return $dispatcher;
		},true);
		
		$di->set('session', function () {
			$session = new \Phalcon\Session\Adapter\Files(array('uniqueId' => 'Admin_'));
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
			$view->setViewsDir('./app/Admin/View/');
			$view->registerEngines(array(
					".phtml" => function($view, $di){
						$volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
						$volt->setOptions(array(
								"compiledPath" => CACHE_PATH.'/',
								"compiledExtension" => ".compiled"
						));
						return $volt;
					}
			));
			return $view;
		});
			
	}

}