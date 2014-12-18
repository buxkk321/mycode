<?php
namespace Common\My;
class hhw_tptools {
	public static $tn_ac='hhw_area_code';
	public static $tn_pc='hhw_pack_category';
	public static $tn_sundry='hhw_sundry';
	public static $default_company=array(
				'yidong'=>'移动',
				'liantong'=>'联通',
				'dianxin'=>'电信'
			);
	public static function get_area_code($refresh=false,$range=null,$insure=false){
		$cacheKey=self::$tn_ac.'.data';
		if (!fc::exists($cacheKey) || $refresh) {
			$addr['list']=M()->table(self::$tn_ac)->field('code,title')->select();
			data_tree::set_index($addr['list'],0,'code');
			$addr['tree']=array();
			foreach ($addr['list'] as $ke => $vo) {
				$kp=str_split($ke,2);
				if($kp[1].$kp[2]=='0000'){
					$addr['tree'][$ke]=array();
				}elseif($kp[2]=='00'){
					$addr['tree'][$kp[0].'0000'][$ke]=array();
				}else{
					$addr['tree'][$kp[0].'0000'][$kp[0].$kp[1].'00'][$ke]=1;
				}
			}
	
			fc::save($cacheKey,$addr);
		}else{
			$addr=fc::get($cacheKey);
		}
		if(!isset($addr['tree']) && !$insure){
			return self::get_area_code(1,$range,true);
		}
		if(is_numeric($range)){
			$p=str_pad($range,6,'0',STR_PAD_RIGHT);
			$addr['tree']=array($p=>$addr['tree'][$p]);
			foreach ($addr['list'] as $k=>$v){
				if(substr($k,0,2)!=$range){
					unset($addr['list'][$k]);
				}
			}
		}elseif(is_string($range)){
		}elseif(is_array($range)){
			$temp=array();
			foreach ($range as $v){
				$p=str_pad($v,6,'0',STR_PAD_RIGHT);
				$temp[$p]=$addr[$p];
			}
			$addr=$temp;
		}
		return $addr;
	}
	
	public static function get_columns($tn,$refresh=false,$insure=false){
		$cacheKey = $tn.'.cache';
		if (!fc::exists($cacheKey) || $refresh) {
			$info=M()->query('SHOW FULL COLUMNS FROM '.$tn);
			$cols_info=array();
			foreach($info as $vv){
				$cols_info[$vv['field']]=array_change_key_case($vv);
			}
			fc::save($cacheKey, $cols_info);
		}else{
			$cols_info=fc::get($cacheKey);
		}
		if(!$cols_info && !$insure){
			$cols_info=self::get_columns($tn,true,true);
		}
		return $cols_info;
	}
	/**
	 * 套餐分类
	 */
	public static function get_pack_category($refresh=false,$company=null,$insure=false){
		$cacheKey=self::$tn_pc.'.data';
		if (!fc::exists($cacheKey) || $refresh) {
			$category['data']=M()->query('select * from '.self::$tn_pc);
			$category['map']=data_tree::build_map($category['data'],'company');
			$category['index']=data_tree::set_index($category['data'],1);
			fc::save($cacheKey,$category);
		}else{
			$category=fc::get($cacheKey);
		}
		if(!isset($category['index']) && !$insure){
			$category=self::get_pack_category(true,null,true);
		}
		$c=array('yidong'=>1,'liantong'=>1,'dianxin'=>1);
		if(isset($c[$company])){
			$temp=array();
			foreach((array)$category['map'][$company] as $k=>$v){
				$temp[]=$category['data'][$v];
			}
			unset($category['map']);
			$category=array('data'=>$temp);
		}
		return $category;
	}
	public static function get_sundry_content($refresh=false,$name='',$insure=false){
		$cacheKey=self::$tn_sundry.'.'.$name.'.data';
		fc::$enctype=1;
		if (!fc::exists($cacheKey) || $refresh) {
			$data=M()->table(self::$tn_sundry)->where('name="'.$name.'"')->getField('content');
			fc::save($cacheKey,$data);
		}else{
			$data=fc::get($cacheKey);
		}
		if(($data=='' || !is_string($data)) && !$insure){
			$data=self::get_sundry_content(true,$name,true);
		}
		return $data;
	}
	public static function getd(){
		
	}
	/**
	 * 入口
	 * @param int|string $num 号码
	 * @param int|string $generation 第几代
	 * @param array $rules $匹配规则
	 * @return unknown
	 */
	public static function num_match($num,$generation){
		$input['status']=1;
		$input['generation']=$generation.'';//第几代
		$input['num']=$num.'';//号码
		$input['prefix']=substr($num,0,3);//前三位
		$r=M()->table('hhw_mobile_num')->select();
		$prefix_info=self::$prefix[$input['prefix']];//根据号码前三位找到对应的信息
		if($generation<2){
			$input['status']=2;
			$input['msg']='请选择电话网络的代数';
		}elseif($prefix_info==null){
			$input['status']=3;
			$input['msg']=$num.'该号码没有匹配的运营商';
		}else{
			$input['company']=$prefix_info[0];//运营商
			$input['correct']=$prefix_info[1];//修正值
			self::do_match($input);
			$input['company']=self::$company[$prefix_info[0]];
		}
	
		return $input;
	}
}