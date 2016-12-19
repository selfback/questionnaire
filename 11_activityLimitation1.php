<?php
    include("api/service.php");

    sessionCheck();

    class ActivityLimitation1{
        public $pageIndex = 0;
        public $work = "";
        public $leisure = "";

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
        }

        public function preparedStack(&$tab){
            $temp = new stdClass();
            $temp->value = $this->work;
            $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q01" => $temp));
            $temp = new stdClass();
            $temp->value = $this->leisure;
            $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q02" => $temp));
        }

        public function loadValue($tab){
            if(isset($tab["p".getPageNumber($this->pageIndex)."_q01"])) $this->work = $tab["p".getPageNumber($this->pageIndex)."_q01"]->value;
            if(isset($tab["p".getPageNumber($this->pageIndex)."_q02"])) $this->leisure = $tab["p".getPageNumber($this->pageIndex)."_q02"]->value;
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new ActivityLimitation1($_SESSION['page_index']);
    $object->loadValue($user_response);
    
    if(!empty($_POST)){
        if(isset($_POST['work']) && $_POST['work'] != "") $object->work = $_POST['work'];
        else $noGood = true;

        if(isset($_POST['leisure']) && $_POST['leisure'] != "") $object->leisure = $_POST['leisure'];
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
                    <?= CATEGORIE_2 ?>: <?= P11_TITLE ?>
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

                    <div id="historyTable" class="history-row">
                        <p>
                            <?= P11_QUESTION ?>
                        </p>
                        <table class="general-table">
                            <tr class="table-header">
                                <th>
                                    &nbsp;
                                </th>
                                <th class="radio-cell radio-cell-history">
                                    <?= GENERAL_YES ?>
                                </th>
                                <th class="radio-cell radio-cell-history">
                                    <?= GENERAL_NO ?>
                                </th>
                            </tr>
                            <tr>
                                <th class="history-table-question<?= $noGood && $object->work == "" ? " elem-warning" : "" ?>">
                                    <?= P11_QUESTION_1 ?>
                                </th>
                                <td class="radio-cell css3-radio">
                                    <input type="radio" id="work_1" name="work" value="1" <?= $object->work == "1" ? "CHECKED" : "" ?> />
                                    <label class="toggler_r" for="work_1"></label>
                                </th>
                                <td class="radio-cell css3-radio">
                                    <input type="radio" id="work_2" name="work" value="0" <?= $object->work == "0" ? "CHECKED" : "" ?> />
                                    <label class="toggler_r" for="work_2"></label>
                                </th>
                            </tr>
                            <tr>
                                <th class="history-table-question<?= $noGood && $object->leisure == "" ? " elem-warning" : "" ?>">
                                    <?= P11_QUESTION_2 ?>
                                </th>
                                <td class="radio-cell css3-radio">
                                    <input type="radio" id="leisure_1" name="leisure" value="1" <?= $object->leisure == "1" ? "CHECKED" : "" ?> />
                                    <label class="toggler_r" for="leisure_1"></label>
                                </th>
                                <td class="radio-cell css3-radio">
                                    <input type="radio" id="leisure_2" name="leisure" value="0" <?= $object->leisure == "0" ? "CHECKED" : "" ?> />
                                    <label class="toggler_r" for="leisure_2"></label>
                                </th>
                            </tr>
                        </table>
                    </div>
                        

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