<html>
  <head>
    <meta charset="UTF-8">
    <title>科学科実験Aサンプルプログラム</title>
    <!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.16.22/dist/css/uikit.min.css" />

    <!-- UIkit JS -->
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.16.22/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.16.22/dist/js/uikit-icons.min.js"></script>
  </head>

  <body class="uk-flex uk-flex-column uk-padding uk-background-muted" style="font-family:cursive, san-serif;">
    <h1 class="uk-align-center">住所検索</h1>

    <form action="database.php" method="GET">
      <div class="uk-flex uk-flex-center">
        <div class="uk-inline">
          <span class="uk-form-icon" uk-icon="icon: home"></span>
          <input class="uk-input uk-form-width midium" type="text" name="keyword">
        </div>
        <input type="hidden" name="page" value="1">
        <input type="submit" value="検索">
      </div>
    </form>
<?php
# 初期設定
$mysqli = new mysqli('localhost', 'shizutaro', 'Fate46ushizutaro', 'CSexp1DB', 80);

# mysqlとの接続
if ($mysqli->connect_error) {
  echo $mysqli->connect_error;
  exit();
} else {
  $mysqli->set_charset("utf8");
}

# クエリの受け取り
if (!empty($_GET['keyword'])){
  $keyword = $_GET['keyword'];
}
# keywordクエリの中身が何もなかった場合終了
if (!isset($keyword) || empty($keyword)) {
  exit();
}

echo "<h2>\"$keyword\"". "の検索結果</h2>";

# クエリの受け取り
if (!empty($_GET['page'])){
  $page = $_GET['page'];
}

# pageクエリの中身が何もなかった場合、もしくはpage番号が負の数であれば1を入れる
if (!isset($page) || $page < 0) {
  $page = 1;
}

# 入力データの形式を判定
$addr = "";
$zip = "";
$kana = "";
if (preg_match("/^[0-9]+$/", $keyword)) {
  $zip = $keyword;
} else if (preg_match("/^[ァ-ヾ]+$/u", $keyword)){
    $kana = $keyword;
} else {
  $addr = $keyword;
}

# CONCATを用いて入力されたものを結合して検索
$query = "SELECT addr1, addr2, addr3, zip FROM zipJapan WHERE CONCAT(addr1, addr2, addr3) like ? AND CONCAT(kana1, kana2, kana3) like ? AND zip like ? LIMIT ?, ?";

$addr = '%'.$addr.'%';
$zip =  '%'.$zip.'%';
$kana = '%'.$kana.'%';

# offset値を訂正してください
$rowNum = 10;
$start = ($page - 1) * $rowNum;

# オフセット含めて10件のみ検索
if ($stmt = $mysqli->prepare($query)) {
  $stmt->bind_param("sssii", $addr, $kana, $zip, $start, $rowNum);
  $stmt->execute();
  $stmt->bind_result($addr1, $addr2, $addr3, $zipcode);
  echo "<table class=\"uk-table uk-table-divider\">";
  echo "<tr><th>都道府県名</th><th>市区町村名</th><th>町域名</th><th>郵便番号</th></th>";
  while ($stmt->fetch()) {
    echo "<tr><td>$addr1</td><td>$addr2</td><td>$addr3</td><td>$zipcode</td></tr>";
  }
  echo "</table>";
  $stmt->close();
} else {
  echo "db error";
}

# 総数を求める
$query_sum = "SELECT COUNT(*) as count FROM zipJapan WHERE CONCAT(addr1, addr2, addr3) like ? AND CONCAT(kana1, kana2, kana3) like ? AND zip like ?";

if ($stmt = $mysqli->prepare($query_sum)) {
  $stmt->bind_param("sss", $addr, $kana, $zip);
  $stmt->execute();
  $stmt->bind_result($allnum);
  while ($stmt->fetch()) {
  }
  
  $stmt->close();
} else {
  echo "db error";
}

# mysqlとの接続をやめる
$mysqli->close();

echo "<p class=\"uk-align-center\">$page / $allnum</p>";

?>
    </div>
    <div class="uk-flex uk-flex-around">
      <a href="./database.php?keyword=<?php echo $_REQUEST["keyword"] ?>&page=<?php if($page <= 1){echo 1;}else{echo $_REQUEST["page"] - 1;} ?>">
        ←前のページへ
      </a>
      <a href="./database.php?keyword=<?php echo $_REQUEST["keyword"] ?>&page=<?php if($page >= $allnum){echo $allnum;}else{echo $_REQUEST["page"] + 1;} ?>">
        次のページへ→
      </a>
    </div>
    
  </body>

</html>
