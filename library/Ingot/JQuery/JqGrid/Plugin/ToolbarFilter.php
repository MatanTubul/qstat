<?php
/**
 * @see Ingot_JQuery_JqGrid_Plugin_Abstract
 */
require_once 'Ingot/JQuery/JqGrid/Plugin/Abstract.php';

/**
 * Display a search filter on each column
 *
 * @package Ingot_JQuery_JqGrid
 * @copyright Copyright (c) 2005-2009 Warrant Group Ltd. (http://www.warrant-group.com)
 * @author Andy Roberts
 */
class Ingot_JQuery_JqGrid_Plugin_ToolbarFilter extends Ingot_JQuery_JqGrid_Plugin_Abstract
{
    protected $_options;

    public function __construct ($options = array())
    {
        $this->setOptions($options);
    }

    public function preRender ()
    {
        if (! isset($this->_options['stringResult'])) {
            $this->_options['stringResult'] = true;
        }
        $js = sprintf('%s("#%s").filterToolbar(%s);', ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(), $this->getGrid()->getId(), $this->encodeJsonOptions($this->_options));
        $this->addOnLoad($js);
        $columns = $this->getGrid()->getColumns();
        foreach ($columns as $column) {
            if (! $column->isSetOption("search")) {
                $column->setOption('search', true);
            }
        }

        if ($this->getOption('triggerReload')) {
            $js = sprintf(' setTimeout(function(){ %s("#%s")[0].triggerToolbar();}, 2000);', ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(), $this->getGrid()->getId());

            $this->addOnLoad($js);
        }

    }

    public function postRender ()
    { // Not implemented
    }

    public function preResponse ()
    { // Not implemented
    }

    public function postResponse ()
    { // Not implemented
    }

    public function getMethods ()
    {
        return array();
    }

    public function getEvents ()
    {
        return array("beforeSearch", "afterSearch", "beforeClear", "afterClear");
    }
}