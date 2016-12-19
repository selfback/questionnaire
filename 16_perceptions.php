<?php
    include("api/service.php");

    sessionCheck();

    class Perception{
        public $pageIndex = 0;
        public $stat = array ();

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
            for($i = 0; $i < 8; $i++){
                array_push($this->stat, "");
            }
        }

        public function preparedStack(&$tab){
            for($i = 1; $i <= count($this->stat); $i++){
                $temp = new stdClass();
                $temp->value = $this->stat[$i-1];
                $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i => $temp));
            }
        }

        public function loadValue($tab){
            for($i = 1; $i <= count($this->stat); $i++){
                if(isset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i])) 
                    $this->stat[$i-1] = $tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i]->value;
            }
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new Perception($_SESSION['page_index']);
    $object->loadValue($user_response);
    
    if(!empty($_POST)){
        for($i = 1; $i <= count($object->stat); $i++){
            if(isset($_POST['stat_'.$i]) && $_POST['stat_'.$i] != "") $object->stat[$i-1] = $_POST['stat_'.$i];
            else $noGood = true;
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
                    <?= CATEGORIE_2 ?>: <?= P16_TITLE ?>
                </h1>
                <form id="generalForm" action="" method="POST" class="general-form">
                    <input type="hidden" id="action" name="action" />
                    <p>
                        <?= P16_LABEL ?>
                    </p>
<?php
    if($noGood){
?>                      
                    <div class="warning">
                        <?= GENERAL_WARNING ?>
                    </div>
<?php
    }
?>
                    <table class="general-table row">
<?php
    for($i = 1; $i <= count($object->stat); $i++){
?>
                        <tr>
                            <td class="patient-table-td">
                                <p class="perception-question<?= $noGood && $object->stat[$i-1] == "" ? " elem-warning" : "" ?>">
                                    <?= constant("P16_QUESTION_".$i) ?>
                                </p>
                                <table class="sub-tab">
                                    <tr>
<?php
        for($j = 0; $j <= 10; $j++){
?>                                  
                                        <td class="pain-cell css3-radio">
                                            <input id="scale_<?= $i ?>_<?= $j ?>" name="stat_<?= $i ?>" type="radio" value="<?= $j ?>" <?= $object->stat[$i-1] == "".$j ? "CHECKED" : "" ?> />
                                            <label class="toggler_r" for="scale_<?= $i ?>_<?= $j ?>"></label>
                                        </td>
<?php
        }
?>
                                    </tr>
                                    <tr>
<?php
        for($j = 0; $j <= 10; $j++){
?>                                      
                                        <td class="pain-cell"><label for="scale_<?= $i ?>_<?= $j ?>"><?= $j ?></label></td>
<?php
        }
?>
                                    </tr>
                                </table>
                                <div class="scale-info">
                                    <span class="scale-left">
                                        <?= constant("P16_QUESTION_".$i."_0") ?>
                                    </span>
                                    <span class="scale-right">
                                        <?= constant("P16_QUESTION_".$i."_10") ?>
                                    </span>
                                </div>
                            </td>
                        </tr>
<?php
    }
?>
                    </table>

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