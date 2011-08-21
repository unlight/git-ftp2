<?php

class Action_Push extends Action_Abstract {

	function __construct() {
		parent::__construct();
		$this->getLogger()->info('Starting action push');
	}
}
