<?php
abstract class Action_Abstract {

	protected $verbosed = false;
	protected $forced = false;
	protected $silent = false;
	protected $running_dry = false;
	protected $all_files = false;
	protected $help = false;
	protected $action = "";
	protected $scope = "";
	protected $repo_path = ".";

	protected $remote_url = "";
	protected $remote_host = "";
	protected $remote_user = "";
	protected $remote_passwd = "";
	protected $remote_protocol = "";
	protected $remote_path = "";
	protected $remote_sha1 = "";

	private $Logger = NULL;
	private $Git = NULL;

	function __construct() {
		$this->handleOptions();
	}

	private function handleOptions() {
		try {
			$opts = new Zend_Console_Getopt(array(
				'v|verbose'		=> 'Verbosity',
				'f|force'		=> 'Force',
				'D|dry-run'		=> 'Dry run',
				'R|repo=s'		=> 'Repository',
				'n|silent'		=> 'Silent',
				's|scope=s'		=> 'Scope',
				'u|user=s'		=> 'Usernane',
				'p|passwd-s'	=> 'Password',
				'l|url=s'		=> 'URL',
				'h|help'		=> 'Help'
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
				$this->running_dry = true;
			}

			if (isset($opts->R)) {
				$this->repo_path = $opts->R;
			}

			if (isset($opts->a)) {
				$this->all_files = true;
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
				$this->remote_url = $opts->l;
			}

			if (isset($opts->s)) {
				$this->scope = $opts->s;
			}

		} catch (Zend_Console_Getopt_Exception $e) {
	    		echo $e->getUsageMessage();
	    		exit(ERROR_USAGE);
		}
	}

	protected function getRemoteUrl() {
		if ($this->remote_url != "") {
			return $this->remote_url; 
		} else {
			$this->getLogger()->emerg("Missing remote_url");
			exit(ERROR_USAGE);
		}
	}

	protected function getLogger() {
		if ($this->Logger == NULL) {
			$writer = new Zend_Log_Writer_Stream('php://output');
			if (!$this->verbosed) {
				$writer->addFilter(Zend_Log::WARN); 
			}
			$this->Logger = new Zend_Log($writer);
		}
		return $this->Logger;
	}

	protected function handleRemoteParams() {
		$this->setRemoteParamFromConfigfile($this->remote_user, 'user');
		$this->setRemoteParamFromConfigfile($this->remote_passwd, 'password');
		$this->setRemoteParamFromConfigfile($this->remote_url, 'url');
		$this->splitRemoteUrl($this->getRemoteUrl());
	}

	private function splitRemoteUrl($url = "") {
		$pattern = "#(ftp|ftps)://([a-zA-Z0-9:.-]*)(/[a-zA-Z0-9-~/]*)#"; // this may be fixed
		preg_match($pattern, $url, $matches);
		if (!isset($matches[2])) {
			$this->getLogger()->emerg("Wrong url");
			exit(ERROR_USAGE);
		}
		
		$this->remote_protocol = $matches[1];
		$this->getLogger()->info("Protocol: ".strtoupper($this->remote_protocol));
		$this->remote_host = $matches[2];
		$this->getLogger()->info("Host: ".$this->remote_host);
		$this->remote_path = $matches[3];
		$this->getLogger()->info("Path: ".$this->remote_path);
	}

	protected function getGit() {
		if ($this->Git == NULL) {
			$this->Git = Git_Git::open($this->repo_path);
			$this->getLogger()->info("Open git repo: ".$this->repo_path);
		}
		return $this->Git;
	}

	protected function runGit($command) {
		try {
			$this->getGit()->run($command);
		} catch (Exception $e) {
			$this->getLogger()->emerg("Error: ".$e);
			exit(ERROR_GIT);
		}
	} 

	protected function setRemoteParamFromConfigfile($param, $configparam) {
		try {
			if ($param == "" && file_exists(CONFIG_FILE) && $this->scope != "") {
				$param = $this->getGit()->run("config -f '".CONFIG_FILE."' --get git-ftp.".$this->scope.".".$configparam);
			}
			if ($param == "" && file_exists(CONFIG_FILE)) {
				$param = $this->getGit()->run("config -f '".CONFIG_FILE."' --get git-ftp.".$configparam);
			}
			if ($param == "" && $this->scope != "") {
				$param = $this->getGit()->run("config --get git-ftp.".$this->scope.".".$configparam);
			}
			if ($param == "") {
				$param = $this->getGit()->run("config --get git-ftp.".$configparam);
			}
		} catch (Exception $e) {
			//ignored
		}
		$this->getLogger()->info("Remote ".$configparam.": ".$param);
	}

	protected function checkDirtyWorkspace() {
		if (!$this->getGit()->is_clean_workspace()) {
			$this->getLogger()->emerg("Error: workspace is dirty");
			exit(ERROR_USAGE);
		}
	}
}
