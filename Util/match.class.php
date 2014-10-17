<?php
namespace Common\My;
/**
 * 号码匹配
 * @author Administrator
 */
class match{
	private static $company=array('yidong','liantong','dianxin');//1移动,2联通,3电信
	private static $prefix=array(
		/*移动的*/
		134=>'10',135=>'10',136=>'10',137=>'10',138=>'10',139=>'10',
		150=>'10',151=>'10',152=>'10',157=>'10',158=>'10',159=>'10',
		182=>'10',183=>'10',184=>'10',187=>'10',188=>'10',
		/*联通的*/
		130=>'20',131=>'20',132=>'20',
		155=>'20',156=>'20',
		185=>'20',186=>'20',
		/*电信的*/
		133=>'31',153=>'31',/*第一类*/
		177=>'32',/*第二类*/
		180=>'33',181=>'33',189=>'33'/*第三类*/
	);
	/**
	 * 开始匹配
	 * @param int|string $num 号码
	 * @param int|string $generation 第几代
	 * @param array $rules $匹配规则
	 * @return unknown
	 */
	public static function entry($num,$generation){
		$re['num']=$num.'';//号码
		$re['revnum']=strrev($re['num']);//反转的号码
		$re['generation']=$generation.'';//第几代
		$prefix_info=self::$prefix[substr($num,0,3)]?:'00';
		$re['company']=$prefix_info[0];//运营商
		$re['correct']=$prefix_info[1];//修正值
		
		eval('$re["sufix_type"]=self::match_type_'.$re['company'].$re['generation'].'($re)');//根据运营商和代数选择匹配对策
		
		return $re;
	}
	
	//联通2g靓号规则
	public static function match_type_22($input){
	
	
	
	}
	//联通3g靓号规则
	public static function match_type_23($input){
	
	
	
	}
	//电信3G靓号规则
	public static function match_type_33($input){
	
	
	
	}
	//电信4G靓号规则
	public static function match_type_34($input){
	
	
	
	}
}