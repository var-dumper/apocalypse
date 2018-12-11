<?php  header("Content-type: text/html; charset=utf-8");
  // 通信ヘッダは,文字を書き出す前に実行しなければならない
	$prio=0;
		if(isset($_POST['priority'])){
			// post値は文字列型なので数値型に変更している,カラなら0になる
			$prio=(int)$_POST['priority'];
		}
  
   require_once('connect.php');
   require_once('mojifilter.php'); 
   $dbh=dbconnect();
 
if(!empty($_POST['insert']) && !empty($_POST['todocont']))
{
 // 追加ボタンが押されたときの処理
	if( $prio === 0 ) $prio = 1;  // すべてを選んだら強制的に低にする
	 $sql= ' 
		INSERT INTO todolist ( todo, prio,created )
		VALUES(?,?,CURDATE() )';
		$sth=$dbh->prepare($sql);
		$sth->bindValue(1 ,$_POST['todocont'],PDO::PARAM_STR);
		$sth->bindValue(2 ,$prio,PDO::PARAM_INT);
  	$sth->execute(); 

}else if(!empty($_POST['search'])){
	// 検索ボタンが押されたときの処理  優先で絞り込む
//	$_POST['prio'] は2なら"すべて"にしたい

	$sql = "SELECT * FROM todolist " ; 
		if($_POST['priority'] != 0 )
			$sql .=	" WHERE prio = ?";
	
	$sth=$dbh->prepare($sql);
	if($_POST['priority'] != 0 )
		$sth->bindValue(1,$_POST['priority'] , PDO::PARAM_INT);
	$sth->execute(); // MySQLから持ってきたデータ
	
	$ichiran = ""; 	
		foreach ($sth as $key => $row) {
			$ichiran .= "
			<br><input type='checkbox' name='checktodo[]' value='"
			. h($row["id"]) ."'> "
			.	h($row['todo']) ;
		}
}else if( !empty($_POST['delete']) 
			&& !empty($_POST['checktodo']) ){
	// 削除ボタンが押されていた場合
		
	$checkOn = $_POST['checktodo']; //数字配列
	// 一回のDELETEで複数行を削除する
	$ids = '';
		foreach ($checkOn as $id ) {
			$ids .= $id . ",";
		}
		$ids = rtrim($ids , ",");  // 文字列の最後のカンマを除去
		$sql="DELETE FROM todolist WHERE id IN( $ids )";
		$sth=$dbh->prepare($sql);
		$sth->execute();
}

?> 
<form action="" method="post">
 <p>やることを入力
	<textarea name="todocont"></textarea>

	<select name="priority">
		<option value="0">すべて</option>
		<option value="1">低</option>
		<option value="2">高</option>
	</select>
 </p>
	<input type="submit" name="insert" value="追加">
	<input type="submit" name="search" value="検索">
	<input type="submit" name="delete" value="削除">
<p>
<?php echo @$ichiran;  // 警告を出さないための @ ?>
</form>