	include "../utility.as";
import flash.external.ExternalInterface;
ExternalInterface.addCallback("grabColorValues", grabColorValues);
ExternalInterface.addCallback("startGame", startGame);
ExternalInterface.addCallback("endGame", endGame);
ExternalInterface.addCallback("reset", reset);
var local = loaderInfo.url.indexOf("file:") != -1 ? true : false;
var debug = false;
var groups = new Array();
var buckets;
var myBuckets = new Array();
var currentGroup;
var currentGroupIndex;
var myStage = stage;
var leftOver;
var gameOver = false;
var displayColors = new Array();
var colorData = new Array();
var ended = false;
var stackTop = null;
var stack = null;
//var colorTopTitle = null;
var leftShift = 50;
var finishedURL = "";
var url = null;
var oldGameURL = "http://neuron.illinois.edu/games/paintchip/ajax/getOldGame.php?";
var colorDataURL = "http://neuron.illinois.edu/games/paintchip/ajax/getColorFrequency.php?"+Math.random();
var dataURL = "http://neuron.illinois.edu/games/paintchip/ajax/getColors.php";
var baseURL = "http://neuron.illinois.edu/games/paintchip/index.php";


var scrollSpeed = 0;
var normalSquareSize = 70;
var smallSquareRatio = .5;
var spaceBetweenFinalSquares = 5
var moveX = 0;
var moveY = 0;

//graph stuff
var graph;
var gBuckets;
var gBars;
//var gTitle;
var graphHeight = 300;
var graphY = 520;
function processXMLData(e:Event):void{
	try{
		var xml = new XML(e.target.data);
		var colors = xml.colors.color;
		
		var count = 0;
		for(var color in colors){
			if(groups[colors[color].@group_id] == null){
				groups[colors[color].@group_id] = new Array();
			}
			groups[colors[color].@group_id].push(colors[color]);
			colorData[colors[color].@id] = colors[color];
			if(debug){
				count+=1;
				if(count==20){
					break
				}
			}
	
		}
	} catch 
		(er:TypeError){ trace("XMLParser: Error - parsing "+er.message);
	}
	
	//cURL(oldGameURL+"gameId=50", fillBuckets);
	
	var URLparams =  LoaderInfo(this.root.loaderInfo).parameters; //getURLParams();
	
	if(URLparams.gameId != undefined){
		//fillBuckets(URLparams);
		fTrace(URLparams.gameId);
		var url = oldGameURL+"gameId="+URLparams.gameId;
		fTrace(url);
		cURL(url, fillBuckets);
	}
	if(local){
		moveY = 200;
		startGame(1);
	}

	
}

function processColorXMLData(e:Event):void{
	try{
		var xml = new XML(e.target.data);
		var colors = xml.colors.color;
		
		var count = 0;
		for(var color in colors){
			if(colorData[colors[color].@id]){
				
				colorData[colors[color].@id].@total = colors[color].@total;
				for(var i = 0; i<=6; i++){
					colorData[colors[color].@id]["@count"+i] = colors[color]["@count"+i];
				}
			}
		}
	} catch 
		(er:TypeError){ trace("XMLParser: Error - parsing "+er.message);
	}
}

function createRect(color, xLoc, yLoc, w, h){
	var square:mySprite = new mySprite();
	
	myStage.addChild(square);
	square.graphics.lineStyle(1,0x000000);
	square.graphics.beginFill(Number(color));
	square.graphics.drawRect(0,0,w,h);
	square.graphics.endFill();
	square.x = xLoc-square.width/2;
	square.y = yLoc-square.height/2+50+moveY;
	
	return square;
}

function createNonRect(color, xLoc, yLoc, w, h){
	var square:mySprite = new mySprite();
	
	myStage.addChild(square);
	square.graphics.lineStyle(1,0x000000);
	square.graphics.beginFill(Number(color));
	//square.graphics.drawRect(0,0,w,h);
	square.graphics.moveTo(0,h);
	square.graphics.lineTo(w,h);
	square.graphics.lineTo(w*4/5,0);
	square.graphics.lineTo(w*1/5,0);
	square.graphics.lineTo(0,h);
	square.graphics.endFill();
	square.x = xLoc-square.width/2;
	square.y = yLoc-square.height/2+50+moveY;
	
	return square;
}

function createNonSquare(color, xLoc, yLoc, multSize = 1){
	var size = multSize * normalSquareSize;
	var square = createRect("0x"+color.@rgb, xLoc, yLoc, size, size);
	square["color"] = color;
	displayColors.push(square);
	return square;
}

function createSquare(color, xLoc, yLoc, multSize = 1){
	
	var size = multSize * normalSquareSize;
	var square = createRect("0x"+color.@rgb, xLoc, yLoc, size, size);
	square["color"] = color;
	displayColors.push(square);
	return square;
}
function createLeftOver(){
	var myFormat:TextFormat = new TextFormat();
	myFormat.align = TextFormatAlign.CENTER;
	myFormat.size =20;
	
	leftOver = new TextField();
	
	setProps(leftOver, "defaultTextFormat", myFormat,
			 		"x", myStage.width/2-50-leftShift,
			 		"y", myStage.height/2+100-350,
			 	   "width", 100,
				   "height", 30,
				   "textColor", "0x000000",
				   "text", "0/0"
				  );
	
	myStage.addChild(leftOver);
}

function updateLeftOver(){
		leftOver.text = currentGroup.length+"/"+groups[currentGroupIndex].length;
}

function createBucket(xLoc, yLoc, cTitle){
	var back = new Back();
	back.gotoAndStop(0);
	var w = 85*1.7;
	var h = 81*1.7;
	setProps(back, "x", xLoc-w/2-leftShift, 
			 	   "y", yLoc-h/2-350,
				   "width", w,
				   "height", h);
	
	var front = new Front();
	setProps(front, "x", xLoc-w/2-leftShift, 
				   "y", yLoc-h/2+15-350,
				   "width", w,
				   "height", h);
	
	
	var myFormat:TextFormat = new TextFormat();
	myFormat.align = TextFormatAlign.CENTER;
	myFormat.size =20;
	/*
	var cName = new TextField();
	
	setProps(cName, "defaultTextFormat", myFormat,
			 		"x", xLoc - 50-leftShift,
			 		"y", yLoc-350,
			 	   "width", 100,
				   "height", 30,
				   "textColor", "0x000000",
				   "text", cTitle
				  );
	*/
	
	setProps(cTitle, "x", xLoc-w/2-leftShift, 
				   "y", yLoc-h/2+15-350,
				   "width", w,
				   "height", h);
	
	var count = new TextField();
	
	setProps(count, "defaultTextFormat", myFormat,
			 		"x", xLoc - 50-leftShift,
			 		"y", yLoc+30-350,
			 	   "width", 100,
				   "height", 30,
				   "textColor", "0x000000",
				   "text", "0"
				  );

	
	myStage.addChild(back);
	myStage.addChild(front);
	//myStage.addChild(cName);
	myStage.addChild(cTitle);
	myStage.addChild(count);
	return {"back": back, "front" : front, "cName" : cTitle, "count": count}
	
}

function makeBuckets(){
	return [createBucket(myStage.width/2, myStage.height/2-100, new Red()),
	createBucket(myStage.width/2-150, myStage.height/2-50, new Green()),
	createBucket(myStage.width/2+150, myStage.height/2-50, new Blue()),
	createBucket(myStage.width/2+225, myStage.height/2+130, new Yellow()),
	createBucket(myStage.width/2-225, myStage.height/2+130, new Brown()),
	createBucket(myStage.width/2-100, myStage.height/2+225, new Orange()),
	createBucket(myStage.width/2+100, myStage.height/2+225, new Purple())];
}
function createStack(){
	/*stack = new Stack();
	var w = normalSquareSize;
	var h = 20;
	setProps(stack, "x",myStage.width/2-w/2-leftShift-60, 
			 	   "y",myStage.height/2-h/2-255,
				   "width", w,
				   "height",h);
	myStage.addChild(stack);
	*/
	var back = new ChipBack();
	var w2 = normalSquareSize;
	var h2 = w2;
	setProps(back, "x",myStage.width/2-w2/2-leftShift-60, 
			 	   "y",myStage.height/2-h2/2-300,
				   "width", w2,
				   "height",h2);
	//myStage.addChild(stack);
	myStage.addChild(back);
	stackTop = back;
}

function stackAnimation(square, callback){
	
	var w2 = normalSquareSize;
	var h2 = normalSquareSize;
	var oldTop = stackTop;
	
	/*var oldHeight = stack.height;
	var newHeight = 20*(currentGroup.length/groups[currentGroupIndex].length);
	trace(stack);
	stack.height = newHeight;
	stack.y += (oldHeight-newHeight);
	*/
	//createTween(stack, "height", None.easeInOut, newHeight, -1, time);
	//createTween(stack, "y", None.easeInOut, stack.y+(oldHeight-newHeight), -1, time);
	
	if(currentGroup.length/groups[currentGroupIndex].length!=0){
		var back = new ChipBack();
		setProps(back, "x",myStage.width/2-w2/2-leftShift-60, 
					   "y",myStage.height/2-h2/2-300,//+(20-newHeight),
					   "width", w2,
					   "height",h2);
		myStage.addChild(back);
		bringToFront(myStage, stackTop);
	
	}
	var time = 8;
	square.visible = false;
	createTween(stackTop, "x", None.easeInOut, stackTop.x+stackTop.width+20, -1, time);
	createTween(stackTop, "width", None.easeInOut, 0, -1, time, function(){
		var newHeight = square.height;
		var newWidth = square.width;
		var newx = square.x+40;
		var newy = square.y;
		square.height = oldTop.height;
		square.width = oldTop.width;
		square.x = oldTop.x;
		square.y = oldTop.y;
		myStage.removeChild(oldTop);
		square.visible = true;
		//createTween(square, "height", None.easeInOut, newHeight, -1, time);
		createTween(square, "width", None.easeInOut, newWidth, -1, time);
		createTween(square, "x", None.easeInOut, newx, -1, time, callback);
		//createTween(square, "y", None.easeInOut, newy, -1, time);
	});
	stackTop = back;
	if(stackTop){
		sendToBack(myStage, stackTop);
	}
}

function bringBucketForward(bucket){
	bringToFront(myStage, bucket["front"]);
	bringToFront(myStage, bucket["cName"]);
	bringToFront(myStage, bucket["count"]);
}

function sendBucketBackward(bucket){
	sendToBack(myStage, bucket["cName"]);
	sendToBack(myStage, bucket["count"]);
	sendToBack(myStage, bucket["front"]);
	sendToBack(myStage, bucket["back"]);
}
function bucketAnimation(square, bucket){
	createTween(square, "x", Regular.easeInOut, bucket["front"].x+ bucket["front"].width/2-square.width/2);
				createTween(square, "y", Regular.easeInOut, bucket["front"].y-150, -1, 10, function(){
					 bringBucketForward(bucket);
					 createTween(square, "y", Regular.easeInOut, square.y+200, -1, 10, function(){
						 bucket["count"].text =  Number(bucket["count"].text) + 1 
						 displayColors.shift();
						 myStage.removeChild(square); 
						 sendBucketBackward(bucket);
						 if(gameOver){
							endGame(true); 
						 }
					}); 
					 
				});
}

function createInteraction(square){
	makeDraggable(square, null, function(evt){
	  for(var i in buckets){
			if(buckets[i]["front"].hitTestPoint(mouseX, mouseY, true) || buckets[i]["front"].hitTestPoint(mouseX, mouseY+80, true)){
				
				if(myBuckets[i] == null){
					myBuckets[i] = new Array();
				}
				myBuckets[i].push(square.color);
				
				bringToFront(myStage, square);
				bucketAnimation(square,  buckets[i]);
				pullColor();
			
				break;
			}
	  }
	});
	
}

function isGameOver(){
	return (currentGroup.length == 0);
}
function pullColor(){
   updateLeftOver();	
   if(!isGameOver()){
	   var index = Math.floor(Math.random() * currentGroup.length);
	   var color = currentGroup[index];
	   currentGroup.splice(index, 1);
	   //var square = createSquare(color, myStage.stageWidth/2+35, myStage.stageHeight/2+80-300);
	   var nonsquare = createNonSquare(color, myStage.stageWidth/2+35, myStage.stageHeight/2+80-300);
	   stackAnimation(nonsquare, function(){
			createInteraction(nonsquare);			
		});
	   
   }else{
		gameOver = true;   
   }
	
}

cURL(dataURL, processXMLData);
reset();


function fillBuckets(e:Event){
	
	try{
		
		var xml = new XML(e.target.data);
		var buckets = xml.bucket;
		
		for(var bucket in buckets){
			var i = buckets[bucket].@bid;
			myBuckets[i] = new Array();
			var colors = buckets[bucket].colors;
			for(var color in colors){
				var cid = colors[color].@id;
				myBuckets[i].push(colorData[cid]);
			}
		}
		} catch (er:TypeError){ fTrace("XMLParser: Error - parsing "+er.message);
	}
	endGame(false);
	
}

function reset(){
	while( myStage.numChildren > 0 ){
       	myStage.removeChildAt( 0 );
	}
	myBuckets = new Array();
	currentGroup = null;
	currentGroupIndex = null;
	leftOver = null;
	gameOver = false;
	displayColors = new Array();
	leftOver = new TextField();
	ended = false;
	url = null;
	var temp = new TextField();
	var temp2 = new TextField();
	setProps(temp, "x",0,
			 		"y", 0
				  );
	setProps(temp2, "x",800,
				"y", 1000
			  );
	
	myStage.addChild(temp);
	myStage.addChild(temp2);
	
	buckets = makeBuckets();
	createStack();
    createLeftOver();

}

function startGame(group:int):void {
   currentGroup = clone(groups[group]);
   currentGroupIndex = group;
   pullColor();
}

function scrollSquares(){
	for(var i in displayColors){
		var square = displayColors[i];
		createTween(square, "y", Regular.easeInOut, square.y + scrollSpeed);
		
		
	}
}

function squareDrop(square, j){
	createTween(square, "y", Regular.easeInOut, square.y+(spaceBetweenFinalSquares+normalSquareSize*smallSquareRatio)*(j+1), -1, 20, function(){
		createHoverListeners(square,
			function(e, obj){
				
				//gTitle.text = obj.color.@id;
				//gTitle.text =  obj.color.@title
				//gTitle.x = graph.x+graph.width/2-gTitle.textWidth/2;
				
				trace("id:"+obj.color.@id);
				trace("total: " + colorData[obj.color.@id]['@total']);
				
				for(var i = 0; i<=6; i++){
					//trace("i: " + colorData[obj.color.@id]['@count'+i]);
					var ratio = (colorData[obj.color.@id]['@count'+i]/colorData[obj.color.@id]['@total']);
					//var ratio = Math.random();
					trace(ratio*graphHeight);
					var toAdd = ratio*graphHeight;
					
					createTween(gBars[i], "y", Regular.easeInOut, graphY-toAdd+.02*toAdd);
					createTween(gBars[i], "height", Regular.easeInOut, toAdd);
		
					gBuckets[i].count.text = int(100*ratio) + "%"; 
					
					createTween(gBuckets[i].count, "y", Regular.easeInOut, graphY-toAdd+.02*toAdd-gBuckets[i].count.height/2);
				}
				
				var cName = "'" + obj.color.@title +"'";
				//fTrace(cName);
				graph.title.text = "Player Classification of "+cName;
				//myFormat = new TextFormat();
				//myFormat.align = TextFormatAlign.CENTER;
				//myFormat.size = 24;
				//myTitle.setTextFormat(myFormat);
				
				//ExternalInterface.call("updateColor", cName);
				
				
				
				/*if(colorTopTitle==null){
					var myFormat:TextFormat = new TextFormat();
					myFormat.align = TextFormatAlign.CENTER;
					myFormat.size =20;
					
					var colorName = new TextField();
					
					setProps(colorName, "defaultTextFormat", myFormat,
									"x", myStage.width/2-150-leftShift,
									"y", 5,
								   "width", 300,
								   "height", 30,
								   "textColor", "0x000000",
								   "text", obj.color.@title
								  );
					
					myStage.addChild(colorName);
					colorTopTitle = colorName;
				}
			},
			function(e,obj){
				if(colorTopTitle != null){
					var colorName = myStage.getChildByName(colorTopTitle.name);
					if(colorName != null&&!obj.hitTestPoint(mouseX, mouseY, true)){
						myStage.removeChild(colorName);
						colorTopTitle = null;
					}
				}
			*/}
			
			)
	});
	
}


function createGraph(){
	graph = new Graph();
	setProps(graph, "x", myStage.width/2-50, 
				   "y", graphY,
				   "width", myStage.width/2-120,
				   "height", graphHeight);
	

	
	/*
	gTitle = new TextField();
	
	setProps(gTitle, "defaultTextFormat", myFormat,
			 		"x", graph.x+graph.width/2,
			 		"y", 30,
			 	   "width", 300,
				   "height", 30,
				   "textColor", "0x000000",
				   "text", ""
				  );
	*/
	var myFormat:TextFormat = new TextFormat();
	myFormat.align = TextFormatAlign.CENTER;
	myFormat.size = 30;
	
	var Title = new TextField();
	setProps(Title, "x", myStage.width/2-50,
		"y", graphY-520,
	   "width", myStage.width/2-100,
	   "height", 40,
	   "textColor", "0x000000",
	   "text", "Compare Your Results"
	  );
	myStage.addChild(Title);
	Title.setTextFormat(myFormat);
	
	var Title2 = new TextField();
	setProps(Title2, "x", 0,
		"y", graphY-520,
	   "width", myStage.width/2-100,
	   "height", 40,
	   "textColor", "0x000000",
	   "text", "Your Paint Chips"
	  );
	myStage.addChild(Title2);
	Title2.setTextFormat(myFormat);
	
	
	myFormat = new TextFormat();
	myFormat.align = TextFormatAlign.CENTER;
	myFormat.size = 19;
	
	
	var description = new TextField();
	setProps(description, "x", myStage.width/2-75,
		"y", graphY-480,
	   "width", myStage.width/2-75,
	   "height", 120,
	   "textColor", "0x000000",
	   "wordWrap", true,
	   "text", "The bars below show how all other players classified your selected chip.  The higher a bar, the more times the chip was placed in that bucket."
	  );
	myStage.addChild(description);
	description.setTextFormat(myFormat);
	
	var description2 = new TextField();
	setProps(description2, "x", 0,
		"y", graphY-480,
	   "width", myStage.width/2-100,
	   "height", 120,
	   "textColor", "0x000000",
	   "wordWrap", true,
	   "text", "Place your cursor over a chip to see the name of the color.  You can also compare your classification of the chip in the graph to the right."
	   );
	myStage.addChild(description2);
	description2.setTextFormat(myFormat);
	
	myFormat = new TextFormat();
	myFormat.align = TextFormatAlign.CENTER;
	myFormat.size = 22;
	
	
	var myTitle = new TextField();
	myTitle.defaultTextFormat = (myFormat);
	setProps(myTitle, "x", myStage.width/2-75,
		"y", graphY-340,
	   "width", myStage.width/2-75,
	   "height", 120,
	   "textColor", "0x000000",
	   "wordWrap", true,
	   "text", "Player Classification of ..."
	  );
	graph.title = myTitle;
	myStage.addChild(myTitle);
	//myTitle.setTextFormat(myFormat);
	
	
	myFormat = new TextFormat();
	myFormat.align = TextFormatAlign.CENTER;
	myFormat.size =19;
	
	var yTitle = new TextField();
	setProps(yTitle, "x", myStage.width/2-75,
		"y", graphY+75,
	   "width", myStage.width/2-75,
	   "height", 120,
	   "textColor", "0x000000",
	   "wordWrap", true,
	   "text", "Bucket Color"
	  );
	myStage.addChild(yTitle);
	yTitle.setTextFormat(myFormat);
	
	
	var sideTitle = addImage("title",  myStage.width/2-110, graphY-250);
	myStage.addChild(sideTitle);
	
	
	myFormat = new TextFormat();
	myFormat.align = TextFormatAlign.CENTER;
	myFormat.size =20;
	
	
	for(var k = 0; k<11; k+=2){
		var temp = new TextField();
	
		setProps(temp, "defaultTextFormat", myFormat,
						"x", myStage.width/2-120,
						"y", graphY-10-(300*k/10),
					   "width", 100,
					   "height", 30,
					   "textColor", "0x000000",
					   "text", ""+(k*10)
					  );
		myStage.addChild(temp);
		
	}
	
	gBuckets = makeBuckets();
	var buckets = gBuckets;
	var w = buckets[0]["front"].width/3;
	var h = buckets[0]["front"].height/3;
	for(var i in gBuckets){
		var bucket = gBuckets[i];
		bucket["back"].gotoAndStop(i+2);
		for(var obj in bucket){
			bucket[obj].width = w;
			bucket[obj].height = h;
			bucket[obj].x = 400+w*i;
			bucket[obj].y =430+graphY-400;
		}
		 bucket["count"].text = "0%";
		  bucket["count"].y -= 50;
		    bucket["back"].y -= 5;
																			 
	}
	gBars = new Array();
	var colors = new Array("FF0000", "00FF00", "0000FF", "FFFF00", "A52A2A", "FFA500", "A2627A");
	for(var bar = 0; bar<gBuckets.length; bar++){
		gBars.push(createRect("0x"+colors[bar],423+w*bar,graphY,25,50));
		gBars[bar].height = 0;
	}

myStage.addChild(graph);
//myStage.addChild(gTitle);
	
	
}



function createURL(){
	finishedURL = "";// baseURL+"?";
	for(var i in myBuckets){
		if(i!=0){
			finishedURL += "&";
		}
		finishedURL += i+"=";
		var colors = myBuckets[i];
		for(var j in colors){
			if(j!=0){
				finishedURL += ","
			}
			finishedURL += colors[j].@id;
		}
	}
	return finishedURL;
}

function bucketRally(bucket, obj, i){
	bucket["back"].gotoAndStop(i+2)
	createTween(bucket[obj], "width", Regular.easeInOut, bucket["front"].width/3);
	createTween(bucket[obj], "height", Regular.easeInOut, bucket["front"].height/3);
	var newX = bucket["front"].width/2+(myStage.width/2-(buckets.length*bucket["front"].width/2)+bucket[obj].x-bucket["front"].x+(bucket["front"].width*i))/3;
	var newY = (bucket[obj].y-bucket["front"].y+bucket["front"].height/2+bucket["back"].height/2)/2+moveY+graphY-400;
	createTween(bucket[obj], "x", Regular.easeInOut, newX);
	createTween(bucket[obj], "y", Regular.easeInOut, newY, -1, 10, function(){
	createTween(bucket[obj], "rotation", Regular.easeInOut, 180, -1, 20);
		if(obj == "front"){ 
			createTween(bucket["back"], "y", Regular.easeInOut, bucket["back"].y+24,-1, 20, function(){  //+111 on y
				bucket["back"].gotoAndStop(i+9);
				var colors = myBuckets[i];
				for(var j in colors){
					var color = colors[j];
					var square = createSquare(color, bucket["front"].x-bucket["front"].width/2, bucket["front"].y-30, smallSquareRatio);
					displayColors.push(square);
					bringBucketForward(bucket);
					squareDrop(square, j);
					
				}								   
			});
		 
		}
	});
}

function grabColorValues(){
	cURL(colorDataURL, processColorXMLData);
}

function endGame(actualGame):void {
	
	if(!ended){
		if(actualGame == undefined){
			actualGame = true;
		}
		while(displayColors.length > 0){
			myStage.removeChild(myStage.getChildByName(displayColors.shift().name));
		}
		if(stackTop){
			myStage.removeChild(stackTop);
		}
	
		var ended = true;
		myStage.removeChild(myStage.getChildByName(leftOver.name));
		var finishedURL = "";
		if(actualGame){
			finishedURL = createURL();
		}
		fTrace("aboutToCall");
		ExternalInterface.call("gameFinished", finishedURL, actualGame);
		//fTrace("made it here");
		//ExternalInterface.addCallback("gameFinished", getTextFromJavaScript);
		//function getTextFromJavaScript(str):void {
		//	fTrace(str);
		//}
		
		//getURL("javascript:gameFinished("+finishedURL+");");
		createGraph();
		for(var i in buckets){
			var bucket = buckets[i];
			for(var obj in bucket){
				bucketRally(bucket, obj, i);
			}
		}
		
		
		/*var topBG = createRect("0x000000", 0, 0, myStage.width*2, 100);
		//bgSquare.alpha = .1;
		var bottomBG = createRect("0x000000", 0, 400, myStage.width*2, 100);
		timer(100, function(){
			bringToFront(myStage, topBG);
			bringToFront(myStage, bottomBG);
			scrollSquares();
		},0);
		createHoverListeners(bottomBG, function(evt){
			scrollSpeed = 1;
		}, function(evt){
			scrollSpeed = 0;
		});
				
		createHoverListeners(topBG, function(evt){
			scrollSpeed = -1;
		}, function(evt){
			scrollSpeed = 0;
		});
		on the 
		*/
															 
	}
}

function addImage(className,x ,y){
	var ClassReference:Class = getDefinitionByName(className) as Class;
	var instance:* = new ClassReference();
	var myImage:Bitmap = new Bitmap(instance);
	var sprite:Sprite = new Sprite();
	sprite.x = x;
	sprite.y = y;
	sprite.addChild(myImage);
	//stage.addChild(sprite);
	return sprite;
}

