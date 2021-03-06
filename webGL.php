<?php
echo '
<html>

<head>
<title>WebGL - drawing a 3d Surface</title>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<link rel=\'stylesheet\' type=\'text/css\' href=\'navMenu.css\'>
<script src="navMenu.js"></script>

<script type="text/javascript" src="glMatrix-0.9.5.min.js"></script>
<script type="text/javascript" src="webgl-utils.js"></script>

<script type="text/javascript"
  src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
</script>

<script id="shader-fs" type="x-shader/x-fragment">
    precision mediump float;

    varying vec4 vColor;
	varying vec2 vVertexPosition;

    void main(void) {
		if (mod(vVertexPosition.x, 0.1) < 0.005 || mod(vVertexPosition.y, 0.1) < 0.005) {
			gl_FragColor = vec4(0.5, 0.5, 0.5, 1.0);
			}
		else {
			gl_FragColor = vColor;
			}
		//gl_FragColor = vColor;
    }
</script>

<script id="shader-vs" type="x-shader/x-vertex">

	attribute vec2 aVertexPosition;
	attribute vec3 aSurfaceNormals;
    attribute float aVertexValue;

    uniform mat4 uMVMatrix;
    uniform mat4 uPMatrix;

	varying vec4 vColor;
	varying vec2 vVertexPosition;

    void main(void) {
        gl_Position = uPMatrix * uMVMatrix * vec4(aVertexPosition.x, aVertexValue, aVertexPosition.y, 1.0);
		vColor = vec4(1.0, aVertexValue, 0.0, 1.0);
		vVertexPosition = aVertexPosition;
    }
</script>

<script id="axisFS" type="x-shader/x-fragment">
    precision mediump float;

	varying vec3 vVertexPosition;

    void main(void) {
		if (vVertexPosition.x > 0.0) gl_FragColor = vec4(1.0, 0.0, 0.0, 1.0);
		else if (vVertexPosition.y > 0.0) gl_FragColor = vec4(0.0, 1.0, 0.0, 1.0);
		else gl_FragColor = vec4(0.0, 0.0, 1.0, 1.0);
    }
</script>

<script id="axisVS" type="x-shader/x-vertex">

	attribute vec3 aVertexPosition;

    uniform mat4 uMVMatrix;
    uniform mat4 uPMatrix;

	varying vec3 vVertexPosition;

    void main(void) {
        gl_Position = uPMatrix * uMVMatrix * vec4(aVertexPosition, 1.0);
		vVertexPosition = aVertexPosition;
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
	var axisProgram;

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

        shaderProgram.vertexValue = gl.getAttribLocation(shaderProgram, "aVertexValue");
        gl.enableVertexAttribArray(shaderProgram.vertexValue);

		shaderProgram.surfaceNormalAttribute = gl.getAttribLocation(shaderProgram, "aSurfaceNormals");
        gl.enableVertexAttribArray(shaderProgram.surfaceNormalAttribute);

        shaderProgram.pMatrixUniform = gl.getUniformLocation(shaderProgram, "uPMatrix");
        shaderProgram.mvMatrixUniform = gl.getUniformLocation(shaderProgram, "uMVMatrix");

		var fragmentShader = getShader(gl, "axisFS");
        var vertexShader = getShader(gl, "axisVS");

        axisProgram = gl.createProgram();
        gl.attachShader(axisProgram, vertexShader);
        gl.attachShader(axisProgram, fragmentShader);
        gl.linkProgram(axisProgram);

        if (!gl.getProgramParameter(axisProgram, gl.LINK_STATUS)) {
            alert("Could not initialise shaders");
        }

        gl.useProgram(axisProgram);

        axisProgram.vertexPositionAttribute = gl.getAttribLocation(axisProgram, "aVertexPosition");
        gl.enableVertexAttribArray(axisProgram.vertexPositionAttribute);

        axisProgram.pMatrixUniform = gl.getUniformLocation(axisProgram, "uPMatrix");
        axisProgram.mvMatrixUniform = gl.getUniformLocation(axisProgram, "uMVMatrix");
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

	function calcSurface() {
		valList = [];
		normList = new Array();
		a = parseFloat(document.getElementById("a").value);
		b = parseFloat(document.getElementById("b").value);
		n = parseFloat(document.getElementById("n").value);
		m = parseFloat(document.getElementById("m").value);
		xMin = parseFloat(document.getElementById("xmin").value);
		xMax = parseFloat(document.getElementById("xmax").value);
		yMin = parseFloat(document.getElementById("ymin").value);
		yMax = parseFloat(document.getElementById("ymax").value);
		xStep = (xMax-xMin)/100;
		yStep = (yMax-yMin)/100;
		//alert(xStep + "," + xMax);
		for (var i=0; i<100; i++) {
			y=yMin + yStep*i;
			for (var j=0; j<100; j++) {
			x=xMin + xStep*j;
			valList.push(a*Math.pow(x,n)+b*Math.pow(y,m));
			}
		}
		gl.bindBuffer(gl.ARRAY_BUFFER, surfaceVals);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(valList), gl.STATIC_DRAW);
	}


    var surfaceVerts;
	var surfaceVals;
	var surfaceNormals;
	var indexBuffer;
	var xAxis;
	var yAxis;
	var zAxis;

    function initBuffers() {
		vertList = [];
		valList = [];
		normList = new Array();
		for (var i=0; i<100; i++) {
			for (var j=0; j<100; j++) {
			vertList.push((j-50)/100, (i-50)/100);
			valList.push(((i-50)*(i-50)/10000-(j-50)*(j-50)/10000));
			normList.push(0.0, 0.0, 0.0);
			}
		}

        surfaceVerts = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, surfaceVerts);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertList), gl.STATIC_DRAW);

        surfaceVals = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, surfaceVals);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(valList), gl.STATIC_DRAW);

		surfaceNormals = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, surfaceNormals);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(normList), gl.STATIC_DRAW);
		//alert(vertList.length + ", " + valList.length);

		elementList = new Array();
		for (var i=0; i<99; i++) {
			elementList.push(i*100);
			for (var j=0; j<100; j++) {
				elementList.push(i*100+j, (i+1)*100+j);
				}
			elementList.push((i+1)*100+99);
			}
		indexBuffer = gl.createBuffer();
		gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, indexBuffer);
		gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, new Uint16Array(elementList), gl.STATIC_DRAW);

		xAxis = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, xAxis);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([0.0, 0.0, 0.0, 10.0, 0.0, 0.0]), gl.STATIC_DRAW);

		yAxis = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, yAxis);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([0.0, 0.0, 0.0, 0.0, 10.0, 0.0]), gl.STATIC_DRAW);

		zAxis = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, zAxis);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([0.0, 0.0, 0.0, 0.0, 0.0, 10.0]), gl.STATIC_DRAW);
		}

    function drawScene() {
        gl.viewport(0, 0, gl.viewportWidth, gl.viewportHeight);
        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

        mat4.perspective(45, gl.viewportWidth / gl.viewportHeight, 0.1, 100.0, pMatrix);

        mat4.identity(mvMatrix);
		mat4.translate(mvMatrix, [0.0, -0.250, -2.50]);
        mat4.rotate(mvMatrix, (rY), [0, 1, 0]);
        mat4.rotate(mvMatrix, (rX), [1, 0, 0]);
		gl.useProgram(shaderProgram);
        gl.bindBuffer(gl.ARRAY_BUFFER, surfaceVerts);
        gl.vertexAttribPointer(shaderProgram.vertexPositionAttribute, 2, gl.FLOAT, false, 0, 0);

        gl.bindBuffer(gl.ARRAY_BUFFER, surfaceVals);
        gl.vertexAttribPointer(shaderProgram.vertexValue, 1, gl.FLOAT, false, 0, 0);

		gl.bindBuffer(gl.ARRAY_BUFFER, surfaceNormals);
        gl.vertexAttribPointer(shaderProgram.surfaceNormalAttribute, 3, gl.FLOAT, false, 0, 0);

        setMatrixUniforms();
		gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, indexBuffer);
		gl.drawElements(gl.TRIANGLE_STRIP, 19998, gl.UNSIGNED_SHORT, 0);
        //gl.drawArrays(gl.TRIANGLE_STRIP, 0, 10000);

		gl.useProgram(axisProgram);
		gl.bindBuffer(gl.ARRAY_BUFFER, xAxis);
        gl.vertexAttribPointer(axisProgram.vertexPositionAttribute, 3, gl.FLOAT, false, 0, 0);

		gl.uniformMatrix4fv(axisProgram.pMatrixUniform, false, pMatrix);
        gl.uniformMatrix4fv(axisProgram.mvMatrixUniform, false, mvMatrix);

		gl.drawArrays(gl.LINES, 0, 2);

		gl.bindBuffer(gl.ARRAY_BUFFER, yAxis);
        gl.vertexAttribPointer(axisProgram.vertexPositionAttribute, 3, gl.FLOAT, false, 0, 0);

		gl.uniformMatrix4fv(axisProgram.pMatrixUniform, false, pMatrix);
        gl.uniformMatrix4fv(axisProgram.mvMatrixUniform, false, mvMatrix);

		gl.drawArrays(gl.LINES, 0, 2);

		gl.bindBuffer(gl.ARRAY_BUFFER, zAxis);
        gl.vertexAttribPointer(axisProgram.vertexPositionAttribute, 3, gl.FLOAT, false, 0, 0);

		gl.uniformMatrix4fv(axisProgram.pMatrixUniform, false, pMatrix);
        gl.uniformMatrix4fv(axisProgram.mvMatrixUniform, false, mvMatrix);

		gl.drawArrays(gl.LINES, 0, 2);
    }

	function handleKeyDown(event) {
        currentlyPressedKeys[event.keyCode] = true;
		}

    function handleKeyUp(event) {
        currentlyPressedKeys[event.keyCode] = false;
		}

	var currentlyPressedKeys = {};
	function handleKeys() {

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
			wX = 0.001;
			} else if (currentlyPressedKeys[40] || currentlyPressedKeys[83]) {
			// Down cursor key
			wX = -0.001;
			} else {
			wX = 0;
			}
		if (currentlyPressedKeys[81]) {

			// Q key
			wY = 0.001;
			} else if (currentlyPressedKeys[69]) {
			// Down cursor key
			wY = -0.001;
			} else {
			wY = 0;
			}
		}

    var lastTime = 0;
	var rY = 0;
	var wY = 0;
	var rX = 0;
	var wX = 0;
    function animate() {
        var timeNow = new Date().getTime();
        if (lastTime != 0) {
            var elapsed = timeNow - lastTime;

            rY += elapsed*wY;
			rX += elapsed*wX;
        }
        lastTime = timeNow;
    }


    function tick() {
        requestAnimFrame(tick);
		handleKeys();
        drawScene();
        animate();
    }


    function webGLStart() {
        var canvas = document.getElementById("myDemoCanvas");
        initGL(canvas);
        initShaders()
        initBuffers();
		calcSurface();
        gl.clearColor(0.0, 0.0, 0.0, 1.0);
        gl.enable(gl.DEPTH_TEST);

        tick();
    }

	document.onkeydown = handleKeyDown;
	document.onkeyup = handleKeyUp;
</script>

</head>';
 include('navBody.php');
 echo '
<body onload="webGLStart();">
    <canvas id="myDemoCanvas" style="border: none; position:relative; top:30;" width="500" height="500"></canvas>
	<div style="position:fixed; top:350; left:725;">
	<table>
		<tr><td>a</td><td><input id="a" value="0.0001"></td></tr>
		<tr><td>b</td><td><input id="b" value="0.0001"></td></tr>
		<tr><td>n</td><td><input id="n" value="2.0"></td></tr>
		<tr><td>m</td><td><input id="m" value="2.0"></td></tr>
		<tr><td>xMin</td><td><input id="xmin" value="-50.0"></td></tr>
		<tr><td>xMax</td><td><input id="xmax" value="50.0"></td></tr>
		<tr><td>yMin</td><td><input id="ymin" value="-50.0"></td></tr>
		<tr><td>yMax</td><td><input id="ymax" value="50.0"></td></tr>
		<tr style="cursor:pointer;"><td colspan="2" onclick="calcSurface()">ReCalculate</td></tr>

	</table>
	</div>
	<div style="position:fixed; top:25; left:725;">
	This demonstration will provide a graph of a function in the format:
	$$z = f(x,y) = ax^2 + by^2$$
	<p>
	One you enter your desired values, click "Recalculate" to redraw the surface.  You may also rotate the surface using the following keys:
	<br>Q/E: Rotates around the Y axis<br>
	W/S: Rotates around the X axis
	</div>
</body>

</html>';
?>
