<?php
    include("api/service.php");

    sessionCheck();

    class PSFS{
        public $pageIndex = 0;
        public $numberOfActivities = 1;
        public $activity = null;
        public $scale = null;

        public $nbActivityMax = 2;

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
            $this->activity = new stdClass();
            $this->scale = new stdClass();
        }

        public function preparedStack(&$tab){
            $index = 1;
            for($i = 1; $i <= $this->numberOfActivities; $i++){
                if(isset($this->scale->{$i})){
                    $temp = new stdClass();
                    $temp->value = $this->activity->{$i};
                    $temp->scale = $this->scale->{$i};
                    $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q0".$index++ => $temp));
                }
            }

            for($i = $index; $i <= $this->nbActivityMax; $i++){
                unset($tab["p".getPageNumber($this->pageIndex)."_q0".$i]);
            }
        }

        public function loadValue($tab){
            for($i = 1; $i <= $this->nbActivityMax; $i++){
                if(isset($tab["p".getPageNumber($this->pageIndex)."_q0".$i])){
                    $this->activity->{$i} = $tab["p".getPageNumber($this->pageIndex)."_q0".$i]->value;
                    $this->scale->{$i} = $tab["p".getPageNumber($this->pageIndex)."_q0".$i]->scale;
                    $this->numberOfActivities = $i;
                }else break;
            }
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new PSFS($_SESSION['page_index']);
    $object->loadValue($user_response);

    if(!empty($_POST)){
        $oneActivityGood = false;
        $object->numberOfActivities = $_POST['numberOfActivities'];

        for($i = 1; $i <= $object->numberOfActivities; $i++){
            if(isset($_POST['activity_'.$i]) && trim($_POST['activity_'.$i]) != "" && isset($_POST['scale_'.$i])){
                $oneActivityGood = true;
                break;
            } 
        }

        $noGood = ! $oneActivityGood;

        for($i = 1; $i <= $object->numberOfActivities; $i++){
            if(isset($_POST['activity_'.$i]) && trim($_POST['activity_'.$i]) != "" && isset($_POST['scale_'.$i])){
                $object->activity->{$i} = trim($_POST['activity_'.$i]);
                $object->scale->{$i} = $_POST['scale_'.$i];
            }else{
                unset($object->activity->{$i});
                unset($object->scale->{$i});
            }
        }

        $object->preparedStack($user_response);
        navigator($user_response, $_POST['action'], $noGood, $object->pageIndex);

        if($_POST['action'] == "addActivity"){
            $noGood = false;
            $object->numberOfActivities++;
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
                    <?= CATEGORIE_2 ?>: <?= P17_TITLE ?>
                </h1>
                <form id="generalForm" action="" method="POST" class="general-form">
                    <input type="hidden" name="numberOfActivities" value="<?= $object->numberOfActivities ?>" />
                    <input type="hidden" id="action" name="action" />
                    <p>
                        <?= P17_LABEL ?>
                    </p>
<?php
    if($noGood){
?>                      
                    <div class="warning">
                        <?= P17_WARNING ?>
                    </div>
<?php
    }
?>                  
<?php
    for($i = 1; $i <= $object->numberOfActivities; $i++){
?>
                    <table class="patient-table row">
                        <tr>
                            <th class="patient-table-th patient-table-th-func">
                                <?= P17_ACTIVITY ?>
                            </th>
                            <td class="patient-table-td patient-table-td-func">
                                <input type="text" name="activity_<?= $i ?>" placeholder="<?= P17_ACTIVITY_PLACEHOLDER ?>" onfocus="this.placeholder = ''" onblur="this.placeholder = '<?= P17_ACTIVITY_PLACEHOLDER ?>'" class="patient-table-input-text" value="<?= isset($object->activity->{$i}) ? htmlspecialchars($object->activity->{$i}) : "" ?>" />
                            </td>
                        </tr>
                        <tr>    
                            <th class="patient-table-th patient-table-th-func">
                                <?= P17_SCALE ?>
                            </th>
                            <td class="patient-table-td patient-table-td-func">
                                <table>
                                    <tr>
<?php
        for($j = 0; $j <= 10; $j++){
?>                                  
                                        <td class="pain-cell css3-radio">
                                            <input id="scale_<?= $i ?>_<?= $j ?>" name="scale_<?= $i ?>" type="radio" value="<?= $j ?>" <?= isset($object->scale->{$i}) && $object->scale->{$i} == $j ? "CHECKED" : "" ?> />
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
                                        <?= P17_ABLE ?>
                                    </span>
                                    <span class="scale-right scale-right-func">
                                        <?= P17_UNABLE ?>
                                    </span>
                                </div>  
                            </td>           
                        </tr>
                    </table>
<?php 
    }
    if($object->numberOfActivities < $object->nbActivityMax){
?>
                    <div class="general-form-footer">
                        <input type="button" name="add" class="finish-input-button" value="<?= P17_BTN_MORE_ACTIVITY ?>" onclick="addActivity();" />
                    </div>
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