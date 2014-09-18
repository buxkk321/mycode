<?php
namespace Modules\Admin\Controller;
use Phalcon\Cache\Frontend\Data;

class MainController extends \Phalcon\Mvc\Controller {
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
		//Check if the Role have access to the controller (resource)
		if ($acl->isAllowed($role, $dispatcher->getControllerName(),$dispatcher->getActionName()) != \Phalcon\Acl::ALLOW) {
		
			$this->flash->error("You don't have access to this module");
			$this->response->redirect("Home");
			//Returning "false" we tell to the dispatcher to stop the current operation
			return false;
		}
	}
	
	/**
	 * post请求添加或修改数据的处理函数
	 * @param string $table 完整的表名
	 * @param array $field 要更新的字段信息,格式说明:
	 *  ['pk'] 主键名
	 *  ['filename'] 保存上传文件路劲的字段名
	 *  ['fileconfig'] 文件上传的配置数据
	 *  ['cols'] 直接从$_POST中获取的字段名,多个字段用英文逗号分隔，也可以是数组
	 * @param function $before_edit 在执行数据库操作前的处理(验证)函数,可选,参数说明:
	 * 	$result:上一部操作的结果数据,引用传值
	 * 	return $result 出错时需要设置返回的 $result['status']=3;
	 * @param function $func_edit 仅用于更新操作时使用的自定义处理函数,可选,格式说明:
	 *  $do_edit:function,实际的update操作,不填参数直接调用即可,不需要返回值
	 *  $table:表名,同@param1
	 *  $field:字段信息,同@param2
	 * @return array $result 操作结果
	 * 返回值说明:
	 *  ['data'] 待操作的数据
	 *	['status'] 状态:
	 * 	 0:操作失败
	 *   1:操作成功
	 *   
	 *   3:执行数据库操作前的处理函数出错
	 *  ['msg'] 错误信息
	 */
	function post_edit($table,$field,$before_edit=null,$func_edit=null){
		$pk=$field['pk'];
		$result['status']=1;
		$result['data'] = array_batch($field['cols'],$_POST,false);
		if($field['filename']!=null){
			$info=\Think\Upload::dealUploadFiles($field['filename'],$field['fileconfig']);
			if($info===false){
				$result['msg']=\Think\Upload::$_upload_error;
				$result['status']=3;
			}elseif(!empty($info)){
				foreach($info as $kk=>$vv){
					if(is_numeric($kk)){
						//TODO 多文件上传
						
					}else{
						$result['data'][$kk]=$vv['savepath'].$vv['savename'];
					}
				}
			}
		}
		
		is_callable($before_edit) && $before_edit($result);
		
		if($result['status']==1){
			try {
				if($_POST[$pk]>0){
					$do_edit=function()use($table,$result,$pk){
						$this->db->update($table,array_keys($result['data']),array_values($result['data']),$pk.'='.$_POST[$pk]);
					};
					if(is_callable($func_edit)){
						$func_edit($do_edit,$table,$field);
					}else{
						$do_edit();
					}
				}else{
					$this->db->insert($table,array_values($result['data']),array_keys($result['data']));
				}
			} catch (\Exception $e) {
				$result['status']=0;
				if($_POST[$pk]>0){
					$result['msg']='更新失败:'.$e->__toString();
				}else{
					$result['msg']='添加失败:'.$e->__toString();
				}
			}
		}
		
		return $result;
	}
	/**
	 * ajax请求删除数据的处理函数,只接收post方式传递的参数
	 * @param 表名 $tn
	 * @param 主键名 $pk
	 * @param 删除之前的验证 $check 需要在验证通过时返回true,反之返回false
	 * @return array $data
	 * 返回值说明:
	 *	['status'] 状态:
	 * 	 0:删除失败
	 *   1:删除成功
	 *   2:没有提供有效的主键值
	 *   3:没有通过删除前的验证
	 *  ['msg'] 错误信息
	 *  
	 */
	function ajax_del($tn,$pk,$check=null){
		$this->view->disable();
		$data=array();
		$ids=is_array($_POST[$pk])?implode(',', $_POST[$pk]):$_POST[$pk];

		if ( empty($ids) ) {
			$data['status']=2;
			$data['msg']='请选择要操作的数据';
			return $data;
		}
		
		is_callable($check) && $re=$check($ids);
		
		if($re){
			try{
				$re=$this->db->delete($tn,$pk.' in('.$ids.')');
				$data['status']=1;
				return $data;
			}catch(\Exception $e){
				$data['status']=0;
				$data['msg']=$e->__toString();
				return $data;
			}
		}else{
			$data['status']=3;
			return $data;
		}
	}
	
	function error_page($url=null,$errstr=''){
		dump($errstr);
		echo 'error';
		if($url!=null){
			echo '<a href="'.$url.'" ><input type="button" value="点击返回"/></a>';
		}
		exit;
	}
	function home_page(){
		echo index;
	}
}
