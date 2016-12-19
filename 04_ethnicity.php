<?php
    include("api/service.php");

    sessionCheck();

    class Ethnicity {
        public $pageIndex = 0;
        public $ethnicity = "-1";
        public $ethnicityTab = [];
        public $ethnicBloc = "-1";
        public $describe = "";

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
            $temp = new stdClass();
            $temp->name = P4_ETHNIC_1;
            $temp->min = 1;
            $temp->max = 4;
            array_push($this->ethnicityTab, $temp);
            $temp = new stdClass();
            $temp->name = P4_ETHNIC_2;
            $temp->min = 5;
            $temp->max = 8;
            array_push($this->ethnicityTab, $temp);
            $temp = new stdClass();
            $temp->name = P4_ETHNIC_3;
            $temp->min = 9;
            $temp->max = 13;
            array_push($this->ethnicityTab, $temp);
            $temp = new stdClass();
            $temp->name = P4_ETHNIC_4;
            $temp->min = 14;
            $temp->max = 16;
            array_push($this->ethnicityTab, $temp);
            $temp = new stdClass();
            $temp->name = P4_ETHNIC_5;
            $temp->min = 17;
            $temp->max = 18;
            array_push($this->ethnicityTab, $temp);
        }

        public function preparedStack(&$tab){
            $temp = new stdClass();
            $temp->value = $this->ethnicity;

            if($this->needDescribe()){
                $temp->describe = $this->describe;
            }

            $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q1" => $temp));
        }

        public function loadValue($tab){
            if(isset($tab["p".getPageNumber($this->pageIndex)."_q1"])){
                $this->ethnicity = $tab["p".getPageNumber($this->pageIndex)."_q1"]->value;
                if(isset($tab["p".getPageNumber($this->pageIndex)."_q1"]->describe)){
                    $this->describe = $tab["p".getPageNumber($this->pageIndex)."_q1"]->describe;
                }
            }
        }

        public function needDescribeAndInitEthnicBloc(){
            $this->ethnicBloc = "-1";
            for($i = 0; $i < count($this->ethnicityTab); $i++){
                if($this->ethnicity >= $this->ethnicityTab[$i]->min && $this->ethnicity <= $this->ethnicityTab[$i]->max){
                    $this->ethnicBloc = "".$i;
                    break;
                }
            }

            return $this->needDescribe();
        }

        public function needDescribe(){
            for($i = 0; $i < count($this->ethnicityTab); $i++){
                if($this->ethnicity == $this->ethnicityTab[$i]->max){
                    return true;
                }
            }
            return false;
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new Ethnicity($_SESSION['page_index']);
    $object->loadValue($user_response);

    $needDescribe = false;

    if(!empty($_POST)){
        if(isset($_POST['ethnicityRadio']) && $_POST['ethnicityRadio'] != ""){
            $object->ethnicity = $_POST['ethnicityRadio'];

            $needDescribe = $object->needDescribe();

            if($needDescribe){
                if(isset($_POST['describeText']) && trim($_POST['describeText']) != "") $object->describe = trim($_POST['describeText']);
                else{
                    $object->describe = "";
                    $noGood = true;
                }
            }else{
                $object->describe = "";
            }
        }else{
            $object->ethnicity = "-1";
            $noGood = true;
        }

        // if($noGood) 

        $object->preparedStack($user_response);
        navigator($user_response, $_POST['action'], $noGood, $object->pageIndex);
    }

    $needDescribe = $object->needDescribeAndInitEthnicBloc();
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
                    <?= CATEGORIE_1 ?>: <?= P4_TITLE ?>
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
                    <p class="row<?= $noGood ? " elem-warning" : "" ?>">
                        <?= P4_QUESTION ?>
                    </p>
                    <div class="select-combobox">
                        <div class="dropdown-label" onclick="dropdownClick(this);">
<?php
    $visibleEthni = -1;
    for($i = 0; $i < count($object->ethnicityTab); $i++){
        if($object->ethnicBloc == "".$i){
            $visibleEthni = $i;
            echo $object->ethnicityTab[$i]->name;
            break;
        }
    }
?>
                        </div>
                        <div class="dropdown-content">
<?php
    for($i = 0; $i < count($object->ethnicityTab); $i++){
?>
                        <a href="javascript:void(0);" class="dropdown-selected" onclick="dropdownSelect(this, <?= $i ?>);"><?= $object->ethnicityTab[$i]->name ?></a>
<?php
    }
?>
                        </div>
                    </div>
<?php
    for($i = 0; $i < count($object->ethnicityTab); $i++){
?>
                    <div name="ethnicRadioParagraphe" id="ethni_<?= $i ?>" <?= $object->ethnicBloc != $i ? 'style="display: none;"' : '' ?>>
<?php
        for($j = $object->ethnicityTab[$i]->min; $j <= $object->ethnicityTab[$i]->max; $j++){
?>
                        <p class="css3-radio">
                            <input id="radio<?= $i ?>_<?= $j ?>" type="radio" name="ethnicityRadio" value="<?= $j ?>" <?= $object->ethnicity == "".$j ? "CHECKED" : "" ?> 
                                onchange='<?= $j == $object->ethnicityTab[$i]->max ? "showDescribe();" : "hideDescribe();" ?>' />
                            <label class="toggler_r" for="radio<?= $i ?>_<?= $j ?>">
                                <?= constant("P4_ANSWER_".$j) ?>
                            </label>
                        </p>
<?php
        }
?>
                    </div>
<?php
    }
?>
                    <div id="describeParagraphe" <?= $needDescribe ? '' : 'style="display: none;"' ?>>
                        <p class="<?= $noGood ? " elem-warning" : "" ?>">
                            <?= P4_DESCRIBE ?>
                        </p>
                        <textarea rows="4" cols="50" class="describe-text" name="describeText" id="describeText"><?= $object->describe ?></textarea>
                    </div>
                    <div class="general-form-footer">
                        <input type="button" name="previous" class="general-form-input-button general-form-input-button-previous" value="<?= GENERAL_BTN_PREVIOUS ?>" onclick="page('P');" />
                        <input type="button" name="next" class="general-form-input-button" value="<?= GENERAL_BTN_NEXT ?>" onclick="page('N');" />
                    </div>
                </form>
            </div>
        </div>
        <div class="footer"></div>
        <script>
            var visibleEthni = <?= $visibleEthni ?>;

            function dropdownClick(elem){
                $(".dropdown-content").toggle();
            }

            function dropdownSelect(elem, id){
                $(".dropdown-content").toggle();
                if(id != visibleEthni){
                    visibleEthni = id;
                    $(".dropdown-label").html($(elem).html());
                    $('input[name="ethnicityRadio"]').prop('checked',false);
                    $('#describeText').val("");
                    $('div[name="ethnicRadioParagraphe"]').hide();
                    $('#describeParagraphe').hide();
                    $('#ethni_' + id).show();
                }
            }


            function ethnicChange(elem){
                $('input[name="ethnicityRadio"]').prop('checked',false);
                $('#describeText').val("");
                $('div[name="ethnicRadioParagraphe"]').hide();
                $('#describeParagraphe').hide();
                $('#ethni_' + elem.value).show();
            }

            function showDescribe(){
                $('#describeParagraphe').show();
            }

            function hideDescribe(){
                $('#describeParagraphe').hide();
                $('#describeText').val("");
            }
        </script>
    </body>
</html>