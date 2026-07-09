<?php ##########################################################################
################################################################################

class FileWatchServer {

	protected string
	$AppRoot;

	protected string
	$ReadRoot;

	protected string
	$BankRoot;

	protected string
	$Bank;

	protected array
	$BankIndex;

	protected mixed
	$NInst;

	protected mixed
	$NNode;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	__Construct(string $AppRoot, string $Bank='default') {

		$this->Prepare($AppRoot, $Bank);
		$this->IndexCurrentBank();
		$this->CleanReadRoot();
		$this->PrimeReadRoot();
		$this->StartNotifyService();

		printf('* AppRoot: %s%s', $this->AppRoot, PHP_EOL);
		printf('* ReadRoot: %s%s', $this->ReadRoot, PHP_EOL);
		printf('* BankRoot: %s%s', $this->BankRoot, PHP_EOL);
		printf('* Current Bank: %s%s', $this->Bank, PHP_EOL);

		printf(
			'* Bank Contents (%d): %s%s',
			count($this->BankIndex),
			join(', ', $this->BankIndex),
			PHP_EOL
		);

		return;
	}

	protected function
	Prepare(string $AppRoot, string $Bank):
	void {

		$this->AppRoot = $AppRoot;
		$this->Bank = $Bank;

		$this->ReadRoot = sprintf(
			'%s%s%s',
			$this->AppRoot, DIRECTORY_SEPARATOR, 'files'
		);

		$this->BankRoot = sprintf(
			'%s%s%s',
			$this->AppRoot, DIRECTORY_SEPARATOR, 'banks'
		);

		return;
	}

	protected function
	CleanReadRoot():
	void {

		$Indexer = new FileIndexer($this->ReadRoot);

		foreach($Indexer as $File) {
			/** @var SplFileInfo $File */
			unlink($File->GetPathName());
			continue;
		}

		return;
	}

	protected function
	PrimeReadRoot():
	void {

		$Path = $this->GetCurrentBankPath();
		$Bank = NULL;

		$Indexer = new FileIndexer($Path);

		foreach($Indexer as $File) {
			/** @var SplFileInfo $File */

			$Bank = $File->GetBasename();
			$File = $this->GetBankPathToRandom($Bank);
			$Prime = $this->GetReadPathForBankChoice($Bank, basename($File));

			copy($File, $Prime);

			continue;
		}

		return;
	}

	protected function
	IndexCurrentBank():
	void {

		$this->BankIndex = [];

		////////

		$Path = $this->GetBankPathTo($this->Bank);

		if(!file_exists($Path))
		mkdirgood($Path);

		////////

		$Indexer = new FileIndexer($Path);

		foreach($Indexer as $Item) {
			/** @var SplFileInfo $Item */
			$this->BankIndex[] = $Item->GetBasename();
		}

		return;
	}

	protected function
	StartNotifyService():
	void {

		$this->NInst = inotify_init();

		$this->NNode = inotify_add_watch(
			$this->NInst,
			$this->ReadRoot,
			IN_CLOSE
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	GetReadPathForBankChoice(string $Bankname, string $Filename):
	string {

		$Ext = NULL;

		////////

		// if the file chosen has an extension we want to reuse that on
		// the primed file as i do not want to hardcode this for any
		// filetypes.

		if(str_contains($Filename, '.'))
		$Ext = substr($Filename, (strpos($Filename, '.', 0)));

		return sprintf(
			'%s%s',
			$this->GetReadPathTo($Bankname),
			$Ext
		);
	}

	protected function
	GetBankableName(string $Filename):
	string {

		if(str_contains($Filename, '.'))
		return substr($Filename, 0, strpos($Filename, '.', 0));

		return $Filename;
	}

	protected function
	GetReadPathTo(string $Basename):
	string {

		return join(DIRECTORY_SEPARATOR, [
			$this->ReadRoot,
			$Basename
		]);
	}

	protected function
	GetBankPathTo(string $Basename):
	string {

		return join(DIRECTORY_SEPARATOR, [
			$this->BankRoot,
			$this->Bank
		]);
	}

	protected function
	GetBankPathToRandom(string $Filename):
	string {

		$Bankpath = join(DIRECTORY_SEPARATOR, [
			$this->GetBankPathTo($this->Bank),
			$this->GetBankableName($Filename)
		]);

		////////

		$Indexer = new FileIndexer($Bankpath);
		$Found = [];

		foreach($Indexer as $File) {
			$Found[] = $File->GetPathName();
			continue;
		}

		////////

		$Choice = random_int(0, (count($Found) - 1));

		return $Found[$Choice];
	}

	protected function
	GetCurrentBankPath():
	string {

		return $this->GetBankPathTo($this->Bank);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Run():
	int {

		$SR = [ $this->NInst ];
		$SW = NULL;
		$SE = NULL;
		$ST = 60;

		$Select = NULL;
		$Notify = NULL;
		$Note = NULL;
		$Filepath = NULL;
		$Bankpath = NULL;

		////////

		while($Select = stream_select($SR, $SW, $SE, $ST)) {

			if($Select === FALSE)
			break;

			////////

			$Notify = inotify_read($this->NInst);

			if(!is_array($Notify) || !count($Notify))
			break;

			////////

			foreach($Notify as $Note) {

				// these events get emitted even though we never asked
				// to hear abou them. they happen for things like a
				// file being deleted, or calling inotify_rm_watch().

				if(($Note['mask'] & IN_IGNORED) !== 0)
				break;

				// only care about the files in this folder not the
				// folder itself.

				if(($Note['mask'] & IN_ISDIR) !== 0)
				break;

				$Filepath = $this->GetReadPathTo($Note['name']);
				$Bankpath = $this->GetBankPathToRandom($Note['name']);

				printf('>> %s%s', $Filepath, PHP_EOL);
				printf('<< %s%s', $Bankpath, PHP_EOL);

				inotify_rm_watch($this->NInst, $this->NNode);
				copy($Bankpath, $Filepath);
				$this->NNode = inotify_add_watch($this->NInst, $this->ReadRoot, IN_CLOSE);

				continue;
			}

			continue;
		}

		return 0;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	New(string $AppRoot):
	static {

		$Output = new static($AppRoot);

		return $Output;
	}

};
