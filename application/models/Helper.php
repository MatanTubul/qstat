<?php
class Application_Model_Helper {

	public static function validateDate($receivedDate, $defaultValue) {
		$validator = new Zend_Validate_Date(array('format' => 'yyyy-mm-dd'));

		if (!$validator->isValid($receivedDate)) {
			return $defaultValue;
		}

		return "'".$receivedDate."'";
	}

	public static function prepareDataForTable( Array $prepared, Array $data) {
		$amount = count($data);
		for ($i = 0; $i < $amount; $i++) {
			$device_type = $data[$i]['device_type'];
			unset($data[$i]['device_type']);
			$prepared[intval($device_type)]['table_params'][] = array($data[$i]['usage_persent'], $data[$i]['model'], $data[$i]['ip'], $data[$i]['title'],);
		}

		return $prepared;
	}

	public static function prepareDataForGraph( Array $data) {
		$prepared = array();

		foreach ($data as $part) {
			$prepared[intval($part['device_type'])]['title'] = str_replace(' ', '_', strtolower($part['device_title']));
			$prepared[intval($part['device_type'])]['graph_params'][0] = array('Unused', self::_getParameter($prepared, $part, 'Unused'));
			$prepared[intval($part['device_type'])]['graph_params'][1] = array('Used', self::_getParameter($prepared, $part, 'Used'));
		}

		return $prepared;
	}

	private static function _getParameter(Array $prepared, Array $part, $current) {
		$pattern = array(
			'Unused' => '0',
			'Used' => '1',
		);

		if ($part['using_type'] === $pattern[$current]) {
			$result = $part['amount'];
		} else {
			$result = isset($prepared[intval($part['device_type'])]['graph_params'][intval($pattern[$current])][1]) ? $prepared[intval($part['device_type'])]['graph_params'][intval($pattern[$current])][1] : 0;
		}

		return $result;
	}
}
