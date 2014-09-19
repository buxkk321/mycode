<?php
namespace Modules\Admin\Model;
class MainModel extends \Phalcon\Mvc\Model {
	/**
	 * 当前表的字段详情,即'show full fields from tbl_name'的结果
	 * @var array 
	 */
	protected $_cols_info;
	
	public function onConstruct(){
		foreach ($this->getDI()->get('db')->fetchAll('SHOW FULL COLUMNS FROM '.$this->getSource(),\Phalcon\Db::FETCH_ASSOC) as $vv){
			$vv=array_change_key_case($vv);
			$this->_cols_info[$vv['field']]=$vv;
		}
	}
// 	/**
// 	 * 取得表的字段类型信息
// 	 * @return array 键名为字段名  键值为数据类型的字符串表达式
// 	 */
// 	function getTypes(){
// 		return $this->fields['_type'];
// 	}
// 	/**
// 	 * 取得完整的数据表名
// 	 * @return string
// 	 */
// 	function getTname(){
// 		return $this->trueTableName;
// 	}
	/**
	 * 取得表的字段注释信息
	 * @return array[tbl_name]=array(字段名=>注释,字段名=>注释,...);
	 */
	function getComments(){
// 		$tn=$this->getSource();
		$re=array();
		foreach ($this->_cols_info as $kk=>$vv){
			$re[$kk]=$vv['comment'];
		}
		return $re;
	}
	/**
	 * 取得需要输出的表头信息(单表查询用)
	 */
	function getGrids($cols='',$key_prefix=''){
		switch (true){
			case (empty($cols)):
				foreach ($this->_cols_info as $kk=>$vv){
					$re[$key_prefix.$kk]=$vv['comment'];
				}
				unset($vv);
				break;
			case (is_array($cols) && !empty($cols)):
				foreach($cols as $vv){
					isset($this->_cols_info[$vv]) && $re[$key_prefix.$vv]=$this->_cols_info[$vv]['comment'];
				}
				unset($vv);
				break;
			case (is_string($cols)):
				$cols=explode(',', $cols);
				foreach($cols as $vv){
					isset($this->_cols_info[$vv]) && $re[$key_prefix.$vv]=$this->_cols_info[$vv]['comment'];
				}
				unset($vv);
				break;
		}
		
		return $re;
	}
// 	/**
// 	 * 生成添加和修改用的表单信息
// 	 */
// 	function buildForm($cols=''){
// 		switch (true){
// 			case (empty($cols) || $cols===true):
// 				$re=$this->_cols_info;
// 				break;
// 			case (is_array($cols)):
// 				foreach($cols as $vv){
// 					isset($this->_cols_info[$vv]) && $re[$vv]=$this->_cols_info[$vv];
// 				}
// 				unset($vv);
// 				break;
// 			case (is_string($cols)):
// 				$cols=explode(',', $cols);
// 				foreach($cols as $vv){
// 					isset($this->_cols_info[$vv]) && $re[$vv]=$this->_cols_info[$vv];
// 				}
// 				unset($vv);
// 				break;
// 		}
// 		return $re;
// 	}
}
