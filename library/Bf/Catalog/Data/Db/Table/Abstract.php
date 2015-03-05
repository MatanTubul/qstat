<?php

abstract class Bf_Catalog_Data_Db_Table_Abstract extends Bf_Db_Table implements Bf_Catalog_Data_Db_Table_Interface {

	const TBL_NAME			= 'catalog_data';

	const COL_ID_CATALOG 	= 'id_catalog';
	const COL_ID_LANGUAGES 	= 'id_languages';
	const COL_ID_TITLE 		= 'title';

	protected $_name = self::TBL_NAME;
}