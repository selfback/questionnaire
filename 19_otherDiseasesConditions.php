<?php
    include("api/service.php");

    sessionCheck();

    class DiseasesConditions {
        public $pageIndex = 0;
        public $stat = array ();
        public $other = "";
        public $none = "0";
        public $limbIndex = -1;
        public $limb = "";
        public $limbYes1 = "";
        public $limbYes2 = "";
        public $breathAtRest = "";

        private $otherIndex = "0";
        private $noneIndex = "0";

        private $breathAtRestQuestions = "--1-2-4-5-";

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
            for($i = 0; $i < 17; $i++){
                array_push($this->stat, "0");
                if(defined("P19_ANSWER_".$i."_PLUS")){
                    $this->limbIndex = $i;
                }
            }
            $this->otherIndex = count($this->stat);
            $this->noneIndex = $this->otherIndex + 1;
        }

        public function preparedStack(&$tab){
            for($i = 1; $i <= count($this->stat); $i++){
                $temp = new stdClass();
                $temp->value = $this->stat[$i-1];
                $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i => $temp));

                if($i == $this->limbIndex && $this->stat[$i-1] == 1){
                    $temp = new stdClass();
                    $temp->value = $this->limb;
                    $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb" => $temp));
                    if($this->limb == 1){
                        $temp = new stdClass();
                        $temp->value = $this->limbYes1;
                        $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb_yes1" => $temp));
                        $temp = new stdClass();
                        $temp->value = $this->limbYes2;
                        $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb_yes2" => $temp));
                    }else{
                        unset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb_yes1"]);
                        unset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb_yes2"]);
                    }
                }else{
                    unset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb"]);
                    unset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb_yes1"]);
                    unset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb_yes2"]);
                }
            }

            if($this->breathAtRest != ""){
                $temp = new stdClass();
                $temp->value = $this->breathAtRest;
                $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q_breathAtRest" => $temp));
            }else{
                unset($tab["p".getPageNumber($this->pageIndex)."_q_breathAtRest"]);
            }
            

            $temp = new stdClass();
            $temp->value = $this->other;
            $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q".$this->otherIndex => $temp));

            $temp = new stdClass();
            $temp->value = $this->none;
            $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q".$this->noneIndex => $temp));
        }

        public function loadValue($tab){
            for($i = 1; $i <= count($this->stat); $i++){
                if(isset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i])) 
                    $this->stat[$i-1] = $tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i]->value;
                if($i == $this->limbIndex){
                    if(isset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb"]))
                        $this->limb = $tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb"]->value;
                    if(isset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb_yes1"]))
                        $this->limbYes1 = $tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb_yes1"]->value;
                    if(isset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb_yes2"]))
                        $this->limbYes2 = $tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i."_limb_yes2"]->value;
                }
            }

            if(isset($tab["p".getPageNumber($this->pageIndex)."_q_breathAtRest"]))
                $this->breathAtRest = $tab["p".getPageNumber($this->pageIndex)."_q_breathAtRest"]->value;

            if(isset($tab["p".getPageNumber($this->pageIndex)."_q".$this->noneIndex]))
                $this->other = $tab["p".getPageNumber($this->pageIndex)."_q".$this->otherIndex]->value;

            if(isset($tab["p".getPageNumber($this->pageIndex)."_q".$this->otherIndex]))
                $this->none = $tab["p".getPageNumber($this->pageIndex)."_q".$this->noneIndex]->value;
        }

        function isBreathIndex($index){
            $test = strpos($this->breathAtRestQuestions, "-".$index."-");
            if($test && $test >= 1){
                return true;
            }else{
                return false;
            }
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new DiseasesConditions($_SESSION['page_index']);
    $object->loadValue($user_response);

    $noGoodLimb = false;
    $noGoodLimbYes = false;
    $noGoodBreathAtRest = false;
    $needBreathAtRest = false;

    if(!empty($_POST)){
        $moreThanOne = 0;
        for($i = 1; $i <= count($object->stat); $i++){
            if(isset($_POST['stat_'.$i]) && $_POST['stat_'.$i] != ""){
                $object->stat[$i-1] = $_POST['stat_'.$i];
                $moreThanOne++;
            }else{
                $object->stat[$i-1] = 0;
            }

            if($i == $object->limbIndex){
                if($object->stat[$i-1] == 1){
                    if(isset($_POST['limb']) && $_POST['limb'] != ""){
                        $object->limb = $_POST['limb'];
                        if($object->limb == 1){
                            if(isset($_POST['limbYes1']) && $_POST['limbYes1'] != ""){
                                $object->limbYes1 = $_POST['limbYes1'];
                            }else{
                                $object->limbYes1 = "0";
                            }
                            if(isset($_POST['limbYes2']) && $_POST['limbYes2'] != ""){
                                $object->limbYes2 = $_POST['limbYes2'];
                            }else{
                                $object->limbYes2 = "0";
                            }
                            if($object->limbYes1 == "0" && $object->limbYes2 == "0"){
                                $noGoodLimbYes = true;
                                $noGood = true;
                            }
                        }else{
                            $object->limbYes1 = "";
                            $object->limbYes2 = "";
                        }
                    }else{
                        $object->limb = "";
                        $object->limbYes1 = "";
                        $object->limbYes2 = "";
                        $noGoodLimb = true;
                        $noGood = true;
                    }
                }else{
                    $object->limb = "";
                    $object->limbYes1 = "";
                    $object->limbYes2 = "";
                }
            }

            if($object->isBreathIndex($i) && $object->stat[$i-1] == 1){
                $needBreathAtRest = true;
            }
        }

        if($needBreathAtRest){
            if(isset($_POST['breathAtRest']) && trim($_POST['breathAtRest']) != ""){
                $object->breathAtRest = $_POST['breathAtRest'];
            }else{
                $object->breathAtRest = "";
                $noGood = true;
                $noGoodBreathAtRest = true;
            }
        }else{
            $object->breathAtRest = "";
        }

        if(isset($_POST['other']) && trim($_POST['other']) != ""){
            $object->other = trim($_POST['other']);
            $moreThanOne++;
        }else{
            $object->other = "";
        }

        if(isset($_POST['none']) && trim($_POST['none']) != ""){
            $object->none = trim($_POST['none']);
            $moreThanOne++;
        }else{
            $object->none = "";
        }

        if($moreThanOne == 0){
            $noGood = true;
        }

        $object->preparedStack($user_response);
        navigator($user_response, $_POST['action'], $noGood, $object->pageIndex);
    }

    $breathAtRestInitShow = 0;
    for($i = 1; $i <= count($object->stat); $i++){
        if($object->isBreathIndex($i) && $object->stat[$i-1] == 1){
            $breathAtRestInitShow++;
        }
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
                    <?= CATEGORIE_3 ?>: <?= P19_TITLE ?>
                </h1>
                <form id="generalForm" action="" method="POST" class="general-form">
                    <input type="hidden" id="action" name="action" />
<?php
    if($noGood){
?>                      
                    <div class="warning">
                        <?= ANSWER_CORRECTLY ?>
                    </div>
<?php
    }
?>
                    <p class="row<?= $noGood ? " elem-warning" : "" ?>">
                        <?= P19_QUESTION ?>
                    </p>
                    <p>
                        <i class="i-info"><?= GENERAL_INFO_MULTI_CHOISE ?></i>
                    </p>
<?php
    for($i = 1; $i <= count($object->stat); $i++){
        $optionPlus = $i == $object->limbIndex;
?>
                    <p id="question_<?= $i ?>">
                        <label for="stat_<?= $i ?>"><?= constant("P19_ANSWER_".$i) ?></label>
                        <input id="stat_<?= $i ?>" type="checkbox" name="stat_<?= $i ?>" class="css3-checkbox stat_checkbox" value="1" <?= $object->stat[$i-1] == 1 ? "CHECKED" : "" ?> onchange="resetNone();<?= $optionPlus ? " optionPlus(this);" : "" ?><?= $object->isBreathIndex($i) ? " breathAtRestFunction(this);" : "" ?>" />
                        <label for="stat_<?= $i ?>">&nbsp;</label>
                    </p>
<?php 
        if($optionPlus){
?>
                    <div class="sub-option" id="sub-option-<?= $i ?>"<?= $object->stat[$i-1] != 1 ? " style='display: none;'" : "" ?>>
                        <p class="row<?= $noGood && $noGoodLimb ? " elem-warning" : "" ?>">
                            <?= constant("P19_ANSWER_".$i."_PLUS") ?>
                        </p>
                        <p class="css3-radio">
                            <input id="limbNo" name="limb" type="radio" value="0" class="stat_radio_limb" <?= $object->limb == "0" ? "CHECKED" : "" ?> onchange="limbChange(this);"/>
                            <label class="toggler_r" for="limbNo">
                                <?= GENERAL_NO ?>
                            </label>
                        </p>
                        <p class="css3-radio">
                            <input id="limbYes" name="limb" type="radio" value="1" class="stat_radio_limb" <?= $object->limb == "1" ? "CHECKED" : "" ?> onchange="limbChange(this);"/>
                            <label class="toggler_r" for="limbYes">
                                <?= GENERAL_YES ?>
                            </label>
                        </p>
                        <div class="sub-option" id="limbYesBlock"<?= $object->limb != 1 ? " style='display: none;'" : "" ?>>
                            <p class="row<?= $noGood && $noGoodLimbYes ? " elem-warning" : "" ?>">
                                <?= constant("P19_ANSWER_".$i."_PLUS_YES") ?>
                            </p>
                            <p>
                                <label for="limbYes1"><?= constant("P19_ANSWER_".$i."_PLUS_YES_1") ?></label>
                                <input id="limbYes1" type="checkbox" name="limbYes1" class="css3-checkbox stat_checkbox stat_checkbox_limbYes" value="1" <?= $object->limbYes1 == 1 ? "CHECKED" : "" ?>/>
                                <label for="limbYes1">&nbsp;</label>
                            </p>
                            <p>
                                <label for="limbYes2"><?= constant("P19_ANSWER_".$i."_PLUS_YES_2") ?></label>
                                <input id="limbYes2" type="checkbox" name="limbYes2" class="css3-checkbox stat_checkbox stat_checkbox_limbYes" value="1" <?= $object->limbYes2 == 1 ? "CHECKED" : "" ?>/>
                                <label for="limbYes2">&nbsp;</label>
                            </p>
                        </div>


                    </div>
<?php
        }
    }
?>
                    <div id="breathAtRest" style="display: none;">
                        <span class="breath-question left-col<?= $noGood && $noGoodBreathAtRest ? " elem-warning" : "" ?>">
                            <?= P19_QUESTION_BREATH ?>
                        </span>
                        <span class="left-col css3-radio">
                            <input id="breathNo" name="breathAtRest" type="radio" class="stat_radio_breath" value="0" <?= $object->breathAtRest == "0" ? "CHECKED" : "" ?> />
                            <label class="toggler_r" for="breathNo">
                                <?= GENERAL_NO ?>
                            </label>
                        </span>
                        <span class="left-col css3-radio">
                            <input id="breathYes" name="breathAtRest" type="radio" class="stat_radio_breath" value="1" <?= $object->breathAtRest == "1" ? "CHECKED" : "" ?> />
                            <label class="toggler_r" for="breathYes">
                                <?= GENERAL_YES ?>
                            </label>
                        </span>
                        <div class="clearfix"></div>
                    </div>
                    <p>
                        <label for="other">
                            <?= P19_ANSWER_OTHER ?>
                        </label>
                        <input id="other" type="text" name="other" class="general-input-text long-input-text" value="<?= htmlspecialchars($object->other) ?>" onchange="resetNone();" onkeypress="resetNone();" />
                    </p>
                    <br>
                    <p>
                        <input id="none" type="checkbox" name="none" class="css3-checkbox" value="1" <?= $object->none == 1 ? "CHECKED" : "" ?> onchange="resetAllExpectNone();" />
                        <label for="none">
                            <?= P19_ANSWER_NONE ?>
                        </label>
                    </p>

                    <div class="general-form-footer">
                        <input type="button" name="previous" class="general-form-input-button general-form-input-button-previous" value="<?= GENERAL_BTN_PREVIOUS ?>" onclick="page('P');" />
                        <input type="button" name="next" class="general-form-input-button" value="<?= GENERAL_BTN_NEXT ?>" onclick="page('N');" />
                    </div>
                </form>
            </div>
        </div>
        <div class="footer"></div>
        <script>
            function resetNone(){
                $("#none").prop("checked", false);
            }

            function resetAllExpectNone(){
                $(".stat_checkbox").prop("checked", false);
                $("#sub-option-<?= $object->limbIndex ?>:visible").hide();
                $("#limbYesBlock:visible").hide();
                resetLimb();
                resetLimbYes();
                $("#breathAtRest:visible").hide();
                resetBreath();
                $("#other").val("");
            }

            function optionPlus(elem){
                if($(elem).prop("checked")){
                    $("#sub-option-<?= $object->limbIndex ?>").show();
                }else{
                    $("#sub-option-<?= $object->limbIndex ?>").hide();
                    $("#limbYesBlock").hide();
                    resetLimb();
                    resetLimbYes();
                }
            }

            function limbChange(elem){
                if(elem.value == 1){
                    $("#limbYesBlock").show();
                }else{
                    $("#limbYesBlock").hide();
                    resetLimbYes();
                }
            }

            function resetLimb(){
                $(".stat_radio_limb").prop("checked", false);
            }

            function resetLimbYes(){
                $(".stat_checkbox_limbYes").prop("checked", false);
            }

            var breathAtRestInit = <?= $breathAtRestInitShow ?>;

            function breathAtRestFunction(elem){
                if(elem != null){
                    if($(elem).prop("checked")){
                        breathAtRestInit++;
                    }else{
                        breathAtRestInit--;
                    }
                }
                if(breathAtRestInit > 0){
                    $("#breathAtRest:hidden").show();
                }else{
                    $("#breathAtRest:visible").hide();
                    resetBreath();
                }
            }

            function resetBreath(){
                $(".stat_radio_breath").prop("checked", false);
            }

            breathAtRestFunction(null);
        </script>
    </body>
</html>