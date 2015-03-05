<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	protected function _initHeader() {
		$this->bootstrap ( 'FrontController' );
		$front = $this->getResource ( 'FrontController' );
		$response = new Zend_Controller_Response_Http ();
		$front->setResponse ( $response );
	}

	protected function _initConfig() {
		Zend_Registry::set("config",$this->getOptions());
	}

	public function _initScripts() {

		$this->bootstrap ( 'view' );
		$view = $this->view;

		$view->headMeta ()->appendHttpEquiv ( 'Content-Language', 'en-US' );
		$view->jQuery () -> setVersion("1.6.3");
		$view->jQuery () -> setUiVersion("1.8.16");
		$view->jQuery ()->uiEnable ();


		$strBaseUrl = $view->baseUrl ();
		$strJsUrl = $strBaseUrl . '/js/';
		$strCssUrl = $strBaseUrl . '/css/';

		defined('URL_BASE') || define('URL_BASE',$strBaseUrl);
		defined('URL_JS') 	|| define('URL_JS',$strJsUrl);
		defined('URL_CSS') 	|| define('URL_CSS',$strCssUrl);

		// CSS
		$view->headLink ()->appendStylesheet ( $strCssUrl . 'styles.css' );
		$view->headLink ()->appendStylesheet ( $strCssUrl . 'menu.css' );
		$view->headLink ()->appendStylesheet ( $strCssUrl . 'menu_ie.css','screen','IE' );
		$view->headLink ()->appendStylesheet ( $strCssUrl . 'styles_ie6.css', 'screen', 'IE 6' );

		$view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
		$view->addHelperPath ("Ingot/JQuery/JqGrid/View/Helper/JqGrid", "Ingot_JQuery_JqGrid_View_Helper_JqGrid" );

		$view->jQuery()->setLocalPath("/jquery/js/jquery-1.6.2.min.js")
		->setUiLocalPath("/jquery/js/jquery-ui-1.8.16.custom.min.js")
		->addStyleSheet("/jquery/css/redmond/jquery-ui-1.8.16.custom.css");

		$view->headScript()->appendFile("/jquery/plugins/jqGrid/js/i18n/grid.locale-en.js", 'text/javascript', array());
		$view->headScript()->appendFile("/jquery/plugins/jqGrid/js/jquery.jqGrid.min.js", 'text/javascript', array());
		$view->headLink()->prependStylesheet("/jquery/plugins/jqGrid/css/ui.jqgrid.css");

		//old version of jqgrid
//		$view->headLink ()->appendStylesheet ( $strCssUrl . 'smoothness/jquery-ui-1.8.2.custom.css' );
//
//		$view->headLink()->appendStylesheet($strCssUrl . 'ui.jqgrid.css');
		$view->headLink()->appendStylesheet($strCssUrl . 'jquery.jgrowl.css');
		$view->headLink()->appendStylesheet($strCssUrl . 'jquery.qtip.min.css');
//
//		$view->headScript ()->appendFile ( $strJsUrl . 'i18n/grid.locale-en.js', 'text/javascript' );
//		$view->headScript ()->appendFile ( $strJsUrl . 'jquery.jqGrid.min.js', 'text/javascript' );
		$view->headScript ()->appendFile ( $strJsUrl . 'jquery.jgrowl.js', 'text/javascript' );

	}

	protected function _initNav() {
		$this->bootstrap ( 'view' );
		$view = $this->view;
// 		$view->getHelper ( 'navigation' );
		$view->getHelper ( 'menu' );
		$view->getHelper ( 'breadcrumbs' );
	}

	protected function _initDatabase() {
		$this->bootstrap ( 'multidb' );
//		Bf_SystemLabels::initLables ();
	}
}


