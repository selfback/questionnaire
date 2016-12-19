<?php
    include("api/service.php");

    sessionCheck();

    class Familly {
        public $pageIndex = 0;
        public $stat = array ();

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
            array_push($this->stat, "-1");
            for($i = 1; $i < 7; $i++){
                array_push($this->stat, "0");
            }
        }

        public function preparedStack(&$tab){
            for($i = 1; $i <= count($this->stat); $i++){
                $temp = new stdClass();
                $temp->value = $this->stat[$i-1];
                $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q0".$i => $temp));
            }
        }

        public function loadValue($tab){
            for($i = 1; $i <= count($this->stat); $i++){
                if(isset($tab["p".getPageNumber($this->pageIndex)."_q0".$i])) 
                    $this->stat[$i-1] = $tab["p".getPageNumber($this->pageIndex)."_q0".$i]->value;
            }
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new Familly($_SESSION['page_index']);
    $object->loadValue($user_response);

    if(!empty($_POST)){
        $moreThanOne = 0;
        $lineAlone = false;
        if(isset($_POST['stat_1']) && $_POST['stat_1'] != ""){
            $object->stat[0] = $_POST['stat_1'];
            if($_POST['stat_1'] == 1){
                $lineAlone = true;
                $moreThanOne++;
                for($i = 2; $i <= count($object->stat); $i++){
                    $object->stat[$i-1] = 0;
                }
            }
        }

        if(! $lineAlone){
            for($i = 2; $i <= count($object->stat); $i++){
                if(isset($_POST['stat_'.$i]) && $_POST['stat_'.$i] != ""){
                    $object->stat[$i-1] = $_POST['stat_'.$i];
                    $moreThanOne++;
                }else{
                    $object->stat[$i-1] = 0;
                }
            }
        }

        if($moreThanOne == 0){
            $noGood = true;
        }

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
                <h1>
                    <?= CATEGORIE_1 ?>: <?= P3_TITLE ?>
                </h1>
                <form id="generalForm" action="" method="POST" class="general-form">
                    <input type="hidden" id="action" name="action" />
<?php
    if($noGood){
?>                      
                    <div class="warning">
                        <?= AT_LEAST_ONE_WARNING ?>
                    </div>
<?php
    }
?>
                    <p class="row<?= $noGood ? " elem-warning" : "" ?>">
                        <?= P3_QUESTION ?>
                    </p>
                    <p class="css3-radio">
                        <input id="radio_alone" type="radio" name="stat_1" value="1" <?= $object->stat[0] == "1" ? "CHECKED" : "" ?> onchange="aloneOrNot(true);" />
                        <label class="toggler_r" for="radio_alone">
                            <?= P3_ANSWER_ALONE ?>
                        </label>
                    </p>
                    <p class="css3-radio">
                        <input id="radio_not_alone" type="radio" name="stat_1" value="0" <?= $object->stat[0] == "0" ? "CHECKED" : "" ?> onchange="aloneOrNot(false);" />
                        <label class="toggler_r" for="radio_not_alone">
                            <?= P3_ANSWER_NOT_ALONE ?>
                        </label>
                    </p>
                    <p class="not-alone sub-option">
                        <i class="i-info"><?= GENERAL_INFO_MULTI_CHOISE ?></i>
                    </p>
<?php
    for($i = 2; $i <= count($object->stat); $i++){
?>
                    <p class="not-alone sub-option">
                        <input id="stat_<?= $i ?>" type="checkbox" name="stat_<?= $i ?>" class="css3-checkbox" value="1" <?= $object->stat[1] == "1" ? "" : "disabled" ?><?= $object->stat[$i-1] == 1 ? "CHECKED" : "" ?> disable />
                        <label for="stat_<?= $i ?>">
                            <?= constant("P3_ANSWER_".$i) ?>
                        </label>
                    </p>
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
            function aloneOrNot(alone){
                if(alone){
                    $(".not-alone input").prop("disabled", true);
                    for(var i = 2; i < <?= count($object->stat) ?>; i++){
                        $("#stat_" + i).prop("checked", false);
                    }
                }else{
                    $(".not-alone input").prop("disabled", false);
                }
            }
        </script>
    </body>
</html>