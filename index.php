<?php
session_start();
require_once('config.php');
require_once('src/userpage.php');
require_once('src/Parsedown.php');

if (isset($_GET['logout'])) {
    logout();
}

echo "<head><script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'>
	  </script></head>";

	  
 //Login page

if (isset($_POST['login'])) {
	$username = $_POST['username'];

	//$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	//$email = strtolower($email);
	$password = strip_tags($_POST['password']);
	
	if(empty($username)) {
		$errorMsg[] = 'You must enter a username to login';
	} elseif (empty($password)) {
		$errorMsg[] = "You must enter a password to login";
	} elseif (strlen($password) < 5) {
		$errorMsg[] = "Your password must be at least 5 characters.";
	}
	
	//Valid input
	else{
		$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);
		$qry = "SELECT * FROM users WHERE name = ?";
		$stmt = $pdo->prepare($qry);
		$stmt->execute([$username]);
		$row = $stmt->fetch();
		
		if($stmt->rowCount() > 0) {
			if(password_verify($password, $row['password'])){
				$_SESSION['user']['email'] = $row['email'];
                $_SESSION['user']['name'] = $row['name'];
                $_SESSION['user']['id'] = $row['id'];
			
				$errorMsg[] = "You are now logged in";
			}else{
				$errorMsg[] = $password;
			}
		} else {
			$errorMsg[] = "PASSWORD OR EMAIL IS INVALID";
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<body>

<style>
    * {
        font-family: 'Helvetica', sans-serif;
    }

    big {
    	font-size: 20px;
    	font-weight: bold;
    }
	body {
		background-color: #010823;
		color: #e8f2f8; 
	}	
	a {
		color: #e8f2f8;
	}	
	.button_link {
		color: #e8f2f8;
	}
	input, textarea, button {
		background-color: #010823;
		color: #e8f2f8
	}
	.third { float: left; } 
	.rating { width: 15% }
	.comment-feed { width: 60% }
	.rating-feed { width: 25% } 
	.footer {
            position: absolute;
            bottom: 0px;
        }
	table {
		width: 100%; /* Make the table take up the full width of its container */
		max-width: 1200px; /* Set the maximum width of the table */
		/* border-collapse: collapse; /* Optional: Make borders collapse */
	}
	td {
		word-wrap: break-word; /* Forces text wrapping */
	}
	.wide-column {
		max-width: 400px
	}
	.narrow-column {
		max-width: 150px
	}
	.column {
		width: 40%
	}
	.centered {
		text-align: center
	}
    .header {
    		position: absolute;
    		top: 0px;
    	}
	body {
		position: relative;
	}
    .top-right {
            position: absolute;
            top: 0;
            right: 0;
        }
	.button_link {
		background: none!important;
		border: none;
		padding: 0!important;
		/*optional*/
		/*input has OS specific font-family*/
		text-decoration: underline;
		cursor: pointer;
	}
	.container {
      display: flex; /* Use Flexbox to arrange the divs in a row */
      justify-content: space-between; /* Distribute the columns with space in between */
    }
</style>

<form action="" method="get">
	<input type="search" name="search_input" placeholder="Search">

	<button type="search" class="search" name="search_button">
		Search
	</button>



<script>
	
	// Save the relative scroll position to localStorage before the page reload
	window.onbeforeunload = function() {
		const scrollPosition = window.scrollY;
		const docHeight = document.documentElement.scrollHeight - window.innerHeight;
		const scrollPercentage = scrollPosition / docHeight;
		localStorage.setItem("scrollPosition", scrollPercentage);
	};

	// On page load, scroll back to the saved relative position
	window.onload = function() {
		const scrollPercentage = localStorage.getItem("scrollPosition");
		if (scrollPercentage !== null) {
			const docHeight = document.documentElement.scrollHeight - window.innerHeight;
			const newScrollPosition = scrollPercentage * docHeight;
			window.scrollTo(0, newScrollPosition);
			localStorage.removeItem("scrollPosition"); // Clear it after scrolling
		}
	};

//Vanity comment
</script>


<?php


///Header section

if (isset($_SESSION['user'])) { //

	//Settings link as well
	echo '<body> <div class="top-right"> 
		<a href ="?settings=1">Settings</a> |  
		<a href ="?logout=1">Logout</a> 
	</div> </body>';

	$style_text = query_user_style($_SESSION['user']['id']);
	echo $style_text[0];
	
	
} else {
	echo '</form> <body> <div class="top-right">';
		generate_login();
	echo '</div> </body>';
}

//Create link back to self page if session is active
if (isset($_SESSION['user'])) {
	$username = $_SESSION['user']['name'];
	echo '      <a href =?user=' . $username . '>' . $username . '</a>';
}

echo '</form>';


if (isset($_GET['settings'])) {
	generate_settings_form($_SESSION['user']['id']);
}

if (isset($_GET['add-paper'])) {
	generate_manual_paper_add_form();
}

if (isset($_GET['create-account'])) {
	header("Location: src/registration_paper.html");
}


//Populate search results
if (isset($_GET['search_button'])) {
	$search_string = $_GET['search_input'];
	generate_search_results($search_string);
}

if (isset($_GET['tag'])) {
	$tag = $_GET['tag'];
	generate_tag_page($tag);
}


///The homepage
if (empty($_GET) | isset($_GET['logout'])) {
    echo '<marquee behavior="alternate"> <big class="centered"> Welcome to the unnamed academic paper rating website </big> </marquee>';
	echo '<br> <br> <br> <br> <br>';
	echo '<big> Recent ratings </big>';
	echo '<br> <br>';
	generate_sample_feed();
} 


?>

<p><?php 
//Visiting a userpage
	if (isset($_GET['user'])):
		$username = $_GET['user'];
		generate_userpage($username) ;
		endif;?>
</p>


<p><?php
//Visiting a paper page
	if(isset($_GET['paperid'])) {
		$paperid = $_GET['paperid'];
		generate_paper_page($paperid);
	}
?>
</p>
		


</body>

<div class="footer">

<?php 

//Login status



?>

</div>

</html>