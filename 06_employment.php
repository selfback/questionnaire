<?php
    include("api/service.php");

    sessionCheck();

    class Employment{
        public $pageIndex = 0;
        public $employment = "";

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
        }

        public function preparedStack(&$tab){
            $temp = new stdClass();
            $temp->value = $this->employment;
            $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q01" => $temp));
        }

        public function loadValue($tab){
            if(isset($tab["p".getPageNumber($this->pageIndex)."_q01"])) $this->employment = $tab["p".getPageNumber($this->pageIndex)."_q01"]->value;
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new Employment($_SESSION['page_index']);
    $object->loadValue($user_response);

    $blockYes = false;
    $blockNo = false;
    
    if(!empty($_POST)){
        if(isset($_POST['employment']) && $_POST['employment'] != "") $object->employment = $_POST['employment'];
        else{
            $object->employment = "";
            $noGood = true;
        }

        $object->preparedStack($user_response);
        navigator($user_response, $_POST['action'], $noGood, $object->pageIndex);   
    }

    $blockYes = $object->employment != "" && $object->employment >= 0 && $object->employment < 3;
    $blockNo = $object->employment != "" && $object->employment >= 3 && $object->employment < 7;
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
                    <?= CATEGORIE_1 ?>: <?= P6_TITLE ?>
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
                        <?= P6_QUESTION ?>
                    </p>

                    <div class="bock-yes">
                        <p class="css3-radio">
                            <input id="radio_yes" type="radio" name="employedOrNot" value="1" <?= $blockYes ? "CHECKED" : "" ?> onchange="employedOrNotFunc(true);" />
                            <label class="toggler_r" for="radio_yes">
                                <?= GENERAL_YES ?>
                            </label>
                        </p>

<?php
    for($i = 0; $i < 3; $i++){
?>
                        <p class="css3-radio sub-option">
                            <input id="radio_yes<?= $i ?>" type="radio" name="employment" value="<?= $i ?>" <?= $blockYes ? "" : "disabled" ?> <?= $object->employment == "".$i ? "CHECKED" : "" ?> />
                            <label class="toggler_r" for="radio_yes<?= $i ?>">
                                <?= constant("P6_ANSWER_YES_".($i + 1)) ?>
                            </label>
                        </p>
<?php
    }
?>
                    </div>

                    <div class="bock-no">
                        <p class="css3-radio">
                            <input id="radio_no" type="radio" name="employedOrNot" value="0" <?= $blockNo ? "CHECKED" : "" ?> onchange="employedOrNotFunc(false);" />
                            <label class="toggler_r" for="radio_no">
                                <?= GENERAL_NO ?>
                            </label>
                        </p>
<?php
    for($i = 0; $i < 4; $i++){
?>
                        <p class="css3-radio sub-option">
                            <input id="radio_no<?= $i ?>" type="radio" name="employment" value="<?= ($i + 3) ?>" <?= $blockNo ? "" : "disabled" ?> <?= $object->employment == "".($i + 3) ? "CHECKED" : "" ?> />
                            <label class="toggler_r" for="radio_no<?= $i ?>">
                                <?= constant("P6_ANSWER_NO_".($i + 1)) ?>
                            </label>
                        </p>
<?php
    }
?>
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
            function employedOrNotFunc(employment){
                if(employment){
                    $(".bock-no .sub-option input").prop("disabled", true);
                    $(".bock-yes .sub-option input").prop("disabled", false);
                    $(".bock-no .sub-option input").prop("checked", false);
                }else{
                    $(".bock-yes .sub-option input").prop("disabled", true);
                    $(".bock-no .sub-option input").prop("disabled", false);
                    $(".bock-yes .sub-option input").prop("checked", false);
                }
            }
        </script>
    </body>
</html>