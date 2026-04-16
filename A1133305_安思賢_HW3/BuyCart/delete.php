<?php
    if(isset($_GET['Id'])) {
        $id = $_GET['Id']; 
        setcookie("Cart[" . $id . "]", "", time() - 100);
        header('Location: shopingcart.php');
        exit;
    }
?>