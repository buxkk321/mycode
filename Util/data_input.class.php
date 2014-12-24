<?php
namespace Common\My;
class data_input {
	public static function for_PHPExcel($phpexcelpath=''){
		error_reporting(E_ALL);
		set_time_limit(0);
		ob_end_clean ();
		ob_start ();
		$filetype=array('Excel5'=>'Excel5','Excel2007'=>'Excel2007');
		$do=function(&$inputFileName)use($filetype){
			set_include_path(get_include_path() . PATH_SEPARATOR . './'.$phpexcelpath.'PHPExcel_1.8.0_doc/Classes/');
			include 'PHPExcel/IOFactory.php';
	
			$inputFileType = $_POST['filetype'];
			if(!isset($filetype[$inputFileType])){
				opr('选择的文件类型错误:'.$inputFileType.'<br />程序终止','echo');
				exit;
			}
				
			$filename='excel';
			if(check_file($filename)){
				if(!is_dir(C('EXCEL_UPLOAD.rootPath'))) mkdir(C('EXCEL_UPLOAD.rootPath'));
				$Upload = new \Think\Upload(C('EXCEL_UPLOAD'));
				$info   = $Upload->upload(array($filename=>$_FILES[$filename]));
				if($info===false){
					opr('上传错误:'.$Upload->getError().'<br />程序终止','echo');
					exit;
				}else{
					$inputFileName=C('EXCEL_UPLOAD.rootPath').$info[$filename]['savepath'].$info[$filename]['savename'];
				}
			}else{
				opr('没有文件上传<br />程序终止','echo');
				exit;
			}
	
			opr('上传成功,文件载入中:'.$inputFileName.',文件类型:'.$inputFileType.',使用:PHPExcel_IOFactory<br />','echo');
			$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
			opr('Turning Formatting off for Load<br />','echo');
			$objReader->setReadDataOnly(true);
			try{
				$objPHPExcel = $objReader->load($inputFileName);
			}catch (\PHPExcel_Reader_Exception $e){
				opr('错误:'.$e->__toString().',<br />程序终止','echo');
				unlink($inputFileName);
				exit;
			}
	
			opr('<hr />','echo');
			$sheetData=$objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			return $sheetData;
		};
		$inputFileName='';
		$data=$do($inputFileName);
		self::post_code($data);
		unlink($inputFileName);
	}
	protected static function post_code($data){
		foreach($data as $k=>$v){
			opr($v,'dump',1);
		}
	}
}