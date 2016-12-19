<?php
    include("api/service.php");

    sessionCheck();

    class PainMedication{
        public $pageIndex = 0;
        public $medication = "";

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
        }

        public function preparedStack(&$tab){
            $temp = new stdClass();
            $temp->value = $this->medication;
            $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q01" => $temp));
        }

        public function loadValue($tab){
            if(isset($tab["p".getPageNumber($this->pageIndex)."_q01"])) $this->medication = $tab["p".getPageNumber($this->pageIndex)."_q01"]->value;
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new PainMedication($_SESSION['page_index']);
    $object->loadValue($user_response);
    
    if(!empty($_POST)){
        if(isset($_POST['medication']) && $_POST['medication'] != "") $object->medication = $_POST['medication'];
        else $noGood = true;

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
                    <?= CATEGORIE_2 ?>: <?= P12_TITLE ?>
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
                        <?= P12_QUESTION ?>
                    </p>
<?php
    for($i = 0; $i < 4; $i++){
?>  
                    <p class="row css3-radio">
                        <input id="radio<?= $i ?>" type="radio" name="medication" value="<?= $i ?>" <?= $object->medication == "".$i ? "CHECKED" : "" ?> />
                        <label class="toggler_r" for="radio<?= $i ?>">
                            <?= constant("P12_ANSWER_".($i + 1)) ?>
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
    </body>
</html>