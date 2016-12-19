<?php
    include('api/service.php');

    sessionCheck();

    class FinalObject {
        public $pageIndex = 0;

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
        }

        public function preparedStack(&$tab){
        }

        public function loadValue($tab){
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $object = new FinalObject($_SESSION['page_index']);
    $noGood = false;

    if(!empty($_POST)){
        $object->preparedStack($user_response);
        navigator($user_response, $_POST['action'], $noGood, $object->pageIndex);
    }
?>
<!DOCTYPE html>
<html lang="en">
	<header>
		<meta charset="utf-8">
		<link href='https://fonts.googleapis.com/css?family=Raleway:400,300,500,700,800' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>
		
		<link type="text/css" rel="stylesheet" href="css/style.css">

		<script src="js/lib/jquery-2.2.3.min.js"></script>
		<script src="js/script.js"></script>
	</header>
	<body>
		<div class="content">
			<img src="./img/logo.png" alt="logo" class="logo" />
			<div class="shadow">
				<div class="content-header">
					<p><?= $object->pageIndex+1 ?> / <?= count($NAVIGATION_PAGES) ?></p>
				</div>
				<form id="generalForm" action="" method="POST" class="general-form">
					<input type="hidden" id="action" name="action" />
					<p class="questionnaire">
						<?= FINAL_LABEL_1 ?>
						<br/>
						<?= FINAL_LABEL_2 ?>
					</p>

					<div class="general-form-footer">
						<input type="button" name="previous" class="general-form-input-button general-form-input-button-previous" value="<?= GENERAL_BTN_PREVIOUS ?>" onclick="page('P');" />
						<input type="button" class="general-form-input-button final-button" value="<?= FINAL_BTN ?>" onclick="page('N');" />
					</div>
				</form>
			</div>
		</div>
		<div class="footer"></div>
	</body>
</html>