<?php

class Action_Push extends Action_Abstract {

	function __construct() {
		parent::__construct();
		$this->checkDirtyWorkspace();
		$this->handleRemoteParams();
		$this->getLogger()->info('Starting action push');
	}
}
