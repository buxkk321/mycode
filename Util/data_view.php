<?php
namespace Common\My;
class data_view {
	public static function getColumns($tn,$refresh){
		$cacheKey = $tn.'.cache';
		if (!$this->adminCache->exists($cacheKey) || $refresh) {
			$cols_info=$this->db->fetchAll('SHOW FULL COLUMNS FROM '.$tn,\Phalcon\Db::FETCH_ASSOC);
			$this->adminCache->save($cacheKey, $cols_info);
		}else{
			$cols_info=$this->adminCache->get($cacheKey);
		}
		return $cols_info;
	}
	/**
	 * 生成列表表头或表单
	 * @param unknown_type $input
	 * @param unknown_type $cols
	 * @param unknown_type $except
	 * @param unknown_type $key_prefix
	 * @return multitype:
	 */
	public static function getGrids($input,$cols=array(),$except=false,$key_prefix=''){
		$re=array();
		is_string($cols) && $cols=explode(',', $cols);
	
		if(is_string($input)){
			$cols_info=self::getColumns($input);
		}else{
			$cols_info=$input;
		}
		
		if(empty($cols)){
			foreach ($cols_info as $kk=>$vv){
				$vv=array_change_key_case($vv);
				$re[$key_prefix.$vv['field']]['title']=$vv['comment'];
			}
		}else{
			$cols=array_flip($cols);
			foreach($cols_info as $vv){
				$vv=array_change_key_case($vv);
				isset($cols[$vv['field']])!=$except && $re[$key_prefix.$vv['field']]['title']=$vv['comment'];
			}
		}
		unset($vv);
	
		return $re;
	}
	
}