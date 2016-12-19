<?php
    $adminPage = true;
    include("../api/service.php");

    if(adminIsSet()){
        log_message("Admin is already set", SB_LOG_WARNING);
        header('location:../index.php');
        sqlClose();
        exit();
    }

    $isPost = false;
    $isGood = false;
    if(!empty($_POST)){
        $isPost = true;
        if(isset($_POST['password'])){
            $isGood = createAdmin($_POST['password']);

            if($isGood){
                $rows = userConnect("admin", $_POST['password']);
                $row = $rows[0];
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_uid'] = $row['uid'];
            }
            sqlClose(); 
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
    <header>
        <meta charset="utf-8">
        <link href='https://fonts.googleapis.com/css?family=Raleway:400,300,500,700,800' rel='stylesheet' type='text/css'>
        <link href='https://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>
        
        <link type="text/css" rel="stylesheet" href="../css/style.css">

        <script src="../js/lib/jquery-2.2.3.min.js"></script>
        <script src="../js/script.js"></script>
    </header>
    <body>
        <div class="content">
            <img src=".././img/logo.png" alt="logo" class="logo" />
            <p class="admin-tile">
                Admin
            </p>
            <div class="shadow">
                <div class="content-header"></div>
                <form id="adminPasswordForm" action="" method="POST" class="login-form">
                    <center>Please choose a new admin password</center>
                    <input id="password" type="password" name="password" placeholder="Password" class="login-form-input login-form-input-text" />
                    <input id="password2" type="password" name="password2" placeholder="Repeat Password" class="login-form-input login-form-input-text" />
                    <div id="pwdNotMatch" class="warning" style="display: none;">
                        <center>Passwords do not match</center>
                    </div>
                    <div id="pwdRegEx" class="warning" style="display: none;">
                        <center>Your password must be 6 characters minimum composed of letter(s) and number(s)</center>
                    </div>
                    <div class="general-form-footer">
                        <input type="button" class="general-form-input-button" value="Ok" onclick="submitPassword();"/>
                    </div>
                </form>
            </div>
        </div>
        <div class="footer"></div>
        <script>
<?php  
    if($isPost){
        if($isGood){
?>
            alert("The admin password has just been change.");
            document.location = "userList.php";
<?php  
        }else{
?>
            alert("A problem has been detected. Please contact selfBACK support.");
<?php
        }
    }
?>

            function submitPassword(){
                document.getElementById("pwdNotMatch").style.display = "none";
                document.getElementById("pwdRegEx").style.display = "none";

                var pwdRegEx = new RegExp("(?=(.*\\d))(?=.*[a-zA-Z])[0-9a-zA-Z]{6,}");

                var pwd = document.getElementById("password");
                var pwd2 = document.getElementById("password2");
                if(pwd.value != "" && pwd2.value != ""){
                    if(pwd.value != pwd2.value){
                        document.getElementById("pwdNotMatch").style.display = "";
                        return;
                    }

                    if(! pwdRegEx.test(pwd.value)){
                        document.getElementById("pwdRegEx").style.display = "";
                        return;
                    }
                    document.getElementById("adminPasswordForm").submit();
                    
                }
            }
        </script>
    </body>
</html>