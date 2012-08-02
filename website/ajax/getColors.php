<?php
header ("Content-Type:text/xml");  
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.inc');
$data = array();
$colors =& $neuronDB->getAssoc("SELECT paintchip_colors_groups.id as assocID, * FROM paintchip_colors_groups LEFT JOIN paintchip_colors ON paintchip_colors_groups.color_id = paintchip_colors.id",$force_array=TRUE, $data);
if(PEAR::isError($colors))
{
	$messages->addFailure("Could not find any colors!");
}

echo "<xml>";
	echo "<colors>";
		foreach($colors as $color){
			echo '<color id="'.$color['id'].'" color_id="'.$color['color_id'].'" group_id="'.$color['group_id'].'" title="'.$color['title'].'" rgb="'.$color['rgb'].'">';
			echo "</color>";
		}
	echo "</colors>";
echo "</xml>";
?>