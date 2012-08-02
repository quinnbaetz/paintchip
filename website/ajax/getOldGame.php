<?php
header ("Content-Type:text/xml");  
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.inc');

$id = $_GET['gameId'];

$data = array($id);
$data =& $neuronDB->getAssoc("SELECT color_id, bucket_id FROM paintchip_games_results WHERE game_id = ? ORDER BY bucket_id",$force_array=TRUE, $data);
if(PEAR::isError($colors))
{
	$messages->addFailure("Could not find any colors!");
}

$buckets = array();
foreach($data as $cid => $bucket){
	$bid = $bucket['bucket_id']-1;
	if(!isset($buckets[$bid])){
		$buckets[$bid] = array();	
	}
	$buckets[$bid][] = $cid;
}

echo "<xml>";
	foreach($buckets as $bid => $bucket){
		echo "<bucket bid=\"".$bid."\">";
		foreach($bucket as $cid){
			echo "<colors id=\"".$cid."\"></colors>";
		}
	echo "</bucket>";
	}
echo "</xml>";
		
?>