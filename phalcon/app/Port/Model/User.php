<?php
namespace Modules\Admin\Model;
use \Phalcon\Mvc\Model\Validator\InclusionIn;
class User extends \Phalcon\Mvc\Model{
	public $id;
	public $username;
	public $password;
	public $email;
	public $mobile;
	public $nickname;
	public $face;
    public function getSource(){
        return 'maopu_ucenter_member';
    }
    public function validation(){
//     	$this->validate(
//     		new InclusionIn(array(
//     			'field' => 'status',
//     			'domain' => array('A', 'I')
//     	)));
//    	if ($this->validationHasFailed() == true) {
//     		return false;
//     	}
    }
    static function login(){
    	
    }
    		
}
