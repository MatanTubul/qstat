<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start();
?>
<?php include_once "phprptinc/ewrcfg5.php"; ?>
<?php include_once "phprptinc/ewmysql.php"; ?>
<?php include_once "phprptinc/ewrfn5.php"; ?>
<?php include_once "phprptinc/ewrusrfn.php"; ?>
<?php

// Global variable for table object
$Server_Incriment_per_month = NULL;

//
// Table class for Server Incriment per month
//
class crServer_Incriment_per_month {
	var $TableVar = 'Server_Incriment_per_month';
	var $TableName = 'Server Incriment per month';
	var $TableType = 'REPORT';
	var $ShowCurrentFilter = EWRPT_SHOW_CURRENT_FILTER;
	var $FilterPanelOption = EWRPT_FILTER_PANEL_OPTION;
	var $CurrentOrder; // Current order
	var $CurrentOrderType; // Current order type

	// Table caption
	function TableCaption() {
		global $ReportLanguage;
		return $ReportLanguage->TablePhrase($this->TableVar, "TblCaption");
	}

	// Session Group Per Page
	function getGroupPerPage() {
		return @$_SESSION[EWRPT_PROJECT_VAR . "_" . $this->TableVar . "_grpperpage"];
	}

	function setGroupPerPage($v) {
		@$_SESSION[EWRPT_PROJECT_VAR . "_" . $this->TableVar . "_grpperpage"] = $v;
	}

	// Session Start Group
	function getStartGroup() {
		return @$_SESSION[EWRPT_PROJECT_VAR . "_" . $this->TableVar . "_start"];
	}

	function setStartGroup($v) {
		@$_SESSION[EWRPT_PROJECT_VAR . "_" . $this->TableVar . "_start"] = $v;
	}

	// Session Order By
	function getOrderBy() {
		return @$_SESSION[EWRPT_PROJECT_VAR . "_" . $this->TableVar . "_orderby"];
	}

	function setOrderBy($v) {
		@$_SESSION[EWRPT_PROJECT_VAR . "_" . $this->TableVar . "_orderby"] = $v;
	}

//	var $SelectLimit = TRUE;
	var $Incrimet_Per_Month;
	var $Total_Server_Per_Month;
	var $DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729;
	var $create_month;
	var $create_year;
	var $cumulative_sum;
	var $new_servers;
	var $_40running_total_3A3D_0;
	var $fields = array();
	var $Export; // Export
	var $ExportAll = TRUE;
	var $UseTokenInUrl = EWRPT_USE_TOKEN_IN_URL;
	var $RowType; // Row type
	var $RowTotalType; // Row total type
	var $RowTotalSubType; // Row total subtype
	var $RowGroupLevel; // Row group level
	var $RowAttrs = array(); // Row attributes

	// Reset CSS styles for table object
	function ResetCSS() {
    	$this->RowAttrs["style"] = "";
		$this->RowAttrs["class"] = "";
		foreach ($this->fields as $fld) {
			$fld->ResetCSS();
		}
	}

	//
	// Table class constructor
	//
	function crServer_Incriment_per_month() {
		global $ReportLanguage;

		// DATE_FORMAT(t.created_on,'%Y %m')
		$this->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729 = new crField('Server_Incriment_per_month', 'Server Incriment per month', 'x_DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729', 'DATE_FORMAT(t.created_on,\'%Y %m\')', '`DATE_FORMAT(t.created_on,\'%Y %m\')`', 200, EWRPT_DATATYPE_STRING, -1);
		$this->fields['DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729'] =& $this->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729;
		$this->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->DateFilter = "";
		$this->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->SqlSelect = "";
		$this->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->SqlOrderBy = "";

		// create_month
		$this->create_month = new crField('Server_Incriment_per_month', 'Server Incriment per month', 'x_create_month', 'create_month', '`create_month`', 200, EWRPT_DATATYPE_STRING, -1);
		$this->create_month->FldDefaultErrMsg = $ReportLanguage->Phrase("IncorrectInteger");
		$this->fields['create_month'] =& $this->create_month;
		$this->create_month->DateFilter = "";
		$this->create_month->SqlSelect = "";
		$this->create_month->SqlOrderBy = "";

		// create_year
		$this->create_year = new crField('Server_Incriment_per_month', 'Server Incriment per month', 'x_create_year', 'create_year', '`create_year`', 3, EWRPT_DATATYPE_NUMBER, -1);
		$this->create_year->FldDefaultErrMsg = $ReportLanguage->Phrase("IncorrectInteger");
		$this->fields['create_year'] =& $this->create_year;
		$this->create_year->DateFilter = "";
		$this->create_year->SqlSelect = "";
		$this->create_year->SqlOrderBy = "";

		// cumulative_sum
		$this->cumulative_sum = new crField('Server_Incriment_per_month', 'Server Incriment per month', 'x_cumulative_sum', 'cumulative_sum', '`cumulative_sum`', 20, EWRPT_DATATYPE_NUMBER, -1);
		$this->cumulative_sum->FldDefaultErrMsg = $ReportLanguage->Phrase("IncorrectInteger");
		$this->fields['cumulative_sum'] =& $this->cumulative_sum;
		$this->cumulative_sum->DateFilter = "";
		$this->cumulative_sum->SqlSelect = "";
		$this->cumulative_sum->SqlOrderBy = "";

		// new_servers
		$this->new_servers = new crField('Server_Incriment_per_month', 'Server Incriment per month', 'x_new_servers', 'new_servers', '`new_servers`', 20, EWRPT_DATATYPE_NUMBER, -1);
		$this->new_servers->FldDefaultErrMsg = $ReportLanguage->Phrase("IncorrectInteger");
		$this->fields['new_servers'] =& $this->new_servers;
		$this->new_servers->DateFilter = "";
		$this->new_servers->SqlSelect = "";
		$this->new_servers->SqlOrderBy = "";

		// @running_total := 0
		$this->_40running_total_3A3D_0 = new crField('Server_Incriment_per_month', 'Server Incriment per month', 'x__40running_total_3A3D_0', '@running_total := 0', '`@running_total := 0`', 3, EWRPT_DATATYPE_NUMBER, -1);
		$this->_40running_total_3A3D_0->FldDefaultErrMsg = $ReportLanguage->Phrase("IncorrectInteger");
		$this->fields['_40running_total_3A3D_0'] =& $this->_40running_total_3A3D_0;
		$this->_40running_total_3A3D_0->DateFilter = "";
		$this->_40running_total_3A3D_0->SqlSelect = "";
		$this->_40running_total_3A3D_0->SqlOrderBy = "";

		// Incrimet Per Month
		$this->Incrimet_Per_Month = new crChart('Server_Incriment_per_month', 'Server Incriment per month', 'Incrimet_Per_Month', 'Incrimet Per Month', 'DATE_FORMAT(t.created_on,\'%Y %m\')', 'new_servers', '', 5, 'SUM', 550, 440);
		$this->Incrimet_Per_Month->SqlSelect = "SELECT `DATE_FORMAT(t.created_on,'%Y %m')`, '', SUM(`new_servers`) FROM ";
		$this->Incrimet_Per_Month->SqlGroupBy = "`DATE_FORMAT(t.created_on,'%Y %m')`";
		$this->Incrimet_Per_Month->SqlOrderBy = "";
		$this->Incrimet_Per_Month->SeriesDateType = "";

		// Total Server Per Month
		$this->Total_Server_Per_Month = new crChart('Server_Incriment_per_month', 'Server Incriment per month', 'Total_Server_Per_Month', 'Total Server Per Month', 'DATE_FORMAT(t.created_on,\'%Y %m\')', 'cumulative_sum', '', 5, 'SUM', 550, 440);
		$this->Total_Server_Per_Month->SqlSelect = "SELECT `DATE_FORMAT(t.created_on,'%Y %m')`, '', SUM(`cumulative_sum`) FROM ";
		$this->Total_Server_Per_Month->SqlGroupBy = "`DATE_FORMAT(t.created_on,'%Y %m')`";
		$this->Total_Server_Per_Month->SqlOrderBy = "";
		$this->Total_Server_Per_Month->SeriesDateType = "";
	}

	// Single column sort
	function UpdateSort(&$ofld) {
		if ($this->CurrentOrder == $ofld->FldName) {
			$sLastSort = $ofld->getSort();
			if ($this->CurrentOrderType == "ASC" || $this->CurrentOrderType == "DESC") {
				$sThisSort = $this->CurrentOrderType;
			} else {
				$sThisSort = ($sLastSort == "ASC") ? "DESC" : "ASC";
			}
			$ofld->setSort($sThisSort);
		} else {
			if ($ofld->GroupingFieldId == 0) $ofld->setSort("");
		}
	}

	// Get Sort SQL
	function SortSql() {
		$sDtlSortSql = "";
		$argrps = array();
		foreach ($this->fields as $fld) {
			if ($fld->getSort() <> "") {
				if ($fld->GroupingFieldId > 0) {
					if ($fld->FldGroupSql <> "")
						$argrps[$fld->GroupingFieldId] = str_replace("%s", $fld->FldExpression, $fld->FldGroupSql) . " " . $fld->getSort();
					else
						$argrps[$fld->GroupingFieldId] = $fld->FldExpression . " " . $fld->getSort();
				} else {
					if ($sDtlSortSql <> "") $sDtlSortSql .= ", ";
					$sDtlSortSql .= $fld->FldExpression . " " . $fld->getSort();
				}
			}
		}
		$sSortSql = "";
		foreach ($argrps as $grp) {
			if ($sSortSql <> "") $sSortSql .= ", ";
			$sSortSql .= $grp;
		}
		if ($sDtlSortSql <> "") {
			if ($sSortSql <> "") $sSortSql .= ",";
			$sSortSql .= $sDtlSortSql;
		}
		return $sSortSql;
	}

	// Table level SQL
	function SqlFrom() { // From
		return "( Select DATE_FORMAT(t.created_on,'%Y %m'), Monthname(t.created_on) As create_month, Year(t.created_on) As create_year, Count(1) As new_servers From catalog t Group By Year(t.created_on), Month(t.created_on) ) as subq JOIN (SELECT @running_total := 0) r";
	}

	function SqlSelect() { // Select
		return "SELECT *, (@running_total := @running_total + subq.new_servers) AS cumulative_sum FROM " . $this->SqlFrom();
	}

	function SqlWhere() { // Where
		return "";
	}

	function SqlGroupBy() { // Group By
		return "";
	}

	function SqlHaving() { // Having
		return "";
	}

	function SqlOrderBy() { // Order By
		return "";
	}

	// Table Level Group SQL
	function SqlFirstGroupField() {
		return "";
	}

	function SqlSelectGroup() {
		return "SELECT DISTINCT " . $this->SqlFirstGroupField() . " FROM " . $this->SqlFrom();
	}

	function SqlOrderByGroup() {
		return "";
	}

	function SqlSelectAgg() {
		return "SELECT * FROM " . $this->SqlFrom();
	}

	function SqlAggPfx() {
		return "";
	}

	function SqlAggSfx() {
		return "";
	}

	function SqlSelectCount() {
		return "SELECT COUNT(*) FROM " . $this->SqlFrom();
	}

	// Sort URL
	function SortUrl(&$fld) {
		return "";
	}

	// Row attributes
	function RowAttributes() {
		$sAtt = "";
		foreach ($this->RowAttrs as $k => $v) {
			if (trim($v) <> "")
				$sAtt .= " " . $k . "=\"" . trim($v) . "\"";
		}
		return $sAtt;
	}

	// Field object by fldvar
	function &fields($fldvar) {
		return $this->fields[$fldvar];
	}

	// Table level events
	// Row Rendering event
	function Row_Rendering() {

		// Enter your code here	
	}

	// Cell Rendered event
	function Cell_Rendered(&$Field, $CurrentValue, &$ViewValue, &$ViewAttrs, &$CellAttrs, &$HrefValue) {

		//$ViewValue = "xxx";
		//$ViewAttrs["style"] = "xxx";

	}

	// Row Rendered event
	function Row_Rendered() {

		// To view properties of field class, use:
		//var_dump($this-><FieldName>); 

	}

	// Load Filters event
	function Filters_Load() {

		// Enter your code here	
		// Example: Register/Unregister Custom Extended Filter
		//ewrpt_RegisterFilter($this-><Field>, 'StartsWithA', 'Starts With A', 'GetStartsWithAFilter');
		//ewrpt_UnregisterFilter($this-><Field>, 'StartsWithA');

	}

	// Page Filter Validated event
	function Page_FilterValidated() {

		// Example:
		//global $MyTable;
		//$MyTable->MyField1->SearchValue = "your search criteria"; // Search value

	}

	// Chart Rendering event
	function Chart_Rendering(&$chart) {

		// var_dump($chart);
	}

	// Chart Rendered event
	function Chart_Rendered($chart, &$chartxml) {

		// Example:	
		//$doc = $chart->XmlDoc; // Get the DOMDocument object
		// Enter your code to manipulate the DOMDocument object here
		//$chartxml = $doc->saveXML(); // Output the XML

	}

	// Email Sending event
	function Email_Sending(&$Email, &$Args) {

		//var_dump($Email); var_dump($Args); exit();
		return TRUE;
	}
}
?>
<?php ewrpt_Header(FALSE) ?>
<?php

// Create page object
$Server_Incriment_per_month_summary = new crServer_Incriment_per_month_summary();
$Page =& $Server_Incriment_per_month_summary;

// Page init
$Server_Incriment_per_month_summary->Page_Init();

// Page main
$Server_Incriment_per_month_summary->Page_Main();
?>
<?php include_once "phprptinc/header.php"; ?>
<?php if ($Server_Incriment_per_month->Export == "" || $Server_Incriment_per_month->Export == "print" || $Server_Incriment_per_month->Export == "email") { ?>
<script type="text/javascript">

// Create page object
var Server_Incriment_per_month_summary = new ewrpt_Page("Server_Incriment_per_month_summary");

// page properties
Server_Incriment_per_month_summary.PageID = "summary"; // page ID
Server_Incriment_per_month_summary.FormID = "fServer_Incriment_per_monthsummaryfilter"; // form ID
var EWRPT_PAGE_ID = Server_Incriment_per_month_summary.PageID;

// extend page with Chart_Rendering function
Server_Incriment_per_month_summary.Chart_Rendering =  
 function(chart, chartid) { // DO NOT CHANGE THIS LINE!

 	//alert(chartid);
 }

// extend page with Chart_Rendered function
Server_Incriment_per_month_summary.Chart_Rendered =  
 function(chart, chartid) { // DO NOT CHANGE THIS LINE!

 	//alert(chartid);
 }
</script>
<?php } ?>
<?php if ($Server_Incriment_per_month->Export == "") { ?>
<script type="text/javascript">

// extend page with ValidateForm function
Server_Incriment_per_month_summary.ValidateForm = function(fobj) {
	if (!this.ValidateRequired)
		return true; // ignore validation

	// Call Form Custom Validate event
	if (!this.Form_CustomValidate(fobj)) return false;
	return true;
}

// extend page with Form_CustomValidate function
Server_Incriment_per_month_summary.Form_CustomValidate =  
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }
<?php if (EWRPT_CLIENT_VALIDATE) { ?>
Server_Incriment_per_month_summary.ValidateRequired = true; // uses JavaScript validation
<?php } else { ?>
Server_Incriment_per_month_summary.ValidateRequired = false; // no JavaScript validation
<?php } ?>
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
//-->

</script>
<?php } ?>
<?php if ($Server_Incriment_per_month->Export == "" || $Server_Incriment_per_month->Export == "print" || $Server_Incriment_per_month->Export == "email") { ?>
<script src="<?php echo EWRPT_FUSIONCHARTS_JSCLASS_FILE; ?>" type="text/javascript"></script>
<script src="<?php echo EWRPT_FUSIONCHARTS_FREE_JSCLASS_FILE; ?>" type="text/javascript"></script>
<?php } ?>
<?php if ($Server_Incriment_per_month->Export == "") { ?>
<div id="ewrpt_PopupFilter"><div class="bd"></div></div>
<script src="phprptjs/ewrptpop.js" type="text/javascript"></script>
<script type="text/javascript">

// popup fields
</script>
<?php } ?>
<?php if ($Server_Incriment_per_month->Export == "" || $Server_Incriment_per_month->Export == "print" || $Server_Incriment_per_month->Export == "email") { ?>
<!-- Table Container (Begin) -->
<table id="ewContainer" cellspacing="0" cellpadding="0" border="0">
<!-- Top Container (Begin) -->
<tr><td colspan="3"><div id="ewTop" class="phpreportmaker">
<!-- top slot -->
<a name="top"></a>
<?php } ?>
<p class="phpreportmaker ewTitle"><?php echo $Server_Incriment_per_month->TableCaption() ?>
&nbsp;&nbsp;<?php $Server_Incriment_per_month_summary->ExportOptions->Render("body"); ?></p>
<?php $Server_Incriment_per_month_summary->ShowPageHeader(); ?>
<?php $Server_Incriment_per_month_summary->ShowMessage(); ?>
<br><br>
<?php if ($Server_Incriment_per_month->Export == "" || $Server_Incriment_per_month->Export == "print" || $Server_Incriment_per_month->Export == "email") { ?>
</div></td></tr>
<!-- Top Container (End) -->
<tr>
	<!-- Left Container (Begin) -->
	<td style="vertical-align: top;"><div id="ewLeft" class="phpreportmaker">
	<!-- Left slot -->
	</div></td>
	<!-- Left Container (End) -->
	<!-- Center Container - Report (Begin) -->
	<td style="vertical-align: top;" class="ewPadding"><div id="ewCenter" class="phpreportmaker">
	<!-- center slot -->
<?php } ?>
<!-- summary report starts -->
<?php if ($Server_Incriment_per_month->Export <> "pdf") { ?>
<div id="report_summary">
<?php if ($Server_Incriment_per_month->Export == "") { ?>
<?php
if ($Server_Incriment_per_month->FilterPanelOption == 2 || ($Server_Incriment_per_month->FilterPanelOption == 3 && $Server_Incriment_per_month_summary->FilterApplied) || $Server_Incriment_per_month_summary->Filter == "0=101") {
	$sButtonImage = "phprptimages/collapse.gif";
	$sDivDisplay = "";
} else {
	$sButtonImage = "phprptimages/expand.gif";
	$sDivDisplay = " style=\"display: none;\"";
}
?>
<a href="javascript:ewrpt_ToggleFilterPanel();" style="text-decoration: none;"><img id="ewrptToggleFilterImg" src="<?php echo $sButtonImage ?>" alt="" width="9" height="9" border="0"></a><span class="phpreportmaker">&nbsp;<?php echo $ReportLanguage->Phrase("Filters") ?></span><br><br>
<div id="ewrptExtFilterPanel"<?php echo $sDivDisplay ?>>
<!-- Search form (begin) -->
<form name="fServer_Incriment_per_monthsummaryfilter" id="fServer_Incriment_per_monthsummaryfilter" action="<?php echo ewrpt_CurrentPage() ?>" class="ewForm" onsubmit="return Server_Incriment_per_month_summary.ValidateForm(this);">
<table class="ewRptExtFilter">
	<tr id="r_create_month">
		<td><span class="phpreportmaker"><?php echo $Server_Incriment_per_month->create_month->FldCaption() ?></span></td>
		<td></td>
		<td colspan="4"><span class="ewRptSearchOpr">
		<select name="sv_create_month[]" id="sv_create_month[]" multiple<?php echo ($Server_Incriment_per_month_summary->ClearExtFilter == 'Server_Incriment_per_month_create_month') ? " class=\"ewInputCleared\"" : "" ?>>
		<option value="<?php echo EWRPT_ALL_VALUE; ?>"<?php if (ewrpt_MatchedFilterValue($Server_Incriment_per_month->create_month->DropDownValue, EWRPT_ALL_VALUE)) echo " selected=\"selected\""; ?>><?php echo $ReportLanguage->Phrase("SelectAll"); ?></option>
<?php

// Popup filter
$cntf = is_array($Server_Incriment_per_month->create_month->AdvancedFilters) ? count($Server_Incriment_per_month->create_month->AdvancedFilters) : 0;
$cntd = is_array($Server_Incriment_per_month->create_month->DropDownList) ? count($Server_Incriment_per_month->create_month->DropDownList) : 0;
$totcnt = $cntf + $cntd;
$wrkcnt = 0;
	if ($cntf > 0) {
		foreach ($Server_Incriment_per_month->create_month->AdvancedFilters as $filter) {
			if ($filter->Enabled) {
?>
		<option value="<?php echo $filter->ID ?>"<?php if (ewrpt_MatchedFilterValue($Server_Incriment_per_month->create_month->DropDownValue, $filter->ID)) echo " selected=\"selected\"" ?>><?php echo $filter->Name ?></option>
<?php
				$wrkcnt += 1;
			}
		}
	}
	for ($i = 0; $i < $cntd; $i++) {
?>
		<option value="<?php echo $Server_Incriment_per_month->create_month->DropDownList[$i] ?>"<?php if (ewrpt_MatchedFilterValue($Server_Incriment_per_month->create_month->DropDownValue, $Server_Incriment_per_month->create_month->DropDownList[$i])) echo " selected=\"selected\"" ?>><?php echo ewrpt_DropDownDisplayValue($Server_Incriment_per_month->create_month->DropDownList[$i], "", 0) ?></option>
<?php
		$wrkcnt += 1;
	}
?>
		</select>
		</span></td>
	</tr>
	<tr id="r_create_year">
		<td><span class="phpreportmaker"><?php echo $Server_Incriment_per_month->create_year->FldCaption() ?></span></td>
		<td></td>
		<td colspan="4"><span class="ewRptSearchOpr">
		<select name="sv_create_year[]" id="sv_create_year[]" multiple<?php echo ($Server_Incriment_per_month_summary->ClearExtFilter == 'Server_Incriment_per_month_create_year') ? " class=\"ewInputCleared\"" : "" ?>>
		<option value="<?php echo EWRPT_ALL_VALUE; ?>"<?php if (ewrpt_MatchedFilterValue($Server_Incriment_per_month->create_year->DropDownValue, EWRPT_ALL_VALUE)) echo " selected=\"selected\""; ?>><?php echo $ReportLanguage->Phrase("SelectAll"); ?></option>
<?php

// Popup filter
$cntf = is_array($Server_Incriment_per_month->create_year->AdvancedFilters) ? count($Server_Incriment_per_month->create_year->AdvancedFilters) : 0;
$cntd = is_array($Server_Incriment_per_month->create_year->DropDownList) ? count($Server_Incriment_per_month->create_year->DropDownList) : 0;
$totcnt = $cntf + $cntd;
$wrkcnt = 0;
	if ($cntf > 0) {
		foreach ($Server_Incriment_per_month->create_year->AdvancedFilters as $filter) {
			if ($filter->Enabled) {
?>
		<option value="<?php echo $filter->ID ?>"<?php if (ewrpt_MatchedFilterValue($Server_Incriment_per_month->create_year->DropDownValue, $filter->ID)) echo " selected=\"selected\"" ?>><?php echo $filter->Name ?></option>
<?php
				$wrkcnt += 1;
			}
		}
	}
	for ($i = 0; $i < $cntd; $i++) {
?>
		<option value="<?php echo $Server_Incriment_per_month->create_year->DropDownList[$i] ?>"<?php if (ewrpt_MatchedFilterValue($Server_Incriment_per_month->create_year->DropDownValue, $Server_Incriment_per_month->create_year->DropDownList[$i])) echo " selected=\"selected\"" ?>><?php echo ewrpt_DropDownDisplayValue($Server_Incriment_per_month->create_year->DropDownList[$i], "", 0) ?></option>
<?php
		$wrkcnt += 1;
	}
?>
		</select>
		</span></td>
	</tr>
</table>
<table class="ewRptExtFilter">
	<tr>
		<td><span class="phpreportmaker">
			<input type="Submit" name="Submit" id="Submit" value="<?php echo $ReportLanguage->Phrase("Search") ?>">&nbsp;
			<input type="Reset" name="Reset" id="Reset" value="<?php echo $ReportLanguage->Phrase("Reset") ?>">&nbsp;
		</span></td>
	</tr>
</table>
</form>
<!-- Search form (end) -->
</div>
<br>
<?php } ?>
<?php if ($Server_Incriment_per_month->ShowCurrentFilter) { ?>
<div id="ewrptFilterList">
<?php $Server_Incriment_per_month_summary->ShowFilterList() ?>
</div>
<br>
<?php } ?>
<table class="ewGrid" cellspacing="0"><tr>
	<td class="ewGridContent">
<?php } ?>
<?php if ($Server_Incriment_per_month->Export == "") { ?>
<div class="ewGridUpperPanel">
<form action="<?php echo ewrpt_CurrentPage() ?>" name="ewpagerform" id="ewpagerform" class="ewForm">
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="white-space: nowrap;">
<?php if (!isset($Pager)) $Pager = new crPrevNextPager($Server_Incriment_per_month_summary->StartGrp, $Server_Incriment_per_month_summary->DisplayGrps, $Server_Incriment_per_month_summary->TotalGrps) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<table border="0" cellspacing="0" cellpadding="0"><tr><td><span class="phpreportmaker"><?php echo $ReportLanguage->Phrase("Page") ?>&nbsp;</span></td>
<!--first page button-->
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<td><a href="<?php echo ewrpt_CurrentPage() ?>?start=<?php echo $Pager->FirstButton->Start ?>"><img src="phprptimages/first.gif" alt="<?php echo $ReportLanguage->Phrase("PagerFirst") ?>" width="16" height="16" border="0"></a></td>
	<?php } else { ?>
	<td><img src="phprptimages/firstdisab.gif" alt="<?php echo $ReportLanguage->Phrase("PagerFirst") ?>" width="16" height="16" border="0"></td>
	<?php } ?>
<!--previous page button-->
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<td><a href="<?php echo ewrpt_CurrentPage() ?>?start=<?php echo $Pager->PrevButton->Start ?>"><img src="phprptimages/prev.gif" alt="<?php echo $ReportLanguage->Phrase("PagerPrevious") ?>" width="16" height="16" border="0"></a></td>
	<?php } else { ?>
	<td><img src="phprptimages/prevdisab.gif" alt="<?php echo $ReportLanguage->Phrase("PagerPrevious") ?>" width="16" height="16" border="0"></td>
	<?php } ?>
<!--current page number-->
	<td><input type="text" name="pageno" id="pageno" value="<?php echo $Pager->CurrentPage ?>" size="4"></td>
<!--next page button-->
	<?php if ($Pager->NextButton->Enabled) { ?>
	<td><a href="<?php echo ewrpt_CurrentPage() ?>?start=<?php echo $Pager->NextButton->Start ?>"><img src="phprptimages/next.gif" alt="<?php echo $ReportLanguage->Phrase("PagerNext") ?>" width="16" height="16" border="0"></a></td>	
	<?php } else { ?>
	<td><img src="phprptimages/nextdisab.gif" alt="<?php echo $ReportLanguage->Phrase("PagerNext") ?>" width="16" height="16" border="0"></td>
	<?php } ?>
<!--last page button-->
	<?php if ($Pager->LastButton->Enabled) { ?>
	<td><a href="<?php echo ewrpt_CurrentPage() ?>?start=<?php echo $Pager->LastButton->Start ?>"><img src="phprptimages/last.gif" alt="<?php echo $ReportLanguage->Phrase("PagerLast") ?>" width="16" height="16" border="0"></a></td>	
	<?php } else { ?>
	<td><img src="phprptimages/lastdisab.gif" alt="<?php echo $ReportLanguage->Phrase("PagerLast") ?>" width="16" height="16" border="0"></td>
	<?php } ?>
	<td><span class="phpreportmaker">&nbsp;<?php echo $ReportLanguage->Phrase("of") ?> <?php echo $Pager->PageCount ?></span></td>
	</tr></table>
	</td>	
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td>
	<span class="phpreportmaker"><?php echo $ReportLanguage->Phrase("Record") ?> <?php echo $Pager->FromIndex ?> <?php echo $ReportLanguage->Phrase("To") ?> <?php echo $Pager->ToIndex ?> <?php echo $ReportLanguage->Phrase("Of") ?> <?php echo $Pager->RecordCount ?></span>
<?php } else { ?>
	<?php if ($Server_Incriment_per_month_summary->Filter == "0=101") { ?>
	<span class="phpreportmaker"><?php echo $ReportLanguage->Phrase("EnterSearchCriteria") ?></span>
	<?php } else { ?>
	<span class="phpreportmaker"><?php echo $ReportLanguage->Phrase("NoRecord") ?></span>
	<?php } ?>
<?php } ?>
		</td>
<?php if ($Server_Incriment_per_month_summary->TotalGrps > 0) { ?>
		<td style="white-space: nowrap;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right" style="vertical-align: top; white-space: nowrap;"><span class="phpreportmaker"><?php echo $ReportLanguage->Phrase("GroupsPerPage"); ?>&nbsp;
<select name="<?php echo EWRPT_TABLE_GROUP_PER_PAGE; ?>" onchange="this.form.submit();">
<option value="1"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 1) echo " selected=\"selected\"" ?>>1</option>
<option value="2"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 2) echo " selected=\"selected\"" ?>>2</option>
<option value="3"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 3) echo " selected=\"selected\"" ?>>3</option>
<option value="4"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 4) echo " selected=\"selected\"" ?>>4</option>
<option value="5"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 5) echo " selected=\"selected\"" ?>>5</option>
<option value="10"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 10) echo " selected=\"selected\"" ?>>10</option>
<option value="20"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 20) echo " selected=\"selected\"" ?>>20</option>
<option value="50"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 50) echo " selected=\"selected\"" ?>>50</option>
<option value="ALL"<?php if ($Server_Incriment_per_month->getGroupPerPage() == -1) echo " selected=\"selected\"" ?>><?php echo $ReportLanguage->Phrase("AllRecords") ?></option>
</select>
		</span></td>
<?php } ?>
	</tr>
</table>
</form>
</div>
<?php } ?>
<!-- Report Grid (Begin) -->
<?php if ($Server_Incriment_per_month->Export <> "pdf") { ?>
<div class="ewGridMiddlePanel">
<?php } ?>
<table class="<?php echo $Server_Incriment_per_month_summary->ReportTableClass ?>" cellspacing="0">
<?php

// Set the last group to display if not export all
if ($Server_Incriment_per_month->ExportAll && $Server_Incriment_per_month->Export <> "") {
	$Server_Incriment_per_month_summary->StopGrp = $Server_Incriment_per_month_summary->TotalGrps;
} else {
	$Server_Incriment_per_month_summary->StopGrp = $Server_Incriment_per_month_summary->StartGrp + $Server_Incriment_per_month_summary->DisplayGrps - 1;
}

// Stop group <= total number of groups
if (intval($Server_Incriment_per_month_summary->StopGrp) > intval($Server_Incriment_per_month_summary->TotalGrps))
	$Server_Incriment_per_month_summary->StopGrp = $Server_Incriment_per_month_summary->TotalGrps;
$Server_Incriment_per_month_summary->RecCount = 0;

// Get first row
if ($Server_Incriment_per_month_summary->TotalGrps > 0) {
	$Server_Incriment_per_month_summary->GetRow(1);
	$Server_Incriment_per_month_summary->GrpCount = 1;
}
while (($rs && !$rs->EOF && $Server_Incriment_per_month_summary->GrpCount <= $Server_Incriment_per_month_summary->DisplayGrps) || $Server_Incriment_per_month_summary->ShowFirstHeader) {

	// Show header
	if ($Server_Incriment_per_month_summary->ShowFirstHeader) {
?>
	<thead>
	<tr>
<td class="ewTableHeader">
<?php if ($Server_Incriment_per_month->Export <> "") { ?>
<?php echo $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->FldCaption() ?>
<?php } else { ?>
	<table cellspacing="0" class="ewTableHeaderBtn"><tr>
<?php if ($Server_Incriment_per_month->SortUrl($Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729) == "") { ?>
		<td style="vertical-align: bottom;"><?php echo $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->FldCaption() ?></td>
<?php } else { ?>
		<td class="ewPointer" onmousedown="ewrpt_Sort(event,'<?php echo $Server_Incriment_per_month->SortUrl($Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729) ?>',0);"><?php echo $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->FldCaption() ?></td><td style="width: 10px;">
		<?php if ($Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->getSort() == "ASC") { ?><img src="phprptimages/sortup.gif" width="10" height="9" border="0"><?php } elseif ($Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->getSort() == "DESC") { ?><img src="phprptimages/sortdown.gif" width="10" height="9" border="0"><?php } ?></td>
<?php } ?>
	</tr></table>
<?php } ?>
</td>
<td class="ewTableHeader">
<?php if ($Server_Incriment_per_month->Export <> "") { ?>
<?php echo $Server_Incriment_per_month->cumulative_sum->FldCaption() ?>
<?php } else { ?>
	<table cellspacing="0" class="ewTableHeaderBtn"><tr>
<?php if ($Server_Incriment_per_month->SortUrl($Server_Incriment_per_month->cumulative_sum) == "") { ?>
		<td style="vertical-align: bottom;"><?php echo $Server_Incriment_per_month->cumulative_sum->FldCaption() ?></td>
<?php } else { ?>
		<td class="ewPointer" onmousedown="ewrpt_Sort(event,'<?php echo $Server_Incriment_per_month->SortUrl($Server_Incriment_per_month->cumulative_sum) ?>',0);"><?php echo $Server_Incriment_per_month->cumulative_sum->FldCaption() ?></td><td style="width: 10px;">
		<?php if ($Server_Incriment_per_month->cumulative_sum->getSort() == "ASC") { ?><img src="phprptimages/sortup.gif" width="10" height="9" border="0"><?php } elseif ($Server_Incriment_per_month->cumulative_sum->getSort() == "DESC") { ?><img src="phprptimages/sortdown.gif" width="10" height="9" border="0"><?php } ?></td>
<?php } ?>
	</tr></table>
<?php } ?>
</td>
<td class="ewTableHeader">
<?php if ($Server_Incriment_per_month->Export <> "") { ?>
<?php echo $Server_Incriment_per_month->new_servers->FldCaption() ?>
<?php } else { ?>
	<table cellspacing="0" class="ewTableHeaderBtn"><tr>
<?php if ($Server_Incriment_per_month->SortUrl($Server_Incriment_per_month->new_servers) == "") { ?>
		<td style="vertical-align: bottom;"><?php echo $Server_Incriment_per_month->new_servers->FldCaption() ?></td>
<?php } else { ?>
		<td class="ewPointer" onmousedown="ewrpt_Sort(event,'<?php echo $Server_Incriment_per_month->SortUrl($Server_Incriment_per_month->new_servers) ?>',0);"><?php echo $Server_Incriment_per_month->new_servers->FldCaption() ?></td><td style="width: 10px;">
		<?php if ($Server_Incriment_per_month->new_servers->getSort() == "ASC") { ?><img src="phprptimages/sortup.gif" width="10" height="9" border="0"><?php } elseif ($Server_Incriment_per_month->new_servers->getSort() == "DESC") { ?><img src="phprptimages/sortdown.gif" width="10" height="9" border="0"><?php } ?></td>
<?php } ?>
	</tr></table>
<?php } ?>
</td>
	</tr>
	</thead>
	<tbody>
<?php
		$Server_Incriment_per_month_summary->ShowFirstHeader = FALSE;
	}
	$Server_Incriment_per_month_summary->RecCount++;

		// Render detail row
		$Server_Incriment_per_month->ResetCSS();
		$Server_Incriment_per_month->RowType = EWRPT_ROWTYPE_DETAIL;
		$Server_Incriment_per_month_summary->RenderRow();
?>
	<tr<?php echo $Server_Incriment_per_month->RowAttributes(); ?>>
		<td<?php echo $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->CellAttributes() ?>>
<span<?php echo $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->ViewAttributes(); ?>><?php echo $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->ListViewValue(); ?></span></td>
		<td<?php echo $Server_Incriment_per_month->cumulative_sum->CellAttributes() ?>>
<span<?php echo $Server_Incriment_per_month->cumulative_sum->ViewAttributes(); ?>><?php echo $Server_Incriment_per_month->cumulative_sum->ListViewValue(); ?></span></td>
		<td<?php echo $Server_Incriment_per_month->new_servers->CellAttributes() ?>>
<span<?php echo $Server_Incriment_per_month->new_servers->ViewAttributes(); ?>><?php echo $Server_Incriment_per_month->new_servers->ListViewValue(); ?></span></td>
	</tr>
<?php

		// Accumulate page summary
		$Server_Incriment_per_month_summary->AccumulateSummary();

		// Get next record
		$Server_Incriment_per_month_summary->GetRow(2);
	$Server_Incriment_per_month_summary->GrpCount++;
} // End while
?>
	</tbody>
	<tfoot>
<?php
if ($Server_Incriment_per_month_summary->TotalGrps > 0) {
	$Server_Incriment_per_month->ResetCSS();
	$Server_Incriment_per_month->RowType = EWRPT_ROWTYPE_TOTAL;
	$Server_Incriment_per_month->RowTotalType = EWRPT_ROWTOTAL_GRAND;
	$Server_Incriment_per_month->RowTotalSubType = EWRPT_ROWTOTAL_FOOTER;
	$Server_Incriment_per_month->RowAttrs["class"] = "ewRptGrandSummary";
	$Server_Incriment_per_month_summary->RenderRow();
?>
	<!-- tr><td colspan="3"><span class="phpreportmaker">&nbsp;<br></span></td></tr -->
	<tr<?php echo $Server_Incriment_per_month->RowAttributes(); ?>><td colspan="3"><?php echo $ReportLanguage->Phrase("RptGrandTotal") ?> (<?php echo ewrpt_FormatNumber($Server_Incriment_per_month_summary->TotCount,0,-2,-2,-2); ?><?php echo $ReportLanguage->Phrase("RptDtlRec") ?>)</td></tr>
<?php } ?>
	</tfoot>
</table>
<?php if ($Server_Incriment_per_month->Export <> "pdf") { ?>
</div>
<?php } ?>
<?php if ($Server_Incriment_per_month_summary->TotalGrps > 0) { ?>
<?php if ($Server_Incriment_per_month->Export == "") { ?>
<div class="ewGridLowerPanel">
<form action="<?php echo ewrpt_CurrentPage() ?>" name="ewpagerform" id="ewpagerform" class="ewForm">
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="white-space: nowrap;">
<?php if (!isset($Pager)) $Pager = new crPrevNextPager($Server_Incriment_per_month_summary->StartGrp, $Server_Incriment_per_month_summary->DisplayGrps, $Server_Incriment_per_month_summary->TotalGrps) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<table border="0" cellspacing="0" cellpadding="0"><tr><td><span class="phpreportmaker"><?php echo $ReportLanguage->Phrase("Page") ?>&nbsp;</span></td>
<!--first page button-->
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<td><a href="<?php echo ewrpt_CurrentPage() ?>?start=<?php echo $Pager->FirstButton->Start ?>"><img src="phprptimages/first.gif" alt="<?php echo $ReportLanguage->Phrase("PagerFirst") ?>" width="16" height="16" border="0"></a></td>
	<?php } else { ?>
	<td><img src="phprptimages/firstdisab.gif" alt="<?php echo $ReportLanguage->Phrase("PagerFirst") ?>" width="16" height="16" border="0"></td>
	<?php } ?>
<!--previous page button-->
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<td><a href="<?php echo ewrpt_CurrentPage() ?>?start=<?php echo $Pager->PrevButton->Start ?>"><img src="phprptimages/prev.gif" alt="<?php echo $ReportLanguage->Phrase("PagerPrevious") ?>" width="16" height="16" border="0"></a></td>
	<?php } else { ?>
	<td><img src="phprptimages/prevdisab.gif" alt="<?php echo $ReportLanguage->Phrase("PagerPrevious") ?>" width="16" height="16" border="0"></td>
	<?php } ?>
<!--current page number-->
	<td><input type="text" name="pageno" id="pageno" value="<?php echo $Pager->CurrentPage ?>" size="4"></td>
<!--next page button-->
	<?php if ($Pager->NextButton->Enabled) { ?>
	<td><a href="<?php echo ewrpt_CurrentPage() ?>?start=<?php echo $Pager->NextButton->Start ?>"><img src="phprptimages/next.gif" alt="<?php echo $ReportLanguage->Phrase("PagerNext") ?>" width="16" height="16" border="0"></a></td>	
	<?php } else { ?>
	<td><img src="phprptimages/nextdisab.gif" alt="<?php echo $ReportLanguage->Phrase("PagerNext") ?>" width="16" height="16" border="0"></td>
	<?php } ?>
<!--last page button-->
	<?php if ($Pager->LastButton->Enabled) { ?>
	<td><a href="<?php echo ewrpt_CurrentPage() ?>?start=<?php echo $Pager->LastButton->Start ?>"><img src="phprptimages/last.gif" alt="<?php echo $ReportLanguage->Phrase("PagerLast") ?>" width="16" height="16" border="0"></a></td>	
	<?php } else { ?>
	<td><img src="phprptimages/lastdisab.gif" alt="<?php echo $ReportLanguage->Phrase("PagerLast") ?>" width="16" height="16" border="0"></td>
	<?php } ?>
	<td><span class="phpreportmaker">&nbsp;<?php echo $ReportLanguage->Phrase("of") ?> <?php echo $Pager->PageCount ?></span></td>
	</tr></table>
	</td>	
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td>
	<span class="phpreportmaker"><?php echo $ReportLanguage->Phrase("Record") ?> <?php echo $Pager->FromIndex ?> <?php echo $ReportLanguage->Phrase("To") ?> <?php echo $Pager->ToIndex ?> <?php echo $ReportLanguage->Phrase("Of") ?> <?php echo $Pager->RecordCount ?></span>
<?php } else { ?>
	<?php if ($Server_Incriment_per_month_summary->Filter == "0=101") { ?>
	<span class="phpreportmaker"><?php echo $ReportLanguage->Phrase("EnterSearchCriteria") ?></span>
	<?php } else { ?>
	<span class="phpreportmaker"><?php echo $ReportLanguage->Phrase("NoRecord") ?></span>
	<?php } ?>
<?php } ?>
		</td>
<?php if ($Server_Incriment_per_month_summary->TotalGrps > 0) { ?>
		<td style="white-space: nowrap;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right" style="vertical-align: top; white-space: nowrap;"><span class="phpreportmaker"><?php echo $ReportLanguage->Phrase("GroupsPerPage"); ?>&nbsp;
<select name="<?php echo EWRPT_TABLE_GROUP_PER_PAGE; ?>" onchange="this.form.submit();">
<option value="1"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 1) echo " selected=\"selected\"" ?>>1</option>
<option value="2"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 2) echo " selected=\"selected\"" ?>>2</option>
<option value="3"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 3) echo " selected=\"selected\"" ?>>3</option>
<option value="4"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 4) echo " selected=\"selected\"" ?>>4</option>
<option value="5"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 5) echo " selected=\"selected\"" ?>>5</option>
<option value="10"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 10) echo " selected=\"selected\"" ?>>10</option>
<option value="20"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 20) echo " selected=\"selected\"" ?>>20</option>
<option value="50"<?php if ($Server_Incriment_per_month_summary->DisplayGrps == 50) echo " selected=\"selected\"" ?>>50</option>
<option value="ALL"<?php if ($Server_Incriment_per_month->getGroupPerPage() == -1) echo " selected=\"selected\"" ?>><?php echo $ReportLanguage->Phrase("AllRecords") ?></option>
</select>
		</span></td>
<?php } ?>
	</tr>
</table>
</form>
</div>
<?php } ?>
<?php } ?>
<?php if ($Server_Incriment_per_month->Export <> "pdf") { ?>
</td></tr></table>
</div>
<?php } ?>
<!-- Summary Report Ends -->
<?php if ($Server_Incriment_per_month->Export == "" || $Server_Incriment_per_month->Export == "print" || $Server_Incriment_per_month->Export == "email") { ?>
	</div><br></td>
	<!-- Center Container - Report (End) -->
	<!-- Right Container (Begin) -->
	<td style="vertical-align: top;"><div id="ewRight" class="phpreportmaker">
	<!-- Right slot -->
	</div></td>
	<!-- Right Container (End) -->
</tr>
<!-- Bottom Container (Begin) -->
<tr><td colspan="3" class="ewPadding"><div id="ewBottom" class="phpreportmaker">
	<!-- Bottom slot -->
<a name="cht_Incrimet_Per_Month"></a>
<div id="div_Server_Incriment_per_month_Incrimet_Per_Month"></div>
<?php

// Initialize chart data
$Server_Incriment_per_month->Incrimet_Per_Month->ID = "Server_Incriment_per_month_Incrimet_Per_Month"; // Chart ID
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("type", "5", FALSE); // Chart type
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("seriestype", "0", FALSE); // Chart series type
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("bgcolor", "FCFCFC", TRUE); // Background color
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("caption", $Server_Incriment_per_month->Incrimet_Per_Month->ChartCaption(), TRUE); // Chart caption
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("xaxisname", $Server_Incriment_per_month->Incrimet_Per_Month->ChartXAxisName(), TRUE); // X axis name
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("yaxisname", $Server_Incriment_per_month->Incrimet_Per_Month->ChartYAxisName(), TRUE); // Y axis name
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("shownames", "1", TRUE); // Show names
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("showvalues", "1", TRUE); // Show values
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("showhovercap", "0", TRUE); // Show hover
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("alpha", "50", FALSE); // Chart alpha
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("colorpalette", "#FF0000|#FF0080|#FF00FF|#8000FF|#FF8000|#FF3D3D|#7AFFFF|#0000FF|#FFFF00|#FF7A7A|#3DFFFF|#0080FF|#80FF00|#00FF00|#00FF80|#00FFFF", FALSE); // Chart color palette
?>
<?php
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("showLimits", "1", TRUE); // showLimits
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("showDivLineValues", "1", TRUE); // showDivLineValues
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("yAxisMinValue", "0", TRUE); // yAxisMinValue
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("yAxisMaxValue", "0", TRUE); // yAxisMaxValue
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("showAlternateVGridColor", "0", TRUE); // showAlternateVGridColor
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("isSliced", "1", TRUE); // isSliced
$Server_Incriment_per_month->Incrimet_Per_Month->SetChartParam("showAsBars", "0", TRUE); // showAsBars

// Define trend lines
?>
<?php
$SqlSelect = $Server_Incriment_per_month->SqlSelect();
$SqlChartSelect = $Server_Incriment_per_month->Incrimet_Per_Month->SqlSelect;
if (EWRPT_IS_MSSQL) // skip SqlOrderBy for MSSQL
	$sSqlChartBase = "(" . ewrpt_BuildReportSql($SqlSelect, $Server_Incriment_per_month->SqlWhere(), $Server_Incriment_per_month->SqlGroupBy(), $Server_Incriment_per_month->SqlHaving(), "", $Server_Incriment_per_month_summary->Filter, "") . ") EW_TMP_TABLE";
else
	$sSqlChartBase = "(" . ewrpt_BuildReportSql($SqlSelect, $Server_Incriment_per_month->SqlWhere(), $Server_Incriment_per_month->SqlGroupBy(), $Server_Incriment_per_month->SqlHaving(), $Server_Incriment_per_month->SqlOrderBy(), $Server_Incriment_per_month_summary->Filter, "") . ") EW_TMP_TABLE";

// Load chart data from sql directly
$sSql = $SqlChartSelect . $sSqlChartBase;
$sSql = ewrpt_BuildReportSql($sSql, "", $Server_Incriment_per_month->Incrimet_Per_Month->SqlGroupBy, "", $Server_Incriment_per_month->Incrimet_Per_Month->SqlOrderBy, "", "");
if (EWRPT_DEBUG_ENABLED) echo "(Chart SQL): " . $sSql . "<br>";
ewrpt_LoadChartData($sSql, $Server_Incriment_per_month->Incrimet_Per_Month);
ewrpt_SortChartData($Server_Incriment_per_month->Incrimet_Per_Month->Data, 0, "");

// Call Chart_Rendering event
$Server_Incriment_per_month->Chart_Rendering($Server_Incriment_per_month->Incrimet_Per_Month);
$chartxml = $Server_Incriment_per_month->Incrimet_Per_Month->ChartXml();

// Call Chart_Rendered event
$Server_Incriment_per_month->Chart_Rendered($Server_Incriment_per_month->Incrimet_Per_Month, $chartxml);
echo $Server_Incriment_per_month->Incrimet_Per_Month->ShowChartFC($chartxml, TRUE, FALSE);
?>
<?php if ($Server_Incriment_per_month->Export <> "email") { ?>
<a href="#top"><?php echo $ReportLanguage->Phrase("Top") ?></a>
<?php } ?>
<br><br>
<a name="cht_Total_Server_Per_Month"></a>
<div id="div_Server_Incriment_per_month_Total_Server_Per_Month"></div>
<?php

// Initialize chart data
$Server_Incriment_per_month->Total_Server_Per_Month->ID = "Server_Incriment_per_month_Total_Server_Per_Month"; // Chart ID
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("type", "5", FALSE); // Chart type
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("seriestype", "0", FALSE); // Chart series type
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("bgcolor", "FCFCFC", TRUE); // Background color
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("caption", $Server_Incriment_per_month->Total_Server_Per_Month->ChartCaption(), TRUE); // Chart caption
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("xaxisname", $Server_Incriment_per_month->Total_Server_Per_Month->ChartXAxisName(), TRUE); // X axis name
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("yaxisname", $Server_Incriment_per_month->Total_Server_Per_Month->ChartYAxisName(), TRUE); // Y axis name
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("shownames", "1", TRUE); // Show names
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("showvalues", "1", TRUE); // Show values
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("showhovercap", "0", TRUE); // Show hover
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("alpha", "50", FALSE); // Chart alpha
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("colorpalette", "#FF0000|#FF0080|#FF00FF|#8000FF|#FF8000|#FF3D3D|#7AFFFF|#0000FF|#FFFF00|#FF7A7A|#3DFFFF|#0080FF|#80FF00|#00FF00|#00FF80|#00FFFF", FALSE); // Chart color palette
?>
<?php
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("showLimits", "1", TRUE); // showLimits
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("showDivLineValues", "1", TRUE); // showDivLineValues
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("yAxisMinValue", "0", TRUE); // yAxisMinValue
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("yAxisMaxValue", "0", TRUE); // yAxisMaxValue
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("showAlternateVGridColor", "0", TRUE); // showAlternateVGridColor
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("isSliced", "1", TRUE); // isSliced
$Server_Incriment_per_month->Total_Server_Per_Month->SetChartParam("showAsBars", "0", TRUE); // showAsBars

// Define trend lines
?>
<?php
$SqlSelect = $Server_Incriment_per_month->SqlSelect();
$SqlChartSelect = $Server_Incriment_per_month->Total_Server_Per_Month->SqlSelect;
if (EWRPT_IS_MSSQL) // skip SqlOrderBy for MSSQL
	$sSqlChartBase = "(" . ewrpt_BuildReportSql($SqlSelect, $Server_Incriment_per_month->SqlWhere(), $Server_Incriment_per_month->SqlGroupBy(), $Server_Incriment_per_month->SqlHaving(), "", $Server_Incriment_per_month_summary->Filter, "") . ") EW_TMP_TABLE";
else
	$sSqlChartBase = "(" . ewrpt_BuildReportSql($SqlSelect, $Server_Incriment_per_month->SqlWhere(), $Server_Incriment_per_month->SqlGroupBy(), $Server_Incriment_per_month->SqlHaving(), $Server_Incriment_per_month->SqlOrderBy(), $Server_Incriment_per_month_summary->Filter, "") . ") EW_TMP_TABLE";

// Load chart data from sql directly
$sSql = $SqlChartSelect . $sSqlChartBase;
$sSql = ewrpt_BuildReportSql($sSql, "", $Server_Incriment_per_month->Total_Server_Per_Month->SqlGroupBy, "", $Server_Incriment_per_month->Total_Server_Per_Month->SqlOrderBy, "", "");
if (EWRPT_DEBUG_ENABLED) echo "(Chart SQL): " . $sSql . "<br>";
ewrpt_LoadChartData($sSql, $Server_Incriment_per_month->Total_Server_Per_Month);
ewrpt_SortChartData($Server_Incriment_per_month->Total_Server_Per_Month->Data, 0, "");

// Call Chart_Rendering event
$Server_Incriment_per_month->Chart_Rendering($Server_Incriment_per_month->Total_Server_Per_Month);
$chartxml = $Server_Incriment_per_month->Total_Server_Per_Month->ChartXml();

// Call Chart_Rendered event
$Server_Incriment_per_month->Chart_Rendered($Server_Incriment_per_month->Total_Server_Per_Month, $chartxml);
echo $Server_Incriment_per_month->Total_Server_Per_Month->ShowChartFC($chartxml, TRUE, FALSE);
?>
<?php if ($Server_Incriment_per_month->Export <> "email") { ?>
<a href="#top"><?php echo $ReportLanguage->Phrase("Top") ?></a>
<?php } ?>
<br><br>
	</div><br></td></tr>
<!-- Bottom Container (End) -->
</table>
<!-- Table Container (End) -->
<?php } ?>
<?php $Server_Incriment_per_month_summary->ShowPageFooter(); ?>
<?php if (EWRPT_DEBUG_ENABLED) echo ewrpt_DebugMsg(); ?>
<?php

// Close recordsets
if ($rsgrp) $rsgrp->Close();
if ($rs) $rs->Close();
?>
<?php if ($Server_Incriment_per_month->Export == "") { ?>
<script language="JavaScript" type="text/javascript">
<!--

// Write your table-specific startup script here
// document.write("page loaded");
//-->

</script>
<?php } ?>
<?php include_once "phprptinc/footer.php"; ?>
<?php
$Server_Incriment_per_month_summary->Page_Terminate();
?>
<?php

//
// Page class
//
class crServer_Incriment_per_month_summary {

	// Page ID
	var $PageID = 'summary';

	// Table name
	var $TableName = 'Server Incriment per month';

	// Page object name
	var $PageObjName = 'Server_Incriment_per_month_summary';

	// Page name
	function PageName() {
		return ewrpt_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ewrpt_CurrentPage() . "?";
		global $Server_Incriment_per_month;
		if ($Server_Incriment_per_month->UseTokenInUrl) $PageUrl .= "t=" . $Server_Incriment_per_month->TableVar . "&"; // Add page token
		return $PageUrl;
	}

	// Export URLs
	var $ExportPrintUrl;
	var $ExportExcelUrl;
	var $ExportWordUrl;
	var $ExportPdfUrl;
	var $ReportTableClass;

	// Message
	function getMessage() {
		return @$_SESSION[EWRPT_SESSION_MESSAGE];
	}

	function setMessage($v) {
		if (@$_SESSION[EWRPT_SESSION_MESSAGE] <> "") { // Append
			$_SESSION[EWRPT_SESSION_MESSAGE] .= "<br>" . $v;
		} else {
			$_SESSION[EWRPT_SESSION_MESSAGE] = $v;
		}
	}

	// Show message
	function ShowMessage() {
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage);
		if ($sMessage <> "") { // Message in Session, display
			echo "<p><span class=\"ewMessage\">" . $sMessage . "</span></p>";
			$_SESSION[EWRPT_SESSION_MESSAGE] = ""; // Clear message in Session
		}
	}
	var $PageHeader;
	var $PageFooter;

	// Show Page Header
	function ShowPageHeader() {
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		if ($sHeader <> "") { // Header exists, display
			echo "<p><span class=\"phpreportmaker\">" . $sHeader . "</span></p>";
		}
	}

	// Show Page Footer
	function ShowPageFooter() {
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		if ($sFooter <> "") { // Fotoer exists, display
			echo "<p><span class=\"phpreportmaker\">" . $sFooter . "</span></p>";
		}
	}

	// Validate page request
	function IsPageRequest() {
		global $Server_Incriment_per_month;
		if ($Server_Incriment_per_month->UseTokenInUrl) {
			if (ewrpt_IsHttpPost())
				return ($Server_Incriment_per_month->TableVar == @$_POST("t"));
			if (@$_GET["t"] <> "")
				return ($Server_Incriment_per_month->TableVar == @$_GET["t"]);
		} else {
			return TRUE;
		}
	}

	//
	// Page class constructor
	//
	function crServer_Incriment_per_month_summary() {
		global $conn, $ReportLanguage;

		// Language object
		$ReportLanguage = new crLanguage();

		// Table object (Server_Incriment_per_month)
		$GLOBALS["Server_Incriment_per_month"] = new crServer_Incriment_per_month();
		$GLOBALS["Table"] =& $GLOBALS["Server_Incriment_per_month"];

		// Initialize URLs
		$this->ExportPrintUrl = $this->PageUrl() . "export=print";
		$this->ExportExcelUrl = $this->PageUrl() . "export=excel";
		$this->ExportWordUrl = $this->PageUrl() . "export=word";
		$this->ExportPdfUrl = $this->PageUrl() . "export=pdf";

		// Page ID
		if (!defined("EWRPT_PAGE_ID"))
			define("EWRPT_PAGE_ID", 'summary', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EWRPT_TABLE_NAME"))
			define("EWRPT_TABLE_NAME", 'Server Incriment per month', TRUE);

		// Start timer
		$GLOBALS["gsTimer"] = new crTimer();

		// Open connection
		$conn = ewrpt_Connect();

		// Export options
		$this->ExportOptions = new crListOptions();
		$this->ExportOptions->Tag = "span";
		$this->ExportOptions->Separator = "&nbsp;&nbsp;";
	}

	// 
	//  Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsExportFile, $ReportLanguage, $Security;
		global $Server_Incriment_per_month;

		// Get export parameters
		if (@$_GET["export"] <> "") {
			$Server_Incriment_per_month->Export = $_GET["export"];
		}
		$gsExport = $Server_Incriment_per_month->Export; // Get export parameter, used in header
		$gsExportFile = $Server_Incriment_per_month->TableVar; // Get export file, used in header
		if ($Server_Incriment_per_month->Export == "excel") {
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename=' . $gsExportFile .'.xls');
		}
		if ($Server_Incriment_per_month->Export == "word") {
			header('Content-Type: application/vnd.ms-word');
			header('Content-Disposition: attachment; filename=' . $gsExportFile .'.doc');
		}

		// Setup export options
		$this->SetupExportOptions();

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();
	}

	// Set up export options
	function SetupExportOptions() {
		global $ReportLanguage, $Server_Incriment_per_month;

		// Printer friendly
		$item =& $this->ExportOptions->Add("print");
		$item->Body = "<a href=\"" . $this->ExportPrintUrl . "\">" . "<img src=\"phprptimages/print.gif\" alt=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("PrinterFriendly")) . "\" title=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("PrinterFriendly")) . "\" width=\"16\" height=\"16\" border=\"0\">" . "</a>";
		$item->Visible = TRUE;

		// Export to Excel
		$item =& $this->ExportOptions->Add("excel");
		$item->Body = "<a href=\"" . $this->ExportExcelUrl . "\">" . "<img src=\"phprptimages/exportxls.gif\" alt=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("ExportToExcel")) . "\" title=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("ExportToExcel")) . "\" width=\"16\" height=\"16\" border=\"0\">" . "</a>";
		$item->Visible = TRUE;

		// Export to Word
		$item =& $this->ExportOptions->Add("word");
		$item->Body = "<a href=\"" . $this->ExportWordUrl . "\">" . "<img src=\"phprptimages/exportdoc.gif\" alt=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("ExportToWord")) . "\" title=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("ExportToWord")) . "\" width=\"16\" height=\"16\" border=\"0\">" . "</a>";
		$item->Visible = TRUE;

		// Export to Pdf
		$item =& $this->ExportOptions->Add("pdf");
		$item->Body = "<a href=\"" . $this->ExportPdfUrl . "\">" . "<img src=\"phprptimages/exportpdf.gif\" alt=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("ExportToPDF")) . "\" title=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("ExportToPDF")) . "\" width=\"16\" height=\"16\" border=\"0\">" . "</a>";
		$item->Visible = FALSE;

		// Uncomment codes below to show export to Pdf link
//		$item->Visible = TRUE;
		// Export to Email

		$item =& $this->ExportOptions->Add("email");
		$item->Body = "<a name=\"emf_Server_Incriment_per_month\" id=\"emf_Server_Incriment_per_month\" href=\"javascript:void(0);\" onclick=\"ewrpt_EmailDialogShow({lnk:'emf_Server_Incriment_per_month',hdr:ewLanguage.Phrase('ExportToEmail')});\">" . "<img src=\"phprptimages/exportemail.gif\" alt=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("ExportToEmail")) . "\" title=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("ExportToEmail")) . "\" width=\"16\" height=\"16\" border=\"0\">" . "</a>";
		$item->Visible = TRUE;

		// Reset filter
		$item =& $this->ExportOptions->Add("resetfilter");
		$item->Body = "<a href=\"" . ewrpt_CurrentPage() . "?cmd=reset\">" . "<img src=\"phprptimages/resetfilter.gif\" alt=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("ResetAllFilter")) . "\" title=\"" . ewrpt_HtmlEncode($ReportLanguage->Phrase("ResetAllFilter")) . "\" width=\"16\" height=\"16\" border=\"0\">" . "</a>";
		$item->Visible = TRUE;
		$this->SetupExportOptionsExt();

		// Hide options for export
		if ($Server_Incriment_per_month->Export <> "")
			$this->ExportOptions->HideAllOptions();

		// Set up table class
		if ($Server_Incriment_per_month->Export == "word" || $Server_Incriment_per_month->Export == "excel" || $Server_Incriment_per_month->Export == "pdf")
			$this->ReportTableClass = "ewTable";
		else
			$this->ReportTableClass = "ewTable ewTableSeparate";
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $conn;
		global $ReportLanguage;
		global $Server_Incriment_per_month;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();

		// Export to Email (use ob_file_contents for PHP)
		if ($Server_Incriment_per_month->Export == "email") {
			$sContent = ob_get_contents();
			$this->ExportEmail($sContent);
			ob_end_clean();

			 // Close connection
			$conn->Close();
			header("Location: " . ewrpt_CurrentPage());
			exit();
		}

		// Export to PDF (use ob_file_contents for PHP)
		if ($Server_Incriment_per_month->Export == "pdf") {
			$sContent = ob_get_contents();
			$this->ExportPDF($sContent);
			ob_end_clean();

			 // Close connection
			$conn->Close();
		}

		 // Close connection
		$conn->Close();

		// Go to URL if specified
		if ($url <> "") {
			if (!EWRPT_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			header("Location: " . $url);
		}
		exit();
	}

	// Initialize common variables
	var $ExportOptions; // Export options

	// Paging variables
	var $RecCount = 0; // Record count
	var $StartGrp = 0; // Start group
	var $StopGrp = 0; // Stop group
	var $TotalGrps = 0; // Total groups
	var $GrpCount = 0; // Group count
	var $DisplayGrps = 20; // Groups per page
	var $GrpRange = 10;
	var $Sort = "";
	var $Filter = "";
	var $UserIDFilter = "";

	// Clear field for ext filter
	var $ClearExtFilter = "";
	var $FilterApplied;
	var $ShowFirstHeader;
	var $Cnt, $Col, $Val, $Smry, $Mn, $Mx, $GrandSmry, $GrandMn, $GrandMx;
	var $TotCount;

	//
	// Page main
	//
	function Page_Main() {
		global $Server_Incriment_per_month;
		global $rs;
		global $rsgrp;
		global $gsFormError;

		// Aggregate variables
		// 1st dimension = no of groups (level 0 used for grand total)
		// 2nd dimension = no of fields

		$nDtls = 4;
		$nGrps = 1;
		$this->Val =& ewrpt_InitArray($nDtls, 0);
		$this->Cnt =& ewrpt_Init2DArray($nGrps, $nDtls, 0);
		$this->Smry =& ewrpt_Init2DArray($nGrps, $nDtls, 0);
		$this->Mn =& ewrpt_Init2DArray($nGrps, $nDtls, NULL);
		$this->Mx =& ewrpt_Init2DArray($nGrps, $nDtls, NULL);
		$this->GrandSmry =& ewrpt_InitArray($nDtls, 0);
		$this->GrandMn =& ewrpt_InitArray($nDtls, NULL);
		$this->GrandMx =& ewrpt_InitArray($nDtls, NULL);

		// Set up if accumulation required
		$this->Col = array(FALSE, FALSE, FALSE, FALSE);

		// Set up groups per page dynamically
		$this->SetUpDisplayGrps();

		// Load default filter values
		$this->LoadDefaultFilters();

		// Load custom filters
		$Server_Incriment_per_month->Filters_Load();

		// Set up popup filter
		$this->SetupPopup();

		// Extended filter
		$sExtendedFilter = "";

		// Get dropdown values
		$this->GetExtendedFilterValues();

		// Build extended filter
		$sExtendedFilter = $this->GetExtendedFilter();
		if ($sExtendedFilter <> "") {
			if ($this->Filter <> "")
  				$this->Filter = "($this->Filter) AND ($sExtendedFilter)";
			else
				$this->Filter = $sExtendedFilter;
		}

		// Build popup filter
		$sPopupFilter = $this->GetPopupFilter();

		//ewrpt_SetDebugMsg("popup filter: " . $sPopupFilter);
		if ($sPopupFilter <> "") {
			if ($this->Filter <> "")
				$this->Filter = "($this->Filter) AND ($sPopupFilter)";
			else
				$this->Filter = $sPopupFilter;
		}

		// Check if filter applied
		$this->FilterApplied = $this->CheckFilter();
		$this->ExportOptions->GetItem("resetfilter")->Visible = $this->FilterApplied;

		// Get sort
		$this->Sort = $this->GetSort();

		// Get total count
		$sSql = ewrpt_BuildReportSql($Server_Incriment_per_month->SqlSelect(), $Server_Incriment_per_month->SqlWhere(), $Server_Incriment_per_month->SqlGroupBy(), $Server_Incriment_per_month->SqlHaving(), $Server_Incriment_per_month->SqlOrderBy(), $this->Filter, $this->Sort);
		$this->TotalGrps = $this->GetCnt($sSql);
		if ($this->DisplayGrps <= 0) // Display all groups
			$this->DisplayGrps = $this->TotalGrps;
		$this->StartGrp = 1;

		// Show header
		$this->ShowFirstHeader = ($this->TotalGrps > 0);

		//$this->ShowFirstHeader = TRUE; // Uncomment to always show header
		// Set up start position if not export all

		if ($Server_Incriment_per_month->ExportAll && $Server_Incriment_per_month->Export <> "")
		    $this->DisplayGrps = $this->TotalGrps;
		else
			$this->SetUpStartGroup(); 

		// Hide all options if export
		if ($Server_Incriment_per_month->Export <> "") {
			$this->ExportOptions->HideAllOptions();
		}

		// Get current page records
		$rs = $this->GetRs($sSql, $this->StartGrp, $this->DisplayGrps);
	}

	// Accummulate summary
	function AccumulateSummary() {
		$cntx = count($this->Smry);
		for ($ix = 0; $ix < $cntx; $ix++) {
			$cnty = count($this->Smry[$ix]);
			for ($iy = 1; $iy < $cnty; $iy++) {
				$this->Cnt[$ix][$iy]++;
				if ($this->Col[$iy]) {
					$valwrk = $this->Val[$iy];
					if (is_null($valwrk) || !is_numeric($valwrk)) {

						// skip
					} else {
						$this->Smry[$ix][$iy] += $valwrk;
						if (is_null($this->Mn[$ix][$iy])) {
							$this->Mn[$ix][$iy] = $valwrk;
							$this->Mx[$ix][$iy] = $valwrk;
						} else {
							if ($this->Mn[$ix][$iy] > $valwrk) $this->Mn[$ix][$iy] = $valwrk;
							if ($this->Mx[$ix][$iy] < $valwrk) $this->Mx[$ix][$iy] = $valwrk;
						}
					}
				}
			}
		}
		$cntx = count($this->Smry);
		for ($ix = 1; $ix < $cntx; $ix++) {
			$this->Cnt[$ix][0]++;
		}
	}

	// Reset level summary
	function ResetLevelSummary($lvl) {

		// Clear summary values
		$cntx = count($this->Smry);
		for ($ix = $lvl; $ix < $cntx; $ix++) {
			$cnty = count($this->Smry[$ix]);
			for ($iy = 1; $iy < $cnty; $iy++) {
				$this->Cnt[$ix][$iy] = 0;
				if ($this->Col[$iy]) {
					$this->Smry[$ix][$iy] = 0;
					$this->Mn[$ix][$iy] = NULL;
					$this->Mx[$ix][$iy] = NULL;
				}
			}
		}
		$cntx = count($this->Smry);
		for ($ix = $lvl; $ix < $cntx; $ix++) {
			$this->Cnt[$ix][0] = 0;
		}

		// Reset record count
		$this->RecCount = 0;
	}

	// Accummulate grand summary
	function AccumulateGrandSummary() {
		$this->Cnt[0][0]++;
		$cntgs = count($this->GrandSmry);
		for ($iy = 1; $iy < $cntgs; $iy++) {
			if ($this->Col[$iy]) {
				$valwrk = $this->Val[$iy];
				if (is_null($valwrk) || !is_numeric($valwrk)) {

					// skip
				} else {
					$this->GrandSmry[$iy] += $valwrk;
					if (is_null($this->GrandMn[$iy])) {
						$this->GrandMn[$iy] = $valwrk;
						$this->GrandMx[$iy] = $valwrk;
					} else {
						if ($this->GrandMn[$iy] > $valwrk) $this->GrandMn[$iy] = $valwrk;
						if ($this->GrandMx[$iy] < $valwrk) $this->GrandMx[$iy] = $valwrk;
					}
				}
			}
		}
	}

	// Get count
	function GetCnt($sql) {
		global $conn;
		$rscnt = $conn->Execute($sql);
		$cnt = ($rscnt) ? $rscnt->RecordCount() : 0;
		if ($rscnt) $rscnt->Close();
		return $cnt;
	}

	// Get rs
	function GetRs($sql, $start, $grps) {
		global $conn;
		$wrksql = $sql;
		if ($start > 0 && $grps > -1)
			$wrksql .= " LIMIT " . ($start-1) . ", " . ($grps);
		$rswrk = $conn->Execute($wrksql);
		return $rswrk;
	}

	// Get row values
	function GetRow($opt) {
		global $rs;
		global $Server_Incriment_per_month;
		if (!$rs)
			return;
		if ($opt == 1) { // Get first row

	//		$rs->MoveFirst(); // NOTE: no need to move position
		} else { // Get next row
			$rs->MoveNext();
		}
		if (!$rs->EOF) {
			$Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->setDbValue($rs->fields('DATE_FORMAT(t.created_on,\'%Y %m\')'));
			$Server_Incriment_per_month->create_month->setDbValue($rs->fields('create_month'));
			$Server_Incriment_per_month->create_year->setDbValue($rs->fields('create_year'));
			$Server_Incriment_per_month->cumulative_sum->setDbValue($rs->fields('cumulative_sum'));
			$Server_Incriment_per_month->new_servers->setDbValue($rs->fields('new_servers'));
			$Server_Incriment_per_month->_40running_total_3A3D_0->setDbValue($rs->fields('@running_total := 0'));
			$this->Val[1] = $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->CurrentValue;
			$this->Val[2] = $Server_Incriment_per_month->cumulative_sum->CurrentValue;
			$this->Val[3] = $Server_Incriment_per_month->new_servers->CurrentValue;
		} else {
			$Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->setDbValue("");
			$Server_Incriment_per_month->create_month->setDbValue("");
			$Server_Incriment_per_month->create_year->setDbValue("");
			$Server_Incriment_per_month->cumulative_sum->setDbValue("");
			$Server_Incriment_per_month->new_servers->setDbValue("");
			$Server_Incriment_per_month->_40running_total_3A3D_0->setDbValue("");
		}
	}

	//  Set up starting group
	function SetUpStartGroup() {
		global $Server_Incriment_per_month;

		// Exit if no groups
		if ($this->DisplayGrps == 0)
			return;

		// Check for a 'start' parameter
		if (@$_GET[EWRPT_TABLE_START_GROUP] != "") {
			$this->StartGrp = $_GET[EWRPT_TABLE_START_GROUP];
			$Server_Incriment_per_month->setStartGroup($this->StartGrp);
		} elseif (@$_GET["pageno"] != "") {
			$nPageNo = $_GET["pageno"];
			if (is_numeric($nPageNo)) {
				$this->StartGrp = ($nPageNo-1)*$this->DisplayGrps+1;
				if ($this->StartGrp <= 0) {
					$this->StartGrp = 1;
				} elseif ($this->StartGrp >= intval(($this->TotalGrps-1)/$this->DisplayGrps)*$this->DisplayGrps+1) {
					$this->StartGrp = intval(($this->TotalGrps-1)/$this->DisplayGrps)*$this->DisplayGrps+1;
				}
				$Server_Incriment_per_month->setStartGroup($this->StartGrp);
			} else {
				$this->StartGrp = $Server_Incriment_per_month->getStartGroup();
			}
		} else {
			$this->StartGrp = $Server_Incriment_per_month->getStartGroup();
		}

		// Check if correct start group counter
		if (!is_numeric($this->StartGrp) || $this->StartGrp == "") { // Avoid invalid start group counter
			$this->StartGrp = 1; // Reset start group counter
			$Server_Incriment_per_month->setStartGroup($this->StartGrp);
		} elseif (intval($this->StartGrp) > intval($this->TotalGrps)) { // Avoid starting group > total groups
			$this->StartGrp = intval(($this->TotalGrps-1)/$this->DisplayGrps) * $this->DisplayGrps + 1; // Point to last page first group
			$Server_Incriment_per_month->setStartGroup($this->StartGrp);
		} elseif (($this->StartGrp-1) % $this->DisplayGrps <> 0) {
			$this->StartGrp = intval(($this->StartGrp-1)/$this->DisplayGrps) * $this->DisplayGrps + 1; // Point to page boundary
			$Server_Incriment_per_month->setStartGroup($this->StartGrp);
		}
	}

	// Set up popup
	function SetupPopup() {
		global $conn, $ReportLanguage;
		global $Server_Incriment_per_month;

		// Initialize popup
		// Process post back form

		if (ewrpt_IsHttpPost()) {
			$sName = @$_POST["popup"]; // Get popup form name
			if ($sName <> "") {
				$cntValues = (is_array(@$_POST["sel_$sName"])) ? count($_POST["sel_$sName"]) : 0;
				if ($cntValues > 0) {
					$arValues = ewrpt_StripSlashes($_POST["sel_$sName"]);
					if (trim($arValues[0]) == "") // Select all
						$arValues = EWRPT_INIT_VALUE;
					if (!ewrpt_MatchedArray($arValues, $_SESSION["sel_$sName"])) {
						if ($this->HasSessionFilterValues($sName))
							$this->ClearExtFilter = $sName; // Clear extended filter for this field
					}
					$_SESSION["sel_$sName"] = $arValues;
					$_SESSION["rf_$sName"] = ewrpt_StripSlashes(@$_POST["rf_$sName"]);
					$_SESSION["rt_$sName"] = ewrpt_StripSlashes(@$_POST["rt_$sName"]);
					$this->ResetPager();
				}
			}

		// Get 'reset' command
		} elseif (@$_GET["cmd"] <> "") {
			$sCmd = $_GET["cmd"];
			if (strtolower($sCmd) == "reset") {
				$this->ResetPager();
			}
		}

		// Load selection criteria to array
	}

	// Reset pager
	function ResetPager() {

		// Reset start position (reset command)
		global $Server_Incriment_per_month;
		$this->StartGrp = 1;
		$Server_Incriment_per_month->setStartGroup($this->StartGrp);
	}

	// Set up number of groups displayed per page
	function SetUpDisplayGrps() {
		global $Server_Incriment_per_month;
		$sWrk = @$_GET[EWRPT_TABLE_GROUP_PER_PAGE];
		if ($sWrk <> "") {
			if (is_numeric($sWrk)) {
				$this->DisplayGrps = intval($sWrk);
			} else {
				if (strtoupper($sWrk) == "ALL") { // display all groups
					$this->DisplayGrps = -1;
				} else {
					$this->DisplayGrps = 20; // Non-numeric, load default
				}
			}
			$Server_Incriment_per_month->setGroupPerPage($this->DisplayGrps); // Save to session

			// Reset start position (reset command)
			$this->StartGrp = 1;
			$Server_Incriment_per_month->setStartGroup($this->StartGrp);
		} else {
			if ($Server_Incriment_per_month->getGroupPerPage() <> "") {
				$this->DisplayGrps = $Server_Incriment_per_month->getGroupPerPage(); // Restore from session
			} else {
				$this->DisplayGrps = 20; // Load default
			}
		}
	}

	function RenderRow() {
		global $conn, $rs, $Security;
		global $Server_Incriment_per_month;
		if ($Server_Incriment_per_month->RowTotalType == EWRPT_ROWTOTAL_GRAND) { // Grand total

			// Get total count from sql directly
			$sSql = ewrpt_BuildReportSql($Server_Incriment_per_month->SqlSelectCount(), $Server_Incriment_per_month->SqlWhere(), $Server_Incriment_per_month->SqlGroupBy(), $Server_Incriment_per_month->SqlHaving(), "", $this->Filter, "");
			$rstot = $conn->Execute($sSql);
			if ($rstot) {
				$this->TotCount = ($rstot->RecordCount()>1) ? $rstot->RecordCount() : $rstot->fields[0];
				$rstot->Close();
			} else {
				$this->TotCount = 0;
			}
		}

		// Call Row_Rendering event
		$Server_Incriment_per_month->Row_Rendering();

		//
		// Render view codes
		//

		if ($Server_Incriment_per_month->RowType == EWRPT_ROWTYPE_TOTAL) { // Summary row
		} else {

			// DATE_FORMAT(t.created_on,'%Y %m')
			$Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->ViewValue = $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->CurrentValue;
			$Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// cumulative_sum
			$Server_Incriment_per_month->cumulative_sum->ViewValue = $Server_Incriment_per_month->cumulative_sum->CurrentValue;
			$Server_Incriment_per_month->cumulative_sum->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// new_servers
			$Server_Incriment_per_month->new_servers->ViewValue = $Server_Incriment_per_month->new_servers->CurrentValue;
			$Server_Incriment_per_month->new_servers->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// DATE_FORMAT(t.created_on,'%Y %m')
			$Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->HrefValue = "";

			// cumulative_sum
			$Server_Incriment_per_month->cumulative_sum->HrefValue = "";

			// new_servers
			$Server_Incriment_per_month->new_servers->HrefValue = "";
		}

		// Call Cell_Rendered event
		if ($Server_Incriment_per_month->RowType == EWRPT_ROWTYPE_TOTAL) { // Summary row
		} else {

			// DATE_FORMAT(t.created_on,'%Y %m')
			$CurrentValue = $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->CurrentValue;
			$ViewValue =& $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->ViewValue;
			$ViewAttrs =& $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->ViewAttrs;
			$CellAttrs =& $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->CellAttrs;
			$HrefValue =& $Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->HrefValue;
			$Server_Incriment_per_month->Cell_Rendered($Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue);

			// cumulative_sum
			$CurrentValue = $Server_Incriment_per_month->cumulative_sum->CurrentValue;
			$ViewValue =& $Server_Incriment_per_month->cumulative_sum->ViewValue;
			$ViewAttrs =& $Server_Incriment_per_month->cumulative_sum->ViewAttrs;
			$CellAttrs =& $Server_Incriment_per_month->cumulative_sum->CellAttrs;
			$HrefValue =& $Server_Incriment_per_month->cumulative_sum->HrefValue;
			$Server_Incriment_per_month->Cell_Rendered($Server_Incriment_per_month->cumulative_sum, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue);

			// new_servers
			$CurrentValue = $Server_Incriment_per_month->new_servers->CurrentValue;
			$ViewValue =& $Server_Incriment_per_month->new_servers->ViewValue;
			$ViewAttrs =& $Server_Incriment_per_month->new_servers->ViewAttrs;
			$CellAttrs =& $Server_Incriment_per_month->new_servers->CellAttrs;
			$HrefValue =& $Server_Incriment_per_month->new_servers->HrefValue;
			$Server_Incriment_per_month->Cell_Rendered($Server_Incriment_per_month->new_servers, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue);
		}

		// Call Row_Rendered event
		$Server_Incriment_per_month->Row_Rendered();
	}

	function SetupExportOptionsExt() {
		global $ReportLanguage, $Server_Incriment_per_month;
		$item =& $this->ExportOptions->GetItem("pdf");
		$item->Visible = TRUE;
	}

	// Get extended filter values
	function GetExtendedFilterValues() {
		global $Server_Incriment_per_month;

		// Field create_month
		$sSelect = "SELECT DISTINCT `create_month` FROM " . $Server_Incriment_per_month->SqlFrom();
		$sOrderBy = "`create_month` ASC";
		$wrkSql = ewrpt_BuildReportSql($sSelect, $Server_Incriment_per_month->SqlWhere(), "", "", $sOrderBy, $this->UserIDFilter, "");
		$Server_Incriment_per_month->create_month->DropDownList = ewrpt_GetDistinctValues("", $wrkSql);

		// Field create_year
		$sSelect = "SELECT DISTINCT `create_year` FROM " . $Server_Incriment_per_month->SqlFrom();
		$sOrderBy = "`create_year` ASC";
		$wrkSql = ewrpt_BuildReportSql($sSelect, $Server_Incriment_per_month->SqlWhere(), "", "", $sOrderBy, $this->UserIDFilter, "");
		$Server_Incriment_per_month->create_year->DropDownList = ewrpt_GetDistinctValues("", $wrkSql);
	}

	// Return extended filter
	function GetExtendedFilter() {
		global $Server_Incriment_per_month;
		global $gsFormError;
		$sFilter = "";
		$bPostBack = ewrpt_IsHttpPost();
		$bRestoreSession = TRUE;
		$bSetupFilter = FALSE;

		// Reset extended filter if filter changed
		if ($bPostBack) {

		// Reset search command
		} elseif (@$_GET["cmd"] == "reset") {

			// Load default values
			// Field create_month

			$this->SetSessionDropDownValue($Server_Incriment_per_month->create_month->DropDownValue, 'create_month');

			// Field create_year
			$this->SetSessionDropDownValue($Server_Incriment_per_month->create_year->DropDownValue, 'create_year');
			$bSetupFilter = TRUE;
		} else {

			// Field create_month
			if ($this->GetDropDownValue($Server_Incriment_per_month->create_month->DropDownValue, 'create_month')) {
				$bSetupFilter = TRUE;
				$bRestoreSession = FALSE;
			} elseif ($Server_Incriment_per_month->create_month->DropDownValue <> EWRPT_INIT_VALUE && !isset($_SESSION['sv_Server_Incriment_per_month->create_month'])) {
				$bSetupFilter = TRUE;
			}

			// Field create_year
			if ($this->GetDropDownValue($Server_Incriment_per_month->create_year->DropDownValue, 'create_year')) {
				$bSetupFilter = TRUE;
				$bRestoreSession = FALSE;
			} elseif ($Server_Incriment_per_month->create_year->DropDownValue <> EWRPT_INIT_VALUE && !isset($_SESSION['sv_Server_Incriment_per_month->create_year'])) {
				$bSetupFilter = TRUE;
			}
			if (!$this->ValidateForm()) {
				$this->setMessage($gsFormError);
				return $sFilter;
			}
		}

		// Restore session
		if ($bRestoreSession) {

			// Field create_month
			$this->GetSessionDropDownValue($Server_Incriment_per_month->create_month);

			// Field create_year
			$this->GetSessionDropDownValue($Server_Incriment_per_month->create_year);
		}

		// Call page filter validated event
		$Server_Incriment_per_month->Page_FilterValidated();

		// Build SQL
		// Field create_month

		ewrpt_BuildDropDownFilter($Server_Incriment_per_month->create_month, $sFilter, "");

		// Field create_year
		ewrpt_BuildDropDownFilter($Server_Incriment_per_month->create_year, $sFilter, "");

		// Save parms to session
		// Field create_month

		$this->SetSessionDropDownValue($Server_Incriment_per_month->create_month->DropDownValue, 'create_month');

		// Field create_year
		$this->SetSessionDropDownValue($Server_Incriment_per_month->create_year->DropDownValue, 'create_year');

		// Setup filter
		if ($bSetupFilter) {
		}
		return $sFilter;
	}

	// Get drop down value from querystring
	function GetDropDownValue(&$sv, $parm) {
		if (ewrpt_IsHttpPost())
			return FALSE; // Skip post back
		if (isset($_GET["sv_$parm"])) {
			$sv = ewrpt_StripSlashes($_GET["sv_$parm"]);
			return TRUE;
		}
		return FALSE;
	}

	// Get filter values from querystring
	function GetFilterValues(&$fld) {
		$parm = substr($fld->FldVar, 2);
		if (ewrpt_IsHttpPost())
			return; // Skip post back
		$got = FALSE;
		if (isset($_GET["sv1_$parm"])) {
			$fld->SearchValue = ewrpt_StripSlashes($_GET["sv1_$parm"]);
			$got = TRUE;
		}
		if (isset($_GET["so1_$parm"])) {
			$fld->SearchOperator = ewrpt_StripSlashes($_GET["so1_$parm"]);
			$got = TRUE;
		}
		if (isset($_GET["sc_$parm"])) {
			$fld->SearchCondition = ewrpt_StripSlashes($_GET["sc_$parm"]);
			$got = TRUE;
		}
		if (isset($_GET["sv2_$parm"])) {
			$fld->SearchValue2 = ewrpt_StripSlashes($_GET["sv2_$parm"]);
			$got = TRUE;
		}
		if (isset($_GET["so2_$parm"])) {
			$fld->SearchOperator2 = ewrpt_StripSlashes($_GET["so2_$parm"]);
			$got = TRUE;
		}
		return $got;
	}

	// Set default ext filter
	function SetDefaultExtFilter(&$fld, $so1, $sv1, $sc, $so2, $sv2) {
		$fld->DefaultSearchValue = $sv1; // Default ext filter value 1
		$fld->DefaultSearchValue2 = $sv2; // Default ext filter value 2 (if operator 2 is enabled)
		$fld->DefaultSearchOperator = $so1; // Default search operator 1
		$fld->DefaultSearchOperator2 = $so2; // Default search operator 2 (if operator 2 is enabled)
		$fld->DefaultSearchCondition = $sc; // Default search condition (if operator 2 is enabled)
	}

	// Apply default ext filter
	function ApplyDefaultExtFilter(&$fld) {
		$fld->SearchValue = $fld->DefaultSearchValue;
		$fld->SearchValue2 = $fld->DefaultSearchValue2;
		$fld->SearchOperator = $fld->DefaultSearchOperator;
		$fld->SearchOperator2 = $fld->DefaultSearchOperator2;
		$fld->SearchCondition = $fld->DefaultSearchCondition;
	}

	// Check if Text Filter applied
	function TextFilterApplied(&$fld) {
		return (strval($fld->SearchValue) <> strval($fld->DefaultSearchValue) ||
			strval($fld->SearchValue2) <> strval($fld->DefaultSearchValue2) ||
			(strval($fld->SearchValue) <> "" &&
				strval($fld->SearchOperator) <> strval($fld->DefaultSearchOperator)) ||
			(strval($fld->SearchValue2) <> "" &&
				strval($fld->SearchOperator2) <> strval($fld->DefaultSearchOperator2)) ||
			strval($fld->SearchCondition) <> strval($fld->DefaultSearchCondition));
	}

	// Check if Non-Text Filter applied
	function NonTextFilterApplied(&$fld) {
		if (is_array($fld->DefaultDropDownValue) && is_array($fld->DropDownValue)) {
			if (count($fld->DefaultDropDownValue) <> count($fld->DropDownValue))
				return TRUE;
			else
				return (count(array_diff($fld->DefaultDropDownValue, $fld->DropDownValue)) <> 0);
		}
		else {
			$v1 = strval($fld->DefaultDropDownValue);
			if ($v1 == EWRPT_INIT_VALUE)
				$v1 = "";
			$v2 = strval($fld->DropDownValue);
			if ($v2 == EWRPT_INIT_VALUE || $v2 == EWRPT_ALL_VALUE)
				$v2 = "";
			return ($v1 <> $v2);
		}
	}

	// Get dropdown value from session
	function GetSessionDropDownValue(&$fld) {
		$parm = substr($fld->FldVar, 2);
		$this->GetSessionValue($fld->DropDownValue, 'sv_Server_Incriment_per_month_' . $parm);
	}

	// Get filter values from session
	function GetSessionFilterValues(&$fld) {
		$parm = substr($fld->FldVar, 2);
		$this->GetSessionValue($fld->SearchValue, 'sv1_Server_Incriment_per_month_' . $parm);
		$this->GetSessionValue($fld->SearchOperator, 'so1_Server_Incriment_per_month_' . $parm);
		$this->GetSessionValue($fld->SearchCondition, 'sc_Server_Incriment_per_month_' . $parm);
		$this->GetSessionValue($fld->SearchValue2, 'sv2_Server_Incriment_per_month_' . $parm);
		$this->GetSessionValue($fld->SearchOperator2, 'so2_Server_Incriment_per_month_' . $parm);
	}

	// Get value from session
	function GetSessionValue(&$sv, $sn) {
		if (isset($_SESSION[$sn]))
			$sv = $_SESSION[$sn];
	}

	// Set dropdown value to session
	function SetSessionDropDownValue($sv, $parm) {
		$_SESSION['sv_Server_Incriment_per_month_' . $parm] = $sv;
	}

	// Set filter values to session
	function SetSessionFilterValues($sv1, $so1, $sc, $sv2, $so2, $parm) {
		$_SESSION['sv1_Server_Incriment_per_month_' . $parm] = $sv1;
		$_SESSION['so1_Server_Incriment_per_month_' . $parm] = $so1;
		$_SESSION['sc_Server_Incriment_per_month_' . $parm] = $sc;
		$_SESSION['sv2_Server_Incriment_per_month_' . $parm] = $sv2;
		$_SESSION['so2_Server_Incriment_per_month_' . $parm] = $so2;
	}

	// Check if has Session filter values
	function HasSessionFilterValues($parm) {
		return ((@$_SESSION['sv_' . $parm] <> "" && @$_SESSION['sv_' . $parm] <> EWRPT_INIT_VALUE) ||
			(@$_SESSION['sv1_' . $parm] <> "" && @$_SESSION['sv1_' . $parm] <> EWRPT_INIT_VALUE) ||
			(@$_SESSION['sv2_' . $parm] <> "" && @$_SESSION['sv2_' . $parm] <> EWRPT_INIT_VALUE));
	}

	// Dropdown filter exist
	function DropDownFilterExist(&$fld, $FldOpr) {
		$sWrk = "";
		ewrpt_BuildDropDownFilter($fld, $sWrk, $FldOpr);
		return ($sWrk <> "");
	}

	// Extended filter exist
	function ExtendedFilterExist(&$fld) {
		$sExtWrk = "";
		ewrpt_BuildExtendedFilter($fld, $sExtWrk);
		return ($sExtWrk <> "");
	}

	// Validate form
	function ValidateForm() {
		global $ReportLanguage, $gsFormError, $Server_Incriment_per_month;

		// Initialize form error message
		$gsFormError = "";

		// Check if validation required
		if (!EWRPT_SERVER_VALIDATE)
			return ($gsFormError == "");

		// Return validate result
		$ValidateForm = ($gsFormError == "");

		// Call Form_CustomValidate event
		$sFormCustomError = "";
		$ValidateForm = $ValidateForm && $this->Form_CustomValidate($sFormCustomError);
		if ($sFormCustomError <> "") {
			$gsFormError .= ($gsFormError <> "") ? "<br>" : "";
			$gsFormError .= $sFormCustomError;
		}
		return $ValidateForm;
	}

	// Clear selection stored in session
	function ClearSessionSelection($parm) {
		$_SESSION["sel_Server_Incriment_per_month_$parm"] = "";
		$_SESSION["rf_Server_Incriment_per_month_$parm"] = "";
		$_SESSION["rt_Server_Incriment_per_month_$parm"] = "";
	}

	// Load selection from session
	function LoadSelectionFromSession($parm) {
		global $Server_Incriment_per_month;
		$fld =& $Server_Incriment_per_month->fields($parm);
		$fld->SelectionList = @$_SESSION["sel_Server_Incriment_per_month_$parm"];
		$fld->RangeFrom = @$_SESSION["rf_Server_Incriment_per_month_$parm"];
		$fld->RangeTo = @$_SESSION["rt_Server_Incriment_per_month_$parm"];
	}

	// Load default value for filters
	function LoadDefaultFilters() {
		global $Server_Incriment_per_month;

		/**
		* Set up default values for non Text filters
		*/

		// Field create_month
		$Server_Incriment_per_month->create_month->DefaultDropDownValue = EWRPT_INIT_VALUE;
		$Server_Incriment_per_month->create_month->DropDownValue = $Server_Incriment_per_month->create_month->DefaultDropDownValue;

		// Field create_year
		$Server_Incriment_per_month->create_year->DefaultDropDownValue = EWRPT_INIT_VALUE;
		$Server_Incriment_per_month->create_year->DropDownValue = $Server_Incriment_per_month->create_year->DefaultDropDownValue;

		/**
		* Set up default values for extended filters
		* function SetDefaultExtFilter(&$fld, $so1, $sv1, $sc, $so2, $sv2)
		* Parameters:
		* $fld - Field object
		* $so1 - Default search operator 1
		* $sv1 - Default ext filter value 1
		* $sc - Default search condition (if operator 2 is enabled)
		* $so2 - Default search operator 2 (if operator 2 is enabled)
		* $sv2 - Default ext filter value 2 (if operator 2 is enabled)
		*/

		/**
		* Set up default values for popup filters
		*/
	}

	// Check if filter applied
	function CheckFilter() {
		global $Server_Incriment_per_month;

		// Check create_month extended filter
		if ($this->NonTextFilterApplied($Server_Incriment_per_month->create_month))
			return TRUE;

		// Check create_year extended filter
		if ($this->NonTextFilterApplied($Server_Incriment_per_month->create_year))
			return TRUE;
		return FALSE;
	}

	// Show list of filters
	function ShowFilterList() {
		global $Server_Incriment_per_month;
		global $ReportLanguage;

		// Initialize
		$sFilterList = "";

		// Field create_month
		$sExtWrk = "";
		$sWrk = "";
		ewrpt_BuildDropDownFilter($Server_Incriment_per_month->create_month, $sExtWrk, "");
		if ($sExtWrk <> "" || $sWrk <> "")
			$sFilterList .= $Server_Incriment_per_month->create_month->FldCaption() . "<br>";
		if ($sExtWrk <> "")
			$sFilterList .= "&nbsp;&nbsp;$sExtWrk<br>";
		if ($sWrk <> "")
			$sFilterList .= "&nbsp;&nbsp;$sWrk<br>";

		// Field create_year
		$sExtWrk = "";
		$sWrk = "";
		ewrpt_BuildDropDownFilter($Server_Incriment_per_month->create_year, $sExtWrk, "");
		if ($sExtWrk <> "" || $sWrk <> "")
			$sFilterList .= $Server_Incriment_per_month->create_year->FldCaption() . "<br>";
		if ($sExtWrk <> "")
			$sFilterList .= "&nbsp;&nbsp;$sExtWrk<br>";
		if ($sWrk <> "")
			$sFilterList .= "&nbsp;&nbsp;$sWrk<br>";

		// Show Filters
		if ($sFilterList <> "")
			echo $ReportLanguage->Phrase("CurrentFilters") . "<br>$sFilterList";
	}

	// Return poup filter
	function GetPopupFilter() {
		global $Server_Incriment_per_month;
		$sWrk = "";
		return $sWrk;
	}

	//-------------------------------------------------------------------------------
	// Function GetSort
	// - Return Sort parameters based on Sort Links clicked
	// - Variables setup: Session[EWRPT_TABLE_SESSION_ORDER_BY], Session["sort_Table_Field"]
	function GetSort() {
		global $Server_Incriment_per_month;

		// Check for a resetsort command
		if (strlen(@$_GET["cmd"]) > 0) {
			$sCmd = @$_GET["cmd"];
			if ($sCmd == "resetsort") {
				$Server_Incriment_per_month->setOrderBy("");
				$Server_Incriment_per_month->setStartGroup(1);
				$Server_Incriment_per_month->DATE_FORMAT28t2Ecreated_on2C2725Y_25m2729->setSort("");
				$Server_Incriment_per_month->cumulative_sum->setSort("");
				$Server_Incriment_per_month->new_servers->setSort("");
			}

		// Check for an Order parameter
		} elseif (@$_GET["order"] <> "") {
			$Server_Incriment_per_month->CurrentOrder = ewrpt_StripSlashes(@$_GET["order"]);
			$Server_Incriment_per_month->CurrentOrderType = @$_GET["ordertype"];
			$sSortSql = $Server_Incriment_per_month->SortSql();
			$Server_Incriment_per_month->setOrderBy($sSortSql);
			$Server_Incriment_per_month->setStartGroup(1);
		}
		return $Server_Incriment_per_month->getOrderBy();
	}

	// Export email
	function ExportEmail($EmailContent) {
		global $ReportLanguage, $Server_Incriment_per_month;
		$sSender = @$_GET["sender"];
		$sRecipient = @$_GET["recipient"];
		$sCc = @$_GET["cc"];
		$sBcc = @$_GET["bcc"];
		$sContentType = @$_GET["contenttype"];

		// Subject
		$sSubject = ewrpt_StripSlashes(@$_GET["subject"]);
		$sEmailSubject = $sSubject;

		// Message
		$sContent = ewrpt_StripSlashes(@$_GET["message"]);
		$sEmailMessage = $sContent;

		// Check sender
		if ($sSender == "") {
			$this->setMessage($ReportLanguage->Phrase("EnterSenderEmail"));
			return;
		}
		if (!ewrpt_CheckEmail($sSender)) {
			$this->setMessage($ReportLanguage->Phrase("EnterProperSenderEmail"));
			return;
		}

		// Check recipient
		if (!ewrpt_CheckEmailList($sRecipient, EWRPT_MAX_EMAIL_RECIPIENT)) {
			$this->setMessage($ReportLanguage->Phrase("EnterProperRecipientEmail"));
			return;
		}

		// Check cc
		if (!ewrpt_CheckEmailList($sCc, EWRPT_MAX_EMAIL_RECIPIENT)) {
			$this->setMessage($ReportLanguage->Phrase("EnterProperCcEmail"));
			return;
		}

		// Check bcc
		if (!ewrpt_CheckEmailList($sBcc, EWRPT_MAX_EMAIL_RECIPIENT)) {
			$this->setMessage($ReportLanguage->Phrase("EnterProperBccEmail"));
			return;
		}

		// Check email sent count
		$emailcount = ewrpt_LoadEmailCount();
		if (intval($emailcount) >= EWRPT_MAX_EMAIL_SENT_COUNT) {
			$this->setMessage($ReportLanguage->Phrase("ExceedMaxEmailExport"));
			return;
		}
		if ($sEmailMessage <> "") {
			if (EWRPT_REMOVE_XSS) $sEmailMessage = ewrpt_RemoveXSS($sEmailMessage);
			$sEmailMessage .= ($sContentType == "url") ? "\r\n\r\n" : "<br><br>";
		}
		$sAttachmentContent = $EmailContent;
		$sAppPath = ewrpt_FullUrl();
		$sAppPath = substr($sAppPath, 0, strrpos($sAppPath, "/")+1);
		if (strpos($sAttachmentContent, "<head>") !== FALSE)
			$sAttachmentContent = str_replace("<head>", "<head><base href=\"" . $sAppPath . "\">", $sAttachmentContent); // Add <base href> statement inside the header
		else
			$sAttachmentContent = "<base href=\"" . $sAppPath . "\">" . $sAttachmentContent; // Add <base href> statement as the first statement

		//$sAttachmentFile = $Server_Incriment_per_month->TableVar . "_" . Date("YmdHis") . ".html";
		$sAttachmentFile = $Server_Incriment_per_month->TableVar . "_" . Date("YmdHis") . "_" . ewrpt_Random() . ".html";
		if ($sContentType == "url") {
			ewrpt_SaveFile(EWRPT_UPLOAD_DEST_PATH, $sAttachmentFile, $sAttachmentContent);
			$sAttachmentFile = EWRPT_UPLOAD_DEST_PATH . $sAttachmentFile;
			$sUrl = $sAppPath . $sAttachmentFile;
			$sEmailMessage .= $sUrl; // send URL only
			$sAttachmentFile = "";
			$sAttachmentContent = "";
		}

		// Send email
		$Email = new crEmail();
		$Email->Sender = $sSender; // Sender
		$Email->Recipient = $sRecipient; // Recipient
		$Email->Cc = $sCc; // Cc
		$Email->Bcc = $sBcc; // Bcc
		$Email->Subject = $sEmailSubject; // Subject
		$Email->Content = $sEmailMessage; // Content
		$Email->AttachmentContent = $sAttachmentContent; // Attachment
		$Email->AttachmentFileName = $sAttachmentFile; // Attachment file name
		$Email->Format = ($sContentType == "url") ? "text" : "html";
		$Email->Charset = EWRPT_EMAIL_CHARSET;
		$EventArgs = array();
		$bEmailSent = FALSE;
		if ($Server_Incriment_per_month->Email_Sending($Email, $EventArgs))
			$bEmailSent = $Email->Send();

		// Check email sent status
		if ($bEmailSent) {

			// Update email sent count and write log
			ewrpt_AddEmailLog($sSender, $sRecipient, $sEmailSubject, $sEmailMessage);

			// Sent email success
			$this->setMessage($ReportLanguage->Phrase("SendEmailSuccess"));
		} else {

			// Sent email failure
			$this->setMessage($Email->SendErrDescription);
		}
	}

	// Export PDF
	function ExportPDF($html) {
		global $gsExportFile;
		include_once "dompdf060b2/dompdf_config.inc.php";
		@ini_set("memory_limit", EWRPT_PDF_MEMORY_LIMIT);
		set_time_limit(EWRPT_PDF_TIME_LIMIT);
		$dompdf = new DOMPDF();
		$dompdf->load_html($html);
		$dompdf->set_paper("a4", "portrait");
		$dompdf->render();
		ob_end_clean();
		ewrpt_DeleteTmpImages();
		$dompdf->stream($gsExportFile . ".pdf", array("Attachment" => 1)); // 0 to open in browser, 1 to download

//		exit();
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Message Showing event
	function Message_Showing(&$msg) {

		// Example:
		//$msg = "your new message";

	}

	// Page Data Rendering event
	function Page_DataRendering(&$header) {

		// Example:
		//$header = "your header";

	}

	// Page Data Rendered event
	function Page_DataRendered(&$footer) {

		// Example:
		//$footer = "your footer";

	}

	// Form Custom Validate event
	function Form_CustomValidate(&$CustomError) {

		// Return error message in CustomError
		return TRUE;
	}
}
?>
