<html lang="en">
<body>

<?php

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

//Generates table to display
//$start_index
//$number_of_rows
function generate_table($results, $headers, $fields, $number_of_rows = 50, $class = 'default', $sorting = FALSE, $num_authors_limit=3, $abbreviated=FALSE) {
	echo '<table border="1">
          <thead>
          <tr>';
		  
	//stop if there's no results
	if (count($results) == 0) {
		echo '<br>';
		echo 'No results';
		return;
	}
	
	
	for($i = 0, $count = count($headers);$i<$count;$i++) {
		$field = $fields[$i];
		$header = $headers[$i];

		if ($sorting == TRUE) {
			echo '<td> 
					<form method="post" action="" id="postForm' . $field . '" style="display:inline;">';
						if(isset($_POST['sort_type'])) {
							if(isset($_POST['order'])) {
								if($_POST['order'] == 'DESC') {
									echo '<input type="hidden" name="order" value="ASC">';
								} else {
									echo '<input type="hidden" name="order" value="DESC">';
								}
							} else {
								echo '<input type="hidden" name="order" value="ASC">';
							}
						}
						echo '<input type="hidden" name="sort_type" value="' . $field . '">
							  <input type="hidden" name="sort_class" value="' . $class . '">
							  <button type="submit" class="button_link">' . $header . '</button> </td>
						      </form>';
		} else {
			
			echo '<td>' . $header . '</td>';
		}
				//<a href="#" onclick="document.getElementById("postForm' . $field . '").submit();">' . $header . '</a> </td>';
	}
	
	
	echo '</tr>
          </thead>
          <tbody>';

	$table_size = sizeof($results);

//handle pagination

	
	if(isset($_POST[$class])){
		$start_index = $_POST[$class];
	} else {
		$start_index = 0;
	}

//Add sorting logic here

	//array_multisort(array_column($results, 'rating'), SORT_ASC, $results);
	if(isset($_POST['sort_type']) & isset($_POST['sort_class'])) {
		if($_POST['sort_class'] == $class) {
			$sort_by = $_POST['sort_type'];
			if(isset($_POST['order'])) {
				if($_POST['order'] == 'ASC') {
					array_multisort(array_column($results, $sort_by), SORT_ASC, $results);
				} else {
					array_multisort(array_column($results, $sort_by), SORT_DESC, $results);
				}
			} else {
				array_multisort(array_column($results, $sort_by), SORT_DESC, $results);
			}
		}
	}

	//echo '<br>';

	$final_index = $start_index + $number_of_rows;
	if($final_index > $table_size) {
		$final_index = $table_size;
	}

//Parsedown object
	$Parsedown = new Parsedown();
	$Parsedown->setBreaksEnabled(true);
	$Parsedown->setSafeMode(true);

	for($i = $start_index; $i<$final_index; $i++) {
		echo '<tr>';
		$row = $results[$i];
		foreach ($fields as $field) {
			if ($field == 'title') {
				if($abbreviated == FALSE){
					echo '<td class="wide-column"> <a href=?paperid=' . $row['paperid'] . '>' .  htmlspecialchars($row[$field]) . '</a> </td>';
				} else {
					echo '<td class="wide-column"> <a href=?paperid=' . $row['paperid'] . '>' .  substr(htmlspecialchars($row[$field]), 0, 30) . '...' . '</a> </td>';
				}
			} elseif ($field == 'doi') {
				if (str_contains($row[$field], 'arXiv'))  {
					echo '<td> <a href=https://arxiv.org/abs/' . $row[$field] . '>' . htmlspecialchars($row[$field]) .'</a> </td>';
				} else if (filter_var($row[$field], FILTER_VALIDATE_URL)) {
					echo '<td> <a href=' . $row[$field] . '>' . htmlspecialchars($row[$field]) .'</a> </td>';
				} else {
					echo '<td> <a href=https://doi.org/' . $row[$field] . '>' . htmlspecialchars($row[$field]) .'</a> </td>';
				}


			} elseif ($field == 'authors') {
				$num_authors = $row['num_authors'];
				if ($num_authors > $num_authors_limit) {
					echo '<td class="wide-column">' . htmlspecialchars($row[$field]) . ', et al.' . '</td>';
				} else {
					echo '<td class="wide-column">' . htmlspecialchars($row[$field]) . '</td>';
				}
			} elseif ($field == 'name' | $field == 'commenter_name') {
				echo '<td> <a href=?user=' . htmlspecialchars($row[$field]) . '>' . htmlspecialchars($row[$field]) . '</a> </td>';
			} elseif ($field == 'comment_text') {
				echo '<td class="wide-column">' . $Parsedown->text($row[$field]) . '</td>';
			} elseif ($field=='tag' & !is_null($row[$field])) {
				echo '<td> <a href=?tag=' . rawurlencode($row[$field]) . '>' . htmlspecialchars($row[$field]) . '</a> </td>';
			} elseif (is_null($row[$field])) {
				echo '<td> </td>';
			} else {
				echo '<td>' . htmlspecialchars($row[$field]) . '</td>';
			}
			
		}
		echo '</tr>';
	}
	echo '</tbody>
	</table>';

	$new_start = ($start_index + $number_of_rows);

	$previous_start = $start_index - $number_of_rows;
	if($previous_start < 0) {
		$previous_start = 0;
	}

	echo '<div>';
	if($new_start < $table_size) {
		echo '<form method="post" action="" style="display:inline;">
   				<input type="hidden" name="' . $class . '" value="' . $new_start . '">'; 
				if(isset($_POST['sort_type'])) {
					echo '<input type="hidden" name="sort_type" value="' . $_POST["sort_type"] . '">';
				}
				if(isset($_POST['order'])) {
					echo '<input type="hidden" name="order" value="' . $_POST["order"] . '">';
				}
    			echo '<button type="submit">Next page</button>
			  </form>';
		
	}
	if ($previous_start < $start_index) {
		echo '<form method="post" action="" style="display:inline;">
			    <input type="hidden" name="' . $class . '" value="' . $previous_start . '">'; 
				if(isset($_POST['sort_type'])) {
					echo '<input type="hidden" name="sort_type" value="' . $_POST["sort_type"] . '">';
				}
				if(isset($_POST['order'])) {
					echo '<input type="hidden" name="order" value="' . $_POST["order"] . '">';
				}
		   	    echo '<button type="submit">Previous page</button>
		      </form>';
	}

	if ($number_of_rows < $table_size) {
		echo 'Page ' . round($start_index / $number_of_rows) + 1 . ' / ' . floor($table_size / $number_of_rows) + 1;
	}

	echo '</div>';
}


function generate_rating_feed($paperid) {
	global $host;
	global $db;
	global $usr;
	global $charset;
	global $pwd;
	global $hostdb;

	$PDOoptions = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];	
	
	$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);

	$qry = "select name, rating, ingestion_date, tag
		FROM paper_collection
		JOIN users ON paper_collection.userid = users.id 
		LEFT JOIN paper_tags on paper_collection.paperid = paper_tags.paperid
		WHERE paper_collection.paperid = ?
		ORDER BY ingestion_date DESC";

	$stmt = $pdo->prepare($qry);
	$stmt->execute([$paperid]);
	$results = $stmt->fetchAll();
	
	$tableSize = sizeof($results);

	if($tableSize > 0){
		echo 'Recent ratings <br>';
		generate_table($results, ['username','rating','timestamp', 'tag'], ['name','rating','ingestion_date', 'tag'], $number_of_rows=20, $class='rating-feed', $sorting=TRUE);
	} else {
		echo 'No ratings yet <br>';
	}
}

function generate_userpage($username) {
	global $host;
	global $db;
	global $usr;
	global $charset;
	global $pwd;
	global $hostdb;

	$PDOoptions = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];	
	$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);
	

	$qry = "SELECT name, title, journal, pubdate, rating, ingestion_date as full_date, CAST(ingestion_date as DATE) as ingestion_date, papers.paperid, LENGTH(authors) - LENGTH(REPLACE(authors, ',', '')) + 1 as num_authors,
		REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(authors, ',', 3), '\"', ''), '[', ''), ']', '') as authors, ptag1.tag, CAST(tag_date as DATE) as tag_date
		FROM paper_collection
		JOIN users ON paper_collection.userid = users.id 
		JOIN papers ON paper_collection.paperid = papers.paperid
		LEFT JOIN paper_tags ptag1 on papers.paperid = ptag1.paperid 
		WHERE name = ?
		UNION
		SELECT name, title, journal, pubdate, rating, ingestion_date as full_date, CAST(ingestion_date as DATE) as ingestion_date, papers.paperid, LENGTH(authors) - LENGTH(REPLACE(authors, ',', '')) + 1 as num_authors,
		REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(authors, ',', 3), '\"', ''), '[', ''), ']', '') as authors, tag, CAST(tag_date as DATE) as tag_date
		FROM paper_tags
		JOIN users ON paper_tags.userid = users.id 
		JOIN papers ON paper_tags.paperid = papers.paperid
		LEFT JOIN paper_collection pc on papers.paperid = pc.paperid 
		WHERE name = ?
		ORDER BY full_date DESC;
		";
	$stmt = $pdo->prepare($qry);
	$stmt->execute([$username, $username]);
	$results = $stmt->fetchAll();

	
	echo '<big>' . $username . '</big>';
	echo '<br> <br>';

	generate_table($results, ['Title','Journal','Publication Date','Rating','Rating Date', 'Authors', 'tag', 'Tag date'], ['title','journal','pubdate','rating','ingestion_date', 'authors', 'tag', 'tag_date'], $number_of_rows = 10, $class='ratings-page', $sorting=TRUE, $num_authors_limit = 3);

	$qry = "SELECT about
		FROM users
		WHERE name = ?";

	$stmt = $pdo->prepare($qry);
	$stmt->execute([$username]);
	$about_results = $stmt->fetchAll();

	$Parsedown = new Parsedown();
	$Parsedown->setBreaksEnabled(true);
	$Parsedown->setSafeMode(true);
	
	$about_txt = $about_results[0]['about'];

	if (!is_null($about_txt)) {
		echo '<br> <br> <big> Bio </big>';
		echo $Parsedown->text($about_txt);
	}

	echo '<br> <div class="container"> <div class="wide-column">';

	echo '<big>' . $username . '\'s recent comments' . '</big>';

	$qry = "SELECT comment_text, comment_time, papers.title, papers.paperid
		FROM comments
		JOIN users ON users.id = comments.userid
		JOIN papers ON comments.paperid = papers.paperid
		WHERE name = ?
		ORDER BY comment_time DESC";
	$stmt = $pdo->prepare($qry);
	$stmt->execute([$username]);
	$results = $stmt->fetchAll();
	
	

	generate_table($results, ['Comment', 'Comment time', 'Paper'], ['comment_text', 'comment_time', 'title'], $number_of_rows = 10, $class='comments-feed', $sorting=TRUE, $num_authors_limit = 3, $abbreviated=TRUE);
	echo '</div> <div class="column">';

	generate_user_comments($username);
	echo '</span>';


}


function generate_search_results($search_string_input) {
	global $host;
	global $db;
	global $usr;
	global $charset;
	global $pwd;
	global $hostdb;

	$PDOoptions = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];	
	
	$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);

	//Let's take the case where
	
	
	$search_string_trimmed = trim($search_string_input);
	$search_string_trimmed = strtolower($search_string_trimmed);

	//three conditions that you are a doi string
	if(str_contains($search_string_trimmed, 'doi:') | str_contains($search_string_trimmed, 'doi.org') | (substr($search_string_trimmed, 0, 3) == '10.') | str_contains($search_string_trimmed, 'arxiv.')) {

		$search_string_trimmed = str_replace(' ', '', $search_string_trimmed);
		$search_string = preg_replace('#https?://doi\.org/#', '', $search_string_trimmed);
		$search_string = preg_replace('#doi\.org/#', '', $search_string);
		$search_string = preg_replace('#doi:#', '', $search_string);

		if(str_contains($search_string_trimmed, 'arxiv')) {
			$search_string = substr($search_string, (strpos($search_string, 'arxiv.')));
		}

		$qry = "select paperid, title, pubdate, doi, journal, publisher, LENGTH(authors) - LENGTH(REPLACE(authors, ',', '')) + 1 as num_authors,
			REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(authors, ',', 3), '\"', ''), '[', ''), ']', '') as authors from papers
			WHERE doi = ?";

		$stmt = $pdo->prepare($qry);
		$stmt->execute([$search_string]);
		$results = $stmt->fetchAll();
		
		if (count($results) > 0) {
			generate_table($results, ['Title','Journal','doi','Publication Date', 'authors'], ['title','journal','doi','pubdate', 'authors']);
		} else {		

			//handle arxiv papers
			if(str_contains($search_string_trimmed, 'arxiv')) {
				$arxiv_id = substr($search_string, strpos($search_string, 'arxiv.') + 6);

				$url = 'http://export.arxiv.org/api/query?id_list=' . $arxiv_id;
				$response = file_get_contents($url);
				$data = simplexml_load_string($response);

				$entry = $data->entry;

				$authors = [];
				foreach ($entry->author as $author) {
					//echo $author;
					$auth = $author->name;
					//$authors[] = $auth[0];
					array_push($authors, (string)$auth);
				}
				$num_authors = count($authors);
				//print_r($authors);
				$title = $entry->title;
				$doi = 'arxiv.' . $arxiv_id;
				
				$date_array = date_parse($entry->published);
				$publishedDate = $date_array['year'] . '-' . $date_array['month'] . '-' . $date_array['day'];

				//VALUES ('$title', '$publishedDate', '$doi', '$authors_json', '$journalTitle', '$publisher');";	
				$journalTitle = 'arxiv';
				$publisher = 'none';	

			} else { //For non arxiv papers that just have a normal doi that works
				$url = "https://api.crossref.org/works/" . $search_string;
				// Use file_get_contents or cURL to fetch data from the CrossRef API
				// Using file_get_contents for simplicity (make sure allow_url_fopen is enabled in php.ini)
				$response = file_get_contents($url);

				if ($response === FALSE) {
					return "Error fetching DOI metadata.";
				}

				// Decode the JSON response
				$data = json_decode($response, true);
				
				
				if (isset($data['message']['title'][0])) {
					$title = $data['message']['title'][0];
				} else {
					return "Error: No title found for DOI: $doi";
				}

				// Get additional metadata (e.g., authors, publisher, publication date)
				$authors = [];
				if (isset($data['message']['author'])) {
					foreach ($data['message']['author'] as $author) {
						$authors[] = $author['given'] . ' ' . $author['family'];  // Full name of the author
					}
				}

				$publisher = isset($data['message']['publisher']) ? $data['message']['publisher'] : 'Not available';
				//$publishedDate = isset($data['message']['published']['date-parts'][0][0]) ? $data['message']['published']['date-parts'][0][0] : 'Not available';
				$journalTitle = isset($data['message']['container-title'][0]) ? $data['message']['container-title'][0] : 'Not available';
				
				if (isset($data['message']['published']['date-parts'][0][2])) {
					$publishedDate = $data['message']['published']['date-parts'][0][0] . '-' . $data['message']['published']['date-parts'][0][1] . '-' . $data['message']['published']['date-parts'][0][2];
				} else { 
					$publishedDate = $data['message']['published']['date-parts'][0][0] . '-' . $data['message']['published']['date-parts'][0][1] . '-1';
				}

				$doi = $data['message']['DOI'];

			}
			
			$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);
			$authors_json = json_encode($authors);
			
			$qry = "INSERT INTO papers (title, pubdate, doi, authors, journal, publisher) 
					VALUES ('$title', '$publishedDate', '$doi', '$authors_json', '$journalTitle', '$publisher');";
			
			$stmt = $pdo->prepare($qry);
			$stmt->execute();
			

			//$qry = "select paperid from papers where doi = ?";
			
			//$stmt = $pdo->prepare($qry);
			//$stmt->execute([$doi]);
			
			//$resultid = $stmt->fetchAll();
			//$paperid = $resultid[0]['paperid'];

			//$results = [
			//	['title' => $title, 'journal' => $journalTitle, 'doi' => $doi, 'pubdate' => $publishedDate, 'authors' => $authors_json, 'paperid' => $paperid, 'num_authors' => $num_authors]
			//];

			//generate_table($results, ['Title','Journal','doi','Publication Date', 'authors'], ['title','journal','doi','pubdate', 'authors'], $number_of_rows=1, $class='search_result', $sorting=FALSE);
			
			generate_search_results($search_string_input);
		}
	} else {
		////Generic search feature

		$qry = "SELECT paperid, title, journal, doi, pubdate,
				LENGTH(authors) - LENGTH(REPLACE(authors, ',', '')) + 1 as num_authors, 
				REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(authors, ',', 3), '\"', ''), '[', ''), ']', '') as authors from papers
				WHERE LOWER(title) LIKE ? OR 
				LOWER(authors) LIKE ? OR
				LOWER(journal) LIKE ?";

		$stmt = $pdo->prepare($qry);

		$stmt->bindValue(1, '%' . $search_string_trimmed . '%', PDO::PARAM_STR);
		$stmt->bindValue(2, '%' . $search_string_trimmed . '%', PDO::PARAM_STR);
		$stmt->bindValue(3, '%' . $search_string_trimmed . '%', PDO::PARAM_STR);

		$stmt->execute();
		$results = $stmt->fetchAll();

		generate_table($results, ['Title','Journal','doi','Publication Date', 'authors'], ['title','journal','doi','pubdate', 'authors'], $number_of_rows=20, $class='search_results', $sorting=TRUE);


	}

	if(isset($_SESSION['user']['id'])) {
		echo '<br><br> Want to add something without a doi? <a href ="?add-paper=1" class="create_acct">Add it manually</a>';
	}

}
	
function generate_manual_paper_add_form() {
	
	echo '<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			</head>
			<body>
				<h1>Submit a paper or a piece of written media</h1>
				<form action="/submit" method="POST" id="paper-submission">
					<!-- Title Field -->
					<label for="title-input">Title:</label><br>
					<input type="text" id="title-input" name="title-input" required><br><br>

					<!-- Journal Field -->
					<label for="journal">Journal / Publication:</label><br>
					<input type="text" id="journal" name="journal" required><br><br>

					<!-- Publication Date Field -->
					<label for="pubdate">Publication Date:</label><br>
					<input type="date" id="pubdate" name="pubdate" required><br><br>

					<!-- DOI Field -->
					<label for="doi">DOI / Link:</label><br>
					<input type="text" id="doi" name="doi" required><br><br>

					<!-- Authors Field -->
					<label for="authors-container">Authors:</label><br>
					<div id="authors-container">
						<input type="text" name="authors[]" placeholder="Author Name" required><br><br>
					</div>
					<button type="button" id="add-author">Add Another Author</button><br><br>

					<!-- Submit Button -->
					</form>
				<br> <div id="errorContainer"></div> <br>
				<input type="checkbox" id="promise">
				<label for="promise">I promise that this is a real written article and not something which I made up. </label> <br> <br>
				
				<button type="submit" id="submit">Submit</button>


				<script>
					// Add a new input field for authors when the "Add Another Author" button is clicked
					document.getElementById("add-author").addEventListener("click", function() {
						var newAuthorField = document.createElement("input");
						newAuthorField.type = "text";
						newAuthorField.name = "authors[]";
						newAuthorField.placeholder = "Author Name";
						newAuthorField.required = true;

						var br = document.createElement("br");
						document.getElementById("authors-container").appendChild(newAuthorField);
						document.getElementById("authors-container").appendChild(br);
					});
				</script>';

					
			echo   "
					<script type='text/javascript'>
					$('#submit').click(function() {
						event.preventDefault();  // Prevent normal form submission

						const checkbox = document.getElementById('promise');
						if (!(checkbox.checked)) {
							const errorContainer = document.getElementById('errorContainer');
							console.log(errorContainer);
          					errorContainer.innerText = 'Check the box!!';
							return;
						}


            			// Aggregate all authors into an array
						const authors = Array.from(document.querySelectorAll('input[name=\"authors[]\"]')).map(input => input.value);
						console.log(authors);
						let myform = document.getElementById('paper-submission');
						let fd = new FormData(myform);
						// Prepare data for the AJAX request
						const formData = new FormData();

						formData.append('title', document.getElementById('title-input').value);
						formData.append('journal', document.getElementById('journal').value);
						formData.append('pubdate', document.getElementById('pubdate').value);
						formData.append('doi', document.getElementById('doi').value);

						console.log(document.getElementById('title-input').value);
						console.log(document.getElementById('journal').value);
						console.log(document.getElementById('pubdate').value);
						console.log(document.getElementById('doi').value);

						// Add all authors to the FormData object
						authors.forEach((author, index) => {
							formData.append('authors[]', author);
						});
						console.log(authors);

						console.log(formData);
						// AJAX request to send the rating to the PHP script
						$.ajax({
							url: 'src/submit_paper.php',  // The PHP script that handles the rating submission
							method: 'POST',
							data: formData,
							processData: false, // Important! Don't process the data
      						contentType: false, //
							success: function(response) {
								console.log(response); 
								const paperid = parseInt(response);
								console.log(paperid);
								if (Number.isInteger(paperid)) {
									location.href = '?paperid=' + paperid;
								} else {
								 	errorContainer.innerText = 'Error submitting. Make sure every field is properly filled out!';
								}				
							},
							error: function (xhr, ajaxOptions, thrownError) {
								const errorContainer = document.getElementById('errorContainer');
								console.log(thrownError.message);
          						errorContainer.appendChild(document.createTextNode(error.message));
							}
						});
					});
					</script>


			</body>

		  ";



}
	


function generate_paper_page($paperid) {
	global $host;
	global $db;
	global $usr;
	global $charset;
	global $pwd;
	global $hostdb;

	$PDOoptions = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];	
	
	$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);
	$qry = "select paperid, title, pubdate, doi, journal, publisher, 
		LENGTH(authors) - LENGTH(REPLACE(authors, ',', '')) + 1 as num_authors, 
		REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(authors, ',', 20), '\"', ''), '[', ''), ']', '') as authors from papers
		WHERE paperid = ?";

	
	$stmt = $pdo->prepare($qry);
	$stmt->execute([$paperid]);

	$results = $stmt->fetchAll();
	echo '<big>' . $results[0]['title'] . '</big>';

	//$results = $stmt->fetchAll();	
	generate_table($results, ['Journal','doi','Publication Date', 'authors'], ['journal','doi','pubdate', 'authors'], $number_of_rows=1, $class='paper', $sorting=FALSE, $num_authors_limit=20);
	$_SESSION['paperid'] = $paperid;

	if(isset($_SESSION['user']['id'])) {
		$userid = $_SESSION['user']['id'];

		//Do query to get user's current rating
		$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);
		$qry = "select paper_collection.userid, paper_collection.paperid, rating, tag
				FROM paper_collection
				LEFT JOIN paper_tags ON paper_tags.paperid = paper_collection.paperid
				WHERE paper_collection.paperid = " . $paperid . " AND paper_collection.userid = " . $userid;
		
		$stmt = $pdo->prepare($qry);
		$stmt -> execute();

		$results = $stmt->fetch();
		if (is_array($results)) {
			$number_rating = $results['rating'];
			$tag = $results['tag'];
		} else {
			$number_rating = 0;
			$tag = '';
		}

		//$number_rating = $results['rating'];

		echo '

		<br> 
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Star Rating</title>
			<style>
			.star-rating {
				display: flex;
				cursor: pointer;
			}
			.star {
				font-size: 40px;
				color: #ccc; <!-- Default gray color for unselected stars  -->
			}
			.star:hover,
			.star.selected {
				color: gold; <!-- Gold color when hovered or selected -->
			}
			</style>
		</head>
		<body>


		<div class="row">  <!-- Handle three column logic. This is topmost div -->
		  	<div class="third rating">




			<div id="rating-form" style="width: 500px;">
			Rate this paper
			<br>
			<div class="star-rating">
				<img src="bin/left_star_small.png" class="star left" data-value=0.5, clicked=FALSE>
				<img src="bin/right_star_small.png" class="star right" data-value=1, clicked=FALSE>
				<img src="bin/left_star_small.png" class="star left" data-value=1.5, clicked=FALSE>
				<img src="bin/right_star_small.png" class="star right" data-value=2, clicked=FALSE>
				<img src="bin/left_star_small.png" class="star left" data-value=2.5, clicked=FALSE>
				<img src="bin/right_star_small.png" class="star right" data-value=3, clicked=FALSE>
				<img src="bin/left_star_small.png" class="star left" data-value=3.5, clicked=FALSE>
				<img src="bin/right_star_small.png" class="star right" data-value=4, clicked=FALSE>
				<img src="bin/left_star_small.png" class="star left" data-value=4.5, clicked=FALSE>
				<img src="bin/right_star_small.png" class="star right" data-value=5, clicked=FALSE>
				<button id="submit-rating" hidden>Submit Rating</button>
			</div>
			<div id="rating-message"><br>


			</div></div>
			
			
			<form action="" method="post">
				<input type="tag" name="tag_input" id="tag_input" placeholder="'. $tag . '"> <br>
				<button type="tag" class="tag" name="tag_button" id="tag_button">
					Tag paper
				</button> 
			</form>
			';

		
		echo "
			<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'>
			</script>
			<script type='text/javascript'>
			function removeLastComma(str) {
				return str.replace(/,(\s+)?$/, '');   
			}
			var ratingValue = " . $number_rating . ";
			$(document).ready(function() {

				console.log(ratingValue);
				$('.star').each(function() {

					
					var thisValue = $(this).data('value')
					console.log(thisValue);
					thisRating = removeLastComma(thisValue);
					console.log(thisRating);

					if(thisRating <= ratingValue) {
						console.log(this.src);

						console.log($(this).attr('clicked'));

						this.setAttribute('clicked', 'TRUE');
						if($(this).attr('class') == 'star left'){
							this.src = 'bin/left_star_yellow.png'
						} else {
							this.src = 'bin/right_star_yellow.png'
						}
					} else if ((thisRating >= ratingValue) & ($(this).attr('clicked') == 'TRUE')) {
						if($(this).attr('class') == 'star left'){
							this.src = 'bin/left_star_small.png'
						} else {
							this.src = 'bin/right_star_small.png'
						}
					}
				});

				// Hover effect to highlight stars
				// Hover effect to highlight stars
				$('.star').hover(function() {
					var ratingValue = $(this).data('value');
					$('.star').each(function() {
						if($(this).data('value') <= ratingValue) {
							console.log(this.src);
							console.log($(this).attr('clicked'));
							
							if($(this).attr('class') == 'star left'){
								this.src = 'bin/left_star_yellow.png'
							} else {
								this.src = 'bin/right_star_yellow.png'
							}
						} else if (($(this).data('value') >= ratingValue) & ($(this).attr('clicked') == 'TRUE')) {
							if($(this).attr('class') == 'star left'){
								this.src = 'bin/left_star_small.png'
							} else {
								this.src = 'bin/right_star_small.png'
							}
						}
						//$(this).toggleClass('selected', $(this).data('value') <= ratingValue);
					});
				}, function() {
					$('.star').each(function() {
						console.log(selectedRating);
						if(($(this).attr('clicked') == 'FALSE')) {
							if($(this).attr('class') == 'star left'){
								this.src = 'bin/left_star_small.png'
							} else {
								this.src = 'bin/right_star_small.png'
							}
						} else if(($(this).attr('clicked') == 'TRUE')) {
							if($(this).attr('class') == 'star left'){
								this.src = 'bin/left_star_yellow.png'
							} else {
								this.src = 'bin/right_star_yellow.png'
							}
						}
					});
				});


				$('.star').click(function() {
					selectedRating = $(this).data('value');
					var ratingValue = $(this).data('value');
					console.log(ratingValue);
					document.getElementById('submit-rating').click();

					$('.star').each(function() {
						if ($(this).data('value') <= ratingValue) {	
							console.log($(this).data('value'))
							this.setAttribute('clicked', 'TRUE');
							//add highlight
							if($(this).attr('class') == 'star left'){
								this.src = 'bin/left_star_yellow.png'
							} else {
								this.src = 'bin/right_star_yellow.png'
							}
						} else {
							this.setAttribute('clicked', 'FALSE');
						
							//remove highlight
							if($(this).attr('class') == 'star left'){
								this.src = 'bin/left_star_small.png'
							} else {
								this.src = 'bin/right_star_small.png'
							}
						}
					});
				});

				// Handle form submission
				$('#submit-rating').click(function() {
					console.log(selectedRating);
					function removeLastComma(str) {
						return str.replace(/,(\s+)?$/, '');   
					}
					
					selectedRating = removeLastComma(selectedRating);
					if (selectedRating === 0) {
						$('#rating-message').text('Please select a rating.');
						return;
					}

					// AJAX request to send the rating to the PHP script
					$.ajax({
						url: 'src/rate.php',  // The PHP script that handles the rating submission
						method: 'POST',
						data: {
							rating: selectedRating  // Pass the selected rating to PHP
						},
						success: function(response) {
							$('#rating-message').text(response);  // Display the server response
						},
						error: function() {
							$('#rating-message').text('An error occurred while submitting your rating.');
						}
					});
				});
			});
			</script>
			</div>
			";

		//Trigger click to prime rating
		echo "<script>
			  var selectedRating = 2.5;
			  const elements = document.querySelectorAll('[data-value]'); 
			  console.log(elements);
			  elements.forEach(image => {
				  const value = parseFloat(image.getAttribute('data-value'), 10);
				  if (value === 2) {
				  	console.log(image);
				  	image.click();
				  }
				});
			  </script>
			";

/////Tagging system

		echo "
			</form>
			<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'>
			</script>
			<script type='text/javascript'>
			$('#tag_button').click(function() {
				var tag_text = document.getElementById('tag_input');
				console.log(tag_text.value);
				
				if (tag_text.value.length <= 1) {
					return;
				}

				// AJAX request to send the rating to the PHP script
				$.ajax({
					url: 'src/tag_paper.php',  // The PHP script that handles the rating submission
					method: 'POST',
					data: {
						textInput: tag_text.value,
						userid: $userid  // Pass the selected rating to PHP
					}, 
					success: function(response) {

					}
				});
			});
			</script>";
	}
		
	//goes if logged in or not

	echo '<div class="third comment-feed" style="margin-right: 0">';

	generate_comments($paperid);

	echo '</div> <!-- column two -->

			<div class="third rating-feed"> <!-- column three -->';
	
	generate_rating_feed($paperid);

	echo '</div>
	</div>';

	
}

function generate_account_creation_page() {
	echo '
	<!DOCTYPE html>
	<html lang="en">
	<head>
	    <meta charset="UTF-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <title>Create Account</title>

	    <!-- Google reCAPTCHA v3 -->
	    <script src="https://www.google.com/recaptcha/api.js?render=6LeFNrMqAAAAAIp6oL9MYk5Paf0Vz18D58gHHoZy"></script>

	    <script>
	        // This function will be called when the form is submitted
	        function onSubmit(token) {
	            document.getElementById("recaptcha-token").value = token;
	            document.getElementById("registration-form").submit();
	        }
	    </script>
	</head>
	<body>
	    <h1>Create User Account</h1>

	    <form id="registration-form" action="src/register.php" method="POST">
	        <label for="username">Username:</label>
	        <input type="text" id="username" name="username" required><br><br>

	        <label for="email">Email:</label>
	        <input type="email" id="email" name="email" required><br><br>

	        <label for="password">Password:</label>
	        <input type="password" id="password" name="password" required><br><br>

	        <!-- Hidden field to store the reCAPTCHA token -->
	        <input type="hidden" id="recaptcha-token" name="recaptcha-token">

	        <button type="button" onclick="grecaptcha.execute(\'6LeFNrMqAAAAAIp6oL9MYk5Paf0Vz18D58gHHoZy\', {action: \'submit\'}).then(onSubmit);">Register</button>
	    </form>
	</body>
	</html>

';

}

function generate_login() {
	if (!isset($_SESSION['user'])) {
		//echo '<div class="header">';	
		echo '<form action="" method="post">
	
			<input type="username" name="username" placeholder="Enter your username">
			<input type="password" name="password" placeholder="Enter your password">
		
			<button type="Submit" class="login_btn" name="login">
				Login
			</button>
			
			<a href ="?create-account=1" class="create_acct">Create account</a>

		</form>';
	}

}


function generate_comments($paperid) {
	global $host;
	global $db;
	global $usr;
	global $charset;
	global $pwd;
	global $hostdb;

	$PDOoptions = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];	
	
	$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);

	$qry = "select userid, comment_text, comment_time, name
		FROM comments
		JOIN users ON comments.userid = users.id 
		WHERE paperid = ?
		ORDER BY comment_time DESC";
		
	$stmt = $pdo->prepare($qry);
	$stmt->execute([$paperid]);
	$results = $stmt->fetchAll();

	$tableSize = sizeof($results);
	if($tableSize > 0){
		echo 'Comments <br>';
		generate_table($results, ['username','comment','timestamp'], ['name','comment_text','comment_time'], $number_of_rows=10, $class='comment-box', $sorting=TRUE);
	} else {
		echo 'No comments yet <br>';
	}
	
	//Generate a box to leave a comment if user session
	if(isset($_SESSION['user']['id'])) {
		echo '<textarea id="comment"> </textarea>
			  <button id="submit-comment">Submit Comment</button>';
	}

	//handle comment 
	echo "
	<script type='text/javascript'>
	$('#submit-comment').click(function() {
		const textarea = document.getElementById('comment');
		console.log(textarea.value);
		
		if (textarea.value.length <= 1) {
			return;
		}

		// AJAX request to send the rating to the PHP script
		$.ajax({
			url: 'src/comment.php',  // The PHP script that handles the rating submission
			method: 'POST',
			data: {
				textInput: textarea.value  // Pass the selected rating to PHP
			}, 
			success: function(response) {
				location.reload();
			}
		});
	});

	</script>";

}

function generate_settings_form($userid){

	$user_style = query_user_style($userid);
	$font = $user_style[3];

	if (isset($_SESSION['user'])) {

		echo ' <style>
		.helvet {
			font-family: "Helvetica", sans-serif !important
		} 
        .georg {
			font-family: "Georgia" !important
		} 
        .papyr {
            font-family: "Papyrus" !important
        }
		.cour {
            font-family: "Courier New" !important
        }
        </style>
        ';

		echo '
		<big style="font-size=30px"> <em> Settings! </em> </big>
		<br> <br>
		<div class="container">
			<div class="column">
			<big> Font choice </big>
			<form method="post" action="" id="settingsForm">
				<input type="radio" id="helvetica" name="font" value="Helvetica" ' ;

			if($font == "Helvetica") echo 'checked="checked"';
			echo '>';
			echo '<label for="helvetica" class="helvet">Helvetica</label><br>
			
				<input type="radio" id="georgia" name="font" value="Georgia" ';
				if($font == "Georgia") echo 'checked="checked"';
			echo '>';

			echo '<label for="georgia" class="georg">Georgia</label><br>
				  <input type="radio" id="papyrus" name="font" value="Papyrus"';
				  if($font == "Papyrus") echo 'checked="checked"';
			echo '>';

			echo '<label for="papyrus" class="papyr">Papyrus</label><br>
				  <input type="radio" id="courier-new" name="font" value="Courier New"';
				  if($font == "Courier New") echo 'checked="checked"';
			echo '>';

			echo '<label for="courier-new" class="cour">Courier New</label><br>';

			echo '
			</div>
			<div class="column">
				<big> Color selection </big> <br>
					<input type="color" id="background_color" name="background_color" value="' . $user_style[2] . '" />
  					<label for="background_color">Background color</label> <br>
					<input type="color" id="font_color" name="font_color" value="'. $user_style[1] . '" />
  					<label for="font_color">Font color</label>
			</div> 

			<div class="column">
				<big> Write a bio about yourself </big>
				<textarea id="bio">' . $user_style[4] . ' </textarea>
			</div>
		</div>

		<br>
		</form>
		<button type="submit" id="submit-settings"> Save settings </button>
		';
		echo '
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		
		<script>

		$("#submit-settings").click(function() {
			console.log("asdfasdf");
			event.preventDefault();

			
			var font = $("input[name=font]:checked", "#settingsForm").val();
			var font_color = $("#font_color").val();
			var background_color = $("#background_color").val();
			var bio = $("#bio").val();
			console.log(font);
			console.log(font_color);
			console.log(background_color);
			console.log(bio);

			// AJAX request to send the rating to the PHP script
			$.ajax({
				url: "src/settings_update.php",  // The PHP script that handles the rating submission
				method: "POST",
				data: {
					font: font,
					font_color: font_color,
					background_color: background_color,
					bio: bio
				},
				success: function(response) {
					location.reload();  // Display the server response
				},
				error: function() {
					console.log();
				}
			});

			location.reload();
		});
		</script>';
	}


}


function query_user_style($userid) {
	global $host;
	global $db;
	global $usr;
	global $charset;
	global $pwd;
	global $hostdb;

	$PDOoptions = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];	
	
	$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);
	$qry = "select id, background_color, font, font_color, about
			FROM users
			WHERE id = ?";
	
	$stmt = $pdo->prepare($qry);
	$stmt->execute([$userid]);
	$results = $stmt->fetchAll();

	$background_color = $results[0]['background_color'];
	$font = $results[0]['font'];
	$font_color = $results[0]['font_color'];

	$bio = $results[0]['about'];

	$style_text = "<style>
		* {
			font-family: '" . $font . "';
		}
		body {
			background-color: " . $background_color . ";
			color: " . $font_color . "; 
		}	
		a {
			color: " . $font_color . ";
		}	
		.button_link {
			color: " . $font_color . ";
		}
		input, textarea, button {
 			background-color: $background_color;
			color: $font_color

		}
		
		</style>";

	return array($style_text, $font_color, $background_color, $font, $bio);

}

///Yeah yeah okay this is duplicating code sure whatever
function generate_user_comments($username) {
	global $host;
	global $db;
	global $usr;
	global $charset;
	global $pwd;
	global $hostdb;

	$PDOoptions = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];	
	
	$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);

	$qry = "select commenter_id, comment, commentee_id, timestamp, uer.name as commenter_name
		FROM user_comments
		JOIN users uee ON user_comments.commentee_id = uee.id
		JOIN users uer ON user_comments.commenter_id = uer.id
		WHERE uee.name = ?
		ORDER BY timestamp DESC";
		
	$stmt = $pdo->prepare($qry);
	$stmt->execute([$username]);
	$results = $stmt->fetchAll();

	$tableSize = sizeof($results);
	if($tableSize > 0){
		echo 'Comments <br>';
		generate_table($results, ['Commenter','comment','timestamp'], ['commenter_name','comment','timestamp'], $number_of_rows=10, $class='user-comment-box');
	} else {
		echo 'No comments yet <br>';
	}
	
	//Generate a box to leave a comment if user session
	if(isset($_SESSION['user']['id'])) {
		echo '<textarea id="user-comment"> </textarea>
			  <button id="submit-comment">Submit Comment</button>';
	}


	$commentee_name = $_GET['user'];
	//handle comment 

	echo "
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'>
	</script>
	<script type='text/javascript'>
	$('#submit-comment').click(function() {
		const textarea = document.getElementById('user-comment');
		var commentee_name = '$commentee_name';

		console.log(textarea.value);
		

		if (textarea.value.length <= 1) {
			return;
		}

		// AJAX request to send the rating to the PHP script
		$.ajax({
			url: 'src/user_comment.php',  // The PHP script that handles the rating submission
			method: 'POST',
			data: {
				textInput: textarea.value,
				commentee_name: commentee_name
			}, 
			success: function(response) {
				location.reload(); 
			}
		});
	});

	</script>";

}


function generate_tag_page($tag) {
	global $host;
	global $db;
	global $usr;
	global $charset;
	global $pwd;
	global $hostdb;

	$PDOoptions = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];	
	
	$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);

	$qry = "select users.name, papers.title, papers.doi, papers.paperid, tag_date, rating, tag
		FROM paper_tags
		JOIN users ON paper_tags.userid = users.id
		JOIN papers ON paper_tags.paperid = papers.paperid
		LEFT JOIN paper_collection ON paper_tags.userid = paper_collection.userid AND paper_collection.paperid = papers.paperid
		WHERE paper_tags.tag = ?
		ORDER BY tag_date DESC";
		
	$stmt = $pdo->prepare($qry);
	$stmt->execute([$tag]);
	$results = $stmt->fetchAll();

	$tableSize = sizeof($results);
	if($tableSize > 0){
		echo '<big> Recent uses of "' . $tag .'" </big> <br>';
		generate_table($results, ['Username', 'Title', 'Tag date', 'Rating', 'Tag'], ['name','title','tag_date', 'rating', 'tag'], $number_of_rows=10, $class='tag-feed', $sorting=TRUE);
	} else {
		echo 'No tags yet <br>';
	}

	echo '<br>';

	$qry = "select ROUND(AVG(rating), 2) as agg_rating, papers.title as title, paper_collection.paperid, tag
		FROM paper_tags
        JOIN papers on papers.paperid = paper_tags.paperid
        JOIN paper_collection on papers.paperid = paper_collection.paperid
		WHERE paper_tags.tag = ?
        GROUP BY papers.paperid, tag
		ORDER BY agg_rating DESC;";
		
	$stmt = $pdo->prepare($qry);
	$stmt->execute([$tag]);
	$results_agg = $stmt->fetchAll();

	$tableSize = sizeof($results);
	if($tableSize > 0){
		echo '<big> Top papers </big> <br>';
		generate_table($results_agg, ['Title', 'Mean rating', 'Tag'], ['title','agg_rating', 'tag'], $number_of_rows=10, $class='tag-agg-feed', $sorting=TRUE);
	} else {
		echo 'No tags yet <br>';
	}
	
	//Generate a box to leave a comment if user session
}

function generate_sample_feed() {
	global $host;
	global $db;
	global $usr;
	global $charset;
	global $pwd;
	global $hostdb;

	$PDOoptions = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];	
	
	$pdo = new PDO($hostdb, $usr, $pwd, $PDOoptions);

	$qry = "select users.name, papers.title, ingestion_date, paper_collection.paperid, paper_collection.rating 
		FROM paper_collection
		JOIN users ON paper_collection.userid = users.id
		JOIN papers ON paper_collection.paperid = papers.paperid
		ORDER BY ingestion_date DESC
		LIMIT 100";

	$stmt = $pdo->prepare($qry);
	$stmt->execute();
	$results = $stmt->fetchAll();

	generate_table($results, ['Username', 'Title', 'Rating Date', 'Rating'], ['name','title', 'ingestion_date', 'rating'], $number_of_rows=25, $class='frontpage', $sorting=TRUE);
}




?>

</body>
</html>