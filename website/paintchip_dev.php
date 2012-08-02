<?php 
	$title = "Paint Chip Game";
	$head = array();
	$head[] = '<link href="paintchip.css" rel="stylesheet" type="text/css" />';
	include('../templates/header.php');
?>

		<script type="text/javascript" src="http://neuron.illinois.edu/js/jquery.js"></script>
		
		<script language="javascript">
		
			var chosenGroup = 1;
			var startTime;
        	var interval;
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
	
		function stopClock(){
			if(interval){
				clearInterval(interval);
				document.getElementById("timer").innerHTML = "-:--";
			}
		}
		
		function gameFinished(url, submitScores){
			console.log("called function");
			var currentTime = new Date();
			var clockTime = (currentTime.getTime() - startTime);
			var min = Math.floor(clockTime/60000);
            var sec = Math.ceil(clockTime/1000)%60;
			
			console.log("stopping clock");
			stopClock();
			//http://neuron.illinois.edu/games/paintchip.php
			
			console.log("getting values");
			
			if(submitScores != false){
				var gender = $('#genderSelect').val();
				var age = $('#ageSelect').val();
				var data = "age="+age+"&gender="+gender+"&mins="+min+"&secs="+sec+"&"+url;
				
				$.ajax({
				   type: "POST",
				   url: "ajax/submitGame.php",
				   data: data,
				   success: function(msg){
					 document.getElementById("gameChange").innerHTML = "<p>Hover over the paint chips to see the colors' name.  Do you agree with the names?</p>"+
																  "<p>Share the results with a friend:<a href='http://neuron.illinois.edu/games/paintchip.php?gameId=" + msg + "'>http://neuron.illinois.edu/games/paintchip.php?gameId="+msg+"</a>"+
																	"<div id='paintName'></div>";
					 var flashMovie=getFlashMovieObject("nav");
					 flashMovie.grabColorValues();
				   }
				});
			}else{
				var flashMovie=getFlashMovieObject("nav");
				flashMovie.grabColorValues();
			}
		}
		
		function updateColor(name){
			document.getElementById("paintName").innerHTML = name;
		}
		
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
		function subStart(){
			stopClock();
			document.getElementById('information').style.display = "block";
			document.getElementById('options').style.display = "none";
			
		}
		
		function startGame(){
			document.getElementById('information').style.display = "none";
			document.getElementById('options').style.display = "block";
			if(document.getElementById('start').innerHTML == "Start Game"){
				var startDate = new Date();
				startTime = startDate.getTime();
				interval = setInterval(updateTime, 1000);
				colorIndex = 0;
				document.getElementById('groupSelect').disabled = true;
				document.getElementById('start').innerHTML = "replay";
				document.getElementById('gameChange').innerHTML = "<p>Drag the paint chip to the bucket that most closely describes the color on the chip.  <br />"+
                                        				"You have two minutes!  Choose a group from the list and click the button to start.</p>";
				 var flashMovie=getFlashMovieObject("nav");
   				 flashMovie.reset();
				 flashMovie.startGame(chosenGroup);
			}else{
				 var flashMovie=getFlashMovieObject("nav");
   				 flashMovie.reset();
				 flashMovie.startGame(chosenGroup);
				 var startDate = new Date();
				startTime = startDate.getTime();
				interval = setInterval(updateTime, 1000);
			}	
		}
		
		function endGame(didWin){
			clearInterval(interval);
			document.getElementById('groupSelect').disabled = false;
			var flashMovie=getFlashMovieObject("nav");
   			flashMovie.endGame(true);
		}
		</script>
  
       <div id="leftContent">
        <div class="bigBox smallBorder center" style="width:811px;" id='about'>
            <div id='breadcrumbs'>
                <?php
					$title = "PaintChip Game";
               		echo showBreadCrumbs(array(NEURON_ACTIVITIES => $bcs[NEURON_ACTIVITIES], $title))
				?>
            </div>
            <div class='smallBorder center' style="width:750px;" id='aboutText'>
                            <div id="gameWindow" >
                                <h2><?=$title?></h2>
                                <p>
                                As part of the Do you see what I see? unit, students learn how colors can be sorted.
Try this interactive applet to see how you might sort different colors. Its important
to remember that there isn’t one correct way of doing this—go ahead and try it and
then compare how you sorted to ways that other people sorted the same colors.
                                </p>
                                <div id='gameChange'>
                                    <p>
                                        Drag the paint chip to the bucket that most closely describes the color on the chip.  <br />
                                        You have two minutes!  Choose a group from the list and click the button to start.
                                    </p>
                                </div>
                                <div id='options'>
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
                                    <button id='start' onclick="subStart();">Start Game</button>
                                    
                                    <span id='timeDesc'>Time Left:</span><span id="timer">-:--</span>
                               	</div>
                                 <div id="information">
                                    Gender:<select id='genderSelect' name="gender">
                                    	<option value=""></option>
                                        <option value="0">Male</option>	
                                        <option value="1">Female</option>
                                    </select>
                                    Age:<select id='ageSelect' name="age">
                                        <option value="0"></option>
                                        <option value="1">0-8</option>	
                                        <option value="2">9-11</option>
                                        <option value="3">12-14</option>
                                        <option value="4">15-18</option>
                                        <option value="5">19-22</option>
                                        <option value="6">23-29</option>
                                        <option value="7">30-39</option>
                                        <option value="8">40-49</option>
                                        <option value="9">50-59</option>
                                        <option value="10">60-69</option>
                                        <option value="11">70-79</option>
                                        <option value="12">80-89</option>
                                        <option value="13">90-99</option>
                                        <option value="14">100+</option>
                                    </select>
                                    <button id='submit' onclick="startGame();">Submit</button>
                                    <button id='decline' onclick="document.getElementById('genderSelect').selectedIndex = 0; document.getElementById('ageSelect').selectedIndex = 0; startGame();">Decline</button>
                                </div>
                               <?php
                                    include("flash.php");
                                ?>
                           	</div>
                            
                           
                            
                        </div>
                    </div>
                </div>
<?php  include('../templates/footer.php'); ?>
	