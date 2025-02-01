
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
	


if (isset($_POST['rating'])) {
	$rating = $_POST['rating'];
}

$paperid = $_SESSION['paperid'];

$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);

$qry = "DELETE 
		FROM paper_collection 
		WHERE paperid = '$paperid' and userid = '$userid';";
		
//echo $qry;	
$stmt = $pdo->prepare($qry);
$stmt->execute();

$qry = "INSERT INTO paper_collection (userid, paperid, ingestion_date, rating) 
		VALUES ('$userid', '$paperid', CURRENT_TIMESTAMP, '$rating');";
		
$stmt = $pdo->prepare($qry);
$stmt->execute();

echo $rating;
		
?>