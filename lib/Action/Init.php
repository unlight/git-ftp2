<?php

class Action_Init extends Action_Abstract {

	function __construct() {
		parent::__construct();
		$this->checkDirtyWorkspace();
		$this->setRemoteParams();
		$this->getLogger()->info('Starting action init');
		$this->uploadAllFiles();
		$this->deploySha1File();
	}

	public function help() {
		echo "Print some help usage for ".get_class()."\n";
		exit(0);
	}
}
