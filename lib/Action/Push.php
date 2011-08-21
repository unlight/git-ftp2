<?php

class Action_Push extends Action_Abstract {

	function __construct() {
		parent::__construct();
		$this->checkDirtyWorkspace();
		$this->handleRemoteParams();
		$this->getLogger()->info('Starting action push');
	}

	public function help() {
		echo "Print some help usage for ".get_class()."\n";
		exit(0);
	}
}
