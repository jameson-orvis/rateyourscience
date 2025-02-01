
<?php

session_start();

//echo "Thou  hast clicketh le button";

$userid = $_SESSION['user']['id'];
$host = "localhost";
$db = "private_page";
$usr = "root";
$charset = "utf8mb4";
$pwd = "";

$hostdb = "mysql:host=$host;dbname=$db;charset=$charset";

$PDOoptions = [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false,
];	

$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);

if (isset($_POST['font'])) {
	$font = $_POST['font'];

	$qry = "UPDATE users
			SET font = '$font'
			WHERE id = $userid;";

	$stmt = $pdo->prepare($qry);
	$stmt->execute();
}


if (isset($_POST['font_color'])) {
	$font_color = $_POST['font_color'];

	$qry = "UPDATE users
			SET font_color = '$font_color'
			WHERE id = $userid;";

	$stmt = $pdo->prepare($qry);
	$stmt->execute();
}


if (isset($_POST['background_color'])) {
	$background_color = $_POST['background_color'];

	$qry = "UPDATE users
			SET background_color = '$background_color'
			WHERE id = $userid;";

	$stmt = $pdo->prepare($qry);
	$stmt->execute();
}

if (isset($_POST['bio'])) {
	$bio = $_POST['bio'];

	$qry = "UPDATE users
			SET about = ?
			WHERE id = $userid;";

	$stmt = $pdo->prepare($qry);
	$stmt->execute([$bio]);
}


		
?>