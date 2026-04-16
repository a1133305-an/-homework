<?php
    if(isset($_COOKIE['uName'])){
        echo $_COOKIE['uName']."歡迎回來";
        
        echo "<a href = 'DelCookie.php'>刪除COOKIE</a>";
    }
?>
<html>
    <title>臺大資料庫</title>
    <body bgcolor="#fff6e9">
        <center><b><h1 style="color: #000000"><font size = 100%>國立臺灣大學資料庫登入介面</font></b></h1><img src="pic.png" style="width: 40%; height: auto;"></center>
        <form action="logincheck.php" method="post"><center>
            <br/><hr/>
            <h2 style="color: #000000">ID&nbsp;<input type="text" name="nID" style="width: 10%; height: 30px;" required></h2>
            <h2 style="color: #000000">PASSWORD&nbsp;<input type="password" name="nPass" style="width: 10%; height: 30px;" required></h2>
            <br/><hr/>
            <b><input style='color: #000000' type="submit"><input style='color: #000000' type="reset"></b>
        </form></center>
    </body>
</html>