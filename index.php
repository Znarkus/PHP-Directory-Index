<?php

define('USERNAME', 'john');
define('PASSWORD', 'doe');
define('ALLOWED_EXTENSIONS', 'mp3,m4a,wav');

error_reporting(E_ALL | E_STRICT);

// Basic auth
if (!isset($_SERVER['PHP_AUTH_USER']) || !($_SERVER['PHP_AUTH_USER'] === USERNAME && $_SERVER['PHP_AUTH_PW'] === PASSWORD)) {
    header('WWW-Authenticate: Basic realm="' . $_SERVER['HTTP_HOST'] . '"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Requires authorization';
    exit;
}

// Get current directory
$dir = isset($_GET['dir']) 
  ? trim($_GET['dir'], '/') . '/' 
	: '';

// Guard against directory exploits
$script_dir = realpath(__DIR__);

if (substr(realpath($dir), 0, strlen($script_dir)) !== $script_dir) {
	header('HTTP/1.0 403');
	echo "Directory {$dir} not allowed";
	exit;
}

// Read directory
$list = array_merge(
	glob($dir . '*', GLOB_ONLYDIR),
	glob($dir . '*.{' . ALLOWED_EXTENSIONS . '}', GLOB_BRACE) ?: array()
);

function url($path) {
	if (is_dir($path)) {
		return '/?dir=' . trim($path, '/');
	} else {
		return $path;
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title><?= $_SERVER['HTTP_HOST'] ?>/<?= $dir ?></title>
</head>
<body>
	
	<h1>
		<a href="./"><?= $_SERVER['HTTP_HOST'] ?></a> /
		<? $concat_dir = '' ?>
		<? foreach (explode('/', $dir) as $part): ?>
			<? if ($part): ?>
				<? $concat_dir .= $part . '/' ?>
				<a href="<?= url($concat_dir) ?>"><?= $part ?></a> /
			<? endif ?>
		<? endforeach ?>
	</h1>
	
	<ul>
	<? foreach ($list as $path): ?>
		<li><a href="<?= url($path) ?>"><?= basename($path) ?><?= is_dir($path) ? '/' : '' ?></a></li>
	<? endforeach ?>
	</ul>
	
</body>
</html>
