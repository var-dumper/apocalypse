<?php  // thanks.phpから切り貼りしてつくってみよう
  session_start();
 // 世界標準時から+9時間になる 
  date_default_timezone_set('Asia/Tokyo');
// 1.送信されていたら
if( !empty($_POST['toko']) && !empty($_POST['Author'])){ //空じゃなければtrue
  // 4.投稿者ID(Author)はlogin.phpでセッションに代入して取得する

  require_once("connect.php");  // 2.DBにつなげる
    $dbh = dbconnect();
    
    // 3.php で現在時刻の取得 mysqlはハイフン区切り
  $post_date = date("Y-m-d H:i:s");  
  // 5.INSERT するSQL文 → 実行
  $sql = "INSERT INTO posts(post, post_date,  Author )
  VALUES (?, ? ,?)";
  $stmt = $dbh->prepare($sql);
  $stmt->bindValue(1, $_POST['toko'], PDO::PARAM_STR);
  $stmt->bindValue(2, $post_date, PDO::PARAM_STR);
  $stmt->bindValue(3, $_POST['Author'], PDO::PARAM_INT);
  $stmt->execute();
}   // 記事の投稿はここまで
?>
<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title> 投稿文作成 </title></head>
<body>

<form method="post" id="imgform" action="" enctype="multipart/form-data">
  <label >投稿画像のアップロード</label>
  <p>ファイル:<input type="file" name="up_toko"></p>
  <input type="button" id="imageup" value="upload" >
</form>

<hr> 記事本文
<img id="image_file" alt="アップした画像" style="display: none;">

<form action="" method="post">
  <textarea name="toko" id="toko" cols="30" rows="10" ></textarea>
  <input type="hidden" name="gazo" id="gazo" value="">
  <input type="hidden" value="<?=$_SESSION['code']?>" name="Author">
  <p><input type="submit" value="公開"></p>
</form>
<?php 
  if( empty($_SESSION['code'])){
    echo "ログインしてください";
    exit;  // ここで処理をとめる
  }
?>

  <script src="https://code.jquery.com/jquery-2.2.4.js"></script>
  <script>
$('#imageup').click( function(){ 
  //$('#imageup').on("click", function(){
   //画像送信して映すまでのajax通信を書く
   var updir = '/php/very_hard';
   //ファイルを送る場合はこうかく↓
   var formdata = new FormData($('#imgform').get(0));
   $.ajax({
    url: "http://localhost" + updir + "/image_up.php",
    type: "post",   //method
    processData: false, //文字列に変換しない
   contentType: false, // デフォルトではない
    dataType: "html",  // 送信データの種類 ,html ,json とか
    data:formdata 
  })
  .done(function (response) {
    // 通信が成功した場合 php からの戻り値がreoponseにはいる
    var gazoName= updir + "/img/" + response ;
    $("#image_file").attr('src',gazoName ).show();
    $('#gazo').val(response);  //画像のhiddenフィールドに入れる
    $('textarea').text("<img src='"+gazoName+"'>");
  })
  .fail(function (xhr,textStatus,errorThrown) {
      //通信が失敗した場合
      alert('error');
  });
} );
</script>

</body>
</html>
