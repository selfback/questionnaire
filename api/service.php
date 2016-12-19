<?php
	include "mysql.php";
	include "multilingual/mail.php";

	session_start();
	date_default_timezone_set(TIMEZONE);

	$NAVIGATION_PAGES = unserialize (PAGES);
	$MANNEQUIN_LANG = DEFAULT_LANG;

	function logout(){
		log_message("User logout -> ".SELFBACK_ROOT);
		header('location:'.SELFBACK_ROOT);
		session_unset();
		session_destroy();
		exit();
	}

	if(isset($_GET['logout']) && $_GET['logout'] == "1"){
		logout();
	}

	if(isset($_SESSION['user_lang'])){
		$lang = $_SESSION['user_lang'];
		loadLanguage(isset($adminPage) ? $adminPage : false, $lang, $MANNEQUIN_LANG);
	}

	function get_client_ip() {
	    $ipaddress = '';
	    if (isset($_SERVER['HTTP_CLIENT_IP']))
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if(isset($_SERVER['REMOTE_ADDR']))
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}

	function log_message($message, $type=SB_LOG_INFO, $uid="UNKNOWN") {
		$now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
		$local = $now->setTimeZone(new DateTimeZone(TIMEZONE));
		if(isset($_SESSION['user_uid'])){
			$uid = $_SESSION['user_uid'];
		}
	    error_log("\n".$local->format("[Y-m-d H:i:s.u]")." [client ".get_client_ip()."] [".$type."] [".$uid."] ".$message, 3, INFO_LOG_PATH.INFO_LOG_FILENAME);
	    if($type == SB_LOG_ERROR){
	    	error_log($message);
	    }
	}

	function fatal_error($message){
		global $adminPage;
		log_message($message, SB_LOG_ERROR);
		$errorPage = ((isset($adminPage) ? $adminPage : false) ? "../" : "")."error.php";
		header('location:'.$errorPage);
		exit();
	}

	function loadLanguage($adminPage, $lang, &$mannequinLang){
		include(($adminPage ? "../" : "")."api/multilingual/".DEFAULT_LANG.".php");

		$finalLangTab = ${"arrayLang_" . DEFAULT_LANG};

		if($lang != DEFAULT_LANG && file_exists("api/multilingual/".$lang.".php")){
			include(($adminPage ? "../" : "")."api/multilingual/".$lang.".php");
			foreach (${"arrayLang_" . $lang} as $key => $value) {
				$finalLangTab[$key] = $value;
			}
		}

		foreach ($finalLangTab as $key => $value) {
			define($key, $value);
		}

		if($lang != DEFAULT_LANG && file_exists("img/mannequin_".$lang.".png")){
			$mannequinLang = $lang;
		}
	}

	function userConnect($login, $pwd){
		$escapedLogin = getRealEscapeString($login);

		$hashedPwd = hash('sha256', PASSWORD_SALT.$pwd);

		$result = sqlQuery("SELECT id, questionnaire_active, questionnaire_admin, lang, uid FROM user WHERE username = '$escapedLogin' AND password = '$hashedPwd'");

		if(count($result) == 1){
			$row = $result[0];
			updateConnectionDate($row['id']);
		}

		return $result;
	};

	function createUser($login, $email, $phone, $pwd, $lang, $actif, $log=true){
		$escapedLogin = getRealEscapeString($login);
		$escapedEmail = getRealEscapeString($email);
		$escapedPhone = getRealEscapeString($phone);
		$escapedLang = getRealEscapeString($lang);

		$hashedPwd = hash('sha256', PASSWORD_SALT.$pwd);
		$time = date ("Y-m-d H:i:s", time());

		$uid = uidGenerator();

		sqlUpdate("INSERT INTO user (username, password, uid, questionnaire_active, email, date_email_send, phone, lang) values ('$escapedLogin', '$hashedPwd', '$uid', $actif, '$escapedEmail', ".(PROD_MODE ? "'$time'" : "NULL").", '$escapedPhone', '$escapedLang');");
		sqlUpdate("INSERT INTO user_authority (username, authority) VALUES ('$escapedLogin', '".USER_ROLE. "')");
		if($log){
			log_message("User $uid was created");
		}
		return $uid;
	}

	function updateUser($userId, $login, $email, $phone, $actif, $pwd, $lang){
		$escapedLogin = getRealEscapeString($login);
		$escapedEmail = getRealEscapeString($email);
		$escapedPhone = getRealEscapeString($phone);
		$escapedLang = getRealEscapeString($lang);
		$hashedPwd = null;

		if($pwd != null){
			$hashedPwd = hash('sha256', PASSWORD_SALT.$pwd);
		}

		$query = "UPDATE user SET username = '$escapedLogin', email = '$escapedEmail', questionnaire_active = $actif, phone = '$escapedPhone', lang = '$escapedLang'";
		if($hashedPwd != null){
			$query .= ", password = '$hashedPwd'";
		}
		$query .= " WHERE id = $userId";

		$result = sqlQuery("SELECT username FROM user WHERE id = $userId");
		$loginModif = getRealEscapeString($result[0]["username"]) != $escapedLogin;
		if($loginModif){
			sqlUpdate("DELETE FROM user_authority WHERE username = '".getRealEscapeString($result[0]["username"])."'");
		}
		sqlUpdate($query);
		if($loginModif){
			sqlUpdate("INSERT INTO user_authority (username, authority) VALUES ('$escapedLogin', '".USER_ROLE. "')");
		}

		$uid = getUserUid($userId);
		log_message("User $uid was updated");
		return $uid;
	}

	function loadResult($userId){
		$result = sqlQuery("SELECT result FROM user WHERE id = $userId");
		if(count($result) == 1){
			return $result[0]["result"];
		}else{
			return null;
		}
	}

	function insertResult($userId, $result){
		$escapedResult = getRealEscapeString($result);
		$query = "UPDATE user SET result ='$escapedResult' WHERE id = $userId";
		sqlUpdate($query);
	}

	function insertFinalResult($userId, $result){
		$escapedResult = getRealEscapeString($result);
		$query = "UPDATE user SET result ='$escapedResult', questionnaire_active = 0 WHERE id = $userId";
		sqlUpdate($query);
	}

	function adminConnect($login, $pwd){
		$escapedLogin = getRealEscapeString($login);

		$hashedPwd = hash('sha256', PASSWORD_SALT.$pwd);

		$result = sqlQuery("SELECT id, uid FROM user WHERE username = '$escapedLogin' AND password = '$hashedPwd' AND questionnaire_admin = 1");

		if(count($result) == 1){
			$row = $result[0];
			updateConnectionDate($row['id']);
		}

		return $result;
	};

	function allNoActiveUserUid(){
		$result = sqlQuery("SELECT uid FROM user WHERE questionnaire_admin = 0 AND questionnaire_active = 0");
		return $result;
	}

	function availableLogin($login, $id){
		$escapedLogin = getRealEscapeString($login);
		$query = "SELECT 1 FROM user WHERE username = '$escapedLogin'" . ($id != null ? " AND id <> $id" : "");
		$result = sqlQuery($query);
		return count($result) == 0;
	}

	function resultUserUid($uid){
		$escapedUid = getRealEscapeString($uid);
		$result = sqlQuery("SELECT questionnaire_active, result FROM user WHERE uid = '$escapedUid'");
		return $result;
	}

	function userList($filterQActive, $filterMActive){
		$filter = "";
		if($filterQActive == "yes"){
			$filter = " AND questionnaire_active = 1";
		}else if($filterQActive == "no"){
			$filter = " AND questionnaire_active = 0";
		}

		if($filterMActive == "yes"){
			$filter = $filter." AND activated = 1";
		}else if($filterMActive == "no"){
			$filter = $filter." AND activated = 0";
		}

		$result = sqlQuery("SELECT id, username, email, phone, date_last_connection, questionnaire_active, activated FROM user WHERE questionnaire_admin = 0".$filter." ORDER BY username");
		return $result;
	}

	function adminIsSet(){
		$result = sqlQuery("SELECT 1 FROM user WHERE questionnaire_admin = 1 AND username = 'admin'");
		return count($result) >= 1;
	}

	function createAdmin($pwd){
		$result = sqlQuery("SELECT 1 FROM user WHERE questionnaire_admin = 1 AND username = 'admin'");
		if(count($result) >= 1){
			log_message("creatAdmin -> There can not be two admin login!", SB_LOG_ERROR);
			return false;
		}

		$hashedPwd = hash('sha256', PASSWORD_SALT.$pwd);
		$uid = uidGenerator();

		sqlUpdate("INSERT INTO user (username, password, uid, questionnaire_active, questionnaire_admin, lang) values ('admin', '$hashedPwd', '$uid', 1, 1, 'en');");
		sqlUpdate("INSERT INTO user_authority (username, authority) VALUES ('admin', '".USER_ROLE. "')");
		log_message("Admin have set a password");
		return true;
	}

	function getNotificationActive(){
		$result = sqlQuery("SELECT result FROM user WHERE questionnaire_admin = 1 AND username = 'admin'");
		if(count($result) == 0){
			return 0;
		}

		$tab = (array)json_decode($result[0]["result"]);
		if(isset($tab["notification"])){
			return $tab["notification"];
		}else{
			return 0;
		}
	}

	function setNotificationActive($active){
		$result = sqlQuery("SELECT id, result FROM user WHERE questionnaire_admin = 1 AND username = 'admin'");
		if(count($result) == 0){
			return;
		}

		$id = $result[0]["id"];
		$tab = (array)json_decode($result[0]["result"]);
		$tab["notification"] = $active;

		$escapedResult = getRealEscapeString(json_encode($tab));
		sqlUpdate("UPDATE user SET result ='$escapedResult' WHERE id = $id");
		log_message("Admin change configuration reminder to ".($active == 1 ? "active" : "inactive"));
	}

	function getUser($id){
		$result = sqlQuery("SELECT username, questionnaire_active, email, phone, lang FROM user WHERE id = $id");
		return $result;
	}

	function getUserUid($id){
		$result = sqlQuery("SELECT uid FROM user WHERE id = $id");
		if(count($result) == 1){
			return $result[0]["uid"];
		}else{
			return null;
		}
	}

	function delUser($id){
		$uid = getUserUid($id);
		$result = sqlQuery("SELECT username FROM user WHERE id = $id");
		sqlUpdate("DELETE FROM user_authority WHERE username = '".getRealEscapeString($result[0]["username"])."'");
		sqlUpdate("DELETE FROM user WHERE id = $id");
		log_message("Admin delete user ".$uid);
	}

	function userListForEmail(){
		$result = sqlQuery("SELECT id, email, lang FROM user WHERE questionnaire_admin = 0 AND questionnaire_active = 1 AND email IS NOT NULL AND date_email_send IS NOT NULL AND now() > date_add(date_email_send, INTERVAL 3 DAY) AND (date_last_connection IS NULL OR now() > date_add(date_last_connection, INTERVAL 3 DAY))");
		return $result;
	}

	function updateUserEmailSend($id, $now){
		$time = date ("Y-m-d H:00:00", $now);
		sqlUpdate("UPDATE user SET date_email_send = '$time' WHERE id = $id");
	}

	function updateConnectionDate($id){
		$time = date ("Y-m-d H:i:s", time());
		sqlUpdate("UPDATE user SET date_last_connection = '$time' WHERE id = $id");
	}

	function sendMail($email, $lang, $type, $params){
		$multilingual = unserialize (MAIL_TRANSLATE);
		$langTab = null;
		if(array_key_exists($lang, $multilingual)){
			$langTab = $multilingual[$lang];
		}else{
			$langTab = $multilingual[DEFAULT_LANG];
		}

		$message = "";
		$subject = "";
		switch ($type) {
			case "reminder" :
				$subject = $langTab["SUBJECT_REMINDER"];
				$message = $langTab["BODY_REMINDER"];
				break;
			case "create":
				$subject = $langTab["SUBJECT_CREATE"];
				$message = $langTab["BODY_CREATE"];
				$message .= "<ul>";
				$message .= "<li>".$langTab["LOGIN"]." ".$params['login']."</li>";
				$message .= "<li>".$langTab["PASSWORD"]." ".$params['pwd']."</li>";
				$message .= "</ul>";
				break;
			case "update":
				$subject = $langTab["SUBJECT_UPDATE"];
				$message = $langTab["BODY_UPDATE"];
				$message .= "<ul>";
				$message .= "<li>".$langTab["LOGIN"]." ".$params['login']."</li>";
				$message .= $params['pwd'] == "" ? "" : "<li>".$langTab["PASSWORD"]." ".$params['pwd']."</li>";
				$message .= "</ul>";
				break;
		}
		$mailHTML = "<html>
	<body style='background-color: #f6fbfd; padding-top: 30px; padding-bottom: 30px'>
	<table bgcolor='#ffffff' style='width: 530px; margin: 0px auto 0px auto; padding: 40px;'>
			<tr>
				<td>
					<img src='".SELFBACK_ROOT_QUESTIONNAIRE."img/logo-min.png'>
					<div style='margin: 65px 30px;'>
						<p>
							".$langTab["HELLO"]."
						</p>
						<p style='margin-top: 40px;'>
							$message
						</p>
						<p style='margin-top: 40px;'>
							".$langTab["THANK"]."
						</p>
						<p>
							<strong>".$langTab["SIGNATURE"]."</strong>
						</p>
					</div>

					<a href='".SELFBACK_ROOT_QUESTIONNAIRE."' style='text-decoration: none;'>
						<input type='button' value='".$langTab["BUTTON"]."' 
							style='display: block;
								margin: 10px auto 10px auto;
								padding: 16px;
								background-color: #53c0ae;
								border-style: hidden;
								font-size: 1.05em;
								color: white;
								cursor:pointer;'/>
					</a>
				</td>
			</tr>
		</table>
	</body>
</html>";

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: ' . MAIL_FROM . "\r\n";
		$headers .= 'Reply-To: ' . MAIL_FROM . "\r\n";

		return mail($email, $subject, $mailHTML, $headers);
	}

	function generateRandomPassword(){
		$pwd = '';
		$desiredLength = rand(8, 12);
		for($length = 0; $length < $desiredLength; $length++) {
			$pwd .= chr(rand(97, 122));
		}
		return $pwd;
	}

	function uidGenerator(){
		return bin2hex(openssl_random_pseudo_bytes(12));
	}

	function getPageNumber($pageIndex){
		return ($pageIndex < 9 ? "0" : "").($pageIndex + 1);
	}

	function navigator(&$tab, $action, $noGood, $pageIndex){
		global $NAVIGATION_PAGES;
		$final = false;
		$skipedIndex = -1;
		$increment = 1;

		$adminDemo = isset($_SESSION['admin_demo']) && $_SESSION['admin_demo'];

		if(isset($tab["p06_q01"]) && $tab["p06_q01"]->value >= 3){
            unset($tab["p07_q01"]);
            $skipedIndex = 6;
        }

		if($action == "next" && ! $noGood){
			if($skipedIndex == $pageIndex + $increment){
				$increment++;
			}

			$tab["construct_next"] = $pageIndex + $increment;
			if(count($NAVIGATION_PAGES) == ($tab["construct_next"]+1)){
				$final = true;
			}else if(count($NAVIGATION_PAGES) < ($tab["construct_next"]+1)){
				logout();
			}
		}else if($action == "previous"){
			if($skipedIndex == $pageIndex - $increment){
				$increment++;
			}
			$tab["construct_next"] = $pageIndex - $increment;
		}

		$tmp = json_encode($tab);
		if(! $adminDemo){
			if($final) insertFinalResult($_SESSION['user_id'], $tmp);
			else insertResult($_SESSION['user_id'], $tmp);
			sqlClose();
		}
		$_SESSION['user_response'] = $tmp;

		$nextPage = null;
		$uid = $_SESSION['user_uid'];
		$message = "";
		if($action == "previous"){
			$nextPage = $NAVIGATION_PAGES[$pageIndex - $increment];
			$_SESSION['page_index'] = $pageIndex - $increment;
			$message = "Previous action: ".$NAVIGATION_PAGES[$pageIndex]." -> ".$nextPage;
		}else if($action == "next" && ! $noGood){
			$nextPage = $NAVIGATION_PAGES[$pageIndex + $increment];
			$_SESSION['page_index'] = $pageIndex + $increment;
			$message = "Next action: ".$NAVIGATION_PAGES[$pageIndex]." -> ".$nextPage;
		}

		if($nextPage != null){
			log_message(($adminDemo ? "(adminDemo=true) " : "").$message, SB_LOG_INFO, $uid);
			header('location:'.$nextPage);
			exit();
		}
	}

	function sessionCheck($adminPage = false){
		$good = false;

		if($adminPage && isset($_SESSION['user_id'])){
			$good = true;
		}else if(isset($_SESSION['user_id']) && isset($_SESSION['page_index'])) {
			$pageIndex = $_SESSION['page_index'];
			if($pageIndex == 0){
				$good = true;
			}else if(isset($_SESSION['user_response'])){
				$good = true;
			}
		}

		if(! $good){
			log_message("Session is not set or lost", SB_LOG_WARNING);
			header('location:'.($adminPage ? '../' : '').'index.php');
			exit();
		}
	}

	function adminCanDemoMod(){
		if(isset($_SESSION['user_uid'])){
			return $_SESSION['user_uid'] == KIOLIS_UID;
		}else{
			return false;
		}
	}

	function getAdminLangTab(){
		$langTabTemp = unserialize (LANG_TAB);
		$langTab = array();

		foreach($langTabTemp as $code => $langLibel){
			if(file_exists("../api/multilingual/".$code.".php")){
				$langTab[$code] = $langLibel;
			}
		}
		return $langTab;
	}
?>