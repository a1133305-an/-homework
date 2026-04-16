<body bgcolor="#fff6e9"></body>
<?php
    session_start();
    $sID = "student";
    $sPass = "1111";

    $tID = 'teacher';
    $tPass = '2222';

    $aID = 'admin';
    $aPass = '3333';

    $fID = $_POST["nID"];
    $fPass = $_POST["nPass"];

    $date = strtotime("+5 days",time()); 

    if($fID == $sID && $fPass == $sPass){
        $_SESSION['login'] = 'student';
        setcookie("uName",$fID,$date);
        header("location: student.php");
    }elseif($fID == $tID && $fPass ==  $tPass){
        $_SESSION['login'] = 'teacher';
        setcookie("uName",$fID,$date);
        header("location: teacher.php");
    }elseif($fID == $aID && $fPass ==  $aPass){
        $_SESSION['login'] = 'admin';
        setcookie("uName",$fID,$date);
        header("location: admin.php");
    }else{
        echo "登入失敗";
        header("refresh:2 , url = login.php");
    }
?>