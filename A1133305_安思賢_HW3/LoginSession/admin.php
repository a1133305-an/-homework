<body bgcolor="#fff6e9"></body>
<?php
    session_start();
    if(isset($_SESSION['login'])){
        if($_SESSION['login'] == 'admin'){
            echo "<h1>hi admin<h1>";
            echo "<a href = 'logout.php'>Logout</a>";
        }else{
            echo "<h1>error<h1>";
            header("refresh:2 , url = login.php");
        }
    }else{
        echo "<h1>error<h1>";
        header("refresh:2 , url = login.php");
    }
?>