<?php
	$adminPage = true;
	include("../api/service.php");

	$title = "Add new user";
	$newUser = true;

	sessionCheck($adminPage);

	$row = null;
	$noGood = false;
	$errorMessage = "";
	$userId = null;
	$langTab = getAdminLangTab();

	if(isset($_GET['user'])){
		$title = "Modify user profile";
		$newUser = false;
		$userId = $_GET['user'];
		$rows = getUser($_GET['user']);
		$row = $rows[0];
		sqlClose();
	}

	if(!empty($_POST)){
		if(isset($_POST['userId'])){
			$title = "Modify user profile";
			$newUser = false;
			$userId = $_POST['userId'];
		}
		$login = $_POST['userLogin'];
		$email = $_POST['userEmail'];
		$phone = $_POST['userPhone'];
		$lang = $_POST['userLang'];
		$actif = 0;
		if(isset($_POST['actif']) && $_POST['actif'] == "on"){
			$actif = 1;
		}

		$row = array();
		$row["login"] = $login;
		$row["email"] = $email;
		$row["phone"] = $phone;
		$row["lang"] = $lang;
		$row["actif"] = $actif;
		
		$pwd = null;
		if(isset($_POST['userPwd'])){
			$pwd = $_POST['userPwd'];
		}

		if(availableLogin($login, $userId)){
			$userUid = "";
			if($userId == null){
				$userUid = createUser($login, $email, $phone, $pwd, $lang, $actif);
			}else{
				$userUid = updateUser($userId, $login, $email, $phone, $actif, $pwd, $lang);
			}

			$notif = isset($_POST['notif']) && $_POST['notif'] == "on";
			if($notif){
				$params = [
					"login" => $login,
					"pwd" => $pwd
				];
				$type = $userId == null ? "create" : "update";
				sendMail($email, $lang, $type, $params);
				log_message("A $type email have been sent to $userUid");
			}

			header('location:userList.php');
			sqlClose();
			exit();	
		}else{
			$errorMessage = "This login ($login) is not available";
			if($userId == null){
				$row["login"] = "";
			}else{
				$rows = getUser($userId);
				$row["login"] = $rows[0]["login"];
				sqlClose();
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
	<header>
		<meta charset="utf-8">
		<link href='https://fonts.googleapis.com/css?family=Raleway:400,300,500,700,800' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>
		
		<link type="text/css" rel="stylesheet" href="../css/style.css">

		<script src="../js/lib/jquery-2.2.3.min.js"></script>
		<script src="../js/script.js"></script>
	</header>
	<body>
		<div class="content">
			<img src=".././img/logo.png" alt="logo" class="logo" />
			<p class="admin-tile">
				Admin
			</p>
			<div class="shadow">
				<div class="content-header"></div>
				<h1>
					<?= $title ?>
				</h1>
				<div class="logout-admin">
					<p>
						<a href="?logout=1">
							Logout
						</a>
					</p>
				</div>
				<form id="userForm" action="" method="POST" class="general-form" autocomplete="off">
	
					<div id="generalWarning" class="warning" style="display: none;">
						Please fill in all fields before continue!
					</div>
					
					<div class="user-general">
<?php
	if(!empty($errorMessage)) {
		echo '<center class="warning">', htmlspecialchars($errorMessage) ,'</center>';
	}
?>
						<div class="row">
							<label for="userLlogin" class=" admin-left-col">
								Login
							</label>
							<input id="userLogin" name="userLogin" type="text" placeholder="Login" class="general-input-text admin-input-text" value="<?= $row == null ? "" : $row['username'] ?>" /> 
						</div>
						<div class="row">
		                    <div id="emailRegEx" class="warning" style="display: none;">
		                        <center>It's not a valide email.</center>
		                    </div>
							<label for="userEmail" class=" admin-left-col">
								E-mail
							</label>
							<input id="userEmail" name="userEmail" type="text" placeholder="E-mail" class="general-input-text admin-input-text" value="<?= $row == null ? "" : $row['email'] ?>" onchange="emailCheck();" /> 
						</div>
						<div class="row">
							<div id="phoneRegEx" class="warning" style="display: none;">
		                        <center>It's not a valide phone number.</center>
		                    </div>
							<label for="userPhone" class=" admin-left-col">
								Phone number
							</label>
							<input id="userPhone" name="userPhone" type="text" placeholder="Phone number" class="general-input-text admin-input-text" value="<?= $row == null ? "" : $row['phone'] ?>" onchange="phoneCheck();" /> 
						</div>
						<div class="row">
							<label for="userLang" class=" admin-left-col">
								Language
							</label>
							<select id="userLang" name="userLang">
								<option></option>
<?php
	foreach($langTab as $code => $langLibel){
?>
								<option value="<?= $code ?>"<?= $code == $row['lang'] ? " selected" : "" ?>><?= $langLibel ?></option>
<?php
	}
?>
							</select>
						</div>
						<div class="row">
							<label for="actif">
								Activate questionnaire
							</label>
							<input id="actif" name="actif" type="checkbox" <?= $row != null && $row['questionnaire_active'] == 1 || $newUser ? "checked" : "" ?> />
						</div>
						<div class="row">
							<label for="actif">
								Activate smartphone application
							</label>
							<input type="checkbox" disabled>
							<i class="i-info">
								(Not implemented yet)
							</i>
						</div>
					</div>
<?php
	if(! $newUser){
?>
					<input type="hidden" name="userId" value="<?= $userId ?>">
					<input type="button" id="pwdChangeButton" class="change-password-button" value="Change password" onclick="pwdChange();" />
<?php
	}
?>
					<fieldset id="pwdBlock" <?= $newUser ? "" : "style=\"display: none;\"" ?>>
						<div id="pwdWarning" class="warning" style="display: none;">
							Passwords do not match
						</div>
						<input type="button" class="general-form-input-button random-pwd" value="Generate" onclick="generateRandomPassword();"/>
						<div class="row">
							<label for="userPwd" class=" admin-left-col">
								Password
							</label>
							<input id="userPwd" name="userPwd" type="password" placeholder="Password" class="general-input-text admin-input-text" value="" onkeyup="pwdVerif();" /> 
						</div>
						<div class="row">
							<label for="pwd2" class=" admin-left-col">
								Repeat Password
							</label>
							<input id="userPwd2" type="password" placeholder="Password" class="general-input-text admin-input-text" value=""onkeyup="pwdVerif();" /> 
						</div>
					</fieldset>

					<div class="row">
						<label for="notif" class=" admin-left-col">
							Notify the user
						</label>
						<input id="notif" name="notif" type="checkbox" /> 
					</div>
					<div class="general-form-footer">
						<input type="button" class="general-form-input-button general-form-input-button-previous" value="Cancel" onclick="goBack();" />
						<input type="button" class="general-form-input-button" value="Ok" onclick="submitUser();"/>
					</div>
				</form>
			</div>
		</div>
		<div class="footer"></div>
		<script>
			var pwdChanged = <?= $newUser ? "true" : "false" ?>;
			var pwdGood = false;
			var pwdInit = true;
			var emailGood = false;
			var phoneGood = false;

			function goBack(){
				window.location = "userList.php";
			}

			function generateRandomPassword(){
				document.getElementById("userPwd").value = document.getElementById("userPwd2").value = "<?= generateRandomPassword() ?>";
				pwdVerif();
			}

			function emailCheck(){
				document.getElementById("emailRegEx").style.display = "none";
				var email = document.getElementById("userEmail");
				var regEx = /<?= EMAIL_REGEX ?>/;
				if(! regEx.test(email.value)){
					emailGood = false;
                    document.getElementById("emailRegEx").style.display = "";
                }else{
                	emailGood = true;
                }
			}

			function phoneCheck(){
				document.getElementById("phoneRegEx").style.display = "none";
				var phone = document.getElementById("userPhone");
				var regEx = /<?= PHONE_REGEX ?>/;
				if(! regEx.test(phone.value)){
					phoneGood = false;
                    document.getElementById("phoneRegEx").style.display = "";
                }else{
                	phoneGood = true;
                }
			}

			function pwdVerif(){
				var pwd = document.getElementById("userPwd");
				var pwd2 = document.getElementById("userPwd2");

				if(pwd.value != "" && pwd2.value != "" || ! pwdInit){
					pwdInit = false;
					if(pwd.value != pwd2.value){
						document.getElementById("pwdWarning").style.display = "";
					}else{
						document.getElementById("pwdWarning").style.display = "none";
						pwdGood = true;
					}
				}
			}

			function pwdChange(){
				var pwdBlock = document.getElementById("pwdBlock");
				var pwdButton = document.getElementById("pwdChangeButton");

				if(pwdBlock.style.display == "none"){
					pwdBlock.style.display = "";
					pwdButton.value = "Cancel password change";
					pwdChanged = true;
				}else{
					pwdBlock.style.display = "none";
					pwdButton.value = "Click to change password";
					pwdChanged = false;
					document.getElementById("userPwd").value = "";
					document.getElementById("userPwd2").value = "";
				}
			}

			function submitUser(){
				document.getElementById("generalWarning").style.display = "none";
				var error = false;

				if(pwdChanged && ! pwdGood){
					document.getElementById("pwdWarning").style.display = "";
					error = true;
				}

				if(document.getElementById("userLogin").value == "" || document.getElementById("userEmail").value == ""  || document.getElementById("userPhone").value == "" || document.getElementById("userLang").value == ""){
					document.getElementById("generalWarning").style.display = "";
					error = true;
				}

				emailCheck();
				phoneCheck();

				if(error || ! emailGood || ! phoneGood) return;

				document.getElementById("userForm").submit();
			}
		</script>
	</body>
</html>