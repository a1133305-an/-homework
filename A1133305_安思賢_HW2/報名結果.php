<?php
    $name = $_POST["nName"];
    if($_POST["mGender"] == "m"){
        $gen = "男";
    }else{
        $gen = "女";
    }
    switch($_POST["nCity"]){
        case "S":
            $age = "國小";
            break;
        case "J":
            $age = "國中";
            break;
        case "H":
            $age = "高中";
            break;
    }
    $nNum = $_POST["nNum"];
    $tNum = $_POST["tNum"];
    $comment = nl2br(stripslashes($_POST["comment"]));
?>
<html>
    <title>報名結果</title>
    <body bgcolor="#F5F5DC"><center>
        <b><h1><font size = 100%>報名成功！</font></b></h1><img src="阿賢夏令營-2.png" style="width: 40%; height: auto;">
        <br/><br/>
        <table border="1">
            <tr>
                <th>姓名</th>
                <th>性別</th>
                <th>學齡</th>
                <th>聯絡電話</th>
                <th>球衣號碼</th>
                <th>備註</th>
            </tr>
            <tr>
                <td><?php echo $name; ?></td>
                <td><?php echo $gen; ?></td>
                <td><?php echo $age; ?></td>
                <td><?php echo $nNum; ?></td>
                <td><?php echo $tNum; ?></td>
                <td><?php echo $comment; ?></td>
            </tr>
        </table>
    </body></center>
</html>