<?php

echo '
<html>

<head>
<title>Mandelbrot Fractal</title>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<link rel=\'stylesheet\' type=\'text/css\' href=\'navMenu.css\'>
<script src="navMenu.js"></script>
<script type="text/javascript" src="glMatrix-0.9.5.min.js"></script>
<script type="text/javascript" src="webgl-utils.js"></script>

<script id="shader-fs" type="x-shader/x-fragment">
    precision mediump float;

	varying vec2 relCoordinate;
	//uniform vec3 uOffsets;
	float a, b;

    void main(void) {

		vec2 z,c = relCoordinate+vec2(0.0,0.0);
		float ptColor = 0.0;
		for (int i=0;i<64;i++){
			if(dot(z,z)>4.) break;

			a = abs(z.x);
			b = -abs(z.y);
			z = vec2((a*a-b*b), (a*b+a*b))+c;
			ptColor+=1.0;
		}
		float useColor = mod(ptColor,3.0);
		//if (useColor == 0.0) gl_FragColor = vec4(ptColor/64.0, 0.0, 0.0, 1.0);
		//else if (useColor == 1.0) gl_FragColor = vec4(0.0, ptColor/64.0, 0.0, 1.0);
		//else gl_FragColor = vec4(0.0, 0.0, ptColor/64.0, 1.0);
		gl_FragColor = vec4(0.0, ptColor/64.0, 0., 1.0);
    }
</script>

<script id="shader-vs" type="x-shader/x-vertex">
    attribute vec2 aVertexPosition;

    uniform mat4 uMVMatrix;
    uniform mat4 uPMatrix;
	uniform vec3 uOffsets;

	varying vec2 relCoordinate;

    void main(void) {
        gl_Position = uPMatrix * uMVMatrix * vec4(aVertexPosition.xy, 0.0, 1.0);
		relCoordinate = aVertexPosition*uOffsets.z+uOffsets.xy;
    }
</script>


<script type="text/javascript">

    var gl;

    function initGL(canvas) {
        try {
            gl = canvas.getContext("experimental-webgl");
            gl.viewportWidth = canvas.width;
            gl.viewportHeight = canvas.height;
        } catch (e) {
        }
        if (!gl) {
            alert("Could not initialise WebGL, sorry :-(");
        }
    }


    function getShader(gl, id) {
        var shaderScript = document.getElementById(id);
        if (!shaderScript) {
            return null;
        }

        var str = "";
        var k = shaderScript.firstChild;
        while (k) {
            if (k.nodeType == 3) {
                str += k.textContent;
            }
            k = k.nextSibling;
        }

        var shader;
        if (shaderScript.type == "x-shader/x-fragment") {
            shader = gl.createShader(gl.FRAGMENT_SHADER);
        } else if (shaderScript.type == "x-shader/x-vertex") {
            shader = gl.createShader(gl.VERTEX_SHADER);
        } else {
            return null;
        }

        gl.shaderSource(shader, str);
        gl.compileShader(shader);

        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            alert(gl.getShaderInfoLog(shader));
            return null;
        }

        return shader;
    }


    var shaderProgram;

    function initShaders() {
        var fragmentShader = getShader(gl, "shader-fs");
        var vertexShader = getShader(gl, "shader-vs");

        shaderProgram = gl.createProgram();
        gl.attachShader(shaderProgram, vertexShader);
        gl.attachShader(shaderProgram, fragmentShader);
        gl.linkProgram(shaderProgram);

        if (!gl.getProgramParameter(shaderProgram, gl.LINK_STATUS)) {
            alert("Could not initialise shaders");
        }

        gl.useProgram(shaderProgram);

        shaderProgram.vertexPositionAttribute = gl.getAttribLocation(shaderProgram, "aVertexPosition");
        gl.enableVertexAttribArray(shaderProgram.vertexPositionAttribute);

        shaderProgram.pMatrixUniform = gl.getUniformLocation(shaderProgram, "uPMatrix");
        shaderProgram.mvMatrixUniform = gl.getUniformLocation(shaderProgram, "uMVMatrix");
        shaderProgram.offsetsUniform = gl.getUniformLocation(shaderProgram, "uOffsets");
    }


    var mvMatrix = mat4.create();
    var mvMatrixStack = [];
    var pMatrix = mat4.create();

    function mvPushMatrix() {
        var copy = mat4.create();
        mat4.set(mvMatrix, copy);
        mvMatrixStack.push(copy);
    }

    function mvPopMatrix() {
        if (mvMatrixStack.length == 0) {
            throw "Invalid popMatrix!";
        }
        mvMatrix = mvMatrixStack.pop();
    }


    function setMatrixUniforms() {
        gl.uniformMatrix4fv(shaderProgram.pMatrixUniform, false, pMatrix);
        gl.uniformMatrix4fv(shaderProgram.mvMatrixUniform, false, mvMatrix);
    }


    function degToRad(degrees) {
        return degrees * Math.PI / 180;
    }

    var squareVertexPositionBuffer;
    var squareVertexColorBuffer;
	var shadingCoords;

	var lineWidth = 0.25;
	var drawLength = 0;
	var position = [0, 0, 2.0];

    function initBuffers() {
		vertices = [];

        squareVertexPositionBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, squareVertexPositionBuffer);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1,-1,1,1,-1,1,1]), gl.STATIC_DRAW);
    }


    function drawScene() {
        gl.viewport(0, 0, gl.viewportWidth, gl.viewportHeight);
        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

        mat4.perspective(45, gl.viewportWidth / gl.viewportHeight, 0.1, 100.0, pMatrix);

        mat4.identity(mvMatrix);
        mat4.translate(mvMatrix, [0.0, 0.0, -1.0]);

        mvPushMatrix();
        //mat4.rotate(mvMatrix, degToRad(0), [1, 0, 0]);

        gl.bindBuffer(gl.ARRAY_BUFFER, squareVertexPositionBuffer);
        gl.vertexAttribPointer(shaderProgram.vertexPositionAttribute, 2, gl.FLOAT, false, 0, 0);

        gl.uniform3f(shaderProgram.offsetsUniform, position[0], position[1], position[2]);

        setMatrixUniforms();
        gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);

    }


    var currentlyPressedKeys = {};

	function handleKeyDown(event) {

        currentlyPressedKeys[event.keyCode] = true;
		}

    function handleKeyUp(event) {
        currentlyPressedKeys[event.keyCode] = false;
		}


	function handleKeys() {
		//alert("set speed");
		if (currentlyPressedKeys[37] || currentlyPressedKeys[65]) {

			// Left cursor key or A
			xSpeed = -0.0005;
			} else if (currentlyPressedKeys[39] || currentlyPressedKeys[68]) {
			// Right cursor key or D
			xSpeed = 0.0005;
			} else {
			xSpeed = 0;
			}

		if (currentlyPressedKeys[38] || currentlyPressedKeys[87]) {

			// Up cursor key or W
			zSpeed = -0.0005;
			} else if (currentlyPressedKeys[40] || currentlyPressedKeys[83]) {
			// Down cursor key
			zSpeed = 0.0005;
			} else {
			zSpeed = 0;
			}
		if (currentlyPressedKeys[107]) {

			// Up cursor key or W
			zoomVel = 0.9;
			} else if (currentlyPressedKeys[109]) {
			// Down cursor key
			zoomVel = 1.1;
			} else {
			zoomVel = 1.00;
			}
		}


    function tick() {
        requestAnimFrame(tick);
		handleKeys();
        drawScene();
        animate();
    }


    var lastTime = 0;

    function animate() {
        var timeNow = new Date().getTime();
        if (lastTime != 0) {
            var elapsed = timeNow - lastTime;
			position[2] *= zoomVel;
			position[0] += xSpeed*elapsed*position[2];
			position[1] -= zSpeed*elapsed*position[2];
			document.getElementById("xCoord").value = position[0];
			document.getElementById("yCoord").value = position[1];
			document.getElementById("zoom").value = 1.0/position[2];
        }
        lastTime = timeNow;
    }


    function webGLStart() {
        var canvas = document.getElementById("shipCanvas");
        initGL(canvas);
        initShaders()
        initBuffers();

        gl.clearColor(0.0, 0.0, 0.0, 1.0);
        gl.enable(gl.DEPTH_TEST);

        tick();
		document.onkeydown = handleKeyDown;
		document.onkeyup = handleKeyUp;
    }

</script>

</head>';
include ('navBody.php');
echo '
<body onload="webGLStart();">
    <canvas id="shipCanvas" style="border: none; position:relative; top:30;" width="500" height="500"></canvas>
    <div style="position:absolute; top:30; left:625; width:40%"">
    My favorite area is around -1.86, 0.0035, 35 (X, Y, Zoom).  There is a nice looking ship with some towers hidden there.  You can also find some other
    even smaller ships to each side.  Always something new as you go deeper!

    <p>Use the following keys to move:<br>
    Left/Right: A/D<br>
    Up/Down: W/S<br>
    Zoom In/Out: Numpad +/-
	<table>
		<tr><td>X:</td><td><input id="xCoord"></td></tr>
		<tr><td>Y:</td><td><input id="yCoord"></td></tr>
		<tr><td>Zoom:</td><td><input id="zoom"></td></tr>
	</table>
</body>
</div>
</html>
';
?>
