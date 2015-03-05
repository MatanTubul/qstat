<?php
/**
* User Model Class
*
* @author shurf
*/
class Translator_Model_Db_Translation extends Bf_Db_Table
{

	const TBL_NAME = "importer_translate";

	const COL_ID_SYSTEM = "import_code";
	const COL_CONTENT = "import_translate_to";

	protected $_name = self::TBL_NAME;
	protected $_primary = self::COL_ID_SYSTEM;
}