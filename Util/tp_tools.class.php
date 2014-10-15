<?php
namespace Common\My;
class tp_tools {
	protected static $_upload_error;
	/**
	 * 查询数据,生成列表相关数据
	 * @param mixed $model 模型名或模型对象
	 * @param mixed $field 查询的字段
	 * @param array $defmap
	 * @param array $exmap  不在url中传递的搜索条件和其他限制条件
	 * @return array  返回值说明:
	 * $data['_list']数据列表
	 * $data['_page']分页html代码
	 * $data['_total']总记录数
	 * $data['_sh']当前搜索的字段名
	 * $data['_kw']当前搜索的关键字
	 * $data['order']sql语句中的order设置
	 * $data['where']sql语句中的where条件
	 */
	public static function getDataList($model,$field,$defmap=array(),$exmap=array()) {
		$options = array ();
		is_string ( $model ) && $model = M ( $model );
		is_string ( $field ) && $field = explode ( ',', $field );
		
		$OPT = new \ReflectionProperty ( $model, 'options' );
		$OPT->setAccessible ( true );
		
		if($_GET['request']==null){
			$map_use=$defmap;
			$map_pas['request']=base64_encode(json_encode($map_use));
		}else{
			$map_use=json_decode(base64_decode($_GET['request']),true);
			$map_pas['request']=$_GET['request'];
		}
		
		$options['order']=$map_use['order'];
		$options['where']=$map_use['where'];
		$map_use['_sh'] && $options['where'][$map_use['_sh']]=$map_use['_kw'];
		(!empty($exmap['where']) && is_array($exmap['where'])) && $options['where']['_complex']=$exmap['where'];
		
		$options = array_merge ( ( array ) $OPT->getValue ( $model ), $options );//保存where条件
		$page_size=$map_use['page_size'];
		if ($page_size == null) {
			$options['limit'] = '';
		} else {
			$total = $model->group()->where ( $options ['where'] )->count ($exmap['_count']);
			$mypager = new \Think\Page ( $total, $page_size,$map_pas);
			$mypager->setConfig('prev', '上一页');
			$mypager->setConfig('next', '下一页');
			$mypager->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% ');
			$p =$mypager->show ();
			$map_use['_total']=$total;
			$map_use['_page']=$p? $p: '';
			$options['limit'] = $mypager->firstRow.','.$mypager->listRows;//如果有分页参数,则设置limit条件
		}
		
		$model->setProperty('options',$options);
		$map_use['_list']=$model->field($field)->select();
		return $map_use;
	}
	/**
	 * 批量上传文件处理方法,整个流程使用同一个配置
	 * @param unknown $filesname
	 * @param unknown $config
	 * @param string $dealfunc
	 * @return Ambigous <boolean, multitype:mixed string >
	 */
	public static function dealUploadFiles($filesname,$config,$dealfunc=null){
		is_string($filesname) && $filesname=explode(',',$filesname );
		$batch=$files=array();//缓存数组
		foreach($filesname as $v){
			if(is_array($_FILES[$v]['error'])){//单次多文件
				!(count($_FILES[$v]['error'])===1 && $_FILES[$v]['error'][0]===4) && $files[$v]=$_FILES[$v];//只要有上传东西就放入数组
			}else{//单文件
				$_FILES[$v]['error']!==4 && $batch[$v]=$_FILES[$v];
			}
		}
		unset($v);
		$upload = new \Think\Upload ($config);
		
		if(!empty($batch)){
			$info=$upload->upload($batch);//单文件
			if($info===false){
				self::$_upload_error=$upload->getError();
				return false;
			}
		}
		
		if(!empty($files)){
			foreach($files as $k=>$v){//多文件
				$info[$k] = $upload->upload($v);
				if($info[$k]===false){
					self::$_upload_error=$upload->getError();
					return false;
				}
			}
			unset($v);
		}
		
		is_callable($dealfunc) && $dealfunc($info);

		return $info;
	}
}