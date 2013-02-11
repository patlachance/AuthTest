<?php
header('Content-type: application/json');

$reset_session = false;
$session_timeout = 600;
// managing session
// need to rethink everything.
session_start();
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
	// last request was more than 30 minutes ago
	session_unset();	 // unset $_SESSION variable for the run-time 
	session_destroy();	// destroy session data in storage
	$reset_session = true;
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

// avoid session fixation attacks
// http://stackoverflow.com/questions/520237/how-do-i-expire-a-php-session-after-30-minutes
if (!isset($_SESSION['CREATED'])) {
	$_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > $session_timeout) {
	// session started more than 30 minutes ago
	session_regenerate_id(true);	// change session ID for the current session an invalidate old session ID
	$_SESSION['CREATED'] = time();  // update creation time
	$reset_session = true;
}

// return error by default
$retArr["status"] = 'error';

$method = $_REQUEST['method'];

// changes characters used in html to their equivalents, for example: < to &gt;
$method = htmlspecialchars($method); 

#if ( ( $need_login == true ) && ( $method !== "login" ) ) {
if ( $reset_session == true ) {
	$retArr["reason"] = "Session expired";
	echo '{"items":' . json_encode($retArr) . '}';
	die;
}

switch ($method) {
	case "login":
		$res = do_login();
		if ( $res == "failure" ) {
			session_unset();	// unset $_SESSION variable for the run-time 
			session_destroy();	// destroy session data in storage
			$retArr["reason"] = "Login failed";
		}
		$retArr["status"] = $res;
		$retArr["sessionid"] = session_id();
		break;
	default:
		echo "Error: method not defined";
		break;
}

echo '{"items":' . json_encode($retArr) . '}';

//
// END
//

//
// functions
//

function do_login() {
	global $retArr;

	include 'config.php';
	$con = mysql_connect($server, $username, $password) or die ("Could not connect: " . mysql_error());
	mysql_select_db($database, $con);

	// retrieve username and password
	$user = htmlspecialchars($_REQUEST['username']); 
	$pass = htmlspecialchars($_REQUEST['password']); 

	// makes sure nobody uses SQL injection
	$user = mysql_real_escape_string($user);
	$pass = mysql_real_escape_string($pass);
	
	$sql = "SELECT * FROM users WHERE `username` LIKE '".$user."' AND `password` LIKE '".$pass."'";

	$result = mysql_query($sql) or die ("Query error: " . mysql_error());
 
	if (mysql_num_rows($result) > 0) {
		return "success";
	}
	else{
		return "failure";
	}
	mysql_close($con);
}



//
//
//

function findintb() {
	$con = mysql_connect($server, $username, $password) or die ("Could not connect: " . mysql_error());
	mysql_select_db($database, $con);

	$sql = "SELECT id, l_name AS name, l_lat AS latitude, l_long AS longitude FROM landmarks ORDER BY l_name";

	$result = mysql_query($sql) or die ("Query error: " . mysql_error());
 
	$records = array();
 
	while($row = mysql_fetch_assoc($result)) {
	$records[] = $row;
	}
 
	mysql_close($con);
 
	echo $_REQUEST['jsoncallback'] . '(' . json_encode($records) . ');';
echo 'true';
}


function check() {
	$query = $_REQUEST['query']; 
	// gets value sent over search form
	 
	$min_length = 3;
	// you can set minimum length of the query if you want
	 
	if(strlen($query) >= $min_length){ // if query length is more or equal minimum length then
		 
		$query = htmlspecialchars($query); 
		// changes characters used in html to their equivalents, for example: < to &gt;
		 
		$query = mysql_real_escape_string($query);
		// makes sure nobody uses SQL injection
		 
		$raw_results = mysql_query("SELECT * FROM articles
			WHERE (`title` LIKE '%".$query."%') OR (`text` LIKE '%".$query."%')") or die(mysql_error());
			 
		// * means that it selects all fields, you can also write: `id`, `title`, `text`
		// articles is the name of our table
		 
		// '%$query%' is what we're looking for, % means anything, for example if $query is Hello
		// it will match "hello", "Hello man", "gogohello", if you want exact match use `title`='$query'
		// or if you want to match just full word so "gogohello" is out use '% $query %' ...OR ... '$query %' ... OR ... '% $query'
		 
		if(mysql_num_rows($raw_results) > 0){ // if one or more rows are returned do following
			 
			while($results = mysql_fetch_array($raw_results)){
			// $results = mysql_fetch_array($raw_results) puts data from database into array, while it's valid it does the loop
			 
				echo "<p><h3>".$results['title']."</h3>".$results['text']."</p>";
				// posts results gotten from database(title and text) you can also show id ($results['id'])
			}
			 
		}
		else{ // if there is no matching rows do following
			echo "No results";
		}
		 
	}
	else{ // if query length is less than minimum
		echo "Minimum length is ".$min_length;
	}

}
?>
