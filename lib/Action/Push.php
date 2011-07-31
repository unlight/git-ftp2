<?php

class Action_Push extends Action_Abstract {

	function __construct() {
		parent::__construct();
		$this->logger->info('Starting action push');
	}
}
