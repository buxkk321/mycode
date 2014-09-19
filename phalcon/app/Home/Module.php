<?php
namespace Modules\Home;

class Module implements \Phalcon\Mvc\ModuleDefinitionInterface{

	/**
	 * Register a specific autoloader for the module
	 */
	public function registerAutoloaders(){
		$loader = new \Phalcon\Loader();
		
		$loader->registerNamespaces(
				array(
						'Modules\Home\Controller' => './app/Home/Controller/',
						'Modules\Home\Model'      => './app/Home/Model/',
				)
		);

		$loader->register();
	}

	/**
	 * Register specific services for the module
	 */
	public function registerServices($di){
		
		//Registering a dispatcher
		$di->set('dispatcher', function() {
			$dispatcher = new \Phalcon\Mvc\Dispatcher();
			$dispatcher->setDefaultNamespace('Modules\Home\Controller');
			$dispatcher-> setActionSuffix('');
			return $dispatcher;
		});

		$di->set('session', function () {
			$session = new \Phalcon\Session\Adapter\Files(
					array(
							'uniqueId' => ROOT_NAME.'/Home'
					)
			);
			$session->start();
			return $session;
		},true);
		
		//Registering the view component
		$di->set('view', function() {
			$view = new \Phalcon\Mvc\View();
			$view->setViewsDir('./app/Home/View/');
			$view->registerEngines(array(
					".phtml" => 'Phalcon\Mvc\View\Engine\Volt'
			));
			return $view;
		});
	}

}