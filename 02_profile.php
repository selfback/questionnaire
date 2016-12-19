<?php
	include("api/service.php");

	sessionCheck();

	class Profile {
		public $pageIndex = 0;
		public $age = "";
		public $gender = "";
		public $height = "";
		public $height_ft = "";
		public $height_in = "";
		public $weight = "";
		public $weight_st = "";
		public $weight_lb = "";
		public $typeUnits = "SI";

		public $UNIT_SI = "Metric system (cm and kg)";
		public $UNIT_UK = "Imperial system (ft/in and st/lb)";

		function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
        }

		public function preparedStack(&$tab){
			$temp = new stdClass();
			$temp->value = $this->age;
			$tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q01" => $temp));
			$temp = new stdClass();
			$temp->value = $this->gender;
			$tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q02" => $temp));
			$temp = new stdClass();
			$temp->value = $this->height;
			$tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q03" => $temp));
			$temp = new stdClass();
			$temp->value = $this->height_ft;
			$tab = array_merge($tab, array("construct_p".getPageNumber($this->pageIndex)."_q03_ft" => $temp));
			$temp = new stdClass();
			$temp->value = $this->height_in;
			$tab = array_merge($tab, array("construct_p".getPageNumber($this->pageIndex)."_q03_in" => $temp));
			$temp = new stdClass();
			$temp->value = $this->weight;
			$tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q04" => $temp));
			$temp = new stdClass();
			$temp->value = $this->weight_st;
			$tab = array_merge($tab, array("construct_p".getPageNumber($this->pageIndex)."_q04_st" => $temp));
			$temp = new stdClass();
			$temp->value = $this->weight_lb;
			$tab = array_merge($tab, array("construct_p".getPageNumber($this->pageIndex)."_q04_lb" => $temp));
			$temp = new stdClass();
			$temp->value = $this->typeUnits;
			$tab = array_merge($tab, array("construct_p".getPageNumber($this->pageIndex)."_typeUnits" => $temp));
		}

		public function loadValue($tab){
			if(isset($tab["p".getPageNumber($this->pageIndex)."_q01"])) $this->age = $tab["p".getPageNumber($this->pageIndex)."_q01"]->value;
			if(isset($tab["p".getPageNumber($this->pageIndex)."_q02"])) $this->gender = $tab["p".getPageNumber($this->pageIndex)."_q02"]->value;
			if(isset($tab["p".getPageNumber($this->pageIndex)."_q03"])) $this->height = $tab["p".getPageNumber($this->pageIndex)."_q03"]->value;
			if(isset($tab["construct_p".getPageNumber($this->pageIndex)."_q03_ft"])) $this->height_ft = $tab["construct_p".getPageNumber($this->pageIndex)."_q03_ft"]->value;
			if(isset($tab["construct_p".getPageNumber($this->pageIndex)."_q03_in"])) $this->height_in = $tab["construct_p".getPageNumber($this->pageIndex)."_q03_in"]->value;
			if(isset($tab["p".getPageNumber($this->pageIndex)."_q04"])) $this->weight = $tab["p".getPageNumber($this->pageIndex)."_q04"]->value;
			if(isset($tab["construct_p".getPageNumber($this->pageIndex)."_q04_st"])) $this->weight_st = $tab["construct_p".getPageNumber($this->pageIndex)."_q04_st"]->value;
			if(isset($tab["construct_p".getPageNumber($this->pageIndex)."_q04_lb"])) $this->weight_lb = $tab["construct_p".getPageNumber($this->pageIndex)."_q04_lb"]->value;
			if(isset($tab["construct_p".getPageNumber($this->pageIndex)."_typeUnits"])) $this->typeUnits = $tab["construct_p".getPageNumber($this->pageIndex)."_typeUnits"]->value;
		}
	}

	$user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
	$object = new Profile($_SESSION['page_index']);
	$object->loadValue($user_response);

	$displaySI = false;
	$displayUK = false;

	if(!empty($_POST)){
		if(isset($_POST['age']) && $_POST['age'] != ""){
			$object->age = $_POST['age'];
		}else{
			$object->age = "";
			$noGood = true;
		}
		if(isset($_POST['gender']) && $_POST['gender'] != "") $object->gender = $_POST['gender'];
		else $noGood = true;

		if(isset($_POST['typeUnits']) && $_POST['typeUnits'] != ""){
			$object->typeUnits = $_POST['typeUnits'];
		}

		if($object->typeUnits == "SI"){
			if(isset($_POST['height_SI']) && $_POST['height_SI'] != ""){
				$object->height = $_POST['height_SI'];
			}else{
				$object->height = "";
				$noGood = true;
			}
			if(isset($_POST['weight_SI']) && $_POST['weight_SI'] != ""){
				$object->weight = $_POST['weight_SI'];
			}else{
				$object->weight = "";
				$noGood = true;
			}

			$object->height_ft = "";
			$object->height_in = "";
			$object->weight_st = "";
			$object->weight_lb = "";
		}else{//Imperial system
			if(isset($_POST['height_total']) && $_POST['height_total'] != ""){
				$object->height = $_POST['height_total'];
			}else{
				$object->height = "";
				$noGood = true;
			}
			if(isset($_POST['height_ft']) && $_POST['height_ft'] != "") $object->height_ft = $_POST['height_ft'];
			else{ $object->height_ft = ""; $noGood = true; }
			if(isset($_POST['height_in']) && $_POST['height_in'] != "") $object->height_in = $_POST['height_in'];
			else{ $object->height_in = ""; $noGood = true; }

			if(isset($_POST['weight_total']) && $_POST['weight_total'] != ""){
				$object->weight = $_POST['weight_total'];
			}else{
				$object->weight = "";
				$noGood = true;
			}
			if(isset($_POST['weight_st']) && $_POST['weight_st'] != "") $object->weight_st = $_POST['weight_st'];
			else{ $object->weight_st = ""; $noGood = true; }
			if(isset($_POST['weight_lb']) && $_POST['weight_lb'] != "") $object->weight_lb = $_POST['weight_lb'];
			else{ $object->weight_lb = ""; $noGood = true; }
		}

		$object->preparedStack($user_response);
		navigator($user_response, $_POST['action'], $noGood, $object->pageIndex);
	}

	if($_SESSION['user_lang'] != "en" || $object->typeUnits == "SI"){
		$displaySI = true;
	}
	if($_SESSION['user_lang'] == "en" && $object->typeUnits == "UK"){
		$displayUK = true;
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
				<h1>
					<?= CATEGORIE_1 ?>
				</h1>
				<form id="generalForm" action="" method="POST" class="general-form">
					<input type="hidden" id="action" name="action" />
<?php
	if($noGood){
?>						
					<div class="warning">
						<?= GENERAL_WARNING ?>
					</div>
<?php
	}
?>
					<div class="row">
						<label for="age" class="left-col<?= $noGood && $object->age == "" ? " elem-warning" : "" ?>">
							<?= P2_QUESTION_1 ?>
						</label>
						<input id="age" name="age" type="text" placeholder="<?= P2_ANSWER_1 ?>" class="general-input-text" value="<?= $object->age ?>" onchange="ageCheck(this);" /> 
					</div>

					<div class="row">
						<span class="left-col<?= $noGood && $object->gender == "" ? " elem-warning" : "" ?>">
							<?= P2_QUESTION_2 ?>
						</span>
						<span class="left-col css3-radio">
							<input id="genderM" name="gender" type="radio" value="0" <?= $object->gender == "0" ? "CHECKED" : "" ?> />
							<label class="toggler_r" for="genderM">
								<?= P2_MALE ?>
							</label>
						</span>
						<span class="left-col css3-radio">
							<input id="genderF" name="gender" type="radio" value="1" <?= $object->gender == "1" ? "CHECKED" : "" ?> />
							<label class="toggler_r" for="genderF">
								<?= P2_FEMALE ?>
							</label>
						</span>
						<div class="clearfix"></div>
					</div>
<?php
	if($_SESSION['user_lang'] == "en"){
?>
					<input type="hidden" name="typeUnits" value="<?= $object->typeUnits ?>" />
					<div class="units-page select-combobox">
                        <div class="dropdown-label" onclick="dropdownClick(this);">
<?php
		if($object->typeUnits == "SI"){
			echo $object->UNIT_SI;
		}else if($object->typeUnits == "UK"){
			echo $object->UNIT_UK;
		}
?>
						</div>
                        <div class="dropdown-content">
                        <a href="javascript:void(0);" class="dropdown-selected" onclick="dropdownSelect(this, 'SI');"><?= $object->UNIT_SI ?></a>
                        <a href="javascript:void(0);" class="dropdown-selected" onclick="dropdownSelect(this, 'UK');"><?= $object->UNIT_UK ?></a>
                        </div>
                    </div>
<?php
	}
?>
					<div class="units-si" id="system_SI" name="systemParagraphe" <?= $displaySI ? '' : 'style="display: none;"' ?>>
						<div class="row">
							<label for="height_SI" class="left-col<?= $noGood && $object->height == "" ? " elem-warning" : "" ?>">
								<?= P2_QUESTION_3 ?>
							</label>
							<input id="height_SI" name="height_SI" type="text" placeholder="<?= P2_ANSWER_2 ?>" class="general-input-text height-weight-unit" value="<?= $object->height ?>" onchange="heightCheckSI(this);" /> <?= P2_CM ?>
						</div>
						
						<div class="row">
							<label for="weight_SI" class="left-col<?= $noGood && $object->weight == "" ? " elem-warning" : "" ?>">
								<?= P2_QUESTION_4 ?>
							</label>
							<input id="weight_SI" name="weight_SI" type="text" placeholder="<?= P2_ANSWER_3 ?>" class="general-input-text height-weight-unit" value="<?= $object->weight ?>" onchange="weightCheckSI(this);" /> <?= P2_KG ?>
						</div>
					</div>
<?php
	if($_SESSION['user_lang'] == "en"){
?>
					<div class="units-uk" id="system_UK" name="systemParagraphe" <?= $displayUK ? '' : 'style="display: none;"' ?>>
						<div class="row">
							<label for="height_ft" class="left-col<?= $noGood && ($object->height_ft == "" || $object->height_in == "") ? " elem-warning" : "" ?>">
								<?= P2_QUESTION_3 ?>
							</label>
							<input id="height_ft" name="height_ft" type="text" placeholder="<?= P2_FOOT ?>" class="general-input-text height-weight-unit uk-unit" value="<?= $object->height_ft ?>" onchange="heightCheckUK();" /> ft
							<input id="height_in" name="height_in" type="text" placeholder="<?= P2_INCHE ?>" class="general-input-text height-weight-unit uk-unit" value="<?= $object->height_in ?>" onchange="heightCheckUK();" /> in

							<input name="height_total" type="hidden" value="<?= $object->height ?>" />
						</div>
						
						<div class="row">
							<label for="weight_st" class="left-col<?= $noGood && ($object->weight_st == "" || $object->weight_lb == "") ? " elem-warning" : "" ?>">
								<?= P2_QUESTION_4 ?>
							</label>
							<input id="weight_st" name="weight_st" type="text" placeholder="<?= P2_STONE ?>" class="general-input-text height-weight-unit uk-unit" value="<?= $object->weight_st ?>" onchange="weightCheckUK();" /> st
							<input id="weight_lb" name="weight_lb" type="text" placeholder="<?= P2_POUND ?>" class="general-input-text height-weight-unit uk-unit" value="<?= $object->weight_lb ?>" onchange="weightCheckUK();" /> lb

							<input name="weight_total" type="hidden" value="<?= $object->weight ?>" />
						</div>
					</div>
<?php
	}
?>
					<div class="general-form-footer">
						<input type="button" name="previous" class="general-form-input-button general-form-input-button-previous" value="<?= GENERAL_BTN_PREVIOUS ?>" onclick="page('P');" />
						<input type="button" name="next" class="general-form-input-button" value="<?= GENERAL_BTN_NEXT ?>" onclick="page('N');" />
					</div>
				</form>
			</div>
		</div>
		<div class="footer"></div>
		<script>
			var visibleSystem = "<?= $object->typeUnits ?>";

			function dropdownClick(elem){
                $(".dropdown-content").toggle();
            }

            function dropdownSelect(elem, id){
                $(".dropdown-content").toggle();
                if(id != visibleSystem){
                    visibleSystem = id;
                    $(".dropdown-label").html($(elem).html());
                    $("input.height-weight-unit").val("");
                    $('div[name="systemParagraphe"]').hide();
                    $('#system_' + id).show();
                    $('input[name="typeUnits"]').val(id);
                }
            }

			function ageCheck(elem){
				if(elem.value != ""){
					if(! positifCheckInteger(elem)){
						return;
					}

					if(elem.value < 18.0){
						elem.value = "";
						alert("<?= P2_ALERT_AGE_MIN ?>");
						return;
					}

					if(elem.value > 99.0){
						elem.value = "";
						alert("<?= P2_ALERT_AGE_MAX ?>");
						return;
					}
				}
			}

			function heightCheckSI(elem){
				if(elem.value != ""){
					if(! positifCheckDecimal(elem)){
						return;
					}

					if(elem.value < 145.0){
						elem.value = "";
						alert("<?= P2_ALERT_HEIGHT_SI_MIN ?>");
						return;
					}

					if(elem.value > 215.0){
						elem.value = "";
						alert("<?= P2_ALERT_HEIGHT_SI_MAX ?>");
						return;
					}
				}
			}

			function heightCheckUK(){
				var feet = document.getElementById("height_ft");
				var inches = document.getElementById("height_in");
				if(feet.value != "" && inches.value != ""){
					if(! positifCheckInteger(feet, inches)){
						return;
					}

					if(inches.value >= 12){
						inches.value = "";
						alert("<?= P2_ALERT_INCHES_MAX ?>");
						return;
					}

					var ftValue = feet.value;
					var inValue = inches.value;

					total = Math.round((ftValue * 30.5 + inValue * 2.54) * 100) / 100;
					if(total < 144.86){
						feet.value = inches.value = "";
						alert("<?= P2_ALERT_HEIGHT_UK_MIN ?>");
						return;
					}
					if(total > 216.04){
						feet.value = inches.value = "";
						alert("<?= P2_ALERT_HEIGHT_UK_MAX ?>");
						return;
					}
					$("input[name='height_total'").val(total);
				}
			}

			function weightCheckSI(elem){
				if(elem.value != ""){
					if(! positifCheckDecimal(elem)){
						return;
					}

					if(elem.value < 40.0){
						elem.value = "";
						alert("<?= P2_ALERT_WEIGHT_SI_MIN ?>");
						return;
					}

					if(elem.value > 150.0){
						elem.value = "";
						alert("<?= P2_ALERT_WEIGHT_SI_MAX ?>");
						return;
					}
					$("input[name='weight_total'").val(total);
				}
			}

			function weightCheckUK(){
				var stones = document.getElementById("weight_st");
				var pounds = document.getElementById("weight_lb");
				if(stones.value != "" && pounds.value != ""){
					if(! positifCheckInteger(stones, pounds)){
						return;
					}

					if(pounds.value >= 14){
						pounds.value = "";
						alert("<?= P2_ALERT_POUNDS_MAX ?>");
						return;
					}

					var stValue = stones.value;
					var lbValue = pounds.value;

					total = Math.round((stValue * 6.35 + lbValue * 0.45) * 100) / 100;
					if(total < 39.9){
						stones.value = pounds.value = "";
						alert("<?= P2_ALERT_WEIGHT_UK_MIN ?>");
						return;
					}
					if(total > 150.1){
						stones.value = pounds.value = "";
						alert("<?= P2_ALERT_WEIGHT_UK_MAX ?>");
						return;
					}
					$("input[name='weight_total'").val(total);
				}
			}

			function positifCheckDecimal(elem){
				elem.value = elem.value.replace(",", ".");

				var reg = /^\d+\.?\d*$/;

				if(! reg.test(elem.value)){
					elem.value = "";
					alert("<?= P2_ALERT_POSITIF ?>");
					return false;
				}
				return true;
			}

			function positifCheckInteger(elem1, elem2){
				var reg = /^\d+$/;

				if(elem1.value!= "" && ! reg.test(elem1.value)){
					elem1.value = "";
					alert("<?= P2_ALERT_POSITIF_INTEGER ?>");
					return false;
				}
				if(typeof elem2 != "undefined" && elem2.value!= "" && ! reg.test(elem2.value)){
					elem2.value = "";
					alert("<?= P2_ALERT_POSITIF_INTEGER ?>");
					return false;
				}
				return true;
			}
		</script>
	</body>
</html>