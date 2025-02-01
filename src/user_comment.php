

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
}

if (isset($_POST['commentee_name'])) {
	$commentee_name = $_POST['commentee_name'];
}

$commenter_id = $_SESSION['user']['id'];

echo 'wtfwtfwtfw';
$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);

$qry = "INSERT INTO user_comments (commentee_id, commenter_id, comment, timestamp) 
		SELECT id, :commenter_id, :textInput, CURRENT_TIMESTAMP
		FROM users
		where name = :commentee_name
		LIMIT 1;";
		
$stmt = $pdo->prepare($qry);

$stmt->bindParam(':commenter_id', $commenter_id);
$stmt->bindParam(':textInput', $textInput);
$stmt->bindParam(':commentee_name', $commentee_name);


$stmt->execute();


		
?>