<?php
namespace Common\My;
use Common\My\fc;
class hhw_tptools {
	public static $pre_to_company=array(1=>'yidong',2=>'liantong',3=>'dianxin');
	public static $prefix=array(
			/*移动的*/
			134=>'10',135=>'10',136=>'10',137=>'10',138=>'10',139=>'10',
			150=>'10',151=>'10',152=>'10',157=>'10',158=>'10',159=>'10',
			182=>'10',183=>'10',184=>'10',187=>'10',188=>'10',
			/*联通的*/
			130=>'20',131=>'20',132=>'20',
			155=>'20',156=>'20',
			185=>'20',186=>'20',
			/*电信的*/
			153=>'31',/*第一类*/
			133=>'32',/*第二类*/
			180=>'33',181=>'33',189=>'33',/*第三类*/
			177=>'34',
	);
	protected static $def_company_info=array(
			'yidong'=>array(
					'name'=>'移动',
					'deposits'=>100,
					'pre'=>array(
							134=>'0',135=>'0',136=>'0',137=>'0',138=>'0',139=>'0',
							150=>'0',151=>'0',152=>'0',157=>'0',158=>'0',159=>'0',
							182=>'0',183=>'0',184=>'0',187=>'0',188=>'0'
					)
			),
			'liantong'=>array(
					'name'=>'联通',
					'deposits'=>50,
					'pre'=>array(
							130=>'0',131=>'0',132=>'0',
							155=>'0',156=>'0',
							185=>'0',186=>'0',
					)
			),
			'dianxin'=>array(
					'name'=>'电信',
					'deposits'=>50,
					'pre'=>array(
							153=>'1',
							133=>'2',
							180=>'3',181=>'3',189=>'3',
							177=>'0',
					)
			),
	);
	public static function getAreaCode($refresh=false,$table,$range=null){
		$cacheKey=$table.'.data';
		if (!fc::exists($cacheKey) || $refresh) {
			$addr['list']=M()->table($table)->field('code,title')->select();
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
	public static function getSundryInfo($refresh=false,$table,$name=''){
		$cacheKey=$table.'.'.$name.'.data';
		fc::$enctype=9;
		if (!fc::exists($cacheKey) || $refresh) {
			$data=M()->table($table)->where('name="'.$name.'"')->getField('content');
			fc::save($cacheKey,$data);
		}else{
			$data=fc::get($cacheKey);
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
		dump($r);
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
			self::do_match($input,$test);
			$input['company']=self::$company[$prefix_info[0]];
		}
	
		return $input;
	}
}