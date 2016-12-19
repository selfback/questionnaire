<?php
    include("api/service.php");

    sessionCheck();

    class Instruction {
        public $pageIndex = 0;

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
        }

        public function preparedStack(&$tab){
        }

        public function loadValue($tab){
        }
    }

    $user_response = array();
    $noGood = false;
    $object = new Instruction($_SESSION['page_index']);

    if(isset($_SESSION['user_response'])){
        $user_response = (array)json_decode($_SESSION['user_response']);
    }else{
        $tmp = loadResult($_SESSION['user_id']);
        if($tmp != null){
            $user_response = (array)json_decode($tmp);

            if(isset($user_response["construct_next"])){
                log_message("Questionnaire resume on page index ".$user_response["construct_next"]);
                $_SESSION['user_response'] = json_encode($user_response);
                $_SESSION['page_index'] = $user_response["construct_next"];
                header('location:'.$NAVIGATION_PAGES[$user_response["construct_next"]]);
                exit();
            }
        }
        log_message("Questionnaire initialization");
    }
    $object->loadValue($user_response);

    if(!empty($_POST)){
        $object->preparedStack($user_response);
        navigator($user_response, "next", $noGood, $object->pageIndex);
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
                    <?= P1_TITLE ?>
                </h1>
                <form id="generalForm" action="" method="POST" class="general-form">
                    <input type="hidden" id="action" name="action" />
                    <div class="row">
                        <p>
                            <?= P1_LABEL_1 ?>
                        </p> 
                    </div>
                    <div class="row">
                        <p>
                            <?= P1_LABEL_2 ?>
                        </p> 
                    </div>
                    <div class="row">
                        <p>
                            <?= P1_LABEL_3 ?>
                        </p> 
                    </div>
                    <div class="general-form-footer">
                        <input type="button" name="next" class="general-form-input-button" value="<?= GENERAL_BTN_NEXT ?>" onclick="nextPage();" />
                    </div>
                </form>
            </div>
        </div>
        <div class="footer"></div>
    </body>
</html>