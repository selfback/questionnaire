<?php
	$adminPage = true;
	include("../api/service.php");

	sessionCheck($adminPage);

	$demoMod = adminCanDemoMod();
	if($demoMod){
		if(isset($_GET['adminDemo']) && $_GET['adminDemo'] != ""){
			$_SESSION['admin_demo'] = true;
			$demoLang = $_GET['adminDemo'];
			$_SESSION['user_lang'] = $demoLang;
			$pageIndex = 0;
			$_SESSION['page_index'] = $pageIndex;
			log_message("Admin demo connect");
			header('location:../'.$NAVIGATION_PAGES[$pageIndex]);
		}
	}

	if(isset($_GET['delUser']) && $_GET['delUser'] != ""){
		delUser($_GET['delUser']);
	}

	$notificationChange = false;
	if(isset($_GET['notification']) && $_GET['notification'] != ""){
		setNotificationActive($_GET['notification']);
		$notificationChange = true;
	}

	$filterQActive = "all";
	if(isset($_GET['filterQActive']) && $_GET['filterQActive'] != ""){
		$filterQActive = $_GET['filterQActive'];
	}

	$filterMActive = "all";
	if(isset($_GET['filterMActive']) && $_GET['filterMActive'] != ""){
		$filterMActive = $_GET['filterMActive'];
	}

	$rows = userList($filterQActive, $filterMActive);

	$tabExport = array();
	for($i = 0; $i < count($rows); $i++){
		if(empty($rows[$i]['date_last_connection'])){
			$rows[$i]["date_last_connection"] = "";
		}else{
			$rows[$i]["date_last_connection"] = date( 'y-m-d H:i', strtotime($rows[$i]["date_last_connection"]));
		}

		$rows[$i]['questionnaire_active'] = $rows[$i]['questionnaire_active'] ? "Yes" : "No";
		$rows[$i]['mobile_active'] = $rows[$i]['activated'] ? "Yes" : "No";

		$tmp = $rows[$i];
		unset($tmp["id"]);
		unset($tmp["activated"]);
		array_push($tabExport, $tmp);
	}

	$_SESSION['tabExport'] = $tabExport;

	$notificationActive = getNotificationActive();
	$langTab = getAdminLangTab();

	sqlClose();
?>
<!DOCTYPE html>
<html lang="en">
	<header>
		<meta charset="utf-8">
		<link href='https://fonts.googleapis.com/css?family=Raleway:400,300,500,700,800' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>
		
		<link type="text/css" rel="stylesheet" href="../css/style.css">
		<link type="text/css" rel="stylesheet" href="../css/jquery-ui-1.12.1.min.css">

		<script src="../js/lib/jquery-2.2.3.min.js"></script>
		<script src="../js/lib/jquery-ui-1.12.1.min.js"></script>
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
					General Configuration:
				</h1>
				<div class="logout-admin">
					<p>
						<a href="?logout=1">
							Logout
						</a>
					</p>
<?php
	if($demoMod){
?>
					<input type="button" value="Test the questionnaire" id="demo-button" />
<?php
	}
?>
				</div>
				<div class="admin-checkbox">
					<input id="notification" type="checkbox" name="notification" class="css3-checkbox" value="1" <?= $notificationActive ? "CHECKED" : "" ?> onchange="notificationChange(this);" />
                    <label for="notification">
                        Enable reminders 
<?php 
	if($notificationChange){
?>
						<span class="notification-change">(save)</span>
<?php 
	}
?>
                    </label>
				</div>
				<h1>
					User list (<a class="admin-export-link" href="export_users.php">Export as CSV</a>)
				</h1>
				<div class="admin-table">
					<table class="general-table" style="width: 100%;">
						<tr>
							<th style="width: 18%">Login</th>
							<th style="width: 26%">E-mail</th>
							<th style="width: 20%">Phone</th>
							<th style="width: 12%">Last Co.</th>
							<th colspan=2 style="width: 16%">
								Active*
								<table>
									<tr>
										<th>
											Question.
											<br/>
											<select id="filterQActive" onchange="filterActive();">
												<option value="all" <?= $filterQActive == "all" ? "selected" : "" ?>>all</option>
												<option value="yes" <?= $filterQActive == "yes" ? "selected" : "" ?>>yes</option>
												<option value="no" <?= $filterQActive == "no" ? "selected" : "" ?>>no</option>
											</select>
										</th>
										<th>
											Mobile
											<br/>
											<select id="filterMActive" onchange="filterActive();">
												<option value="all" <?= $filterMActive == "all" ? "selected" : "" ?>>all</option>
												<option value="yes" <?= $filterMActive == "yes" ? "selected" : "" ?>>yes</option>
												<option value="no" <?= $filterMActive == "no" ? "selected" : "" ?>>no</option>
											</select>
										</th>
									</tr>
								</table>
							</th>
							<th >Delete</th>
						</tr>
<?php
	foreach($rows as $row){
?>
						<tr>
							<td>
								<a href="user.php?user=<?= $row['id'] ?>"><?= $row['username'] ?></a>
							</td>
							<td>
								<?= $row['email'] ?>
							</td>
							<td style="text-align: center;">
								<?= $row['phone'] ?>
							</td>
							<td style="text-align: center;">
							<?= $row['date_last_connection'] ?>
							</td>
							<td style="text-align: center;">
								<?= $row['questionnaire_active'] ?>
							</td>
							<td style="text-align: center;">
								<?= $row['mobile_active'] ?>
							</td>
							<td style="text-align: center;">
								<a href="javascript:void(0);" onclick="delConfirm('<?= $row['username'] ?>', '<?= $row['id'] ?>');">X</a>
							</td>
						</tr>
<?php
	}
?>
					</table>
				
					<i class="i-info">
						* Users who have completed the questionnaire are no longer active.
					</i>
				</div>
				<div class="general-form-footer">
					<input type="button" class="general-form-input-button" value="Add new user" onclick="addUser();" />
				</div>
			</div>
<?php
	if($demoMod){
?>
			<div id="dialog-confirm" title="Connected in demo mod?">
				<p>
					You can test the questionnaire without saving data.
					<br/>
					Please select the language:
					<select id="demoLang" name="demoLang">
<?php
		foreach($langTab as $code => $langLibel){
?>
						<option value="<?= $code ?>"<?= $code == DEFAULT_LANG ? " selected" : "" ?>><?= $langLibel ?></option>
<?php
		}
?>
					</select>
				</p>
			</div>
<?php
	}
?>
		</div>
		<div class="footer"></div>
		<script type="text/javascript">
			function filterActive(){
				window.location = "userList.php?filterQActive=" + $("#filterQActive").val() + "&filterMActive=" + $("#filterMActive").val();
			}

			function delConfirm(login, id){
				if(confirm("Are you sure you want delete " + login + " account ?")){
					window.location = "userList.php?delUser=" + id;
				}
			}

			function addUser(){
				window.location = "user.php";
			}

			function notificationChange(elem){
				window.location = "userList.php?notification=" + ($(elem).is(':checked') ? 1 : 0);
			}
<?php 
	if($notificationChange){
?>
			$(function() {
				setTimeout(function(){ $(".notification-change").hide(); }, 3000);
			});
<?php 
	}
	if($demoMod){
?>
			$( function() {
				var dialog = $( "#dialog-confirm" ).dialog({
					autoOpen: false,
					resizable: false,
					height: "auto",
					width: 400,
					modal: true,
					buttons: {
						"Connect": function() {
							$( this ).dialog( "close" );
							window.location = "userList.php?adminDemo=" + $("#demoLang").val();
						},
						Cancel: function() {
							$( this ).dialog( "close" );
						}
					}
				});

				$( "#demo-button" ).button().on( "click", function() {
					dialog.dialog( "open" );
				});
			});
<?php
	}
?>
		</script>
	</body>
</html>
