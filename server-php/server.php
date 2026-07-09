<?php ##########################################################################
################################################################################

define('AppRoot', dirname(__FILE__, 2));
define('DS', DIRECTORY_SEPARATOR);

require(sprintf(
	join(DS, [ '%s', 'server-php', 'core', 'FileWatchServer.php' ]),
	AppRoot
));

require(sprintf(
	join(DS, [ '%s', 'server-php', 'core', 'FileIndexer.php' ]),
	AppRoot
));

function mkdirgood(string $Path) {

	$Old = umask(0);
	$Did = mkdir($Path, 0777, TRUE);
	umask($Old);

	return $Did;
};

################################################################################
################################################################################

$Server = FileWatchServer::New(AppRoot);
exit($Server->Run());
