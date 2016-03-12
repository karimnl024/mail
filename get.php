<?php
	if(empty($_GET['a'])) {
		die("An error has occurred");
	}

	$dbh = new PDO('mysql:host=127.0.0.1;dbname=mail', 'root', null);
	$q = $dbh->prepare("SELECT `name`, `original`, files.id, cases.accesskey FROM `cases`, `files` WHERE cases.id = files.case_id AND cases.accesskey = :access");
	$q->execute(array(
		':access' => $_GET['a']
	));

	if($q->rowCount() <= 0) {
		die("An error has occurred");
	}

	$files = $q->fetchAll(PDO::FETCH_ASSOC);
?>
<html>
	<head>
		<title>Download your files </title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
		<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css">
		<link rel="stylesheet" href="../common.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
		<script src="http://code.jquery.com/jquery-2.2.1.min.js"></script>
		<script>
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
			$('.app_send_box').css('width', '100%');
		}
		</script>
	</head>

	<body>

		<div class="app_send_box" style="width: 400px">
			<div class="simpleBox">
				<h1>Download files</h1>
				<table class="table table-condensed">
				    <thead>
				        <tr>
				            <th style="width: 25px;">#</th>
				            <th>Filename</th>
				            <th style="width: 50px"></th>
				        </tr>
				    </thead>
				    <tbody>
				    	<?php
				    	$i = 0;
				    	foreach($files as $file) {
				    		$i++;
				    	?>
				        <tr>
				            <th ="row"><?php echo $i; ?></th>
				            <td><?php echo substr($file['original'], 0, 25); ?></td>
				            <td><a href="../download.php?id=<?php echo $file['id']; ?>&access=<?php echo $file['accesskey']; ?>"><i class="fa fa-download"></i></a></td>
				        </tr>
				        <?php
				    	}
				        ?>
				        <tr>
				            <th ="row"></th>
				            <td>Download all files (.zip)</td>
				            <td><a href="../downloadall.php?access=<?php echo $file['accesskey']; ?>"><i class="fa fa-download"></i></a></td>
				        </tr>
				    </tbody>
				</table>		

			</div>
		</div>

	</body>
</html>