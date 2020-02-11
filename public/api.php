<?php

 // Allow from any origin
 if (isset($_SERVER['HTTP_ORIGIN'])) {
	header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

	exit(0);
}
	
	
require_once('../vendor/autoload.php');
use \Firebase\JWT\JWT;
define('SECRET_KEY','Super-Secret-Key');  // secret key can be a random string and keep in secret from anyone
define('ALGORITHM','HS256');   // Algorithm used to sign the token



$postdata = file_get_contents("php://input");
$request = json_decode($postdata);


$action = $request->action;


// Login section
if ($action == 'login') {
	
	$email = $request->email;
	$password = $request->password;	

		//A dummy credential match.. you should have some SQl queries to match from databases
		if($email == "dar@gmail.com" && $password == "123456")
		{
			$iat = time(); // time of token issued at
			$nbf = $iat + 10; //not before in seconds
			$exp = $iat + 60; // expire time of token in seconds

			$token = array(
				"iss" => "http://localhost",
				"aud" => "http://localhost",
				"iat" => $iat,
				"nbf" => $nbf,
				"exp" => $exp,
				"data" => array(
						"email" => $email
				)
			);

			http_response_code(200);

			$jwt = JWT::encode($token, SECRET_KEY);
		
			$data_insert=array(
				'access_token' => $jwt,									
				'time' => time(),
				'email' => $email, 
				'status' => "success",
				'message' => "Successfully Logged In"
			);
			
			
		}else{
			$data_insert=array(
				"data" => "0",
				"status" => "invalid",
				"message" => "Invalid Request"
			);	
		}	

}
// Get Dashboard stuff
else if($action == 'stuff'){
	$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
	$temp_header = explode(" ", $authHeader);
	$jwt = $temp_header[1];
	
    try {
		JWT::$leeway = 10;
		$decoded = JWT::decode($jwt, SECRET_KEY, array(ALGORITHM));
        // Access is granted. Add code of the operation here 

		$data_from_server = '[{"Name":"Margot Robbie","Rate":"60%","Sales":"639","Date":"11/2"},
		{"Name":"Ewan McGregor","Rate":"23%","Sales":"235","Date":"12/2"},
		{"Name":"Mary Elizabeth Winstead","Rate":"79%","Sales":"728","Date":"13/2"},
		{"Name":"Jurnee Smollett","Rate":"18%","Sales":"135","Date":"14/2"}]';
		
		$data_insert=array(
			"data" => json_decode($data_from_server),
			"status" => "success",
			"message" => "Request authorized",
		);	

    }catch (Exception $e){

		http_response_code(401);

		$data_insert=array(
			//"data" => $data_from_server,
			"jwt" => $jwt,
			"status" => "error",
			"message" => $e->getMessage()
		);
		
	}	
}

echo json_encode($data_insert);
?>
