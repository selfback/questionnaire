<?php
    include("api/service.php");

    sessionCheck();

    class Mannequin{
        public $pageIndex = 0;
        public $stat = array ();
        public $nbElem = 0;
        public $checkName = array(
            ["name" => "neck"      , "x" => 253, "y" =>  55],
            ["name" => "shoulders" , "x" => 667, "y" => 118],
            ["name" => "upper_back", "x" => 253, "y" => 152],
            ["name" => "elbows"    , "x" => 667, "y" => 212],
            ["name" => "lower_back", "x" => 253, "y" => 247],
            ["name" => "hands"     , "x" => 667, "y" => 304],
            ["name" => "hips"      , "x" => 253, "y" => 324],
            ["name" => "knees"     , "x" => 667, "y" => 434],
            ["name" => "feets"     , "x" => 253, "y" => 556],
        );
        public $size = 42;

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
            $this->nbElem = count($this->checkName);
            for($i = 0; $i < $this->nbElem; $i++){
                array_push($this->stat, "0");
            }
        }

        public function preparedStack(&$tab){
            for($i = 1; $i <= $this->nbElem; $i++){
                $temp = new stdClass();
                $temp->value = $this->stat[$i-1];
                $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i => $temp));
            }
        }

        public function loadValue($tab){
            for($i = 1; $i <= $this->nbElem; $i++){
                if(isset($tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i])) 
                    $this->stat[$i-1] = $tab["p".getPageNumber($this->pageIndex)."_q".($i < 10 ? "0" : "").$i]->value;
            }
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new Mannequin($_SESSION['page_index']);
    $object->loadValue($user_response);
    
    if(!empty($_POST)){
        $moreThanOne = 0;
        for($i = 1; $i <= count($object->stat); $i++){
            if(isset($_POST['stat_'.$i]) && $_POST['stat_'.$i] != "0"){
                $object->stat[$i-1] = $_POST['stat_'.$i];
                $moreThanOne++;
            }else{
                $object->stat[$i-1] = 0;
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

        <style type="text/css">
<?php
    for($i = 0; $i < $object->nbElem; $i++){
        $checkName = (object)$object->checkName[$i];
?>
            .check-<?= $checkName->name ?>{
                position: absolute;
                left: <?= $checkName->x ?>px;
                top: <?= $checkName->y ?>px;
                z-index: 5;
            }

<?php 
    }
?>
        </style>

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
                    <?= CATEGORIE_3 ?>: <?= P18_TITLE ?>
                </h1>
                <form id="generalForm" action="" method="POST" class="general-form">
                    <input type="hidden" id="action" name="action" />
                    <p>
                        <?= P18_LABEL ?>
                    </p>
<?php
    if($noGood){
?>
                    <div class="warning">
                        <?= AT_LEAST_ONE_WARNING ?>
                    </div>
<?php
    }
?>
                    <div class="mannequin-block">
<?php
    for($i = 0; $i < $object->nbElem; $i++){
        $checkName = (object)$object->checkName[$i];
?>
                        <img src="img/mannequin_check.png" class="check-<?= $checkName->name ?>" <?= $object->stat[$i] == 0 ? "style='display: none;'" : "" ?> />
                        <input type="hidden" name="stat_<?= $i + 1 ?>" value="<?= $object->stat[$i] ?>">
<?php
    }
?>
                        <img src="img/mannequin_<?= $MANNEQUIN_LANG ?>.png" usemap="#mannequin" class="mannequin-img"/>
                        <map name="mannequin">
<?php
    for($i = 0; $i < $object->nbElem; $i++){
        $checkName = (object)$object->checkName[$i];
?>
                            <area shape="rect" class="<?= $checkName->name ?>" coords="<?= $checkName->x ?>, <?= $checkName->y ?>, <?= $checkName->x + $object->size ?>, <?= $checkName->y + $object->size ?>" href="#<?= $checkName->name ?>" alt="<?= constant("P18_".strtoupper($checkName->name)) ?>" />
<?php
    }
?>
                        </map>
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
            $(function() {
<?php
    for($i = 0; $i < $object->nbElem; $i++){
        $checkName = (object)$object->checkName[$i];
?>
                $(".<?= $checkName->name ?>").on("click", function(e){
                    e.preventDefault();
                    var elem = $("input[name='stat_<?= $i + 1 ?>']");
                    if(elem.val() == 1) elem.val(0);
                    else elem.val(1);
                    $(".check-<?= $checkName->name ?>").toggle();
                });
<?php
    }
?>
            });
        </script>
    </body>
</html>