

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
	

if (isset($_POST['textInput'])) {
	$textInput = $_POST['textInput'];
}

$paperid = $_SESSION['paperid'];
$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);


$qry = "INSERT INTO comments (userid, paperid, comment_text, comment_time) 
		VALUES (:userid, :paperid, :textInput, CURRENT_TIMESTAMP)";

$stmt = $pdo->prepare($qry);
$stmt->bindParam(':userid', $userid);
$stmt->bindParam(':paperid', $paperid);
$stmt->bindParam(':textInput', $textInput);

$stmt->execute();


		
?>