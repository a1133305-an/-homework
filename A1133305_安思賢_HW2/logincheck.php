<?php
    $fID = "raven";
    $fPass = "123456";

    if($fID == $_POST["nID"] && $fPass == $_POST["nPass"]){
        header("location: form.php");
    }else{
        echo "登入失敗";
        header("refresh:2 , url = login.php");
    }
?>