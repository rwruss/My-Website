<?php
echo '
<html>
<head>
<title>rwruss.com - Home of webGL Demos and Other Projects</title>
<link rel="stylesheet" type="text/css" href="navMenu.css">
<script type="text/javascript">var enableGL = false;</script>
<script src="navMenu.js"></script>

</head>
<body>';


include ("navBody.php");
echo '
<div class="contentDiv" style="position:absolute; top:35; display:inline-block; margin:auto; width:75%;">
<div>Thank you for visiting my site.  I will be posting my current project and demonstrations of some of
the thigns I have created.  I am currently focused on several projects using webGL so all of the demos I have
at this time are centered on that.  Please see below for a brief sample of what I have to show so far.</div>
	<div style="padding:1%; position:relative; float:left; width:75%">
		<div class="blockImg"><a href="terrainIntro.php"><img class="imgPrev" src="./wglterrain.png"></a></div>
		<div class="blockContent"><span class="pHead">3d Terrain</span><br>My current project is the interactive terrain
		map.  This map shows the area of Eurasia from 30&deg West to 90 &deg East.  You can move around the map and
		zoom in and out to see different areas.  I would like to use this to show changes in the areas throughout
		history.  My next step will be to show clickable territory overlays that will provide the user information
		about the selected area.</div>
	</div>

	<div style="padding:1%; position:relative; float:left; width:75%">
		<div class="blockImg"><a href="fractal.php"><img class="imgPrev" src="./wgljulia.png"></a></div>
		<div class="blockContent"><span class="pHead">Interactive Fractals</span><br>Fractals are a fun demonstration of what can be done with webGL.  I have examples of Julia Sets, the Mandelbrot Set,
	 and the \"burning ship\".
		</div>
	</div>

	<div style="padding:1%; position:relative; float:left; width:75%">
		<div class="blockImg"><a href="roundedLines.php"><img class="imgPrev" src="./wgllines.png"></a></div>
		<div class="blockContent"><span class="pHead">Thick lines with webGL</span><br>
		Unfortunately WebGL doesn"t provide a good way to draw thick lines.  This is an
		example of something I came up with to draw connected lines with smooth transitions.  I have seen ways of
		doing this with sharp corners but I was really looking for something a little smoother that would
		look more natural for drawing rivers.</div>
	</div>

	<div style="padding:1%; position:relative; float:left; width:75%">
		<div class="blockImg"><a href="core.php"><img class="imgPrev" src="./wglspace.png"></a></div>
		<div class="blockContent"><span class="pHead">webGL movement, space, lasers, etc...!</span><br>
		This is a demonstration of some movement around a ball.
		In this case, I chose to make the ball a planet and have the flying rectangles shoot lasers at it!</div>
	</div>

	<div style="padding:1%; position:relative; float:left; width:75%">
		<div class="blockImg"><a href="webGL.php"><img class="imgPrev" src="./wglgraph.png"></a></div>
		<div class="blockContent"><span class="pHead">3d Graphing using webGL</span><br>
		My first creation as a 3d graphing utility.  Using this page,
		you can enter in the parameters of a function and it will output the calculated surface.</div>
	</div>

</div>
</body>
</html>';
?>
