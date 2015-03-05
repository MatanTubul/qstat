<?php
class Translator_Model_Log extends Zend_Log_Writer_Db
{

	public function __construct ($db, $table)
	{
		$arrColumnMap = array(
			Translator_Model_Db_Translation::COL_ID_SYSTEM => 'message', Translator_Model_Db_Translation::COL_CONTENT => 'text'
		);
		
		parent::__construct($db, $table, $arrColumnMap);
	}

	protected function _write ($event)
	{
		
		if (! empty($event['message']) && stripos($event['message'], 'LBL_') !== FALSE) {
			$event['text'] = substr($event['message'], stripos($event['message'], 'LBL_') + 4);
			if (stripos($event['text'], 'TEXT_') !== FALSE) {
				$event['text'] = "";
			} else {
				$event['text'] = str_ireplace('_', ' ', $event['text']);
				$event['text'] = strtolower($event['text']);
				$event['text'] = ucwords($event['text']);
			}
		} else {
			$event['text'] = $event['message'];
		}
		
		try {
			parent::_write($event);
		} catch (Exception $objE) {}
		    
		Qstat_Importer::$objTranslate = Translator_Model_Translate::initTranslate();
		
	}

}