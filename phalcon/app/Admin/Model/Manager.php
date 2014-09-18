<?php
namespace Modules\Admin\Model;
use \Phalcon\Mvc\Model\Validator\InclusionIn;
class Manager extends \Phalcon\Mvc\Model{
	public $id;
	public $username;
	public $password;
	public $role;
	public $last_login_ip;
	public $last_login_time;
    public function getSource(){
        return 'tp_manager';
    }
    public function validation(){
//     	$this->validate(
//     		new InclusionIn(array(
//     			'field' => 'status',
//     			'domain' => array('A', 'I')
//     	)));
//    		if ($this->validationHasFailed() == true) {
//     		return false;
//     	}
    }
    static function login(){
    	
    }
    		
}
