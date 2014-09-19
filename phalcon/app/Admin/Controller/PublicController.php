<?php
namespace Modules\Admin\Controller;
use Modules\Admin\Model\Manager,
\Phalcon\Validation\Validator\Regex;
class PublicController extends \Phalcon\Mvc\Controller {
// 	function verify(){
// 		$config=array(
// 				'imageW'=>150,
// 				'imageH'=>40,
// 				'fontSize'=>19,
// 				'length'=>4,
// 		);
// 		$verify = new \Think\Verify($config);
// 		$verify->entry(1);
// 	}
	public function login(){
		
		if($this->request->isAjax()){
			$this->view->disable();
			//TODO
			//checkverify()
			$data=array('msg'=>'');
			$va = new \Phalcon\Validation;
			$va->add('username', new Regex(array(
					'pattern' => '/^(?=\C{5,20}$)[\p{Han}\w]+$/u',
					'message' => '用户名格式不正确'
			)));
			$va->add('password', new Regex(array(
					'pattern' => '/^[^\s]{1,}$/',
					'message' => '密码中不能有空格'
			)));
			$messages = $va->validate($_POST);
			if (count($messages)) {
				$data['status'] =   0;
				foreach ($messages as $message) {
					$data['msg']   .=   $message.',';
				}
			}else{
				$conditions = 'username = :username: AND password = :password:';
				$parameters = array(
						'username' => @$_POST['username'],
						'password' => @$_POST['password']
				);
				
				$re = Manager::findFirst(array($conditions,"bind" => $parameters));
				if($re===false){
					$data['status'] =   0;
					$data['msg']='登录失败,用户名或密码错误';
				}else{
					$auth=array(
							'is_login'=>1,
							'id'=>$re->id,
							'username'=>$re->username,
 							/*'last_login_ip'=>$re->last_login_ip,
 							'last_login_time'=>$re->last_login_time*/
							);
					//TODO
// 	 				$this->Manager->where($map)->save($data);
					if($re->id==1){
						$auth['role']='超级管理员';
					}
					$this->session->set('user_auth',$auth);
					//TODO
// 					if($_POST ['save_login']=='yes'){
// 						session_set_cookie_params(3*24*3600);
// 					}
					$data['status'] =   1;
				}
			}
			ajax_return($data);
		}else{
			
			$this->view->setVar('title_text', '后台登录');
		}
	}

}