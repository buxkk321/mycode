<?php
namespace Modules\Admin\Controller;
class IndexController extends \Phalcon\Mvc\Controller {
	public function beforeExecuteRoute($dispatcher){
		$auth = $this->session->get('user_auth');
		if(@$auth['is_login']==null){
			$dispatcher->forward(
					array(
							'controller' => 'Public',
							'action' => 'login'
					)
			);
			return false;
		}
		if($auth['id']==1){return true;}
		
		if (!$auth) {
			$role = 'Guest';
		} else {
			$role = $auth['role'];
		}
	
		$acl = $this->di->get('acl');
		if ($acl->isAllowed($role, $dispatcher->getControllerName(),$dispatcher->getActionName()) != \Phalcon\Acl::ALLOW) {
			
			$this->response->redirect("Home");
			$this->flash->error("You don't have access to this module");
			return false;
		}
	}
    public function index(){
    	$this->tag->setTitle('后台首页');
    }
    public function logout(){
    	$this->session->destroy();
    	$this->response->redirect("Admin");
    }
}
