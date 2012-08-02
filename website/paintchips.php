<?php
header("Pragma: no-cache");
header("cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

require_once($_SERVER['DOCUMENT_ROOT'].'/include/config.inc');


//See http://pear.php.net/manual/en/package.database.db.php for more info
$data = array();
$allBuckets =& $neuronDB->getAssoc("SELECT * FROM paintchip_buckets",$force_array=TRUE,$data);
if(PEAR::isError($allBuckets))
{
	$messages->addFailure("Could not find any buckets!");
}

$data = array("red");
$redBucket =& $neuronDB->getAssoc("SELECT * FROM paintchip_colors WHERE LOWER(title)=? ",$force_array=TRUE,$data);
if(PEAR::isError($redBucket))
{
	$messages->addFailure("Could not find red bucket!");
}

$data = array();
$colors =& $neuronDB->getAssoc("SELECT paintchip_colors_groups.id as assocID, * FROM paintchip_colors_groups LEFT JOIN paintchip_colors ON paintchip_colors_groups.color_id = paintchip_colors.id",$force_array=TRUE, $data);
if(PEAR::isError($colors))
{
	$messages->addFailure("Could not find any colors!");
}

$selGroup = rand(0, 4);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
    
    	 <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
 		<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
         
        <link href="/style.css" rel="stylesheet" type="text/css" />
		<title>Project Neuron</title>
        
		<script type="text/javascript" src="http://neuron.illinois.edu/js/scriptaculous/prototype.js"></script>
		<script type="text/javascript" src="http://neuron.illinois.edu/js/scriptaculous/scriptaculous.js"></script>
		
		<script language="javascript">
			var colors = new Array();
			var groups = new Array();
			var ccid = new Array("ccBox");
			var idCount = 0;
			var chosenGroup = <?=$selGroup?>;
			var colorIndex = 0;
			var dragable;
			var interval;
			<?php
				$groups = array();
				if(is_array($colors) && !empty($colors)){
					foreach($colors as $color){
						if(!is_array($groups[$color['group_id']])){
							$groups[$color['group_id']] = array();
						}
						$groups[$color['group_id']][] = $color;
					}
				}
				
				$i = 0;
				foreach($groups as $group){
						?>
						groups[<?=$i?>] = new Array(
						<?php
						$len = sizeof($group);
						$count = 0;
						foreach($group as $colorID => $color){
							$count++;
							?>
								{
									color: '<?=$color['rgb']?>',	
									title: '<?=htmlentities ($color['title'], ENT_QUOTES)?>',
									id:  '<?=htmlentities ($color['title'], ENT_QUOTES)?>'
								}
							
							<?php
							if($count !== $len){
								echo ",";	
							}
						}
						$i++;
						echo ");\n";
				}
			?>
			
			var startTime;
        
        /*http://forums.devshed.com/javascript-development-115/convert-seconds-to-minutes-seconds-386816.html
        this : string to convert
        l : number of digits to have
        s : single character to pad with
        */
        function pad(str, len, s){
			var ret = '' + str;
			while(len>ret.length){
				ret = s + ret;	
			}
			return ret;
        };

        function updateTime(){
        	var currentTime = new Date();
            var clockTime = (2*60*1000) - (currentTime.getTime() - startTime);
			if(clockTime < 0){
				endGame(false);		
			}else{
				var min = Math.floor(clockTime/60000);
            	var sec = Math.ceil(clockTime/1000)%60;
				if(sec == 0){
					min++;	
				}
            	document.getElementById("timer").innerHTML = min + ":" + pad(sec, 2, "0");
			}
		}
		function startGame(){
			if(document.getElementById('start').innerHTML == "Start Game"){
				var startDate = new Date();
				startTime = startDate.getTime();
				interval = setInterval(updateTime, 1000);
				colorIndex = 0;
				document.getElementById('groupSelect').disabled = true;
				document.getElementById('start').innerHTML = "replay";
				document.getElementById('currentColor').style.background = '#' + groups[chosenGroup][colorIndex].color;
				document.getElementById('currentColor').paintColor =  groups[chosenGroup][colorIndex];
				document.getElementById('nextColor').style.background = '#' + groups[chosenGroup][colorIndex+1].color;
				document.getElementById('colorBox').style.visibility = "";
				dragable = new Draggable('currentColor', { 
												onStart: function(){
													document.getElementById('nextColor').style.visibility = "";
													document.getElementById('nextColor').style.display = "block";
												},
												onEnd: function(dragObj, mouseEvt){
												}
											});
				colorIndex++;
			}else{
				var sURL = unescape(window.location.pathname);

				location.reload(true);
				window.location.replace( sURL );
				window.location.href = sURL;

				window.location.reload( false );

				
				
			}
			
			
		}
		
		function animate(dragObj, dropObj){
			var xMov = dropObj.offsetLeft+25;
			var yMov = dropObj.offsetTop-100;
			new Effect.Move(dragObj, { x: xMov, 
										y: yMov, 
										mode: 'absolute',
										queue: 'end',
										afterFinish: function(){
											dragObj.style.zIndex = 2;	
										}
							});

			var xMov = dropObj.offsetLeft+25;
			var yMov = dropObj.offsetTop+25;
			new Effect.Move(dragObj, { x: xMov, 
									y: yMov, 
									mode: 'absolute',
									afterFinish: function(){
										storeColor(dragObj, dropObj);
										removeColor();
									},
									queue: 'end'
						});
		}
	
		
		function storeColor(dragObj, dropObj){
			var textObjs = dropObj.getElementsByTagName("span");
			var ammount = textObjs[textObjs.length-1];
			ammount.innerHTML++;
			if (typeof dragObj.colors === 'undefined') {
				dropObj.colors = [];
			}
			dropObj.colors.push(dragObj.paintColor);
		}
		
		
		function removeColor(dragObj, dropObj){
				var id = ccid.shift();
				document.getElementById('dragColors').removeChild(document.getElementById(id));
		}
		
		function newColor(dragObj, dropObj){
			var newBoxId = "ccBox"+idCount;
			var newColorId = "currentColor" + idCount;
			idCount++;
			ccid.push(newBoxId);
			document.getElementById('dragColors').innerHTML += "  <a id='"+newBoxId+"' href='#'><div id='"+newColorId+"' class='draggable' style='width: 50px; height: 50px; position:absolute; z-index:5'> </div></a>";
		  dragable = new Draggable(newColorId, { 
                                            onStart: function(){
												document.getElementById('nextColor').style.visibility = "";
												document.getElementById('nextColor').style.display = "block";
											},
											onEnd: function(dragObj, mouseEvt){
                                            }
                                        });
			  
			var currentColor = document.getElementById('colorBox').getElementsByTagName('span')[0];
			currentColor.innerHTML++;
			document.getElementById(newColorId).style.backgroundColor =  '#' + groups[chosenGroup][colorIndex].color;
			document.getElementById(newColorId).paintColor =  groups[chosenGroup][colorIndex];
			document.getElementById('nextColor').style.display = "none";
		
			if(groups[chosenGroup][colorIndex+1]){
				document.getElementById('nextColor').style.backgroundColor = '#' + groups[chosenGroup][colorIndex+1].color;
			}else{
				document.getElementById('nextColor').style.backgroundColor = '';
			}
			colorIndex++;
			
		}
		
		function isFinished(){
			return (colorIndex >= groups[chosenGroup].length);
		}
		
		function endGame(didWin){
			clearInterval(interval);
			document.getElementById('groupSelect').disabled = false;
			if(didWin){
				document.getElementById('colorBox').innerHTML = "YOU WIN";
			}else{
				document.getElementById('colorBox').innerHTML = "Sorry, Time ran out, refresh the page to try again";					
			}
		}
		</script>
    </head>
    <body>
       <div id="header">
            <div id='title'>
                <img id='titleImg' src="/images/Title.png" alt='Project Neuron' />
                <div class='right' id='rightHeader'>
                    <div id="iLogo" class='right'>
                        <a href='http://www.illinois.edu' ><img alt='University of Illinois at Urbana-Champaign logo.' src="/images/imark.png"/></a>
                        <span>ILLINOIS</span><br />
                    </div>
                     <div class='clear'></div>
                     <a href='http://www.chhsepa.com' class='right'><img alt='SEPA: Science Education Partnership Award' src="/images/sepaLogo.png"/></a>
                     
 				</div>
            </div>
            <div id='navBar'>
                <div id='navContent'>
                    <ul id='navOptions'>
                        <li id='current'>Home</li>  <?php //in php mark the current page as current?>
                        <li>Unit Themes</li>
                        <li>About</li>
                        <li>Contact</li>
                    </ul>
                        <form id='navSearch' action="#" class='left'>
                       		<fieldset class='left'>
                                <input type="text" />
                                <input type="submit" value='Search'/>
                        	</fieldset>
                        </form>
                </div>
            </div>
        </div>
        <div id="content">
                <div id="leftContent">
                    <div class="bigBox smallBorder center game" id='about'>
                        <div class='smallBorder center game' id='aboutText'>
                            <h2>Paint Chip Game</h2>
                            <div id="timer"></div>
                            <button id='start' onclick="startGame();">Start Game</button>
                            <select id='groupSelect' name="groups"  onchange="chosenGroup = this.options[this.selectedIndex].value;">
                            <?php
								for($i = 0; $i<5; $i++){
									$selected = "";
									if($i == $selGroup){
										$selected = "selected id='selected'";
									}
                                	echo '<option value="'.$i.'" '.$selected.'>Group '.($i+1).'</option>';	
									
								}
							?>
                            </select>
                            <p>
<?php
if(is_array($allBuckets) && !empty($allBuckets))
{
	/*echo "<h2>All Buckets</h2>".$endl;
	echo "<table>".$endl;
	echo "<tr>".$endl;
	echo "	<th>Bucket ID</th>".$endl;
	echo "	<th>Bucket Title</th>".$endl;
	echo "	<th>Bucket Color</th>".$endl;
	echo "<tr>".$endl;*/
	?>
	
        <div id="colorBox" style='visibility:hidden;'>
        	<div><span>1</span>/<?=sizeof($groups[1])?></div>
        		<div id='dragColors'>
                    <div id='nextColor' class='draggable' style='width: 50px; height: 50px; visibility: hidden; display: none;  position:absolute;'></div>
                    <a id="ccBox" href='#'>
                        <div id='currentColor' class='draggable' style='width: 50px; height: 50px; position:absolute; z-index:5'> </div>
                    </a>
				</div>
        </div>
	
<?php
	
	echo "<ul>".$endl;
	$first = "first";
	foreach($allBuckets as $bucketID => $bucket)
	{
		/*echo "<tr>".$endl;
		echo "	<td>{$bucketID}</td>".$endl;
		echo "	<td>{$bucket['title']}</td>".$endl;
		echo "	<td><div style='width: 50px; height: 50px; background-color: #{$bucket['rgb']}'> </div></td>".$endl;
		echo "<tr>".$endl;*/
	?>
    	<li class="bucket">
            
            <div id="bucket<?=$bucketID?>" class='bucket <?=$first?>'">
                <img class="bucketTop" src="../images/game/bucketBack.png" width=101 height=30 style="z-index:1;"/>
                <img class="bucketFront" src="../images/game/bucketFront.png" width=101 height=111 style="z-index:3;"/>
                <span class="bucketColor" style="z-index:4;"><?=$bucket['title']?></span>
            	<span class="bucketAmmount" style="z-index:4;">0</span>
            </div>
		</li>
        <script type="text/javascript">
            Droppables.add('bucket<?=$bucketID?>', {
              accept: 'draggable',
              onDrop: function(dragObj, dropObj, event) {
              		
                    if(isFinished()){
						endGame(true);                    	
					}else{
						newColor(dragObj, dropObj);
					}
                    console.log(ccid[0]);
                    dragObj = document.getElementById(ccid[0]);
					animate(dragObj, dropObj);
                    
              }
            });
		</script>

    <?php
		$first = "";
	}
	//echo "</table>".$endl;
	echo "</ul>".$endl;
	
}

/*
if(is_array($redBucket) && !empty($redBucket))
{
	echo "<h2>Red Bucket</h2>".$endl;
	echo "<table>".$endl;
	echo "<tr>".$endl;
	echo "	<th>Bucket ID</th>".$endl;
	echo "	<th>Bucket Title</th>".$endl;
	echo "	<th>Bucket Color</th>".$endl;
	echo "<tr>".$endl;
	foreach($redBucket as $bucketID => $bucket)
	{
		echo "<tr>".$endl;
		echo "	<td>{$bucketID}</td>".$endl;
		echo "	<td>{$bucket['title']}</td>".$endl;
		echo "	<td><div style='width: 50px; height: 50px; background-color: #{$bucket['rgb']}'> </div></td>".$endl;
		echo "<tr>".$endl;
	}
	echo "</table>".$endl;*/
//}
?>
                            </p>
                        </div>
                    </div>
                </div>
		</div>
    <div class='clear'></div>
    <div id='footer' class='center centerText'>
    	<script type="text/javascript">
        	document.getElementById('groupSelect').disabled = false;
            document.getElementById('selected').selected = "1";
            
        </script>
        <span>&copy; 2010| Project Neuron| University of Illinois at Urbana-Champaign </span>
   </div>
        
    </body>
</html>