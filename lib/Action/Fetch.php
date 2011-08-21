<?php

class Action_Fetch extends Action_Abstract {

	function __construct() {
		parent::__construct();
		$this->getLogger()->info('Starting action fetch');
	}
}
