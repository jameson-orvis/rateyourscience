

<?php

session_start();

//echo "Thou  hast clicketh le button";


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
	$textInput = strtolower($textInput);
}

if (isset($_POST['userid'])) {
	$userid = $_POST['userid'];
}

$paperid = $_SESSION['paperid'];
$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);

$qry = "DELETE 
		FROM paper_tags
		WHERE paperid = '$paperid' and userid = '$userid';";
		
//echo $qry;	
$stmt = $pdo->prepare($qry);
$stmt->execute();

$qry = "INSERT INTO paper_tags (userid, paperid, tag_date, tag) 
		VALUES (:userid, :paperid, CURRENT_TIMESTAMP, :textInput);";

$stmt = $pdo->prepare($qry);

$stmt->bindParam(':userid', $userid);
$stmt->bindParam(':paperid', $paperid);
$stmt->bindParam(':textInput', $textInput);
$stmt->execute();


		
?>