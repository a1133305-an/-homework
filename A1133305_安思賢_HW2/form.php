<html>
    <title>表單介面</title>
    <body bgcolor="#F5F5DC">
        <form action="報名結果.php" method="post"><center>
            <b><h1><font size = 100%>填寫表單</font></b></h1><img src="阿賢夏令營-2.png" style="width: 40%; height: auto;">
            <br/><br/>
            姓名:<input type="text" placeholder="必填" name="nName" required>
            <br/><br/>
            性別:
            男<input type="radio" name="mGender" value="m">
            女<input type="radio" name="mGender" value="f" checked>
            <br/><br/>
            學齡:<select name="nCity" >
            <option value="S">國小</option>
            <option value="J">國中</option>
            <option value="H">高中</option>
            </select>
            <br/><br/>
            聯絡電話:<input type="text" placeholder="必填" name="nNum" required>
            <br/><br/>
            球衣號碼<input type="number" name="tNum">
            <br/><br/>
            備註:<textarea name="comment" rows="2"col="2"></textarea>
            <br/><br/>
            <input type="submit"><input type="reset">
        </form></center>
    </body>
</html>

