<?php

class Action_Catchup extends Action_Abstract {

	function __construct() {
		parent::__construct();
		$this->getLogger()->info('Starting action catchup');
	}
}
