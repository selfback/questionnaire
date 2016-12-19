<?php
    include("api/service.php");

    sessionCheck();

    class QualityOfLife{
        public $pageIndex = 0;
        public $scale = "-1";
        public $scaleDisplay = "0";

        function __construct($pageIndex){
            $this->pageIndex = $pageIndex;
        }

        public function preparedStack(&$tab){
            $temp = new stdClass();
            $temp->value = $this->scale;
            $tab = array_merge($tab, array("p".getPageNumber($this->pageIndex)."_q01" => $temp));
        }

        public function loadValue($tab){
            if(isset($tab["p".getPageNumber($this->pageIndex)."_q01"])) $this->scale = $tab["p".getPageNumber($this->pageIndex)."_q01"]->value;
        }
    }

    $user_response = (array)json_decode($_SESSION['user_response']);
    $noGood = false;
    $object = new QualityOfLife($_SESSION['page_index']);
    $object->loadValue($user_response);

    if(!empty($_POST)){
        if(isset($_POST['scale']) && $_POST['scale'] != "") $object->scale = $_POST['scale'];
        else $noGood = true;

        $object->preparedStack($user_response);
        navigator($user_response, $_POST['action'], $noGood, $object->pageIndex);
    }

    if($object->scale > -1) $object->scaleDisplay = $object->scale;
?>
<!DOCTYPE html>
<html lang="en">
    <header>
        <meta charset="utf-8">
        <link href='https://fonts.googleapis.com/css?family=Raleway:400,300,500,700,800' rel='stylesheet' type='text/css'>
        <link href='https://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>
        
        <link type="text/css" rel="stylesheet" href="css/style.css">

        <script src="js/lib/jquery-2.2.3.min.js"></script>
        <script src="js/lib/jquery-ui-1.12.1.min.js"></script>
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
                    <?= CATEGORIE_3 ?>: <?= P20_TITLE ?>
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
                    <div class="scale-title">
                        <?= P20_2_SCALE_HEADER ?>
                    </div>
                    <div class="scale-instruction">
                        <ul>
<?php
    for($i = 1; $i <= 4; $i++){
?>
                            <li><?= constant("P20_2_INSTRUCTION_".$i) ?></li>
<?php
    }
?>
                        </ul>

                        <div class="scale-result-block">
                            <div class="sentence">
                                <strong><?= P20_2_RESULT ?></strong>
                            </div>
                            <div class="result">
                                <input type="text" value="<?= $object->scale == -1 ? '0' : $object->scale ?>" name="scale">
                            </div>
                        </div>
                    </div>
                    <div class="scale-img">
                        <img src="img/scale.png"/> 
                        <div class="scale-container">
                            <div class="scale-thumb"></div>
                        </div>
                    </div>
                    <div class="scale-title">
                        <?= P20_2_SCALE_FOOTER ?>
                    </div>
                    <div class="copyright">
                        <?= P20_COPYRIGHT ?>
                    </div>
                    <div class="general-form-footer">
                        <input type="button" name="previous" class="general-form-input-button general-form-input-button-previous" value="<?= GENERAL_BTN_PREVIOUS ?>" onclick="page('P');" />
                        <input type="button" name="next" class="general-form-input-button" value="<?= GENERAL_BTN_NEXT ?>" onclick="checkScaleBeforeNextPage()" />
                    </div>
                </form>
            </div>
        </div>
        <div class="footer"></div>
        <script>
            var scaleHasInit = <?= $object->scale > -1 ? "true" : "false" ?>;

            $(function(){
                var quantum = 7;
                var scaleOffset = 7;
                var imgOffset = 4;
                var resultElem = $('.scale-result-block .result input');
                var thumbElem = $(".scale-thumb");
                var savInput = resultElem.val();

                var initPosi = (100 - <?= $object->scale > -1 ? $object->scale : 0 ?>) * quantum;
                thumbElem.css("top", initPosi);

                function posYToPercent(posY){
                    var percent = Math.round(100 - posY / quantum);
                    if(percent < 0) percent = 0;
                    if(percent > 100) percent = 100;
                    return percent;
                }

                function imgClick(event){
                    var posY = event.pageY - $(this).offset().top - imgOffset;
                    scaleClick(null, posYToPercent(posY));
                }

                function scaleClick(event, initPercent){
                    var percent;
                    if(typeof initPercent == "undefined"){
                        var posY = event.pageY - $(this).offset().top - scaleOffset;
                        percent = posYToPercent(posY);
                    }else{
                        percent = initPercent;
                    }
                    resultElem.val(percent);
                    savInput = percent;
                    var thumbPosi = (100 - percent) * quantum;
                    thumbElem.css("top", thumbPosi);
                    scaleHasInit = true;
                }

                function scaleMove(event){
                    var result = 100 - parseInt($(event.target).css("top")) / quantum;
                    resultElem.val(result);
                    savInput = result;
                    scaleHasInit = true;
                }

                function inputChange(event){
                    var reg = /^\d+$/;
                    var value = $(event.target).val();

                    if(value != "" && ! (reg.test(value) && value >= 0 && value <= 100)){
                        alert("<?= P20_2_ALERT_NUMBER ?>");
                        $(event.target).val(savInput);
                    }else{
                        scaleHasInit = true;
                    }

                    var percent = $(event.target).val();
                    var thumbPosi = (100 - percent) * quantum;
                    thumbElem.css("top", thumbPosi);
                }

                $(".scale-img img").on("click", imgClick);
                $(".scale-container").on("click", scaleClick);
                resultElem.on('input change', inputChange);
                thumbElem.draggable({containment: "parent", axis: "y", grid: [ 7, 7 ], drag: scaleMove});
            });

            function checkScaleBeforeNextPage(){
                if(scaleHasInit) page('N');
                else{
                    if(confirm("<?= P20_2_CONFIRM ?>")){
                        page('N');
                    }
                }
            }
        </script>
    </body>
</html>