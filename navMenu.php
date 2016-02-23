<?php

$postVals = explode(",", $_POST['val1']);

switch ($postVals[0]) {
	case 1: 
		echo "Coming soon!";
		break;
		
	case 2:
		echo "<div class='menuItem' id='webgl')>webGL graphing demo</div>
			<div class='menuItem' id='core'>space Rectangles</div>
			<div class='menuItem' id='terrainIntro'>3d terrain explorer<div>";
		break;
		
	case 3: 
		echo "Coming soon!";
		break;
		
	case 4: 
		echo "Coming soon!";
		break;
}

?>