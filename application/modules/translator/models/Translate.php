<?php

/**
 * User Model Class
 * 
 * @author shurf
 */
class Translator_Model_Translate
{

    public static function initTranslate ()
    {
        
        $objTable = new Translator_Model_Db_Translation();
        
        $objRowSet = $objTable->fetchAll();
        
        $arrLables = array();
        
        foreach ($objRowSet as $objRow) {
            $arrLables[$objRow->{Translator_Model_Db_Translation::COL_ID_SYSTEM}] = $objRow->{Translator_Model_Db_Translation::COL_CONTENT};
        }
        
        $objConfig = new Zend_Config($arrLables);
        
        $translate = new Zend_Translate(array('adapter' => 'array', 'content' => $objConfig->toArray(), 'locale' => 'en'));
        
        // Create a log instance
        $writer = new Translator_Model_Log($objTable->getAdapter(), Translator_Model_Db_Translation::TBL_NAME);
        
        $log = new Zend_Log($writer);
        
        // Attach it to the translation instance
        $translate->setOptions(array('log' => $log, 'logMessage' => "%message%", 'logUntranslated' => true));
        
        return $translate;
    
    }

}