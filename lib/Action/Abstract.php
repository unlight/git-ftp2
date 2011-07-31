<?php
abstract class Action_Abstract {

	protected $verbosed = false;
	protected $forced = false;
	protected $silent = false;
	protected $runningDry = false;
	protected $allFiles = false;
	protected $help = false;
	protected $action = "";
	protected $url = "";

	protected $remote_host = "";
	protected $remote_user = "";
	protected $remote_passwd = "";
	protected $remote_protocol = "";
	protected $remote_path = "";
	protected $remote_sha1 = "";

	protected $logger = NULL;

	function __construct() {
		$this->handleOptions();
		$this->initLogger();
	}

	private function handleOptions() {
		try {
			$opts = new Zend_Console_Getopt(array(
				'v|verbose'	=> 'Verbosity',
				'f|force'	=> 'Force',
				'D|dry-run'	=> 'Dry run',
				'n|silent'	=> 'Silent',
				's|scope=s'	=> 'Scope',
				'u|user=s'	=> 'Usernane',
				'p|passwd-s'	=> 'Password',
				'l|url=s'	=> 'URL',
				'h|help'	=> 'Help'
				)
			);
			$opts->parse();

			if (isset($opts->v)) {
				$this->verbosed = true;
			}

			if (isset($opts->f)) {
				$this->forced = true;
			}

			if (isset($opts->D)) {
				$this->runningDry = true;
			}

			if (isset($opts->a)) {
				$this->allFiles = true;
			}

			if (isset($opts->n)) {
				$this->silent = true;
				$this->verbosed = false;
			}

			if (isset($opts->h)) {
				$this->help = true;
			}

			if (isset($opts->p)) {
				$this->remote_passwd = $opts->p;
			}

			if (isset($opts->u)) {
				$this->remote_user = $opts->u;
			}

			if (isset($opts->l)) {
				$this->url = $opts->l;
			}

		} catch (Zend_Console_Getopt_Exception $e) {
	    		echo $e->getUsageMessage();
	    		exit(ERROR_USAGE);
		}
	}

	private function getUrl() {
		if ($this->url != "") {
			return $this->url; 
		} else {
			$this->logger->emerg("Missing url");
			exit(ERROR_USAGE);
		}
	}

	private function initLogger() {
		$writer = new Zend_Log_Writer_Stream('php://output');
		if (!$this->verbosed) {
			$writer->addFilter(Zend_Log::WARN); 
		}
    	$this->logger = new Zend_Log($writer);
	}

	private function handleUrl($url = "") {
		$pattern = "#(ftp|ftps)://([a-zA-Z0-9:.-]*)(/[a-zA-Z0-9-~/]*)#"; // this may be fixed
		preg_match($pattern, $url, $matches);
		if (!isset($matches[2])) {
			$this->logger->emerg("Wrong url");
			exit(ERROR_USAGE);
		}
		
		$this->remote_protocol = $matches[1];
		$this->logger->info("Protocol: ".strtoupper($this->remote_protocol));
		$this->remote_host = $matches[2];
		$this->remote_path = $matches[3];
	}
}

