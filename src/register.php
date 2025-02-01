<?php

require_once('userpage.php');
function return_pages() {
    echo '<br> <a href ="registration_paper.html">Create account</a>';
    echo '<br> <a href ="../index.php">Return to home</a>';
}

if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['recaptcha-token'])) {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $recaptchaToken = $_POST['recaptcha-token'];

    $pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);
    $qry = "select name
            from users
            WHERE name = ?";
        
    $stmt = $pdo->prepare($qry);
    $stmt->execute([$username]);
    $results = $stmt->fetchAll();
    
    $nums = count($results);
    echo($nums);
    
    //check inputs
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<br> Invalid email';
        return_pages();
        exit();
    } else if (strlen($username) < 5) {
        echo '<br> Username must be at least 5 characters';
        return_pages();
        exit();
    } else if ($nums > 0) {
        echo '<br> Username is taken';
        return_pages();
        exit();
    } else if (strlen($password) < 6) {
        echo '<br> Password must be at least 6 characters';
        return_pages();
        exit();
    }


    // Google reCAPTCHA secret key
    $secretKey = '6LeFNrMqAAAAAGBKuq-vPF03c4GVvPgM5Qt3InBe';

    // Verify the reCAPTCHA token with Google's API
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaToken
    ];

    // Send POST request to Google reCAPTCHA API
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $responseKeys = json_decode($response, true);

    // Check if CAPTCHA validation passed and if score is above threshold (e.g., 0.5)
    if (isset($responseKeys["success"]) && $responseKeys["success"] === true && $responseKeys["score"] >= 0.5) {
        // Process user registration (add database handling here)
        echo "Registration successful!";

        //Salt and hashing

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

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
        
        $qry = "INSERT INTO users (name, email, password) 
                VALUES (:username, :email, :hashedPassword);";

        $stmt = $pdo->prepare($qry);
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hashedPassword', $hashedPassword);


        $stmt->execute();

        echo "<br>User " . htmlspecialchars($username) . " created!";
    } else {
        echo "reCAPTCHA validation failed. Please try again.";
    }
    echo '<br> <a href ="../index.php">Return to home</a>';

}
?>
