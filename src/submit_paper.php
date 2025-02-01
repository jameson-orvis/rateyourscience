

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
$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);


if(isset($_POST['doi'])){
	$doi = $_POST['doi'];
	if(!filter_var($doi, FILTER_VALIDATE_URL)) {
		throw new Exception('Not a URL');
	}
} else {
	exit;
}

if (isset($_POST['title'])) {
	$title = $_POST['title'];
} else {
	throw new Exception('Title is blank');
	exit;
}

if (isset($_POST['journal'])) {
	$journal = $_POST['journal'];
} else {
	throw new Exception('Journal is blank');
	exit;
}

if (isset($_POST['pubdate'])) {
	$pubdate = $_POST['pubdate'];
} else {
	throw new Exception('Date is blank');
	exit;
}


if (isset($_POST['authors'])) {
	$authors = $_POST['authors'];
	$authors_json = json_encode($authors);
} else {
	throw new Exception('Authors are blank');
	exit;
}

if (isset($_SESSION['user']['id'])) {
	$submitter_id = $_SESSION['user']['id'];
}

$qry = "INSERT INTO papers (title, journal, pubdate, doi, authors, submitter_id, publisher) 
		VALUES (:title, :journal, :pubdate, :doi, :authors_json, :submitter_id, 'none');";

$stmt = $pdo->prepare($qry);
        
$stmt->bindParam(':title', $title);
$stmt->bindParam(':journal', $journal);
$stmt->bindParam(':pubdate', $pubdate);
$stmt->bindParam(':doi', $doi);
$stmt->bindParam(':authors_json', $authors_json);
$stmt->bindParam(':submitter_id', $submitter_id);

$stmt->execute();

$qry = "SELECT paperid from papers where doi = ?";

$stmt = $pdo->prepare($qry);
$stmt->execute([$doi]);

$results = $stmt->fetchAll();
echo $results[0]['paperid'];

		
?>