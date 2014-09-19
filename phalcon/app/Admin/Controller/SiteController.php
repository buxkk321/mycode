<?php
namespace Modules\Admin\Controller;

class SiteController extends MainController {
	/**
	 * 网站配置文件
	 * @var string
	 */
	protected $site_config='./Data/site_config.php';
	/**
	 * 公司基本信息文件
	 * @var string
	 */
	protected $company_info='./Data/company_info.php';
	/**
	 * 日志记录表
	 * @var object
	 */
	protected $Record;
	/**
	 * 需要加入验证规则的控制器列表,带模块名
	 * @var array
	 */
	protected $_auth_list=array(
			'Admin/Index',
			'Admin/User',
			'Admin/Act',
			'Admin/Area'
			);
	protected $_def_cfg=array(
							'index'=>array(
	    						'title'=>'首页配置',
    							'type'=>'0',
    							'value'=>'',
	    						'child'=>array()
    							)
    						);

	function company_info() {
		setConfig($this->company_info,false);
		$imagefiles=array('addr_img','tdcode');
		$this->view->setVar('result',include_once $this->company_info);
		$this->tag->setTitle('公司信息');
		$intro='';
// 		$this->assign ( 'title_text', '' );
// 		$this->assign ( 'busi_list',$this->Business->getField('bid,bname'));
// 		$this->assign ( 'result', $result);
// 		$this->display ();
	}
	function company_intro_edit(){
		
	}
	function ajax_add_cfg(){
		if(IS_AJAX){
			if($_POST['parent']!=null && $_POST['name']!=null){
				$data[]=array($_POST['parent'],'child',$_POST['name'],
						array(
								'type'=>1,
								'title'=>$_POST['title'].'',
								'value'=>$_POST['value'].'',
								)
						);
				$re=(int)setConfig($data,'./Data/site.php',1);
			}else{
				$re=0;
			}
			echo $re;
		}
	}
    function site_config(){
    	setConfig($this->site_config,false);
    	if($this->request->isPost()){
//     		$data=array();
//     		foreach($_POST['config'] as $kk=>$vv){
//     			foreach($vv as $k=>$v){
//     				$data[]=array($kk,'child',$k,'value',$v);
//     			}
//     		}
//     		$re=CurdApi::setConfig($data,$path);
//     		if($re){
//     			$this->success('修改成功！', U('site_config'));
//     		}else{
//     			$this->error('修改失败');
//     		}
    	}else{
    		$this->view->setVar('data',include_once $this->site_config);
    	}
    }
    
     function record_list(){
//      	$tn_record=$this->Record->getTname();
//      	$tn_user=$this->User->getTname();
//      	$tn_rule=$this->AuthRule->getTname();
//      	//设置查询的字段名和列表页的表头字段信息
//      	$cols_record=array('arid','name');
//      	$cols_ex1=array('add_time','add_ip');
//      	$cols[$tn_record]=array_merge($cols_record,$cols_ex1);
//      	$cols[$tn_rule]=$cols_rule=array("remarks");
//      	$cols[$tn_user]=$cols_user=array('username');
//      	$cols_user[]='roid';
//      	array_walk($cols_record, 'addprefix',$tn_record.'.');
//      	array_walk($cols_ex1, 'addprefix',$tn_record.'.');
//      	array_walk($cols_rule, 'addprefix',$tn_rule.'.');
//      	array_walk($cols_user, 'addprefix',$tn_user.'.');
//      	$cols_name=array_merge($cols_record,$cols_rule,$cols_user,$cols_ex1);
     	
//      	//设置join语句
//      	$join=array(
//      			"left join $tn_user on $tn_record.lid=$tn_user.lid",
//      			"left join $tn_rule on $tn_record.name=$tn_rule.name"
//      	);
//      	//设置默认搜索条件和分页大小
//      	$defmap['where']=array();
//      	$defmap['order']=array($cols_ex1[0]=>'desc');
//      	$defmap['page_size']=12;
//      	//设置post提交的搜索条件
//      	if($_POST['set_search']==='yes'){
//      		$condition=set_update_data(array('kw','sc','od'));
//      		$condition['sh']=$_POST['sh']?:"$tn_user.username";//如果没有提供搜索字段名,则设置成默认值
//      		$newmap['where'][$condition['sh']]=array('like','%'.$condition['kw'].'%');
//      		$newmap['order'][$condition['od']]=$condition['sc'];
//      		$newmap['page_size']=$_POST['page_size']?:$defmap['page_size'];
//      	}else{
//      		$newmap=null;
//      	}
//      	//开始查询
     	
//      	$list=$this->getListFromMultipleModel($this->Record, $cols_name,$join, $newmap, $defmap);
//     	$this->assign ( 'title_text', '日志列表' );
//     	$this->assign ( 'cols', $cols );
//     	$this->assign ( 'list',$list);
//     	$this->display ();
     }
     
}