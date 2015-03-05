<?php
class Bf_Eav_Db_Entities extends Bf_Db_Table {

	const TBL_NAME = 'entities';

	const COL_ID_ENTITIES = 'id_entities';
	const COL_ID_ENTITIES_TYPES = 'id_entities_types';

	protected $_name = self::TBL_NAME;
	//	, id_entities_types, entity_path, created_by, created_on, updated_by, updated_on, is_deleted
}