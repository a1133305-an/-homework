<?php
    setcookie('uName','',time()-100);
    header("Refresh:0;url = login.php");
?>