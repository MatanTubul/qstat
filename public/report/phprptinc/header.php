<?php if (@$gsExport == "email" || @$gsExport == "pdf") ob_clean(); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Qstat Reports system</title>
<?php if (@$gsExport == "" || @$gsExport == "print") { ?>
<?php } else { // Export to Word/Excel/Pdf/Email ?>
<?php if (EWRPT_ENCODING == "UTF-8") { ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php } ?>
<?php } ?>
<?php if (@$gsExport == "") { ?>
<link rel="stylesheet" type="text/css" href="<?php echo ewrpt_YuiHost() ?>build/menu/assets/skins/sam/menu.css">
<link rel="stylesheet" type="text/css" href="phprptcss/rptmenu.css">
<?php } ?>
<?php if (@$gsExport == "") { ?>
<link rel="stylesheet" type="text/css" href="<?php echo ewrpt_YuiHost() ?>build/button/assets/skins/sam/button.css">
<link rel="stylesheet" type="text/css" href="<?php echo ewrpt_YuiHost() ?>build/container/assets/skins/sam/container.css">
<link rel="stylesheet" type="text/css" href="<?php echo ewrpt_YuiHost() ?>build/resize/assets/skins/sam/resize.css">
<?php } ?>
<?php if (@$gsExport == "" || @$gsExport == "print") { ?>
<link rel="stylesheet" type="text/css" href="<?php echo EWRPT_PROJECT_STYLESHEET_FILENAME ?>">
<?php } else { ?>
<style type="text/css">
<?php $cssfile = (@$gsExport == "pdf") ? (EWRPT_PDF_STYLESHEET_FILENAME == "" ? EWRPT_PROJECT_STYLESHEET_FILENAME : EWRPT_PDF_STYLESHEET_FILENAME) : EWRPT_PROJECT_STYLESHEET_FILENAME ?>
<?php echo file_get_contents($cssfile) ?>
</style>
<?php } ?>
<?php if (@$gsExport == "" || @$gsExport == "print" || @$gsExport == "email") { ?>
<script type="text/javascript" src="<?php echo ewrpt_YuiHost() ?>build/utilities/utilities.js"></script>
<?php } ?>
<?php if (@$gsExport == "") { ?>
<script type="text/javascript" src="<?php echo ewrpt_YuiHost() ?>build/button/button-min.js"></script>
<script type="text/javascript">
<!--
var EWRPT_LANGUAGE_ID = "<?php echo $gsLanguage ?>";
var EWRPT_DATE_SEPARATOR = "/";
if (EWRPT_DATE_SEPARATOR == "") EWRPT_DATE_SEPARATOR = "/"; // Default date separator

//var EWRPT_EMAIL_EXPORT_BUTTON_SUBMIT_TEXT = "<?php echo ewrpt_EscapeJs(ewrpt_BtnCaption($ReportLanguage->Phrase("SendEmailBtn"))) ?>";
//var EWRPT_BUTTON_CANCEL_TEXT = "<?php echo ewrpt_EscapeJs(ewrpt_BtnCaption($ReportLanguage->Phrase("CancelBtn"))) ?>";

var EWRPT_MAX_EMAIL_RECIPIENT = <?php echo EWRPT_MAX_EMAIL_RECIPIENT ?>;

//-->
</script>
<script type="text/javascript" src="<?php echo ewrpt_YuiHost() ?>build/container/container-min.js"></script>
<script type="text/javascript" src="<?php echo ewrpt_YuiHost() ?>build/resize/resize.js"></script>
<?php } ?>
<?php if (@$gsExport == "" || @$gsExport == "print" || @$gsExport == "email") { ?>
<script type="text/javascript" src="phprptjs/ewrpt.js"></script>
<?php } ?>
<?php if (@$gsExport == "") { ?>
<script type="text/javascript">
<!--
<?php echo $ReportLanguage->ToJSON() ?>

//-->
</script>
<script type="text/javascript" src="<?php echo ewrpt_YuiHost() ?>build/menu/menu.js"></script>
<script type="text/javascript">
var EWRPT_IMAGES_FOLDER = "phprptimages";
</script>
<?php } ?>
<meta name="generator" content="PHP Report Maker v5.1.0">
</head>
<body class="yui-skin-sam">
<?php if (@$gsExport == "") { ?>
<div class="ewLayout">
	<!-- header (begin) --><!-- *** Note: Only licensed users are allowed to change the logo *** -->
	<div class="ewHeaderRow"><img src="phprptimages/phprptmkrlogo5.png" alt="" border="0"></div>
	<!-- header (end) -->
	<!-- content (begin) -->
  <table cellspacing="0" class="ewContentTable">
		<tr>
			<td class="ewMenuColumn">
			<!-- left column (begin) -->
<?php include_once "menu.php"; ?>
			<!-- left column (end) -->
			</td>
			<td class="ewContentColumn">
<?php } ?>
