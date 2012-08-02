<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.inc');

$sth = $neuronDB->prepare('INSERT INTO paintchip_games (age, gender, minutes, seconds) VALUES (?, ?, ?, ?)');

$data = array($_POST['age'], $_POST['gender'], $_POST['mins'], $_POST['secs']);
$data = $neuronDB->execute($sth, $data);
$data = array();
$data =& $neuronDB->query("SELECT max(id) FROM paintchip_games");
while ($row =& $data->fetchRow()) {
	$lastInsertId = $row['max'];
}
 
  
for($i = 0; $i<=6; $i++){ 
	if(isset($_POST[$i])){
		$colorIds = explode(",", $_POST[$i]);
		foreach($colorIds as $colorId){
			$sth = $neuronDB->prepare('INSERT INTO paintchip_games_results (game_id, color_id, bucket_id) VALUES (?, ?, ?)');
			$data = array($lastInsertId, $colorId, ($i+1));
			$neuronDB->execute($sth, $data);
		}
	}
}

echo $lastInsertId;

?>