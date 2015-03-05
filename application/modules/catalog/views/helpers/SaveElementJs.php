<?php
class Catalog_View_Helper_SaveElementJs extends Zend_View_Helper_Abstract {
	public function saveElementJs($view) {
		ob_start();
		?>
		<script type="text/javascript">
		function saveFormForUser() {

			var formSerializeData = '';
			
			<?php 
			foreach ($view->arrFormNames as $strFormName){
				?>
				formSerializeData  = formSerializeData + '&' +  $('#<?php echo $strFormName;?>').serialize();
				<?php 
			} 
			?>
		
			$.post('<?php echo $view->url ( array ('module' => 'catalog', 'controller' => 'index', 'action' => 'save-form-for-user',Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG=> $view->intCatalogId), null, true ); ?>', formSerializeData, function(data, textStatus) {
				  //data contains the JSON object
				  //textStatus contains the status: success, error, etc
					if (data.msg !== ''){
						$.jGrowl(data.msg);
					}
					
					$('input').parent().find('.errors').remove();
		
					if (data.error_msg !== ''){
		
				        $.each(data.error_msg,function(element,arrData){
						        var o = '<ul class="errors">';
						        $.each(arrData,function(key,value){
						            o+='<li>'+ value+'</li>';
						        });
						        o+='</ul>';
		
				                $('#'+element).parent().append(o);
						    
				        });
					}
					var usePopup = false;
					var boolIsFolder = 1==$('#<?php echo Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER;?>').val();
					
					if ((boolIsFolder && <?php echo $view->options->editFolder->popup?'true':'false';?>) || (!boolIsFolder && <?php echo $view->options->editItem->popup?'true':'false';?> )) {
						usePopup = true;
					}
		

					if (data.code == '<?php echo Ingot_JQuery_JqGrid::RETURN_CODE_OK ;?>'){
						if (usePopup) {							
							$('#Catalog').trigger("reloadGrid");
							$("#formDiv").dialog( "close" );
						} else {
							alert('<?php echo $view->translate('LBL_SAVE_OK'); ?>');
							var params = {};
							//:TODO handle redirect to index, if folder saved from an edit page
							params['<?php echo Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG;?>'] = parseInt(data.<?php echo Ingot_JQuery_JqGrid::RETURN_INDEX_MSG?>);
							
							var url = '<?php echo $view->url ( array ('module' => 'catalog', 'controller' => 'index', 'action' => 'view'), null, true ); ?>';

							document.location.href = url +'?'+ decodeURIComponent($.param(params));
						}
					}	
				}, "json");
				
		}	

		
		function saveForm(is_redirect_to_add_form) {

			var formSerializeData = '';
			
			<?php 
			foreach ($view->arrFormNames as $strFormName){
				?>
				formSerializeData  = formSerializeData + '&' +  $('#<?php echo $strFormName;?>').serialize();
				<?php 
			} 
			?>
		
			$.post('<?php echo $view->url ( array ('module' => 'catalog', 'controller' => 'index', 'action' => 'save'), null, true ); ?>', formSerializeData, function(data, textStatus) {
				  //data contains the JSON object
				  //textStatus contains the status: success, error, etc
					if (data.msg !== ''){
						$.jGrowl(data.msg);
					}
					
					$('input').parent().find('.errors').remove();
		
					if (data.error_msg !== ''){
		
				        $.each(data.error_msg,function(element,arrData){
						        var o = '<ul class="errors">';
						        $.each(arrData,function(key,value){
						            o+='<li>'+ value+'</li>';
						        });
						        o+='</ul>';
		
				                $('#'+element).parent().append(o);
						    
				        });
					}
					var usePopup = false;
					var boolIsFolder = 1==$('#<?php echo Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER;?>').val();
					
					if ((boolIsFolder && <?php echo $view->options->editFolder->popup?'true':'false';?>) || (!boolIsFolder && <?php echo $view->options->editItem->popup?'true':'false';?> )) {
						usePopup = true;
					}
		

					if (data.code == '<?php echo Ingot_JQuery_JqGrid::RETURN_CODE_OK ;?>'){
						if (usePopup) {							
							$('#Catalog').trigger("reloadGrid");
							$("#formDiv").dialog( "close" );
						} else {
							alert('<?php echo $view->translate('LBL_SAVE_OK'); ?>');
							var params = {};
							//:TODO handle redirect to index, if folder saved from an edit page
							params['<?php echo Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG;?>'] = parseInt(data.<?php echo Ingot_JQuery_JqGrid::RETURN_INDEX_MSG?>);
							
							var url = '<?php echo $view->url ( array ('module' => 'catalog', 'controller' => 'index', 'action' => 'view'), null, true ); ?>';

							if (is_redirect_to_add_form==1){
								var url = '<?php echo $view->url ( array ('module' => 'catalog', 'controller' => 'index', 'action' => 'add'), null, true ); ?>?id_parent='+$('#id_parent').val()+'&id_entities_types='+$('#id_entities_types').val()+'&'+boolIsFolder;
							}

							document.location.href = url +'?'+ decodeURIComponent($.param(params));
						}
					}	
				}, "json");
				
		}		
		</script>
		<?php 
		return ob_get_clean();
	}
}