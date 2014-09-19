<?php
namespace Modules\Port\Model;
use \Phalcon\Mvc\Model\Validator;
class User extends MainModel{
	public $id;
	public $username;
	public $password;
	public $email;
	public $mobile;
	public $nickname;
	public $avatar;
	
    public function getSource(){
        return 'maopu_ucenter_member';
    }
    
    public function validation(){
    	
    	$this->validate(new Validator\Regex(
    		array(
    			'field' => 'password',
    			'pattern' => '/^[^\s]{1,}$/',
    			'message' => '密码中不能有空格'
    		)
    	));
    	
    	$this->validate(new Validator\Regex(
    		array(
    			'field' => 'mobile',
    			'pattern' => '/^1[3-8]\d{9,}$/',
    			'message' => '手机号格式不正确'
    		)
    	));
    	
    	$this->validate(new Validator\Uniqueness(
    		array(
    			"field"   => "mobile",
    			"message" => "该手机号已被注册"
    		)
    	));
    	
    	return (bool)$this->validationHasFailed();
    }
    
    public function beforeCreate(){
    	$this->reg_time = $_SERVER['REQUSET_TIME'];
    }
    
    public function beforeUpdate(){
    	$this->update_time = $_SERVER['REQUSET_TIME'];
    }
    		
}
