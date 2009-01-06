<?php

/* TemplateFieldValue Class v0.1.0
 * Handles TemplateFieldValue properties and methods.
 *
 * CHANGELOG
 * version 0.1.0, 11 Apr 2006
 *   NEW: Created class.
 */

class TemplateFieldValue extends DBA_TemplateFieldValue {

	public function duplicate() {
		if ($this->id > 0) {
			$objReturn = parent::duplicate();
			return $objReturn;
		}

		return NULL;
	}

}

?>