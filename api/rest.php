<?php
	include "service.php";

	require_once("Rest.inc.php");

	class API extends REST {
	
		public $data = "";
		private $AUTHORIZED_FUNCTION = array("login", "user", "userUid", "usersUid", "users");

		public function __construct(){
			parent::__construct();				// Init parent contructor
		}

		/*
		 * Public method for access api.
		 * This method dynmically call the method based on the query string
		 *
		 */
		public function processApi(){
			$func = explode('/', trim($_SERVER['PATH_INFO'],'/'))[0];

			$good = false;
			foreach ($this->AUTHORIZED_FUNCTION as $authorizedFunc){
				if($authorizedFunc == $func){
					$good = true;
					break;
				}
			}

			if(! $good) $this->response('', 404);

			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->sendMessage('', 404);
		}

		private function login(){
			if($this->get_request_method() != "POST"){
				$this->sendMessage("POST not detect", 404);
			}
			
			if(! (isset($this->_request['login']) && $this->_request['login'] != "" && isset($this->_request['password']) && $this->_request['password'] != "")){
				log_message("[Rest login] Authentication problem : login and or password empty or not found", SB_LOG_INFO);
				$this->sendMessage("Authentication problem : login and or password empty or not found", 404);
			}

			$login = $this->_request['login'];
			$password = $this->_request['password'];

			$rows = adminConnect($login, $password);
			if(count($rows) == 1){
				$row = $rows[0];

				$time = (int)microtime(true);
				$token = $this->encode($time.'#'.$row["uid"]);

				header('Authorization-Token: '.$token);
				log_message("[Rest login] Connected, need '/userUid/{user_uid}'", SB_LOG_INFO, $row["uid"]);
				$this->sendMessage("Connected, need '/userUid/{user_uid}'", 200);
			}else {
				log_message("[Rest login] Admin not found", SB_LOG_INFO);
				$this->sendMessage("Admin not found", 404);
			}
		}

		private function usersUid(){
			if($this->get_request_method() != "GET"){
				log_message("[Rest users] GET not detect", SB_LOG_INFO);
				$this->sendMessage("GET not detect", 404);
			}

			$uid = $this->verifHeader();

			$rows = allNoActiveUserUid();
			log_message("[Rest users] Users uid list send", SB_LOG_INFO, $uid);
			$this->response($this->json($rows), 200);
		}

		private function user(){
			$uid = $this->verifHeader();

			if($this->get_request_method() == "POST"){
				if(! (isset($this->_request['login']) && $this->_request['login'] != "" && isset($this->_request['email']) && $this->_request['email'] != "" && isset($this->_request['phone']) && $this->_request['phone'] != "" && isset($this->_request['lang']) && $this->_request['lang'] != "")) {
					log_message("[Rest user] Create problem : login, email, phone, lang empty or not found", SB_LOG_INFO, $uid);
					$this->sendMessage("Create problem : login, email, phone, lang empty or not found", 404);
				}

				if(! preg_match("/".EMAIL_REGEX."/", $this->_request['email'])){
					log_message("[Rest user] Create problem : email is not a valide email", SB_LOG_INFO, $uid);
					$this->sendMessage("Create problem : email is not a valide email", 404);
				}
				if(! preg_match("/".PHONE_REGEX."/", $this->_request['phone'])){
					log_message("[Rest user] Create problem : phone is not a valide phone number", SB_LOG_INFO, $uid);
					$this->sendMessage("Create problem : phone is not a valide phone number", 404);
				}

				$langGood = false;
				$langTab = unserialize (LANG_TAB);
				foreach($langTab as $key => $value){
					if($key == $this->_request['lang']){
						$langGood = true;
						break;
					}
				}

				if(! $langGood){
					log_message("[Rest user] Create problem : lang is not a valide lang", SB_LOG_INFO, $uid);
					$this->sendMessage("Create problem : lang is not a valide lang", 404);
				}

				if(availableLogin($this->_request['login'], null)){
					$pwd = generateRandomPassword();
					$userUid = createUser($this->_request['login'], $this->_request['email'], $this->_request['phone'], $pwd, $this->_request['lang'], 1, false);
					$params = [
						"login" => $this->_request['login'],
						"pwd" => $pwd
					];
					sendMail($this->_request['email'], $this->_request['lang'], "create", $params);
					log_message("[Rest user] User $userUid created and email sended", SB_LOG_INFO, $uid);
					$this->sendMessage("User created and email sended", 201);
				}else{
					log_message("[Rest user] This login is not available", SB_LOG_INFO, $uid);
					$this->sendMessage("This login is not available", 404);
				}
			}else{
				log_message("[Rest user] POST not detect", SB_LOG_INFO, $uid);
				$this->sendMessage("POST not detect", 404);
			}
		}

		private function userUid(){
			$uid = $this->verifHeader();

			if($this->get_request_method() == "GET"){
				$userUid = explode('/', trim($_SERVER['PATH_INFO'],'/'))[1];
				if($userUid == ""){
					log_message("[Rest userUid] Need '/userUid/{user_uid}'", SB_LOG_INFO, $uid);
					$this->sendMessage("Need '/userUid/{user_uid}'", 404);
				}else{
					$rows = resultUserUid($userUid);
					if(count($rows) == 1){
						$row = $rows[0];
						if($row['questionnaire_active'] == 0 && $row['result'] != null){
							sqlClose();
							log_message("[Rest userUid] User '".$userUid."' result send", SB_LOG_INFO, $uid);
							$this->response($row['result'], 200);
						}else{
							log_message("[Rest userUid] User '".$userUid."' has not result yet", SB_LOG_INFO, $uid);
							$this->sendMessage("User '".$userUid."' has not result yet", 404);
						}
					}else{
						log_message("[Rest userUid] userUid '".$userUid."' not found", SB_LOG_INFO, $uid);
						$this->sendMessage("User '".$userUid."' not found", 404);
					}
				}
			}else{
				log_message("[Rest userUid] GET not detect", SB_LOG_INFO, $uid);
				$this->sendMessage("GET not detect", 404);
			}
		}

		private function sendMessage($message, $code){
			sqlClose();
			$responce = new stdClass();
			$responce->message = $message;
			$this->response($this->json($responce), $code);
		}

		private function verifHeader(){
			$token = $this->findAuthorizationToken();
			if($token == null){
				log_message("[Rest] Authentication problem", SB_LOG_INFO);
				$this->sendMessage("Authentication problem", 404);
			}

			$tokenDecrypt = $this->decrypt($token);
			$pos = strrpos($tokenDecrypt, "#");
			if($pos === false){
				log_message("[Rest] Authentication problem", SB_LOG_INFO);
				$this->sendMessage("Authentication problem", 404);
			}

			$timeToken = (int)substr($tokenDecrypt, 0, $pos);
			if($timeToken == 0){
				log_message("[Rest] Authentication problem", SB_LOG_INFO);
				$this->sendMessage("Authentication problem", 404);
			}

			$time = (int)microtime(true);
			$timeToken += 3600; //add one hour
			if($timeToken <= $time){
				log_message("[Rest] Authentication timeout", SB_LOG_INFO);
				$this->sendMessage("Authentication timeout", 404);
			}

			return substr($tokenDecrypt, $pos+1);
		}

		private function getKey(){
			$keySize = mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			return substr(TOKEN_SALT, 0, $keySize);
		}

		private function encode($value){
			return base64_encode (mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->getKey(), $value, MCRYPT_MODE_ECB));
		}

		private function decrypt($value){
			return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->getKey(), base64_decode ($value), MCRYPT_MODE_ECB);
		}

		private function findAuthorizationToken(){
			foreach($_SERVER as $name => $value){
				if(substr($name, 0, 5) == 'HTTP_'){
					$name = str_replace(' ', '-', str_replace('_', ' ', substr($name, 5)));
				}
				if(strtolower($name) == "authorization-token") return $value;
			}
			return null;
		}

		private function json($data){
			return json_encode($data);
		}
	}
	
	// Initiiate Library
	$api = new API;
	$api->processApi();
?>