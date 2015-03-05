<?php

class Bf_Plugin_Cron_SynchronizeDB extends Bf_Plugin_Cron_CronAbstract
{
	const Asset_Name = 'AssetName';
	const CSV_DELIMITER = ',';
	// For now, Servers only
	const COL_ID_ENTITIES_TYPES = 4;

	protected $_filename;
	private $_columns = array();

	public function __construct($args = null) {
		if ( ! is_array($args) || ! array_key_exists('filename', $args)) {
			throw new Bf_Plugin_Cron_Exception('The FileToucher cron task plugin is not configured correctly.');
		}

		$files_array = glob( $args['filename'] );
		$this->_filename = empty( $files_array[0] ) ? '' : $files_array[0];
	}

	public function run() {
		echo PHP_EOL.'Synchronization start.'.PHP_EOL;

		$handle = @fopen($this->_filename, 'r');
		if ( ! $handle) {
			echo PHP_EOL.'Synchronization was broken, the Data file'.$this->_filename.' was not found.'.PHP_EOL;
			return;
		}

		$objApplicationOptions = new Zend_Config($this->getAppOptions());
		$objCatalog = new Bf_Catalog($objApplicationOptions->catalog);

		$arrForms = $objCatalog->getItemForm( self::COL_ID_ENTITIES_TYPES );

		$j = 0;
		while ( ($csv_string = fgets($handle) ) !== false ) {
			if ( ! $j) {
				if ( ! $columns_amount = $this->_read_columns($csv_string) ) {
					return;
				}
				$j++;
				continue;
			}

			$row = $this->_read_cells($csv_string, $columns_amount);
			if ( ! count($row) ) {
				// error;
				continue;
			}

			$objCatalogModel = $objCatalog->getCatalogModel()->getObjCatalogTable();
			$objCatalogSelect = $objCatalogModel->select(true)->setIntegrityCheck(false)
			->join(
				Catalog_Model_CatalogData::TBL_NAME, Catalog_Model_CatalogData::TBL_NAME.".".Catalog_Model_CatalogData::COL_ID_CATALOG." = ".Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG." AND ".
				Catalog_Model_CatalogData::TBL_NAME.".".Catalog_Model_CatalogData::COL_IS_DELETED." = 0"
			)
			->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED." = 0")
			->where( new Zend_Db_Expr( "LOWER(".Catalog_Model_CatalogData::COL_ID_TITLE.")" )." =? ", strtolower( $row[ Catalog_Model_CatalogData::COL_ID_TITLE ] ) )
			->reset(Zend_Db_Select::COLUMNS)
			->columns( array( Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG, ) )
			->columns( array( Bf_Catalog_Models_Db_Catalog::TBL_NAME.".".Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES, ) );
			$row_found = $objCatalogModel->fetchRow($objCatalogSelect);
			if ( empty($row_found) ) {
				continue;
			}

			$row[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG] = $row_found->{Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG};
			$row[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES] = $row_found->{Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES};
			$row[Bf_Eav_Db_EntitiesTypes::COL_ID_ENTITIES_TYPES] = self::COL_ID_ENTITIES_TYPES;

			if ( $objCatalog->save($arrForms, $row) ) {
				echo PHP_EOL.'Updated: '.print_r($row, true).PHP_EOL;
				continue;
			}
		}

		// Finish the Synchronization and delete the data file.
		if ( ! feof($handle) ) {
			echo PHP_EOL."Error: unexpected fgets() fail.".PHP_EOL;
		}
		fclose($handle);
		echo PHP_EOL.'Synchronization end.'.PHP_EOL;
		@unlink($this->_filename);
	}

	private function _read_columns($columns_string) {
		$this->_columns = $this->_read_row_string($columns_string);

		/*
		$last_element = array_pop($this->_columns);
		if ( $last_element !== '' ) {
		// With current structure of the CSV file supplied, last column must be not valid.
		echo PHP_EOL.'Synchronization was broken. Unable to read the Columns. Last Element '.$last_element.' is not empty.'.PHP_EOL;

		return 0;
		}
		*/

		$columns_amount = count($this->_columns);
		if ( ! $columns_amount) {
			echo PHP_EOL.'Synchronization was broken. Columns is not empty.'.PHP_EOL;

			return 0;
		}

		$attributesObject = new Bf_Eav_Db_Attributes();
		$attributesAdaptationSelect = $attributesObject
		->select()
		->from( array( Bf_Eav_Db_Attributes::TBL_NAME ), array( Bf_Eav_Db_Attributes::COL_LANSWEEPER_CODE, Bf_Eav_Db_Attributes::COL_ATTR_CODE, ) )
		->where(Bf_Eav_Db_Attributes::COL_ATTR_CODE." <> ''")
		->where(Bf_Eav_Db_Attributes::COL_ATTR_CODE." IS NOT NULL")
		->where(Bf_Eav_Db_Attributes::COL_LANSWEEPER_CODE." <> ''")
		->where(Bf_Eav_Db_Attributes::COL_LANSWEEPER_CODE." IS NOT NULL");
		$attributesAdaptation = $attributesObject->getAdapter()->fetchPairs($attributesAdaptationSelect);
		array_walk( $this->_columns, array( $this, '_convert_attributes_names' ), $attributesAdaptation );

		return $columns_amount;
	}

	private function _read_cells($row_string, $columns_amount) {
		$row = $this->_read_row_string($row_string);
		if ( count($row) !== $columns_amount ) {
			echo PHP_EOL.'Amount of the current row '.print_r($row, true).' cells is not equivalent to columns amount '.$columns_amount.'.'.PHP_EOL;

			return array();
		}

		$row = array_combine( $this->_columns, $row );
		if ( isset( $row['unrecognized_column_name'] ) ) {
			unset( $row['unrecognized_column_name'] );
		}

		return $row;
	}

	private function _read_row_string($row_string) {
		$row_array = explode(self::CSV_DELIMITER, $row_string);
		return array_map( array( $this, '_remove_wrapper_quotes' ), $row_array );
	}

	private function _remove_wrapper_quotes($cell) {
		return trim( trim( $cell, '"' ) );
	}

	private function _convert_attributes_names( & $column_name, $key, $attributesAdaptation) {
		if ( $column_name === self::Asset_Name) {
			$column_name = Catalog_Model_CatalogData::COL_ID_TITLE;
			return;
		}

		if ( ! empty( $attributesAdaptation[$column_name]) ) {
			$column_name = $attributesAdaptation[$column_name];
			return;
		}

		$column_name = 'unrecognized_column_name';
	}
}
