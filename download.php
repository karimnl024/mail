<?php
session_start();
if(empty($_SESSION['last_cleaned']) || @$_SESSION['last_cleaned'] < (time() - 60 * 5)) {
	$_SESSION['last_cleaned'] = time();
	$_SESSION['down_ops'] = null;
} else {
	$_SESSION['down_ops'] = $_SESSION['down_ops'] + 1;
	if($_SESSION['down_ops'] >= 50) {
		die("Please...");
	}
}
define('UPLOADS', 'C:\Users\karim\Documents\uploads');

if(empty($_GET['id']) || empty($_GET['access'])) {
	header('location: index.html');
	exit;
}

$dbh = new PDO('mysql:host=127.0.0.1;dbname=mail', 'root', null);
$q = $dbh->prepare("SELECT `name`, `original` FROM `cases`, `files` WHERE cases.id = files.case_id AND cases.accesskey = :key AND files.id = :id LIMIT 1;");
$q->execute(array(
	':key' => $_GET['access'],
	':id' => $_GET['id']
));

if($q->rowCount() != 1) {
	die('An error has occurred. ');
}

$data = $q->fetch(PDO::FETCH_ASSOC);



$file = UPLOADS."\\".$data['name'];

$quoted = sprintf('"%s"', addcslashes(basename($data['original']), '"\\'));
$size   = filesize($file);


header("Content-Type: application/octet-stream");
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=".$quoted); 
header("Content-length: " . filesize($file));
//echo readfile($file);

$fh = fopen($file, 'r');
// Run this until we have read the whole file.
while (!feof($fh)) {

    echo fread($fh, 1 * 1024 * 1204);
    ob_flush();
}