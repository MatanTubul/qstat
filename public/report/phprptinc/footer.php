<?php if (@$gsExport == "") { ?>
			<!-- right column (end) -->
			<?php if (isset($gsTimer)) $gsTimer->Stop(); ?>
		</td></tr>
	</table>
	<!-- content (end) -->
	<!-- footer (begin) --><!-- *** Note: Only licensed users are allowed to remove or change the following copyright statement. *** -->
	<div class="ewFooterRow">
		<div class="ewFooterText">&nbsp;<?php echo $ReportLanguage->ProjectPhrase("FooterText"); ?></div>
		<!-- Place other links, for example, disclaimer, here -->
	</div>
	<!-- footer (end) -->	
</div>
<?php } ?>
<?php if (@$gsExport == "") { ?>
<table cellspacing="0" cellpadding="0"><tr><td><div id="ewrptEmailDialog" class="phpreportmaker">
<?php include_once "ewremail5.php"; ?>
</div></td></tr></table>
<?php } ?>
<?php if (@$gsExport == "" || @$gsExport == "print" || @$gsExport == "email") { ?>
<script type="text/javascript">
<!--
ewrptDom.getElementsByClassName(EWRPT_TABLE_CLASS, "TABLE", null, ewrpt_SetupTable); // init the table

//-->
</script>
<?php } ?>
<?php if (@$gsExport == "") { ?>
<script type="text/javascript">
<!--
ewrpt_InitEmailDialog(); // Init the email dialog

//-->
</script>
<?php } ?>
</body>
</html>
