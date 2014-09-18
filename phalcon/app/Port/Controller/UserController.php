<?php
namespace Modules\Port\Controller;
/*用户模块*/
class UserController extends MainController {
	
	//登陆
	protected function login(){
		$username= $_REQUEST['username'];
		$password= $_REQUEST['password'];
		if(!$username || !$password) error_response();
		$data['username'] = $username;
		$data['password'] = think_ucenter_md5($password, UC_AUTH_KEY);
		$data['status'] = 1;
		$res = M('ucenter_member')->field("id")->where($data)->find();
		$uid = $res['id'];
		if(!$res){
			error_response(array ("code" => 26,"msg" => "用户名或密码错误"));
		}else{
			$sql= "SELECT a.username,a.id as uid,b.type,b.school_id FROM ".PRE."ucenter_member a LEFT JOIN ".PRE."member b on a.id = b.uid WHERE a.id = $uid ";
			$info = M()->query($sql);
			$info[0]['class_id'] = M('teacher_class')->where("uid = $uid")->select();
			// 			p($info);
			if($info[0]['type'] == 1){
				//园长登陆后 获取学校所有class_id
				$classes = M('class')->field("id as class_id,name as class_name")->where("school_id=".$info[0]['school_id'])->select();
				if(!empty($classes)){
					foreach($classes as $k=>$v){
						$info[0]['class_id'][$k]['class_id'] = $v['class_id'];
						$info[0]['class_id'][$k]['class_name'] = $v['class_name'];
					}
						
				}else{
					$info[0]['class_id'] =  array();
				}
			}else{
				if($info[0]['class_id']){
					foreach($info[0]['class_id'] as $k=>$v){
						$info[0]['class_id'][$k]['class_name'] = (string)current(M('class')->field('name')->where('id ='.$v['class_id'])->find());
					}
				}else{
					$info[0]['class_id'] = array();
				}
			}
				
			$info[0]['school_id'] = $this->getSchoolId($info[0]['uid'],$info[0]['type']);
			//  		p($info);
			$sucinfo['msg']="登陆成功";
			$sucinfo['result']=$info[0];
			success_response($sucinfo);
		}
	}
	
	//教师版  个人档案_编辑个人档案
	public function grdangan_edit(){
		$email = I('email');
		$graduated = I('graduated');
		$profile = I('profile');
		$mobile  = I('mobile');
		
		if($email) $data['email'] = $email;
		if($email) $data['graduated'] = $graduated;
		if($email) $data['profile'] = $profile;
		if($email) $data['mobile'] = $mobile;
		
		
		$uid = $this->uid;
		
		$res1 = M('ucenter_member')->where("id = $uid")->save($data);
		

// 		echo M()->getLastSql();
		if($res1){
			$sucinfo['msg']="修改成功";
			success_response($sucinfo);
		}else{
			error_response();
		}
	}
	
	
	//教师版  教师风采
	public function teacher_photo(){
		$upload = $this->_upload('user/'.date('Ym')."/");
		$image = new \Think\Image();
		foreach($upload as $k=>$v){
			$pic[$k]['origin_img'] = $tmp =  $v['savepath'].$v['savename'];
			$image->open("./Uploads/".$tmp);
			$pic[$k]['midden_img'] = $thumbaddress = "user/".date("Ym")."/".date("d")."/thumb_".$v['savename'];
			$image->thumb(300, 300,\Think\Image::IMAGE_THUMB_CENTER)->save("./Uploads/".$thumbaddress);
		}
		
		if(!empty($pic)){
			foreach($pic as $v){
				$data['origin_img'] = $v['origin_img'];
				$data['midden_img'] = $v['midden_img'];;
				$data['uid'] = $this->uid;
				M('member_photo')->add($data);
			}
			$sucinfo['msg']="ok";
			$sucinfo['result']=$pic;
			success_response($sucinfo);
		}else{
			error_response();
		}

		
	}
	
	
	//教师版  修改头像
	public function teacher_edit_avatar(){
		$uid = $this->uid;
		$upload = $this->_upload('user/'.date('Ym')."/");
		$pic['orig_img'] = $origimg = $upload[photo]['savepath'].$upload[photo]['savename'];
		$image = new \Think\Image();
		$pic['midden_img'] = $thumbaddress =  "user/".date("Ym")."/".date("d")."/avatar_".$upload[photo]['savename'];
		$image->open("./Uploads/".$origimg);
		$image->thumb(300, 300,\Think\Image::IMAGE_THUMB_CENTER)->save("./Uploads/".$thumbaddress);
		$data['avatar'] = json_encode($pic);
		if(M('member')->where("uid = $uid")->save($data)){
			$sucinfo['msg']="ok";
			$pic['orig_img'] = UPLOAD_PATH.$pic['orig_img'];
			$pic['midden_img'] = UPLOAD_PATH.$pic['midden_img'];
			$sucinfo['result']=$pic;
			success_response($sucinfo);
		}else{
			error_response();
		}
	}
	
	
	
	//教师版  详情回复
	public function tea_info_comment(){
		$data['content'] = $_REQUEST['content'];
		$data['creator_type'] = $this->type;
		$data['creator_id'] = $this->uid;
		$data['create_time'] = time();
		$data['target_id'] = $_REQUEST['target_id'];
		$data['target_type'] = $_REQUEST['target_type'];
		if(!$data['content'] || !$data['target_id'] || !isset($_REQUEST['target_type'])) error_response();
		
// 		print_r($data);
		if(M('comment')->add($data)){
			if($this->type == 2){
				score_add_log($this->uid,"回复/评论",10);
			}
			$sucinfo['msg']="ok";
			success_response($sucinfo);
		}else{
			error_response();
		}
	}
	
	

	//教师版  家长留言列表
	public  function tmessage_list(){
		$page = $_GET['page']?(int)$_GET['page']:1;
		if($_GET['page'] != 0){
			$pages = "$page,10";
		}elseif($_GET['page'] === 0){
			$pages ="";
		}
		//获取所有留言
		$babyid = I('baby_id');
		if(!$babyid) error_response();
		$res=M('message')->where("baby_id = $babyid")->order("id desc");
		if($pages) $res = $res->page($pages);
		$res = $res->select();
		$res = array_reverse($res);
		foreach($res as  $k=>$v){

			$res[$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
			if($res[$k]['filename']) $res[$k]['filename'] = UPLOAD_PATH.$v['filename'];
			
			if($res[$k]['pic']){
				$res[$k]['pic'] = json_decode($res[$k]['pic'],1);
				// 			p($res[$k]['pic']);
				$res[$k]['pic']['orig_img'] = UPLOAD_PATH.$res[$k]['pic']['orig_img'];
				$res[$k]['pic']['middle_img'] = UPLOAD_PATH.$res[$k]['pic']['middle_img'];
			}else{
				$res[$k]['pic']['orig_img'] = '';
				$res[$k]['pic']['middle_img']="";
			}
			
			if($v['creator_type'] == 2){
				//教师
				$meminfo = M('member')->field("nickname,avatar")->where("uid=".$v['creator_id'])->find();
				$meminfo['avatar'] = json_decode($meminfo['avatar'],1);
				if(!empty($meminfo['avatar'])){
// 					$meminfo['avatar']['orig_img'] = UPLOAD_PATH.$meminfo['avatar']['orig_img'];
					$meminfo['avatar'] = UPLOAD_PATH.$meminfo['avatar']['midden_img'];
				}
				if(!empty($meminfo)){
					$meminfo['nickname'] = $meminfo['nickname']."老师";
					$res[$k]['author'] = $meminfo;
				}else{
					$meminfo['nickname'] = "";
					$res[$k]['author'] = $meminfo;
				}
			}elseif($v['creator_type'] == 3){
				//家长
				$meminfo = M('baby')->field("name,avatar")->where("id=$babyid")->find();
				$meminfo['avatar'] = UPLOAD_PATH.$meminfo['avatar'];
				if(!empty($meminfo)){
					$meminfo['name'] = $meminfo['name']."的家长";
					$res[$k]['author'] = $meminfo;
				}else{
					$meminfo['name'] = "";
					$res[$k]['author'] =$meminfo;
				}
				
			}
		}
		foreach($res as $k=>$v){
			$mobile = $this->getparentphonenum($v['creator_id'],$v['creator_type']);
			if(!$mobile) $mobile = "";
			$res[$k]['mobile'] = $mobile;
		}
// 		p($res);
		$sucinfo['msg']="ok";
		$sucinfo['result']=$res;
		success_response($sucinfo);
	}
	
	
	//教师版  家长留言添加
	public  function tmessage_add(){
		//上传文件 语音和图片
		$uid = $this->uid;
		//if($this->type == 1) error_response(array ("code" => 26,"msg" => "没有权限"));
		$upload = $this->_uploadfile('message/'.date('Ym')."/");
		
		
		
		if(isset($upload['pic']) && !empty($upload['pic'])){
			$pic['orig_img'] = $origimg = $upload[pic]['savepath'].$upload[pic]['savename'];
			$image = new \Think\Image();
			$pic['middle_img'] = $thumbaddress =  "message/".date("Ym")."/".date("d")."/middle_".$upload[pic]['savename'];
			$image->open("./Uploads/".$origimg);
			$image->thumb(300, 300,\Think\Image::IMAGE_THUMB_CENTER)->save("./Uploads/".$thumbaddress);
			$data['pic'] = json_encode($pic);
		}
		
		if(isset($upload['yuyin']) && !empty($upload['yuyin'])){
			$data['filename'] = $upload['yuyin']['savepath'].$upload['yuyin']['savename'];
		}
		
		$babyid = (int)$_REQUEST['baby_id'];
		$data['baby_id']  = I('baby_id');
		$data['content']  = I('content');
		$data['creator_type']  = $this->type;
		$data['create_time']  = time();
		$data['creator_id']  = $this->uid;
		if(M('message')->add($data)){
			$sucinfo['msg']="ok";
			success_response($sucinfo);
		}else{
			error_response();
		}
	}
	
	
	

	//上传图片
	public function _upload($dir){
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     3145728 ;// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
// 		$upload->savePath  =      'user/'.date('Ym')."/";
		$upload->savePath  =      $dir;
		// 上传文件
		$upload->subName = date('d');
		$info   =   $upload->upload();
		if(!$info) {// 上传错误提示错误信息
			return false;
		}else{// 上传成功
			return $info;
		}
	}
	
	//上传文件
	public function _uploadfile($dir){
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     60145728 ;// 设置附件上传大小
// 		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
		// 		$upload->savePath  =      'user/'.date('Ym')."/";
		$upload->savePath  =      $dir;
		// 上传文件
		$upload->subName = date('d');
		$info   =   $upload->upload();
		if(!$info) {// 上传错误提示错误信息
			return false;
		}else{// 上传成功
			return $info;
		}
	}
	
	
	


	//家长版 泡泡动态点评列表
	public function  baby_dping(){
		$page = $_GET['page']?(int)$_GET['page']:1;
		if($_GET['page'] != 0){
			$pages = "$page,6";
		}elseif($_GET['page'] === 0){
			$pages ="";
		}
		
		//查找动态详情
		$id = (int)$_REQUEST['id'];
		$map['target_type'] = I('target_type',0);
		switch ($map['target_type']){
			case 4:
				$mn='Dongtai';
				break;
			case 6:
				$mn='Ptrend';
				break;
			default:
				error_response();
		}
		$res = M($mn)->where("id=$id")->find();
		$res['org_pic'] = json_decode($res['org_pic'],1);
		if(!empty($res['org_pic'])){
			foreach($res['org_pic'] as $k=>$v){
				$res['org_pic'][$k] = UPLOAD_PATH.$v;
			}
		}else{
			$res['org_pic'] = array();
		}
		$res['pictures'] = json_decode($res['pictures'],1);
		if(!empty($res['pictures'])){
			foreach($res['pictures'] as $k=>$v){
				$res['pictures'][$k] = UPLOAD_PATH.$v;
			}
		}else{
			$res['pictures'] = array();
		}
		$res['create_time']= date("Y-m-d H:i:s" , $res['create_time']);
		$res['voice_file'] && $res['voice_file'] = UPLOAD_PATH.$res['voice_file'];
		$res['video_file'] && $res['video_file'] = UPLOAD_PATH.$res['video_file'];
		$arr['info'] = $res;
		
		//查找动态相关评论
		
		$map['target_id'] = $id;
		$info = M('comment')->field("id,content,creator_type,creator_id")->page($pages)->where($map)->select();
		foreach($info as $k=>$v){
			if($v['creator_type'] == 2){
				//教师
				$meminfo = M('member')->field("nickname,avatar")->where("uid=".$v['creator_id'])->find();
				$meminfo['avatar'] = json_decode($meminfo['avatar'],1);
				if(!empty($meminfo['avatar'])){
// 					$meminfo['avatar']['orig_img'] = UPLOAD_PATH.$meminfo['avatar']['orig_img'];
					$meminfo['avatar'] = UPLOAD_PATH.$meminfo['avatar']['midden_img'];
				}
				$meminfo['nickname'] = $meminfo['nickname']."老师";
				$info[$k]['author'] = $meminfo;
			
			}elseif($v['creator_type'] == 3){
				//家长
				$meminfo = M()->table(PRE."parent a")->field("b.name,b.avatar")->join(PRE."baby b  on a.baby_id = b.id")->where("a.id=".$v['creator_id'])->find();
				$meminfo['avatar']= UPLOAD_PATH.$meminfo['avatar'];
				$meminfo['name'] = $meminfo['name']."的家长";
				$info[$k]['author'] = $meminfo;
			}
		}
		$arr['pinglun'] = (array)$info;
		if(!empty($res)){
			$sucinfo['msg']="ok";
			$sucinfo['result']=$arr;
			success_response($sucinfo);
		}else{
			error_response();
		}
	}
	/**
	* @Tag:积分月结算
	*/
	function monthly_score_sum(){
		$tn_mb=PRE.'member';
		$tn_ds=PRE.'score_record_byday';
		$date=explode('-', date('Y-m'));
		$aaa=mktime(0,0,0,$date[1]-1,1,$date[0]);
		$date=date('Y-m',$aaa);
		
 		$score=current(current(M()->query("select SUM(ds.score_used-ds.score_submit) AS tp_sum FROM babyonline_score_record_byday as ds where ds.pid={$this->uid} and SUBSTRING(ds.date,1,7)='$date' LIMIT 1")));
 		if($score!==false){
 			//$add=M()->query("insert into babyonline_score_record_bymonth (uid,date,score) values ({$this->uid},'$date',$score) on duplicate key update score=score+$score");
 			$add=M()->table($tn_mb.' as mb')->where('mb.uid='.$this->uid)->setInc('score',$score);
 			if($add!==false){
 				$submit=M()->query("update babyonline_score_record_byday as ds set ds.score_submit=ds.score_used where ds.pid={$this->uid} and SUBSTRING(ds.date,1,7)='$date'");
 				dump($submit);exit;
 				if($submit!==false){
 					$data_score['uid']=$this->uid;
 					$data_score['create_time']=time();
 					$data_score['remark']="月结算,积分+$score";
 					M('ScoreRecord')->add($data_score);
 					
 				}
 				
 			}
 		}
	}
  
	
	
	
	
	
	
	

}
