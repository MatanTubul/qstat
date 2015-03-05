<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
       $strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'index'), null, true);
  	   $session = new Zend_Session_Namespace("user");
  	   		if (!empty($session->userDetails)){
	                switch ($session->userDetails->{User_Model_Db_Users::COL_DEFAULT_SCREEN_COLUMNS}){
	                	case "switch":
	                	    $strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'switch'), null, true);
	                	break;
	                	case "servers":
	                		$strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'index'), null, true);
	                	break;
	                	case "orca":
	                		$strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'orca'), null, true);
	                	break;
	                	case "group":
	                		default:
	                			$arrExtraDetails = unserialize($session->userDetails->extra);

	                			switch (Qstat_Db_Table_Groups::getDefaultScreen($arrExtraDetails['groups'])){
				                	case "switch":
				                	    $strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'switch'), null, true);
				                	break;
				                	case "orca":
				                	    $strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'orca'), null, true);
				                	break;
				                	case "servers":
				                		default:
				                		$strUrl = $this->view->url(array('module' => 'catalog', 'controller' => 'index', 'action' => 'index'), null, true);
	                			}
	                	}
                	}
		$this->_redirect($strUrl);
    }

}

