<?php //shukei.php
  header("Content-type: text/html; charset=utf-8");
  //DBにつなぐ
  require_once("connect.php");
    $dbh = dbconnect();
    date_default_timezone_set('Asia/Tokyo'); //日本時間

  // 1.送信されていたら
  if( !empty($_POST['stdate']) && !empty($_POST['endate']) && !empty($_POST['tokui_id']) ){
    require('mojifilter.php');
    // 2.サニタイジング  mojifilter.php
      $tokui_id = h($_POST['tokui_id']);
      $stdate = h($_POST['stdate']);
      $endate = h($_POST['endate']);

  // 3.SQL文作成 ? プレースホルダ x 3 
    $sql = "SELECT  DATE_FORMAT(hiduke, '%Y-%m') as 月 , sum(tanka * num)as 合計
    FROM `shosai` as a
      LEFT JOIN shohin as n
       ON a.shohin_id = n.shohin_id
      LEFT JOIN denpyo as d
       ON a.denpyo_id = d.denpyo_id
      LEFT JOIN tokui as t
       ON d.tokui_id = t.tokui_id
    WHERE hiduke BETWEEN ? AND ?  
    AND d.tokui_id = ?
    GROUP BY  DATE_FORMAT(hiduke, '%Y-%m')";

// 4.プリペアドステートメント → バインド→ execute(実行)
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $stdate, PDO::PARAM_STR);
  $stmt->bindValue(2, $endate, PDO::PARAM_STR);
  $stmt->bindValue(3, $tokui_id , PDO::PARAM_INT);
  $stmt->execute();
  
  // 5.DBからの戻り値をループ
  echo "<table class='shukei'><tr><th> 月</th><th>合計</th></tr>";
  foreach ($stmt as $k => $v) {
    // 6.表として echo 色とかフォント,罫線もつけてきれいにする
    echo "<tr><td>{$v['月']} </td><td> {$v['合計']} </td></tr>";  

    }
    echo "</table>";
    // 7.github にアップロード (DBのダンプファイルとphp一式)
    
  }
?>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}
td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}
tr:nth-child(odd) {
  background-color: #dddddd;
}
</style>
  <form method="post">
      <h4>得意先と日付</h4>
      <p><select name="tokui_id" id="tokui" >
          <option value="">得意先を指定</option>
    <?php
    
        //得意先テーブル取得のSQL
        $sql="SELECT tokui_id,tokui_mei FROM tokui";
        //テーブルをループしながら <option>タグに例の通りに書き出す
        $stmt = $dbh->prepare($sql);
          $stmt->execute();
          if(isset($stmt))
            foreach ($stmt as $row)
            echo "<option value=\"{$row['tokui_id']}\"> {$row['tokui_mei']}</option>";
 ?>     
        </select>
     <label>開始日</label>   
        <input type="date" name="stdate" >
     <br><label>終了日</label>   
        <input type="date" name="endate" >
      </p>
     <input type="submit" value="帳票作成">
  </form>    