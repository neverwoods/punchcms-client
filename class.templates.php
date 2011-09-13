<?php

/* Templates Class v0.1.0
 * Collection class for the Template objects.
 *
 * CHANGELOG
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 */

class Templates extends DBA__Collection {

	public static function getFromParent($lngParentId, $blnRecursive = FALSE, $intAccountId = 0) {
		global $_CONF;
		$objReturn = NULL;

		if ($intAccountId == 0) {
			$intAccountId = $_CONF['app']['account']->getId();
		}

		$strSql = sprintf("SELECT * FROM pcms_template WHERE parentId = '%s' AND accountId = '%s' ORDER BY sort, name", $lngParentId, $intAccountId);
		$objTemplates = Template::select($strSql);

		if ($objTemplates) {
			$objReturn = new DBA__Collection();
			foreach ($objTemplates as $objTemplate) {
				$objReturn->addObject($objTemplate);
			}
		}

		if ($blnRecursive === TRUE && is_object($objReturn)) {
			foreach ($objReturn as $objTemplate) {
				$objTemplate->getTemplates(TRUE);
			}
		}

		return $objReturn;
	}

	public static function sortChildren($intElementId) {
		$lastSort = 0;
		$arrItemlist = request("itemlist");
		$lastPosition = request("pos", 0);

		if (is_array($arrItemlist) && count($arrItemlist) > 0) {
			$objTemplate = Template::selectByPK($intElementId);

			//*** Find last sort position.
			if ($lastPosition > 0) {
				$objFields = $objTemplate->getFields();
				$objFields->seek($lastPosition);
				$lastSort = $objFields->current()->getSort();
			}

			//*** Loop through the items and manipulate the sort order.
			foreach ($arrItemlist as $value) {
				$lastSort++;
				$objField = TemplateField::selectByPK($value);
				$objField->setSort($lastSort);
				$objField->save(FALSE);
			}
		}
	}

}

?>