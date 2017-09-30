# Your RESTFUL API
<?php
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
//$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
//$input = file_get_contents('php://input'); // for stuff coming from stdin
//$input = json_decode(file_get_contents('php://input'),true); // for stuff coming from stdin
parse_str(file_get_contents('php://input'), $input);

$reply = array();

$conn_string="host=REMOVED.ca dbname=REMOVED user=REMOVED password=PASSWORD";

$dbconn = pg_connect($conn_string);

switch ($method) {
  //Putting user information into the database
	case 'PUT':

		if($input["type"] == 0){
			$md5_pwd = md5($input["userpass"]);
			$query = pg_prepare($dbconn, "myQuery1", "INSERT INTO wwuserinfo (username, userpass, firstname, lastname, email) VALUES ($1, $2, $3, $4, $5)") ;

			$query = pg_execute($dbconn, "myQuery1", array($input["id"], $md5_pwd, $input["firstname"], $input["lastname"], $input["email"]));

			// Populate the reply array with the number of rows affected by the query
			$reply["id"] = pg_affected_rows($query);

			if($reply["id"] > 0){
				header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
				//header("HTTP/1.1 code message");
			}else{
				header($_SERVER["SERVER_PROTOCOL"]." 404 NOT FOUND");
			}
		}elseif ($input["type"] == 1){

			$md5_pwd = md5($input["userpass"]);

			$query = pg_prepare($dbconn, "myQuery1", "INSERT INTO highscore (uid, score) VALUES 
				((SELECT uid from wwuserinfo where username=$1 AND userpass=$2), $3)");

			$query = pg_execute($dbconn, "myQuery1", array($input["userid"], $md5_pwd, $input["currentScore"]));

			// Populate the reply array with the number of rows affected by the query
			$reply["id"] = pg_affected_rows($query);
		}
		break;

	case 'GET':
		//$username = $_REQUEST["id"];
		//$userpass = $_REQUEST["userpass"];

		if($_REQUEST["type"] == 0){

			$md5_pwd = md5($_REQUEST["userpass"]);

			// If there's a user, count will give 1, otherwise it will give 0
			$query = pg_prepare($dbconn, "myQuery1", "SELECT COUNT(*) from wwuserinfo where username=$1 AND userpass=$2") ;

			$query = pg_execute($dbconn, "myQuery1", array($_REQUEST["id"], $md5_pwd));

			$row = pg_fetch_row($query);

			$reply["id"] = $row[0];

			//We found the user in database.
			if($reply["id"] > 0){
				header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
			}else{
			//We didn't find the user in database.
				header($_SERVER["SERVER_PROTOCOL"]." 404 NOT FOUND");
			}		
		}elseif($_REQUEST["type"] == 1){
			$md5_pwd = md5($_REQUEST["userpass"]);

			// If there's a user, count will give 1, otherwise it will give 0
			$query = pg_prepare($dbconn, "myQuery1", "SELECT firstname, lastname, email from wwuserinfo where username=$1 AND userpass=$2") ;

			$query = pg_execute($dbconn, "myQuery1", array($_REQUEST["id"], $md5_pwd));

			$row = pg_fetch_row($query);

			$reply["firstname"] = $row[0];
			$reply["lastname"] = $row[1];
			$reply["email"] = $row[2];

			//We found the user in database.
			header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
		}elseif($_REQUEST["type"] == 2){
			$query = pg_prepare($dbconn, "myQuery1", "SELECT username, score from highscore natural join wwuserinfo order by score desc limit 10");
			$query = pg_execute($dbconn, "myQuery1", array());
			$rows = pg_fetch_all($query);
			$reply["output"] = $rows;
			
			header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
		}
		break;

	case 'POST':

		$md5_pwd = md5($_REQUEST["userpass"]);
		
		$query = pg_prepare($dbconn, "myQuery1", "UPDATE wwuserinfo SET userpass=$1, firstname=$2, lastname=$3, email=$4 WHERE username=$5");

		$query = pg_execute($dbconn, "myQuery1", array($md5_pwd, $_REQUEST["firstname"], $_REQUEST["lastname"], $_REQUEST["email"], $_REQUEST["username"]));

		//"UPDATE wwuser SET password=$1, name=$2, email=$3 WHERE username = $4"
		header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
		break;

	case 'DELETE':
		// pg_close($dbconn);

		$md5_pwd = md5($input["userpass"]);
		
		$query1 = pg_prepare($dbconn, "myQuery1", "DELETE from highscore where uid=(SELECT uid from wwuserinfo WHERE username=$1 AND userpass=$2)");

		$query1 = pg_execute($dbconn, "myQuery1", array($input["username"], $md5_pwd));

		$query2 = pg_prepare($dbconn, "myQuery2", "DELETE from wwuserinfo where username=$1 AND userpass=$2");

		$query2 = pg_execute($dbconn, "myQuery2", array($input["username"], $md5_pwd));

		header($_SERVER["SERVER_PROTOCOL"]." 200 OK");

		$reply["id"] = "DELETED";
		break;
}

pg_close($dbconn);
print json_encode($reply);
?>
