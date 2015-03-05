<?php

class Qstat_JQuery_JqGrid_Column_Decorator_UserExtraSubGroups extends Qstat_JQuery_JqGrid_Column_Decorator_UserExtra
{
	public function cellValue($row) {
		$arrExtra = unserialize( $row[ User_Model_Db_Users::COL_EXTRA_DATA ] );
		if ( empty($arrExtra) || empty( $arrExtra[ $this->getName() ] ) || ! is_array( $arrExtra[ $this->getName() ] ) ) {
			return '';
		}

		// Something was unserialized.
		$strReturnVal = '';
		$arrVal = $this->getArrValuePairs();
		$values = $arrExtra[ $this->getName() ];
		foreach ( $values as $value ) {
			if ( ! empty( $arrVal[ $value ] ) ) {
				$strReturnVal .= $arrVal[ $value ].' ';
			}
		}

		return '<div>'.$strReturnVal.'</div>';
	}

	public function unformatValue($strValue, $strRule) {
		$arrData = array();
		$columnName = $this->getName();
		$arrData[$columnName] = $strValue;

		$strSerializeData = serialize($arrData);

		$strSerializeData = substr($strSerializeData, 5);
		$strSerializeData = substr($strSerializeData, 0, -1);

		if ( $columnName === 'subgroups' ) {
			$strSerializeData = str_replace( 's:9:"subgroups";', 's:9:"subgroups";%', $strSerializeData );
		}

		return $strSerializeData;
	}
}
