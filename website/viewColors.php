<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.inc');


$data = array();
$colors =& $neuronDB->getAssoc("SELECT paintchip_colors_groups.id as assocID, * FROM paintchip_colors_groups LEFT JOIN paintchip_colors ON paintchip_colors_groups.color_id = paintchip_colors.id",$force_array=TRUE, $data);
echo sizeof($colors);
if(PEAR::isError($colors))
{
	$messages->addFailure("Could not find any colors!");
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
    
    	 <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
            
		<title>Project Neuron</title>
        
		<script type="text/javascript" src="http://neuron.illinois.edu/js/scriptaculous/prototype.js"></script>
		<script type="text/javascript" src="http://neuron.illinois.edu/js/scriptaculous/scriptaculous.js"></script>
	</head>
  
  	<body>
    <?php
				$groups = array();
				$i = 0;
				if(is_array($colors) && !empty($colors)){
					foreach($colors as $color){
						if(!is_array($groups[$color['group_id']])){
							$groups[$color['group_id']] = array();
						}
						$groups[$color['group_id']][] = $color;
						$i++;
					}
				}
				echo $i."-0000000";
				$i = 0;
				foreach($groups as $group){
					$i++;
					echo "<br/>".$i."<br/>--------------------<br/>";
					$k = 0;
					foreach($group as $color){
						
						$k++;
						echo "<span style='background-color:#".$color['rgb'].";' >". $color['title'].".....................</span><br/>";	
					}
					echo $k."<br/>";	
				}
				echo $i;
    ?>
    </body>
    
    
</html>