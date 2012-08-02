<?php
header ("Content-Type:text/xml");  
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.inc');

$colors =& $neuronDB->query("SELECT color_id, bucket_id, COUNT(*) FROM paintchip_games_results GROUP BY color_id, bucket_id ORDER BY color_id");

function printGroups($group, $total){
	foreach($group as $i=>$val){
		echo ' count'.$i.'="'.$val.'"';	
	}
	
	echo ' total="'.$total.'"></color>'."\n";
}
echo "<xml>\n";
	echo "<colors>\n";
		$lastColor = -1;
		$total = 0;
		$group = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0);
		while ($color =& $colors->fetchRow()) {
			if($lastColor != $color['color_id'] ){
				if($lastColor != -1){
					printGroups($group, $total);
					$total = 0;
					$group = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0);
				}
				echo "\t".'<color id="'.$color['color_id'].'"' ;
				$lastColor = $color['color_id'] ;
			}
			
			$group[($color['bucket_id']-1)] = $color['count'];
			$total += $color['count'];
		}
		printGroups($group, $total);
	echo "</colors>\n";
echo "</xml>\n";
?>