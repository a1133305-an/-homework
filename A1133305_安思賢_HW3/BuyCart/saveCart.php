<?php
    session_start();
    if(!isset($_SESSION['ID'])){
        header('Refresh:0 ; url = catalog.php');
        exit;
    }
    if(isset($_POST['nBuy']) && isset($_POST['nNum'])){
        $nBuy = $_POST['nBuy'];
        $num = $_POST['nNum'];

        setcookie('Cart[' . $nBuy . "]", $num); #Ex: Cart[Ta] , '.'在php內就是拍森的','
        header('Location: catalog.php');
        exit;
    }
?>