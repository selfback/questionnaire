<?php
    include("api/service.php");

    sessionCheck();

    class PainLastWeek{
        public $pageIndex = 0;
        public $averagePain = "";
        public $worstPain = "";

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
        }

        public function preparedStack(&$tab){
            $temp = new stdClass();
            $temp->value = $this->averagePain;
            $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q01" => $temp));
            $temp = new stdClass();
            $temp->value = $this->worstPain;
            $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q02" => $temp));
        }

        public function loadValue($tab){
            if(isset($tab["p".getPageNumber($this->pageIndex)."_q01"])) $this->averagePain = $tab["p".getPageNumber($this->pageIndex)."_q01"]->value;
            if(isset($tab["p".getPageNumber($this->pageIndex)."_q02"])) $this->worstPain = $tab["p".getPageNumber($this->pageIndex)."_q02"]->value;
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new PainLastWeek($_SESSION['page_index']);
    $object->loadValue($user_response);
    
    if(!empty($_POST)){
        if(isset($_POST['averagePain']) && $_POST['averagePain'] != "") $object->averagePain = $_POST['averagePain'];
        else $noGood = true;
        if(isset($_POST['worstPain']) && $_POST['worstPain'] != "") $object->worstPain = $_POST['worstPain'];
        else{
            $object->worstPain = "";
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
                    <?= CATEGORIE_2 ?>: <?= P8_TITLE ?>
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
                    <p<?= $noGood && $object->averagePain == "" ? " class='elem-warning'" : "" ?>>
                        <?= P8_QUESTION_1 ?>
                    </p>
                    <table>
                        <tr>
<?php
    for($i = 0; $i <= 10; $i++){
?>
                            <td class="pain-cell css3-radio">
                                <input id="averagePain_<?= $i ?>" name="averagePain" type="radio" value="<?= $i ?>" <?= $object->averagePain == "".$i ? "CHECKED" : "" ?> onchange="averagePainChange(this);" />
                                <label class="toggler_r" for="averagePain_<?= $i ?>"></label>
                            </td>
<?php
    }
?>
                        </tr>
                        <tr>
<?php
    for($i = 0; $i <= 10; $i++){
?>                          
                            <td class="pain-cell"><label for="averagePain_<?= $i ?>"><?= $i ?></label></td>
<?php
    }
?>
                        </tr>
                    </table>
                    <div class="scale-info">
                        <span class="scale-left scale-left-15">
                            <?= P8_SCALE_0 ?>
                        </span>
                        <span class="scale-right scale-right-14-15">
                            <?= P8_SCALE_10 ?>
                        </span>
                    </div>

                    <p<?= $noGood && $object->worstPain == "" ? " class='elem-warning'" : "" ?>>
                        <?= P8_QUESTION_2 ?>
                    </p>
                    <table>
                        <tr>
<?php
    for($i = 0; $i <= 10; $i++){
?>                      
                            <td class="pain-cell css3-radio">
                                <input id="worstPain_<?= $i ?>" name="worstPain" type="radio" value="<?= $i ?>" <?= $object->worstPain == "".$i ? "CHECKED" : "" ?> <?= $object->averagePain > "".$i ? "disabled" : "" ?>/>
                                <label class="toggler_r" for="worstPain_<?= $i ?>"></label>
                            </td>
<?php
    }
?>
                        </tr>
                        <tr>
<?php
    for($i = 0; $i <= 10; $i++){
?>                          
                            <td class="pain-cell"><label for="worstPain_<?= $i ?>"><?= $i ?></label></td>
<?php
    }
?>
                        </tr>
                    </table>
                    <div class="scale-info">
                        <span class="scale-left scale-left-15">
                            <?= P8_SCALE_0 ?>
                        </span>
                        <span class="scale-right scale-right-14-15">
                            <?= P8_SCALE_10 ?>
                        </span>
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
            function averagePainChange(elem){
                for(var i = 0; i < elem.value; i++){
                    $("#worstPain_" + i).prop('disabled', true);
                    $("#worstPain_" + i).prop('checked', false);
                }

                for(var i = elem.value; i <= 10; i++){
                    $("#worstPain_" + i).prop('disabled', false);
                }
            }
        </script>
    </body>
</html>