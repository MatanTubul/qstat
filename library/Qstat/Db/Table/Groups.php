<?php

class Qstat_Db_Table_Groups extends Bf_Db_Table
{
	CONST TBL_NAME = "groups";

	CONST COL_ID_GROUPS = 'id_groups';
	CONST COL_GROUP_NAME	= 'groups_name';
	CONST COL_ID_SITES = 'id_sites';
	CONST COL_IS_DELETED	= 'is_deleted';
	CONST COL_USE_CUSTOM_COLUMNS = 'use_custom_fields';
	CONST COL_CUSTOM_COLUMNS = 'custom_fields';
	CONST COL_DEFAULT_SCREEN = 'default_screen';

	protected $_name = self::TBL_NAME;
    protected $_referenceMap = array();

	public function __construct ($config = array(), $definition = null)
	{
		parent::__construct($config, $definition);
		$this->_referenceMap = array(
			'Sites' => array(
				'columns' => array(self::COL_ID_SITES),
				'refTableClass' => 'Qstat_Db_Table_Sites',
				'refColumns' => array(Qstat_Db_Table_Sites::COL_ID_SITES),
				'displayColumn' => Qstat_Db_Table_Sites::COL_SITE_TITLE
			)
		);
	}

	/**
	 *
	 * Enter description here ...
	 * @return Zend_Db_Select
	 */
	public static function getPairSelect($intSiteId = null){
		$objModel = new self();
		$objSelect = $objModel->select(TRUE);
		$objSelect->reset(Zend_Db_Select::COLUMNS);
		$objSelect->columns(array(self::COL_ID_GROUPS,self::COL_GROUP_NAME));
		$objSelect->where(self::COL_IS_DELETED." = ?",FALSE);
		$objSelect->order(self::COL_GROUP_NAME);

		if (!empty($intSiteId)){
			$objSelect->where(self::COL_ID_SITES." = ?",$intSiteId);
		}

		return $objSelect;
	}

	public static function getDefaultScreen($intGroupId){
		$objModel = new self();
		$objSelect = $objModel->select();
		$objSelect->where(self::COL_ID_GROUPS." = ?",$intGroupId);
		$objSelect->where(self::COL_IS_DELETED." = ?",FALSE);
		$objResult= $objModel->fetchRow($objSelect);

		return $objResult->{self::COL_DEFAULT_SCREEN};
	}
}
