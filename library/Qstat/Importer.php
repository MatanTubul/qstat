<?php

class Qstat_Importer
{
	public static $objTranslate;

	/**
	*
	* Enter description here ...
	* @return string
	*/
	protected static function getTranslation ($strTranslation)
	{

		if (empty(self::$objTranslate)) {
			self::$objTranslate = Translator_Model_Translate::initTranslate();
		}

		$objTranslator = self::$objTranslate;

		$strTranslation = trim($strTranslation);

		return $objTranslator->translate($strTranslation);

	}

	protected static function getElementNames (Bf_Catalog $objCatalog)
	{
		$arrElementNames = array();

		$arrForms = $objCatalog->getItemForm(4);

		foreach ($arrForms as $objForm) {
			$arrElements = $objForm->getElements();
			foreach ($arrElements as $objElement) {
				$arrElementNames[] = $objElement->getName();
			}
		}
		Zend_Debug::dump($arrElementNames);
	}

	public static function getSiteId ($strSiteName)
	{
		$objSiteDb = new Qstat_Db_Table_Sites();
		$objSiteDbSelect = $objSiteDb->select(TRUE);
		$objSiteDbSelect->where(Qstat_Db_Table_Sites::COL_SITE_TITLE . " = ?", $strSiteName);
		$objSiteDbSelect->where(Qstat_Db_Table_Sites::COL_IS_DELETED . " = ?", FALSE);

		$objSiteDbRow = $objSiteDb->fetchRow($objSiteDbSelect);

		if (! empty($objSiteDbRow)) {
			return $objSiteDbRow->{Qstat_Db_Table_Sites::COL_ID_SITES};
		}
		return 0;
	}

	public static function getGroupId($intSiteId = null, $strGroupName)
	{
		$objSiteDb = new Qstat_Db_Table_Groups();

		$objSiteDbSelect = $objSiteDb->select(TRUE);
		if ($intSiteId != null) {
			$objSiteDbSelect->where(Qstat_Db_Table_Groups::COL_ID_SITES . " = ?", $intSiteId);
		}
		$objSiteDbSelect->where(Qstat_Db_Table_Groups::COL_GROUP_NAME . " = ?", $strGroupName);
		$objSiteDbSelect->where(Qstat_Db_Table_Groups::COL_IS_DELETED . " = ?", FALSE);

		$objSiteDbRow = $objSiteDb->fetchRow($objSiteDbSelect);

		if (empty($objSiteDbRow)) {
			$objSiteDbRow = $objSiteDb->createRow();
			$objSiteDbRow->{Qstat_Db_Table_Groups::COL_ID_SITES} = $intSiteId;
			$objSiteDbRow->{Qstat_Db_Table_Groups::COL_GROUP_NAME} = $strGroupName;
			$objSiteDbRow->save();
		}

		if ($intSiteId!=null)
			return $objSiteDbRow->{Qstat_Db_Table_Sites::COL_ID_SITES};
		else
			return $objSiteDbRow->{Catalog_Model_CatalogData::COL_ID_GROUPS};
	}

	// @ params catalog Parent id 4 - Servers , 5 - Switches
	public function importXls($target_path, Bf_Catalog $objCatalog, $intCatalogParentId)
	{
		$objCatDataDb = $objCatalog
		->getCatalogModel()
		->getObjCatalogData()
		->getObjDbDataTable();

		if (!intval($intCatalogParentId)) {
			echo 'Error: The $intCatalogParentId is empty.';
			return;
		}
		switch (intval($intCatalogParentId)) {
			case Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::SERVER_DEVICES ):
				// Servers, 5
				$intEntetyId = 4;
				break;
			case Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::SWITCH_DEVICES ):
				// Switches, 6
				$intEntetyId = 5;
				break;
			case Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::ORCA_DEVICES ):
				// Orca, 7
				$intEntetyId = 7;
				break;
			case Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::CABLES_DEVICES ):
				// Cables, 7269
				$intEntetyId = 11;
				break;
			case Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::CARDS_DEVICES ):
				// Cards, 7270
				$intEntetyId = 12;
				break;
			default:
				echo 'Error: The $intCatalogParentId is not proper.';
				return;
		}

		$objAttrRows = $objCatalog->getObjEav()->getAttributeByEntityType($intEntetyId);

		require_once APPLICATION_PATH . '/../library/PHPExcel.php';
		$objPHPExcel = PHPExcel_IOFactory::load($target_path);

		$ws = 1;
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$highestRow = $worksheet->getHighestRow(); // e.g. 10

			$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$num = $highestColumnIndex;

			// Get Header Column
			$arrHeader = array();
			for ($col = 0; $col < $highestColumnIndex; ++ $col) {
				$cell = $worksheet->getCellByColumnAndRow($col, 1);
				$val = $cell->getValue();

				// Find Attribute to this
				if ($val == 'Title') {
					$arrHeader[$col] = 'title';
					continue;
				} elseif ($val == "Groups") {
					$arrHeader[$col] = "Groups";

					continue;
				}

				foreach ($objAttrRows as $objAttrRow) {
					if ($val == $objAttrRow->{Bf_Eav_Db_Attributes::COL_ATTR_CODE}) {
						$arrHeader[$col] = $objAttrRow;

						break;
					}
				}
			}

			$objElement = new Zend_Form_Element('test');

			for ($row = 2; $row <= $highestRow; ++ $row) {
				$boolRowNotEmpty = FALSE;
				$arrVal = array();

				$arrVal[Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT] = intval($intCatalogParentId);
				$arrVal[Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER] = 0;
				$arrVal[Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES] = $intEntetyId;


				for ($col = 0; $col < $highestColumnIndex; ++ $col) {
					$cell = $worksheet->getCellByColumnAndRow($col, $row);
					$val = trim($cell->getValue());

					if (empty($arrHeader[$col])){
						continue;
					}

					if ( $arrHeader[$col] === Catalog_Model_CatalogData::COL_ID_TITLE ) {
						$arrVal[Catalog_Model_CatalogData::COL_ID_TITLE] = trim($val);
					} elseif ($arrHeader[$col] == "Groups") {
						if (empty($val)) {
							echo 'Error: The Group is empty.';
							return;
						}

						$arrVal[Catalog_Model_CatalogData::COL_ID_GROUPS] = self::getGroupId(null, $val);
					} elseif ($arrHeader[$col]->{Bf_Eav_Db_Attributes::COL_VALUE_TYPE} == Bf_Eav_Db_Attributes::ATTR_VAL_TYPE_SELECT && $val != "") {
						$arrVal[$objElement->filterName($arrHeader[$col]->{Bf_Eav_Db_Attributes::COL_ATTR_CODE})] = Bf_Eav_Db_AttributesValues::saveValForAttr(
							$arrHeader[$col]->{Bf_Eav_Db_Attributes::COL_ID_ATTR}, $val); // Select
					} elseif ($val != "") {
						$arrVal[$objElement->filterName($arrHeader[$col]->{Bf_Eav_Db_Attributes::COL_ATTR_CODE})] = self::getTranslation($val);
					}
				}

				if (empty($arrVal['title'])) {
					echo 'The Title is empty'.PHP_EOL;
					continue;
				}

				// Save Row
				$objCatDataDbSelect = $objCatDataDb
				->select(TRUE)->setIntegrityCheck(FALSE)
				->join(Bf_Catalog_Models_Db_Catalog::TBL_NAME,
					Bf_Catalog_Models_Db_Catalog::TBL_NAME.'.'.Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG." = ".Catalog_Model_CatalogData::TBL_NAME.'.'.Catalog_Model_CatalogData::COL_ID_CATALOG)
				->where(Catalog_Model_CatalogData::COL_TITLE . " = ?", $arrVal['title']);
				$objCatDataDbRow = $objCatDataDb->fetchRow($objCatDataDbSelect);

				if ( ! empty($objCatDataDbRow) && intval($objCatDataDbRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED})) {
					// Return the Device to back, if it was deleted.
					$objCatDataDbRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED} = 0;
					$objCatDataDbRow->save();

					$objCatalogTable = new Bf_Catalog_Models_Db_Catalog( array('moduleCode' => 1) );
					$objCatalogTableSelect = $objCatalogTable
					->select()
					->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG."=?", $objCatDataDbRow->{Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG});
					$objCatalogTableRow = $objCatalogTable->fetchRow($objCatalogTableSelect);
					if ( $objCatalogTableRow ) {
						$objCatalogTableRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED} = 0;
						$objCatalogTableRow->save();
					}
				}

				try {
					if ( ! isset( $arrVal[Catalog_Model_CatalogData::COL_ID_GROUPS] ) || ! intval( $arrVal[Catalog_Model_CatalogData::COL_ID_GROUPS] ) ) {
						throw new Exception('The $arrVal['.Catalog_Model_CatalogData::COL_ID_GROUPS.'] is empty.');
					}

					if (empty($objCatDataDbRow)) {
						if (empty($arrVal[Catalog_Model_CatalogData::COL_ID_SITES])) {
							$arrVal[Catalog_Model_CatalogData::COL_ID_SITES] = 4; // Site MTL
						}
					} else {
						$this->_setValue($arrVal, Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES, $objCatDataDbRow);
						$this->_setValue($arrVal, Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED, $objCatDataDbRow);

						$this->_setValue($arrVal, Catalog_Model_CatalogData::COL_ID_CATALOG, 		$objCatDataDbRow);
						$this->_setValue($arrVal, Catalog_Model_CatalogData::COL_ID_SITES, 			$objCatDataDbRow);
						$this->_setValue($arrVal, Catalog_Model_CatalogData::COL_ID_SCRIPT_LOCK, 	$objCatDataDbRow);
						$this->_setValue($arrVal, Catalog_Model_CatalogData::COL_ID_SCRIPT_UNLOCK, 	$objCatDataDbRow);
						$this->_setValue($arrVal, Catalog_Model_CatalogData::COL_IS_SHARED, 		$objCatDataDbRow);
					}
				} catch (Exception $e) {
					echo "Error: " . $e->getMessage() . PHP_EOL;
					return;
				}

				$arrForms = $objCatalog->getItemForm($intEntetyId);
				if ($objCatalog->save($arrForms, $arrVal)) {
					echo "Saved Correctly: " . $arrVal['title'] . '.' . PHP_EOL;
					continue;
				}

				foreach ($arrForms as $objForm) {
					// Get all error messages.
					$arrErrors = array();
					foreach ($arrForms as $objForm) {
						$arrErrors = array_merge($arrErrors, $objForm->getMessages());
					}

					if (! empty($arrErrors)) {
						foreach ($arrErrors as $strField => $mixError) {
							foreach ((array) $mixError as $strError) {
								$arrNotUpdated[] = "Error: " . $arrVal['title'] . " Field: " . $strField . " Msg: " . $strError . "" . PHP_EOL;
								echo "Error: " . $arrVal['title'] . " Field: " . $strField . " Msg: " . $strError . "" . PHP_EOL;
							}
						}
					}
				}
			}
		}
	}

	private function _setValue( & $arrVal, $key, $model) {
		if (isset($model->$key)) {
			$arrVal[$key] = $model->$key;
		}
	}
}
