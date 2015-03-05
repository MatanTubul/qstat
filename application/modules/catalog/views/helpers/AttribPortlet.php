<?php
/**
 *
* @author alexsher
* @version
*/
require_once 'Zend/View/Interface.php';

/**
 * LockData helper
 *
 * @uses viewHelper Catalog_View_Helper
 */
class Catalog_View_Helper_AttribPortlet extends Zend_View_Helper_Abstract
{

	/**
	 *
	 */
	public function attribPortlet ($arrAttribute,$objDbTable)
	{
		ob_start();
		?>
		<div class="portlet">
			<input type="hidden" name="<?php echo $objDbTable::COL_CUSTOM_COLUMNS;?>[]" value="<?php echo $arrAttribute[Bf_Eav_Db_Attributes::COL_ID_ATTR];?>" />
			<div class="portlet-header"><?php echo $this->view->translate($arrAttribute[Bf_Eav_Db_Attributes::COL_ATTR_CODE]);?></div>
			<div class="portlet-content">
			<?php
			echo $arrAttribute[Bf_Eav_Db_Attributes::COL_DESCRIPTION];
			if(!empty($arrAttribute['ent_types'])) {
				echo "Used for:<ul>";
				foreach($arrAttribute['ent_types'] as $strType) {
					echo "<li>{$strType}</li>".PHP_EOL;
				}
				echo "</ul>";
			}
			?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

}
