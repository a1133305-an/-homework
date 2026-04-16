<?php
    session_start();
    $_SESSION['ID'] = 'id';
?>

<body bgcolor="#dedcd9">
    <form action="saveCart.php" method="post">
    選擇訂購商品:<select name="nBuy">
                <option value="Ta">平板電腦 - $100</option>
                <option value="Ph">哀鳳手機 - $100</option>
                <option value="No">筆記型電腦 - $50</option>
                </select>
    <input type="number" name="nNum" style="width: 60px;">
    <input type="submit">
    <br><hr>
    </form>
</body>

<?php
    echo "<a href = 'catalog.php'>商品目錄</a>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<a href = 'shopingCart.php'>檢視購物車</a>";
?>

