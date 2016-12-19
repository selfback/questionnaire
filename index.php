<?php
	include("api/service.php");

	session_unset();
	session_destroy();

	$errorMessage = "";
	if(!empty($_POST)){
		if (isset($_POST['username']) && isset($_POST['password'])) {
			$rows = userConnect($_POST['username'], $_POST['password']);
			if(count($rows) == 1){
				$row = $rows[0];
				$uid = $row['uid'];

				if($row['questionnaire_admin'] == 1){
					session_start();
					$_SESSION['user_id'] = $row['id'];
					$_SESSION['user_uid'] = $uid;
					log_message("User connect");
					header('location:admin/userList.php');
					sqlClose();
					exit();
				}

				if($row['questionnaire_active'] == 1){
					session_start();
					$pageIndex = 0;
					$_SESSION['user_id'] = $row['id'];
					$_SESSION['user_lang'] = $row['lang'];
					$_SESSION['user_uid'] = $uid;
					$_SESSION['page_index'] = $pageIndex;
					log_message("User connect");
					header('location:'.$NAVIGATION_PAGES[$pageIndex]);
					sqlClose();
					exit();
				}else{
					log_message("User already responded to the questionnaire", SB_LOG_INFO, $uid);
					$errorMessage = "You have already responded to the questionnaire.";
				}
			}else{
				if(! adminIsSet() && strtolower($_POST['username']) == 'admin' && $_POST['password'] == DEFAULT_ADMIN_PWD){
					log_message("First admin connection");
					header('location:admin/createAdminPassword.php');
					sqlClose();
					exit();
				}

				log_message("User not found");
				$errorMessage = "User not found";
			}
		}
	}
	sqlClose();
?>
<!DOCTYPE html>
<html lang="en">
	<header>
		<meta charset="utf-8">
		<link href='https://fonts.googleapis.com/css?family=Raleway:400,300,500,700,800' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>
		
		<link type="text/css" rel="stylesheet" href="css/style.css">
	</header>
	<body>
		<div class="content">
			<img src="./img/logo.png" alt="logo" class="logo" />
			<div class="shadow">
				<div class="content-header"></div>
				<form action="" method="POST" class="login-form">
					<input id="username" type="text" name="username" placeholder="Username" class="login-form-input login-form-input-text" />
					<input id="password" type="password" name="password" placeholder="Password" class="login-form-input login-form-input-text" />
					<?php
						if(!empty($errorMessage)) {
							echo '<center class="i-info">', htmlspecialchars($errorMessage) ,'</center>';
						}
					?>
					<input id="submit" type="submit" name="submit" class="login-form-input login-form-input-button" value="Start" />
				</form>
			</div>
		</div>
		<div class="footer"></div>
	</body>
</html>
