<?php
session_start();




include("class.mail.php");

define('UPLOADS', 'C:\Users\karim\Documents\uploads');
define('MAX_SIZE', 10000000);
$errors = array();

function newError($str) {
	global $errors;
	array_push($errors, $str);
}

if(empty($_SESSION['last_cleaned']) || @$_SESSION['last_cleaned'] < (time() - 60 * 5)) {
	$_SESSION['last_cleaned'] = time();
	$_SESSION['send_ops'] = null;
} else {
	$_SESSION['send_ops'] = $_SESSION['send_ops'] + 1;
	if($_SESSION['send_ops'] >= 3) {
		newError("%times%");
	}
}

	//print_r($_POST);
	//print_r($_FILES);
/*
*	form validation
*/
if(empty($_POST['mail']) || empty($_POST['recipients'])) {
	newError("%all-fields%");
} elseif(empty($_POST['tos'])) {
	newError("%tos%");
} elseif(empty($_FILES['files']['name'][0])) {
	newError("%nofile%");
}



if(count($errors) < 1) {

	/*
	*	validate files
	*/

	## total file size. 
	$tSize = 0;
	foreach($_FILES['files']['size'] as $size) {
		$tSize = $tSize + $size;
	}
	if($tSize >= MAX_SIZE) {
		newError("%fileSize%");
	}

	## create database records

	$dbh = new PDO('mysql:host=127.0.0.1;dbname=mail', 'root', null);
	$access = hash('sha384', openssl_random_pseudo_bytes(69).time().time()-rand(0, 9999999).'DFKAj34234a');
	$q = $dbh->prepare("INSERT INTO `cases` (`id`, `date`, `ip`, `recipient`, `mail`, `accesskey`) VALUES (NULL, UNIX_TIMESTAMP(), :ip, :recipient, :mail, :access);");
	$q->execute(array(
		':ip' => $_SERVER['REMOTE_ADDR'],
		':recipient' => $_POST['recipients'],
		':mail' => $_POST['mail'],
		':access' => $access
	));
	$caseId = $dbh->lastInsertId();

	$numfiles = count($_FILES['files']['name']);
	$i = -1;
	for($i = 0; $i < $numfiles; $i++) {
		$x = $i;
		$ext = pathinfo($_FILES['files']['name'][$x], PATHINFO_EXTENSION);
		$file = $_FILES['files']['tmp_name'][$x];
		$q = $dbh->prepare("INSERT INTO `files` (`id`, `case_id`, `file_size`, `name`, `sha1`, `original`) VALUES (NULL, :case, :size, :name, :sha1, :original);");
		$q->execute(array(
			':case' => $caseId,
			':size' => filesize($file),
			':name' => sha1_file($file).".".$ext,
			':sha1' => sha1_file($file),
			':original' => $_FILES['files']['name'][$x]
		));
		
		move_uploaded_file($file, UPLOADS."/".sha1_file($file).".".$ext);
	}

	$mail = new Mail();
	$body = file_get_contents('mail/index.html');
	$body = str_replace('<!--sys_link-->', "http://filemailer.net/download/".$access, $body);
	$mail->setBody($body);
	$mail->send($_POST['recipients'], '');

}

/*
*	Error report and index.html
*/
if(count($errors) >= 1) {
	$errorMsg = array(
		"%all-fields%" => "Please fill in all fields. ",
		"%tos%" => "You must agree to the Terms of Service. ",
		"%fileSize%" => "Upload size of ".MAX_SIZE."MB has been exceeded. ",
		"%nofile%" => "Please select at least one file. ",
		"%times%" => "Please wait 3 minutes before uploading another file. "
	);

	$msg = null;
	foreach($errors as $error) {
		$msg .= "<b>Oh snap!</b> ".$errorMsg[$error]. "<br>";
	}

	$alert = '<div class="alert alert-danger" role="alert">'.$msg.'</div>';

	$pageData = file_get_contents('index.html');
	$pageData = str_replace('<!--sys_error-->', $alert, $pageData);

} else {
	$alert = '<div class="alert alert-success" role="alert"><b>Yey!</b> We have sent your files succesfully. </div>';

	$pageData = file_get_contents('index.html');
	$pageData = str_replace('<!--sys_error-->', $alert, $pageData);

}
echo $pageData;