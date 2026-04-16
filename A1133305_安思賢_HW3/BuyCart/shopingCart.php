<body bgcolor="#dedcd9"></body>
<?php
    echo "<table border='1'>";
    echo "<tr><th>功能</th><th>名稱</th><th>價格</th><th>數量</th></tr>";
    
    $total = 0;

    echo "<h3>我的購物車內容：</h3>";
    if (isset($_COOKIE['Cart'])) {   
        foreach ($_COOKIE['Cart'] as $item => $quantity) {
            switch($item){
                case 'Ta':
                    $name = '平板電腦';
                    $price = 100;
                    break;
                case 'Ph':
                    $name = '哀鳳手機';
                    $price = 100;
                    break;
                case 'No':
                    $name = '筆記電腦';
                    $price = 50;
                    break;
            }
            echo "<tr>";
            echo "<td><a href='delete.php?Id=" . $item . "'>刪除</a></td>";
            echo "<td>" . $name . "</td>";
            echo "<td>" . $price . "</td>";
            echo "<td>" . $quantity . "</td>";
            echo "</tr>";

            $total += ($price * $quantity);
        }
        echo "<tr><td colspan='4' align='right'>總金額 = NT$" . $total . "元</td></tr>";
        echo "</table>";

        echo "<a href = 'catalog.php'>商品目錄</a>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<a href = 'shopingCart.php'>檢視購物車</a>";
    } else {
        echo "購物車目前是空的喔！";
    }
?>