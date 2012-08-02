<?php
//This is a test page and is used for listing colors in database

   header( 'Location: http://www.neuron.illinois.edu' ) ;

require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.inc');


$data = array();
//Super inneficient, I need to not do rand on each row
$colors =& $neuronDB->getAssoc("SELECT * FROM paintchip_colors",$force_array=TRUE, $data);
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
		echo "<br /><br /><br /><br />";
		if(is_array($colors) && !empty($colors)){
				//	foreach($colors as $colorID => $color){
						//echo "\n\n".$colorID."=>\n";
						//print_r($color);
/*						echo "<span style='background-color:#".$color['rgb'].";padding-top:50px;' >".  $colorID . "===" .	$color['title'].".....................</span><br/><br /><br /><br />";	
					}
		}*/
				
				//USE THIS TO GENERATE THE COLOR GROUPS
				/*$i = 0;
				$ii = 0;
				$nums = array();
				
				foreach($colors as $colorID => $color){
					$nums[] = $colorID;
				}
			
				$blahs = array();
				shuffle($nums);
				foreach ($nums as $num) {
					$blahs[$num + $i*112] = true;
					echo "INSERT INTO paintchip_colors_groups (color_id, group_id) VALUES (".$num.",".($i+1).");<br/>";
					$i++;
					if($i==5){
						$i = 0;
						$ii++;
					}
				}
				shuffle($nums);
				foreach($nums as $num){
					if($blahs[$num + $i*112] ){
						continue;	
					}
					$blahs[$num + $i*112] = true;
					echo "INSERT INTO paintchip_colors_groups (color_id, group_id) VALUES (".$num.",".($i+1).");<br/>";
					$i++;
					if($i==5){
						$i = 0;
						$ii++;
						if($ii == 37){
							exit();	
						}
					}
					
				}*/
		}				
				
    ?>
    </body>
    
    
</html>