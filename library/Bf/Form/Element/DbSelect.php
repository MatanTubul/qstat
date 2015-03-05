<?php
class Bf_Form_Element_DbSelect extends Zend_Form_Element_Select
{
    private $_dbAdapter;
    private $_dbSelect;
    private $_identityColumn = 'id';
    private $_valueColumn = '';
    /**
     * Set the database adapter used
     * @param Zend_Db_Adapter_Abstract $adapter
     * @return Bf_Form_Element_DbSelect
     */
    public function setDbAdapter (Zend_Db_Adapter_Abstract $adapter)
    {
        $this->_dbAdapter = $adapter;
        return $this;
    }
    /**
     * Set the query used to fetch the data
     * @param string|Zend_Db_Select $select
     * @return Bf_Form_Element_DbSelect
     */
    public function setDbSelect ($select)
    {
        $this->_dbSelect = $select;
        return $this;
    }
    /**
     * Set the column where the identifiers for the options are fetched
     * @param string $name
     * @return Bf_Form_Element_DbSelect
     */
    public function setIdentityColumn ($name)
    {
        $this->_identityColumn = $name;
        return $this;
    }
    /**
     * Set the column where the visible values in the options are fetched
     * @param string $name
     * @return Bf_Form_Element_DbSelect
     */
    public function setValueColumn ($name)
    {
        $this->_valueColumn = $name;
        return $this;
    }
    public function render (Zend_View_Interface $view = null)
    {
		if ( $this->getName() === 'id_groups' ) {
			$this->setDisableTranslator(true);
		}
        $this->_performSelect();
        return parent::render($view);
    }

	public function isValid($value, $context = null)
	{
		$this->_performSelect();
		$validationResult = parent::isValid($value, $context);

		if ( in_array( 'notInArray', $this->getErrors() ) ) {
			$this->_humanErrorMessage();
		}

		return $validationResult;
	}

    /**
     *
     * Actually perform the select
     * @throws Zend_Form_Element_Exception
     * @return Bf_Form_Element_DbSelect
     */
    private function _performSelect ()
    {
        if ( ! $this->_dbAdapter) {
            $this->_dbAdapter = Zend_Db_Table::getDefaultAdapter();
		}
        $stmt = $this->_dbAdapter->query($this->_dbSelect);
        $results = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        $options = array();
        foreach ($results as $r) {
            if (! isset($r[$this->_identityColumn])) {
                throw new Zend_Form_Element_Exception(
                'Identity column is not present in the result');
            }
            if (! isset($r[$this->_valueColumn])) {
                throw new Zend_Form_Element_Exception(
                'Value column is not present in the result');
            }
            $options[$r[$this->_identityColumn]] = $r[$this->_valueColumn];
        }
        $this->addMultiOptions($options);

        return $this;
    }

	private function _humanErrorMessage()
	{
		$inArrayValidator = $this->getValidator('inArray');
		if ( ! is_object($inArrayValidator) ) {
			return;
		}
		$value = intval( $inArrayValidator->value );
		if ( ! $value || ! is_scalar( $value ) || ! is_object( $this->_dbSelect ) ) {
			return;
		}

		$dbSelectlone = clone $this->_dbSelect;
		$dbSelectlone
		->reset()
		->from( $this->_name, $this->_valueColumn )
		->where( $this->_identityColumn.' = '.intval($value) );
		if ( ! $this->_dbAdapter) {
			$this->_dbAdapter = Zend_Db_Table::getDefaultAdapter();
		}
		$stmt = $this->_dbAdapter->query($dbSelectlone);
		$valueName = $stmt->fetch(Zend_Db::FETCH_COLUMN);
		if ( empty( $valueName ) ) {
			return;
		}

		$messageString = $valueName.' not found between "'.implode(', ', $this->options).'".';
		$this->setErrors( array( $messageString, ) );
	}
}
