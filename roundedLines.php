<?php

echo "

<html>
<head>
<title>Drawing Smooth Lines</title>
<meta http-equiv='content-type' content='text/html; charset=ISO-8859-1'>
<title>Learning WebGL &mdash; lesson 3</title>
<link rel='stylesheet' type='text/css' href='navMenu.css'>
<script src='navMenu.js'></script>


<script type='text/javascript' src='glMatrix-0.9.5.min.js'></script>
<script type='text/javascript' src='webgl-utils.js'></script>

<script id='shader-fs' type='x-shader/x-fragment'>
    precision mediump float;

    varying vec4 vColor;
	varying vec3 vVertexShade;

    void main(void) {		
		if (vVertexShade.y < vVertexShade.x) gl_FragColor = vec4(1.0, 1.0, 1.0, 1.0);
		else {
			float xDiff = vVertexShade.x-vVertexShade.y;
			if ((xDiff*xDiff+vVertexShade.z*vVertexShade.z) < 0.0625) gl_FragColor = vec4(vVertexShade.z, 1.0, 1.0, 1.0);
			else discard;
		}
	gl_FragColor = vec4(1.0, 0.0, 0.0, 1.0);
    }
</script>

<script id='shader-vs' type='x-shader/x-vertex'>
    attribute vec2 aVertexPosition;
    attribute vec3 aVertexShade;
    attribute vec4 aVertexColor;

    uniform mat4 uMVMatrix;
    uniform mat4 uPMatrix;

    varying vec4 vColor;
    varying vec3 vVertexShade;

    void main(void) {
        gl_Position = uPMatrix * uMVMatrix * vec4(aVertexPosition.xy, 0.0, 1.0);
        vColor = aVertexColor;
		vVertexShade = aVertexShade;
    }
</script>


<script type='text/javascript'>

    var gl;

    function initGL(canvas) {
        try {
            gl = canvas.getContext('experimental-webgl');
            gl.viewportWidth = canvas.width;
            gl.viewportHeight = canvas.height;
        } catch (e) {
        }
        if (!gl) {
            alert('Could not initialise WebGL, sorry :-(');
        }
    }


    function getShader(gl, id) {
        var shaderScript = document.getElementById(id);
        if (!shaderScript) {
            return null;
        }

        var str = '';
        var k = shaderScript.firstChild;
        while (k) {
            if (k.nodeType == 3) {
                str += k.textContent;
            }
            k = k.nextSibling;
        }

        var shader;
        if (shaderScript.type == 'x-shader/x-fragment') {
            shader = gl.createShader(gl.FRAGMENT_SHADER);
        } else if (shaderScript.type == 'x-shader/x-vertex') {
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
        var fragmentShader = getShader(gl, 'shader-fs');
        var vertexShader = getShader(gl, 'shader-vs');

        shaderProgram = gl.createProgram();
        gl.attachShader(shaderProgram, vertexShader);
        gl.attachShader(shaderProgram, fragmentShader);
        gl.linkProgram(shaderProgram);

        if (!gl.getProgramParameter(shaderProgram, gl.LINK_STATUS)) {
            alert('Could not initialise shaders');
        }

        gl.useProgram(shaderProgram);

        shaderProgram.vertexPositionAttribute = gl.getAttribLocation(shaderProgram, 'aVertexPosition');
        gl.enableVertexAttribArray(shaderProgram.vertexPositionAttribute);

        shaderProgram.vertexColorAttribute = gl.getAttribLocation(shaderProgram, 'aVertexColor');
        gl.enableVertexAttribArray(shaderProgram.vertexColorAttribute);
		
		shaderProgram.vertexShadeAttr = gl.getAttribLocation(shaderProgram, 'aVertexShade');
        gl.enableVertexAttribArray(shaderProgram.vertexShadeAttr);

        shaderProgram.pMatrixUniform = gl.getUniformLocation(shaderProgram, 'uPMatrix');
        shaderProgram.mvMatrixUniform = gl.getUniformLocation(shaderProgram, 'uMVMatrix');
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
            throw 'Invalid popMatrix!';
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
	var pointList = [0,0,2,0,1,2,-2,0,1,-2];
	var lineWidth = 0.25;
	var drawLength = 0;
	
    function initBuffers() {
		vertices = [];
		colors = [];
		fauxVerts = [];
		
		for (var i=0; i<pointList.length/2-1; i++) {
			dirX = pointList[i*2+2]-pointList[i*2];
			dirY = pointList[i*2+3]-pointList[i*2+1];
			mag = Math.sqrt(dirX*dirX+dirY*dirY);
			normx = dirX/mag;
			normy = dirY/mag;
			
			vertices.push(pointList[i*2]-normy*lineWidth, pointList[i*2+1]+normx*lineWidth,
			pointList[i*2]-normy*lineWidth, pointList[i*2+1]+normx*lineWidth,
			pointList[i*2]+normy*lineWidth, pointList[i*2+1]-normx*lineWidth,
			pointList[i*2+2]-normy*lineWidth+normx*lineWidth, pointList[i*2+3]+normx*lineWidth+normy*lineWidth,
			pointList[i*2+2]+normy*lineWidth+normx*lineWidth, pointList[i*2+3]-normx*lineWidth+normy*lineWidth,
			pointList[i*2+2]+normy*lineWidth+normx*lineWidth, pointList[i*2+3]-normx*lineWidth+normy*lineWidth);
			
			fauxVerts.push(0,0,0,
			mag,0,lineWidth,
			mag,0,-lineWidth,
			mag,mag+lineWidth,lineWidth,
			mag,mag+lineWidth,-lineWidth,
			0,0,0);
		}
		
        squareVertexPositionBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, squareVertexPositionBuffer);       
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertices), gl.STATIC_DRAW);
        drawLength = pointList.length*3-6;
	

        squareVertexColorBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, squareVertexColorBuffer);
        
        for (var i=0; i < 6*pointList.length/2; i++) {
            colors = colors.concat([0.5, 0.5, 1.0, 1.0]);
        }
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(colors), gl.STATIC_DRAW);
        
		shadingCoords = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, shadingCoords);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(fauxVerts), gl.STATIC_DRAW);
    }


    function drawScene() {
        gl.viewport(0, 0, gl.viewportWidth, gl.viewportHeight);
        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

        mat4.perspective(45, gl.viewportWidth / gl.viewportHeight, 0.1, 100.0, pMatrix);

        mat4.identity(mvMatrix);
        mat4.translate(mvMatrix, [0.0, 0.0, -7.0]);

        mvPushMatrix();
        //mat4.rotate(mvMatrix, degToRad(0), [1, 0, 0]);

        gl.bindBuffer(gl.ARRAY_BUFFER, squareVertexPositionBuffer);
        gl.vertexAttribPointer(shaderProgram.vertexPositionAttribute, 2, gl.FLOAT, false, 0, 0);

        gl.bindBuffer(gl.ARRAY_BUFFER, squareVertexColorBuffer);
        gl.vertexAttribPointer(shaderProgram.vertexColorAttribute, 3, gl.FLOAT, false, 0, 0);
		
		gl.bindBuffer(gl.ARRAY_BUFFER, shadingCoords);
        gl.vertexAttribPointer(shaderProgram.vertexShadeAttr, 3, gl.FLOAT, false, 0, 0);

        setMatrixUniforms();
        gl.drawArrays(gl.TRIANGLE_STRIP, 0, drawLength);

    }


    var lastTime = 0;

    function animate() {
        var timeNow = new Date().getTime();
        if (lastTime != 0) {
            var elapsed = timeNow - lastTime;

            //rTri += (90 * elapsed) / 1000.0;
            //rSquare += (75 * elapsed) / 1000.0;
        }
        lastTime = timeNow;
    }


    function tick() {
        requestAnimFrame(tick);
        drawScene();
        animate();
    }


    function webGLStart() {
        var canvas = document.getElementById('curvedLineCanvas');
        initGL(canvas);
        initShaders()
        initBuffers();

        gl.clearColor(0.0, 0.0, 0.0, 1.0);
        gl.enable(gl.DEPTH_TEST);

        tick();
    }
var enableGL = true;
</script>

</head>
<body>";
include ("navBody.php");
echo " <div class='content'><canvas id='curvedLineCanvas'  style='border: none; position:absolute; top:30;' width='500' height='500'></canvas>
	<div style='position:absolute; top:30; left:625; width:40%'>
	WebGL allows you to draw lines using the GL.LINES or GL.LINE_STRIP command but there are no formatting options for giving 
	these lines a set weight or thickness.  There are a few options that I have come across to give a thicker line.  The first was 
	to use GL.TRIANGLE_STRIP where two triangles are created to make each line segment.  The line thickness is controlled by 
	offsetting the vertices in the direction normal to the line on each side.	This is a fairly simple solution to implement 
	but will not provide a consistent line thickness when the lines makes a turn because the normal direction will change.  You 
	can elimate this by averaging the normals of each of the line segments but this gives a very angular sharp corner at each 
	vertex.
	<p>I wanted to use lines to draw rivers so I needed something that would provide a more rounded and natural (in my mind) looking
	vertex at each line segment.  To do this, I created two triangles for each line segment with the width controlled by normal 
	offsets as done in the original method.  However, rather than having a square end for each segment, I want a rounded end 
	that will smoothly cover any gap where there is an angle change.  To create the rounded end, I pass a few extra parameters 
	into the fragment shader.  Each vertex gets the following items to allow calculation of the rounded end - total length of the 
	line segment (we already have this from calculation of the segment normal), a parameter for the total length drawn along the 
	segment, and the segment width.  If the distance along the segment is less than the segment length the 
	segment will be shaded as a normal rectangular segment.  However, once the total length drawn is more than the segment length 
	but less than the segment length + the segment widht, a circular end section will be drawn.  This ensures that regardless of 
	the direction of the next segment, the circular area will cover any gaps and blend seamlessly with it.
	</div>
</div>
</body>

</html>";
?>
