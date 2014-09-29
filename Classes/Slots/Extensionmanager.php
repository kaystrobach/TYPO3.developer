<?php

namespace KayStrobach\Developer\Slots;

class Extensionmanager {

	/**
	 * Extends the list of actions for EXT:developer to change the
	 * icon of "ext_update.php"
	 *
	 * @param array $extension
	 * @param array $actions
	 */
	public function processActions(array $extension, array &$actions) {
		$buffer = '<span class="t3-icon t3-icon-actions t3-icon-actions-document t3-icon-document-open"></span>';
		array_unshift($actions, $buffer);
	}

}