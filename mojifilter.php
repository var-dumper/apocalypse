<?php
// タグや記号を無害化する関数 SQLインジェクション対策
	function h($p){
		$p= htmlspecialchars($p ,ENT_QUOTES); 
		$p= str_replace("," , "，" , $p);
	//	$p= str_replace("-" , "ー" , $p);
		$p= str_replace("/" , "\/" , $p);
		$p= nl2br($p);
			return $p;
	}
  