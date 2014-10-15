<?php
namespace Common\My;
class data_tree {
	public static $default=array(
			'pk'=>'id',
			'parent'=>'pid',
			'child'=>'_child'
			);
	/**
	 * 将输入数组重排成以唯一id或标识作为键名的数组
	 * @param array $data 输入数据
	 * @param int $return_index 可以设置是否返回建立的临时索引,数值说明:
	 *  0:只排序不返回索引
	 *  1:只返回索引不排序
	 *  2:排序并返回索引
	 * @param string $uk 标识值单元的键名,默认为'id'
	 */
	public static function set_id_index(&$data,$return_index=0,$uk='id'){
		$temp=array();
		switch ($return_index){
			case 0:
				foreach($data as $k=>$v){
					$temp[$v[$uk]]=$v;
				}
				$data=$temp;
				$temp=0;
				break;
			case 1:
				foreach($data as $k=>$v){
					$temp[$v[$uk]]=$k;
				}
				break;
			case 2:
				$temp2=array();
				foreach($data as $k=>$v){
					$temp2[$v[$uk]]=$v;
					$temp[$v[$uk]]=$k;
				}
				$data=$temp2;
				unset($temp2);
				break;
		}
		unset($v);
		return $temp;
	}
	/**
	 * 计算单条记录在指定结果集中的父级深度,结束id可选
	 * @param number $start 开始数据主键值
	 * @param number $end 结束数据主键值
	 * @param array $data 原始结果集
	 * @param array $data_index 主键到原始结果集数字索引的映射,如果原始结果集已经排序成由主键作为键名的数组,则该参数应该留空
	 * @return number
	 */
	public static function count_parent($data,$data_index=array(),$start=1,$end=0){
		$re=0;
		$start<$end && flipvar($start,$end);
		$p=self::$default['pid'];
		if(empty($data_index)){
			while($start>$end){
				$start=$data[$start][$p];
				$re++;
			}
		}else{
			while($start>$end){
				$start=$data[$data_index[$start]][$p];
				$re++;
			}
		}
		return $re;
	}
	/** 
	 * 按发散的分类树结构排序,返回根据分类层级排序过的一维数组,键名为主键值
	 * @param unknown $data
	 * @param unknown $data_index
	 * @return array
	 */
	public static function tree_sort($data,$data_index=array()){
		$pid=self::$default['parent'];
		$pk=self::$default['pk'];
		$tree_index='';
		$re=array();
		foreach($data as $v){
			if($tree_index==''){
				$tree_index='_'.$v[$pid].'_'.$v[$pk].'_';
			}else{
				$pos_pid=strpos($tree_index,'_'.$v[$pid].'_');/*找到父项id的位置*/
				$pos_id=strpos($tree_index,'_'.$v[$pk].'_');/*找到自己id的位置*/
				if($pos_pid===false){
					if($pos_id===false){/*1,既没找到父项id也没找到自己id,则将信息追加到索引字符串前面*/
						$tree_index.=$v[$pid].'_'.$v[$pk].'_';
					}else{/*2,没找到父项id但找到自己的id,追加父项id到字符串*/
						$tree_index=substr($tree_index,0,$pos_id).'_'.$v[$pid].substr($tree_index,$pos_id);
					}
				}else{
					$fix=strlen('_'.$v[$pid]);
					if($pos_id===false){/*3,找到父项id但没找到自己的id,在父项id之后插入'_自己id_'*/
						$tree_index=substr($tree_index,0,$pos_pid+$fix).'_'.$v[$pk].substr($tree_index,$pos_pid+$fix);/**/
					}else{/*4,自己id和父项id同时找到,则保留最左边的父项id*/
						if($pos_id<$pos_pid){
							$tree_index=str_replace('_'.$v[$pid].'_','',$tree_index);/*去掉不正确的pid*/
							$tree_index=substr($tree_index,0,$pos_id).'_'.$v[$pid].substr($tree_index,$pos_id);/*同步骤2*/
						}
					}
				}
			}
		}
		
		$tree_index=explode('_', substr($tree_index,1,-1));
	
		foreach($tree_index as $vv){
			@$kp=empty($data_index)?$vv:$data_index[$vv];
			if(isset($data[$kp])){
				$re[$vv]=$data[$kp];
				$re[$vv]['deep']=self::count_parent($data,$data_index,$vv);
			}
		}
	
		return $re;
	}
	/**
	 * 无限级分类,返回分好组的多维数组
	 * @param array $data 原始数据
	 * @param array $data_index 主键到原始数据数字索引的映射
	 * @param bool $first_child  是否只设置下一级的子项
	 * @return multitype:unknown
	 */
	public static function build_tree($data,$data_index=array(),$first_child=false) {
		$tree=array();
		$child=self::$default['child'];
		$pk=self::$default['pk'];
		$parent=self::$default['parent'];
	
		if($first_child){
			foreach ($data as $ke => $vo) {
				$data[$ke][$child]=(array)$data[$ke][$child];
					
				$kp=empty($data_index)?$vo[$parent]:$data_index[$vo[$parent]];
				isset($data[$kp]) && $data[$kp][$child][] =$vo[$pk];
			}
			$tree=&$data;
		}else{
			foreach ($data as $ke => $vo) {
				$kp=empty($data_index)?$vo[$parent]:$data_index[$vo[$parent]];
				if(isset($data[$kp])){
					$data[$kp][$child][] =& $data[$ke];
				}else{
					$tree[]=& $data[$ke];
				}
			}
		}
		unset($vo);
		//TODO:返回只由id组成的树状数组
		// 		$temp=array();
		// 		foreach ($data as $ke => $vo) {
		// 			if(isset($data[$vo[$config['pid']]])){
		// 				$data[$vo[$config['pid']]][$config['child']][$ke] =& $data[$ke];
		// 				array_push((array)$temp[$vo[$config['pid']]],$ke);
		// 			}else{
		// 				$tree[]=& $data[$ke];
		// 			}
		// 		}
	
		return $tree;
	}
}