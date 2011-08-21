<?php

class Action_Init extends Action_Abstract {

	function __construct() {
		parent::__construct();
		$this->getLogger()->info('Starting action init');
	}
}
