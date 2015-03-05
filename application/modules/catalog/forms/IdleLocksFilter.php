<?php
class Catalog_Form_IdleLocksFilter extends ZendX_JQuery_Form {

    protected $arrConf;

    public function __construct($options, $arrConf){
        $this->arrConf = $arrConf;
        parent::__construct($options);



    }

    public function init ()
    {

        $arrConf = $this->arrConf;

    	$this->setName('idleLocks');
    	$this->setAttrib('id', 'idleLocks');
    	$this->setAction($this->getView()->url(array('module'=>'catalog','controller'=>'report','action'=>'idle-locks')));


    	$objElement = new ZendX_JQuery_Form_Element_DatePicker('fromDate');
    	$objElement->setDecorators(array('UiWidgetElement','Label'))->setLabel('LBL_FROM_DATE');

    	$objFromDate = new DateTime();
     	$objFromDate->sub(new DateInterval("P".(int)$arrConf['catalog']['report']['idleLocks']['defaultPeriodDays']."D"));
     	$objElement->setValue($objFromDate->format($arrConf['dateformat']['php']['shortdate2']));

    	$this->addElement($objElement);


    	$objElement = new ZendX_JQuery_Form_Element_DatePicker('toDate');
    	$objElement->setJQueryParam('dateFormat', $arrConf['dateformat']['datepicker']['shortdate']);
    	$objElement->setDecorators(array('UiWidgetElement','Label'))->setLabel('LBL_TO_DATE');

    	$objToDate = new DateTime();
    	$objElement->setValue($objToDate->format($arrConf['dateformat']['php']['shortdate2']));

    	$this->addElement($objElement);

    	$objElement = new Zend_Form_Element_Submit('LBL_FILTER_IDLE_LOCKS');
    	$objElement->setDecorators(array('ViewHelper'));
    	$this->addElement($objElement);

    	$objElement = new Zend_Form_Element_Button('LBL_EXPORT_IDLE_LOCKS');
    	$objElement->setDecorators(array('ViewHelper'));
    	$objElement->setAttrib('onclick', 'exportLocks()');
    	$this->addElement($objElement);
    }
}