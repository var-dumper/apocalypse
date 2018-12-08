<?php  
	//php で文字エンコードを指定する 通信ヘッダで指定するのでhtmlより強い
	header("Content-type: text/html; charset=utf-8");

	//DBにつなぐ
	require_once("connect.php");
		$dbh = dbconnect();
		date_default_timezone_set('Asia/Tokyo'); //日本時間
// postされていたら
	if(!empty($_POST['tokui_id']) && $_POST['shohin_id']){
	//  1.POSTデータを伝票テーブルに挿入
		$sql = "INSERT INTO denpyo(tokui_id,hiduke )
		VALUES (?, ? )";
		$stmt = $dbh->prepare($sql);
		$stmt->bindValue(1, $_POST['tokui_id'], PDO::PARAM_INT);
		$stmt->bindValue(2, $_POST['hiduke'], PDO::PARAM_STR);
		$stmt->execute();

	//  2.A_Iで発行されたdenpyo_idを取得
		$denpyo_id = $dbh->lastInsertId('denpyo_id');
		
		//  3.POSTデータを詳細テーブルに挿入,配列なのでループが必要
		$shohin_id = $_POST['shohin_id'];
		$num = $_POST['num'];

		if( is_array($shohin_id) &&  is_array($num) ){  //回す前に配列かどうか調べる
			$success = "<p>";  //挿入できた商品を入れる変数
			foreach ($shohin_id as $key => $value) {
				// $keyには配列のインデックス[0][1][2]が入る
				if( !empty( $value ) && !empty( $num[$key] )){
						// どちらかがカラならINSERTしない
						$arr = explode(",",$value);
					$sql = "INSERT INTO shosai( denpyo_id,shohin_id,num )
					VALUES (?, ? ,? )";
					$stmt = $dbh->prepare($sql);
						$stmt->bindValue(1, $denpyo_id, PDO::PARAM_INT);
							$stmt->bindValue(2, $arr[0], PDO::PARAM_STR);
							$stmt->bindValue(3, $num[$key] , PDO::PARAM_STR);
					$stmt->execute();
						$success .= $arr[1]."を".$num[$key] ."追加しました<br>";
				}
			} //end foreach
			echo $success;
		} // end 配列ではない
	} // POSTされてない
?>
<style>input[type="number"]{width: 4em}</style>
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
				echo "<option value=\"{$row['tokui_id']}\">	{$row['tokui_mei']}</option>";
	
?>			
		</select>
		<input type="date" name="hiduke" id="hiduke">
	</p>

	<h4>商品明細</h4>
	<p>
		<select name="shohin_id[]" class="a" >
			<?php  shohin_list() ?>
		</select>
			<input type="number" name="num[]" > <br>
		<select name="shohin_id[]" class="b">
			<?php  shohin_list() ?>
		</select>
			<input type="number" name="num[]" > <br>
		<select name="shohin_id[]"  class="c">
			<?php  shohin_list() ?>
		</select>
			<input type="number" name="num[]" >
	</p>
		<input type="submit" value="登録">
</form>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> 
<script>
	$('[type="submit"]').click(function(){
		var aaa = $('.a option:selected').val() ;
		var bbb = $('.b option:selected').val() ;
		var ccc = $('.c option:selected').val() ;
//3つとも違ってたら送信していい,一つでも同じならしない,ただし空文字は省く, aがカラなのは許可しない
			if( ((aaa != bbb) && (bbb != ccc) && (aaa != ccc)) 
			|| ((bbb == "") && (ccc == "") && (aaa != "")) ){
				return true;
			}else{
				alert("商品が重複してます");
				return false;
			}
	});
</script>

<?php
	function shohin_list(){
		global $dbh;
		$sql="SELECT shohin_id,shohin_mei FROM shohin";
		$stmt = $dbh->prepare($sql);
			$stmt->execute();
		echo "<option value=''>商品を選択</option>";
			if(isset($stmt))
				foreach ($stmt as $row)
				echo "<option value=\"{$row['shohin_id']},{$row['shohin_mei']}\">	{$row['shohin_mei']}</option>";
	}

