<?php  
	session_start();
	$sid = session_id();
					//フォームの入力値が有ればを確認
	if ( !empty($_POST['password'])	&& !empty($_POST['email']) ){
							
		require_once ("mojifilter.php"); 		require_once("connect.php");   
			$dbh = dbconnect(); 

	$email = h($_POST['email']);
	$password = h($_POST['password']);
  $zeroflag =false; //counterが0に戻ればtrue 
															//emailでusersテーブルを検索
	$sql="SELECT email, password , code , heatstamp,counter
				FROM users WHERE email = ?";
		$stmt = $dbh->prepare($sql); 
		$stmt->bindValue(1, $email , PDO::PARAM_STR);
		$stmt->execute();


			$rowcount = $stmt->rowCount(); // ヒットした行数を数える
			if ($rowcount) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				// 1件あったら タイムスタンプと現時刻の差を調べる
				$_SESSION['code']=$row['code'];
				
			if($row['heatstamp']!==0 	&&
					 time() - $row['heatstamp'] > (60*30) ){
	//counterを0に戻す
					if($row['counter']!=0){
						//いまの値0でないなら
						$sql="UPDATE users SET counter = 0 ,heatstamp=0
								WHERE email = ? ";
						$stmt = $dbh->prepare($sql);
						$stmt->bindValue(1, $email , PDO::PARAM_STR);
						$res=$stmt->execute();
						$zeroflag = true ;
							
					}
					// ここから パスワード照合
				 		if( pv(1) ){
$_SESSION['code'] =  $row['code'];
							 exit; // 認証成功ならSTOP
						 } else{  // 追加 
							if( !$zeroflag &&  $row['counter'] >= 3){
									updateTime(); //タイムスタンプを刻印する関数
								}
						 } //追加ここまで

				 }else{				//30分経っていないなら
				 		// 失敗回数が < 3 
				 		if($row['counter'] < 3){
				 			// ここから パスワード照合
				 			if( pv(2) ) 
				 				$_SESSION['code'] =  $row['code'];exit;
				 		}else{
				 			echo "只今ログインできません";
				 			// 失敗回数が >=3
				 			updateTime();
				 		}
				 } //30分経っていないならEND

			}else{
				// 登録されていない 
					echo "メールアドレスかパスワードが違います";
			}  // else end	
} 
//パスワードを 入力値とDBの値で照合する関数
function pv($t){
	// 外側の変数を関数内で使うための宣言
	global $password  ; global $row; global $dbh; global $email; global $zerofrag;
	 if(password_verify ($password , $row['password'])){
	 		//echo "認証成功";
			 $sql="UPDATE users SET counter = 0 WHERE email = ?";
			 $stmt = $dbh->prepare($sql);
			 $stmt->bindValue(1, $email , PDO::PARAM_STR);;
			 $stmt->execute();
			// 記事投稿画面へリダイレクト
	 		header('Location: ./dashboard.php');
	 		return true;
	 }else{  
		  //パスワード認証に失敗
			$addcount = $zerofrag ? 1 : ++$row['counter'] ;		
			// ゼロクリアなら 1 を そうじゃなければ加算代入
	 		$sql="UPDATE users SET counter =". $addcount . " WHERE email = ?";
				 $stmt = $dbh->prepare($sql);
				 $stmt->bindValue(1, $email , PDO::PARAM_STR);
					$stmt->execute();
					echo "認証失敗" .$t ;
	var_dump( $addcount ,$zerofrag );	
	 		return false;
	 }
}		

function updateTime(){	
	global $dbh; global $email;
	$sql="UPDATE users SET timestump = ". time() . " WHERE email = ?";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $email , PDO::PARAM_STR);
	$stmt->execute();
}
		 
?> 

<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8">
 	<title>ログインするファイル </title>
 </head> <body>
 	<h2>ユーザーログイン<h2>
 	<form action="" method="post">
 	<input type="hidden" name="himitsu" value="<?=$sid?>">	
 		
 		<p><label>メールアドレス</label>
 		 		<input type="email" name="email"></p>
 		
 		<p><label>パスワード</label>
 		 		<input type='password' name="password"></p>
 	
 		<input type="submit" value="送信">
 	
 	</form>
 </body> </html>


