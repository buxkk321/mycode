<?php
namespace Modules\Port\Model;
class MainModel extends \Phalcon\Mvc\Model {
	public function getMsgString(){
		$messages = '';
		foreach (parent::getMessages() as $vv) {
			$messages.='
					type:'.$vv->getType().',
					msg:'.$vv->getMessage();
		}
		return $messages;
	}

}
