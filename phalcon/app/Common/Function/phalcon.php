<?php
/**
 * 取得需要输出的表头信息(多表)
 */
function getMultiGrids($cols='',$db=null){
	$cc=$re=$con=array();
	$cols=check_array($cols,',');
	foreach ($cols as $vv){
		if(strpos($vv,' ')===false){//没有设置别名
			$tt=explode('.',$vv);//取得带表名的字段名
			$key=$tt[1];
		}else{
			$tt=explode('.',substr($vv,0,strpos($vv,' ')));//取得带表名的字段名
			$key=substr($vv,strrpos($vv,' ')+1);//别名设为映射键名
		}
		$con['tb_name'][]=$tt[0];//表名
		$con['col_name'][]=$tt[1];//字段名
		$cc[$tt[0].'.'.$tt[1]]=$key;//保存映射关系
		$re[$key]='';//设置排序
	}

	$con['tb_name']=implode('","',array_unique($con['tb_name']));
	$con['col_name']=implode('","',array_unique($con['col_name']));
	$sql='select TABLE_NAME,COLUMN_NAME,COLUMN_COMMENT
			FROM information_schema.COLUMNS
			where TABLE_SCHEMA="'.DEFAULT_DB_NAME.'" and
			TABLE_NAME in("'.$con['tb_name'].'")and
			COLUMN_NAME in("'.$con['col_name'].'")';

	if($db==null){
		$db=\Phalcon\DI::getDefault()->get('db');
		$tt=$db->fetchAll($sql,\Phalcon\Db::FETCH_ASSOC);
	}else{
		$tt=$db($sql);
	}

	foreach($tt as $vv){
		isset($cc[$vv['TABLE_NAME'].'.'.$vv['COLUMN_NAME']]) && $re[$cc[$vv['TABLE_NAME'].'.'.$vv['COLUMN_NAME']]]=$vv['COLUMN_COMMENT'];
	}

	return $re;
}