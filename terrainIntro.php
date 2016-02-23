<?php

echo "<head>
<title>3d Terrain with webGL</title>
<link rel='stylesheet' type='text/css' href='navMenu.css'>
<script src='navMenu.js'></script>
</head>
<body style='width:99%; height:90%'>";
include ("navBody.php");
echo "
<div style='position:absolute; top:25; width:99%;'>
	<span style='display:table; margin:0 auto;'>Explanation of the demo</span><br>
	<span>This demo shows an example of terrain created with webGL.  It shows the areas of Eurasia and Africa from about 30&degW to 90&degE.  
	This is very much a work in progress so there are likely to be frequent changes and updates as 
	new features are added or existing things are adjusted.  This demo will display on mobile devices but 
	still needs some control features to allow you to move around.
	For those who are viewing from a desktop, you can interact with the map using the following keys:<br>
	<table>
	<tr><td>Scroll Left/Right:</td><td>A/D</td></tr>
	<tr><td>Scroll Forward/Backward:</td><td>W/S</td></tr>
	<tr><td>Rotate:</td><td>Q/E</td></tr>
	</table><br>
	Using the mouse wheel will allow zooming in and out.<br>
	Clicking at any point on the land surface will return some coordinates of that location.  This is a work in process but 
	will eventually return latitude and longitude of the click location.
	<p>
	Notes for the current version:<br>
	There are fields on the right that are diagnostic items that track movement, zoom and other items.  
	These will update as you scroll and move around the map.<br>
	There is also a demonstration of some area shading (a red circle).  This could be used to 
	show territories or other information to be overlayed on the map.  
	<p>
	Enough - <a href='/ib3/other/scalable/terrain.php'>'Take me to the demo!</a></span>
<div>";

?>