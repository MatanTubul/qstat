<?php
/**
 * ReportController
 *
 * @author BelleRon
 * @version 1.0.0
 */
require_once 'Zend/Controller/Action.php';

class Catalog_ImportController extends Zend_Controller_Action
{
    protected $_options;

    public function init ()
    {
        /* Initialize action controller here */
        $objApplicationOptions = new Zend_Config($this->getInvokeArg('bootstrap')->getOptions());
        $this->_options = $objApplicationOptions->catalog;
    }

    public function importAction ()
    {
        $objForm = new ZendX_JQuery_Form();
        $objForm->setAttrib('id', 'importFile');

        $objFormElement = new Zend_Form_Element_Select('parent_id');
        $objFormElement->setLabel("LBL_IMPORT_TO");
        $objFormElement->addMultiOption( Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::SERVER_DEVICES ), 	Catalog_Model_CatalogData::SERVER_DEVICES );
        $objFormElement->addMultiOption( Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::SWITCH_DEVICES ), 	Catalog_Model_CatalogData::SWITCH_DEVICES );
        $objFormElement->addMultiOption( Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::ORCA_DEVICES ), 	Catalog_Model_CatalogData::ORCA_DEVICES );
        $objFormElement->addMultiOption( Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::CABLES_DEVICES ), 	Catalog_Model_CatalogData::CABLES_DEVICES );
        $objFormElement->addMultiOption( Catalog_Model_CatalogData::getParentId( Catalog_Model_CatalogData::CARDS_DEVICES ), 	Catalog_Model_CatalogData::CARDS_DEVICES );
        $objForm->addElement($objFormElement);

        $objFormElement = new Zend_Form_Element_File('uploadFile');
        $objFormElement->setLabel("LBL_IMPORT_FILE");
        $objForm->addElement($objFormElement);

        if ($this->getRequest()->isPost()){
            if ($objForm->isValid($this->getRequest()->getParams())){
                $arrData = $objForm->getValues();
                $objCatalog = new Bf_Catalog($this->_options);
                $objImporter = new Qstat_Importer();
                $objImporter->importXls($objForm->uploadFile->getFileName(), $objCatalog, $arrData['parent_id']);
            }
        }

        $this->view->objForm = $objForm;

        $arrButtons[] = array('module' => 'catalog', 'controller' => 'import', "action" => "import", "name" => "LBL_BUTTON_CATALOG_IMPORT", 'onClick' => '$("#importFile").submit();');
        $arrButtons[] = array('module' => 'catalog', 'controller' => 'import', "action" => "export-headers", 'onClick' => 'exportHeaders();', "name" => "LBL_BUTTON_CATALOG_EXPORT_HEADER");

        $this->view->arrActions = $arrButtons;
    }

    public function exportHeadersAction ()
    {
        $arrColumnNames = array();
        $arrColumnNames[] = 'Title';

        $objCatalog = new Bf_Catalog($this->_options);
        $objAttrRows = $objCatalog->getObjEav()->getAllAttribute();
        foreach ($objAttrRows as $objAttrRow) {
            $arrColumnNames[] = $objAttrRow->{Bf_Eav_Db_Attributes::COL_ATTR_CODE};
        }
		$arrColumnNames[] = "Groups";

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=file.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo implode(',', $arrColumnNames) . PHP_EOL;

        exit();
    }
}
