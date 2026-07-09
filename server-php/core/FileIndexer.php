<?php ##########################################################################
################################################################################

class FileIndexer
extends FilesystemIterator {

	public function
	__Construct(string $Path) {

		parent::__Construct($Path, (0
			| FilesystemIterator::SKIP_DOTS
			| FilesystemIterator::CURRENT_AS_FILEINFO
		));

		return;
	}

};
