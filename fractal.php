

<?php
echo '
<html>

<head>
<title>Fractals</title>
<link rel=\'stylesheet\' type=\'text/css\' href=\'navMenu.css\'>
<script src="navMenu.js"></script>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">

</head>';
 include('navBody.php');
echo '
<body onload="webGLStart();">
<div class="contentDiv" style="position:absolute; top:35; display:inline-block; margin:auto;">
WebGl allows us to make a huge amount of calculations and display the results in interesting and meaningful ways.  One fun demonstration of this
is fractal images.  There are endless examples of this and many that are much more impressive than what I have here but these examples give
an idea of what can be done and how interesting these images can be.  Select any of the images below to

<div style="padding:1%; position:relative; float:left; width:99%">
  <div class="blockImg"><a href="julia.php"><img class="imgPrev" src="./wgljulia.png"></a></div>
  <div class="blockContent"><span class="pHead">Julia Set</span><br>
  There are more options with this one and a huge range of patterns to dispay.  By adjusting the real and imaginary compnents of the equation you
  can drastically alter the appearance.  The starting image is one of my favorites - it looks like a bunch of endless snowflakes.</div>
</div>

	<div style="padding:1%; position:relative; float:left; width:99%">
		<div class="blockImg"><a href="shipDemo.php"><img class="imgPrev" src="./wglship.png"></a></div>
		<div class="blockContent"><span class="pHead">Burning Ship</span><br>The "burning ship" is very similar to the Mandelbrot Set.
    The difference is that you use the absolute value in the iteration.  I like this one because it has much more irregular detail and the different regions offer
     some interesting, unique patterns.</div>
	</div>


  <div style="padding:1%; position:relative; float:left; width:99%"">
    <div class="blockImg"><a href="mb.php"><img class="imgPrev" src="./wglmb.png"></a></div>
    <div class="blockContent"><span class="pHead">The Mandelbrot Set</span><br>My first fractal project - the Mandelbrot Set</div>
  </div>

</div>
</body>

</html>';
?>
