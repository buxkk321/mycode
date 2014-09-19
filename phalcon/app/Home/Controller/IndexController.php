<?php
namespace Modules\Home\Controller;

class IndexController extends \Phalcon\Mvc\Controller {
    public function index(){
    	dump($this->session,true,'',2);
        echo 'home index';
    }
    function  route404(){
		$this->view->disable();
    	echo '404 not found';
    }
}
