<?php


echo "<html><head>
<title>Space Rectangles with webGL</title>
<link rel='stylesheet' type='text/css' href='navMenu.css'>
<script src='navMenu.js'></script>
<script type='text/javascript' src='spaceMatrix.js'></script>
<script type='text/javascript' src='webgl-utils.js'></script>
<script src='laser.js'></script>

<script id='laser-fs' type='x-shader/x-fragment'>
    precision mediump float;
	
    varying vec4 vColor;

    void main(void) {
        gl_FragColor = vColor;
    }
</script>

<script id='laser-vs' type='x-shader/x-vertex'>
    attribute vec3 aVertexPosition;

    uniform mat4 uMVMatrix;
    uniform mat4 uPMatrix;
	
	uniform float uTE;

    varying vec4 vColor;

    void main(void) {
        gl_Position = uPMatrix * uMVMatrix * vec4(aVertexPosition, 1.0);
        vColor = vec4(0.0, 1.0, 0.0, uTE);
    }
</script>

<script id='color-fs' type='x-shader/x-fragment'>
    precision mediump float;
	
    varying vec4 vColor;

    void main(void) {
        gl_FragColor = vColor;
    }
</script>

<script id='color-vs' type='x-shader/x-vertex'>
    attribute vec3 aVertexPosition;
    attribute vec4 aVertexColor;

    uniform mat4 uMVMatrix;
    uniform mat4 uPMatrix;
	
	uniform float uTE;

    varying vec4 vColor;

    void main(void) {
        gl_Position = uPMatrix * uMVMatrix * vec4(aVertexPosition, 1.0);
        vColor = aVertexColor;
    }
</script>

<script id='shader-fs' type='x-shader/x-fragment'>
    precision mediump float;

    varying vec2 vTextureCoord;
    varying vec3 vLightWeighting;

    uniform sampler2D uSampler;

    void main(void) {
        vec4 textureColor = texture2D(uSampler, vec2(vTextureCoord.s, vTextureCoord.t));
        gl_FragColor = vec4(textureColor.rgb * vLightWeighting, textureColor.a);
    }
</script>

<script id='shader-vs' type='x-shader/x-vertex'>
    attribute vec3 aVertexPosition;
    attribute vec3 aVertexNormal;
    attribute vec2 aTextureCoord;

    uniform mat4 uMVMatrix;
    uniform mat4 uPMatrix;
    uniform mat3 uNMatrix;

    uniform vec3 uAmbientColor;

    uniform vec3 uLightingDirection;
    uniform vec3 uDirectionalColor;

    varying vec2 vTextureCoord;
    varying vec3 vLightWeighting;

    void main(void) {
        gl_Position = uPMatrix * uMVMatrix * vec4(aVertexPosition, 1.0);
        vTextureCoord = aTextureCoord;

		vec3 transformedNormal = uNMatrix * aVertexNormal;
		float directionalLightWeighting = max(dot(transformedNormal, uLightingDirection), 0.0);
		vLightWeighting = uAmbientColor + uDirectionalColor * directionalLightWeighting;
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
	
    var textureShader;
	var colorShader;
	var laserShader;	
	
	function initColShaders() {
		var fragmentShader = getShader(gl, 'color-fs');
        var vertexShader = getShader(gl, 'color-vs');

        colorShader = gl.createProgram();
        gl.attachShader(colorShader, vertexShader);
        gl.attachShader(colorShader, fragmentShader);
        gl.linkProgram(colorShader);

        if (!gl.getProgramParameter(colorShader, gl.LINK_STATUS)) {
            alert('Could not initialise shaders');
        }

        gl.useProgram(colorShader);

        colorShader.vertexPositionAttribute = gl.getAttribLocation(colorShader, 'aVertexPosition');
        gl.enableVertexAttribArray(colorShader.vertexPositionAttribute);

        colorShader.vertexColorAttribute = gl.getAttribLocation(colorShader, 'aVertexColor');
        gl.enableVertexAttribArray(colorShader.vertexColorAttribute);

        colorShader.pMatrixUniform = gl.getUniformLocation(colorShader, 'uPMatrix');
        colorShader.mvMatrixUniform = gl.getUniformLocation(colorShader, 'uMVMatrix');
		
		colorShader.tElapsedU = gl.getUniformLocation(colorShader, 'uTE');
		}

    function initShaders() {
		
        var fragmentShader = getShader(gl, 'shader-fs');
        var vertexShader = getShader(gl, 'shader-vs');

        textureShader = gl.createProgram();
        gl.attachShader(textureShader, vertexShader);
        gl.attachShader(textureShader, fragmentShader);
        gl.linkProgram(textureShader);

        if (!gl.getProgramParameter(textureShader, gl.LINK_STATUS)) {
            alert('Could not initialise shaders');
        }
		
        gl.useProgram(textureShader);
        textureShader.vertexPositionAttribute = gl.getAttribLocation(textureShader, 'aVertexPosition');
        gl.enableVertexAttribArray(textureShader.vertexPositionAttribute);
		
        textureShader.textureCoordAttribute = gl.getAttribLocation(textureShader, 'aTextureCoord');
        gl.enableVertexAttribArray(textureShader.textureCoordAttribute);

        textureShader.vertexNormalAttribute = gl.getAttribLocation(textureShader, 'aVertexNormal');
        gl.enableVertexAttribArray(textureShader.vertexNormalAttribute);
		
        textureShader.pMatrixUniform = gl.getUniformLocation(textureShader, 'uPMatrix');
        textureShader.mvMatrixUniform = gl.getUniformLocation(textureShader, 'uMVMatrix');
        textureShader.nMatrixUniform = gl.getUniformLocation(textureShader, 'uNMatrix');
        textureShader.samplerUniform = gl.getUniformLocation(textureShader, 'uSampler');
        
        textureShader.ambientColorUniform = gl.getUniformLocation(textureShader, 'uAmbientColor');
        textureShader.lightingDirectionUniform = gl.getUniformLocation(textureShader, 'uLightingDirection');
        textureShader.directionalColorUniform = gl.getUniformLocation(textureShader, 'uDirectionalColor');		
    }

	function initLasers() {
		var fragmentShader = getShader(gl, 'laser-fs');
        var vertexShader = getShader(gl, 'laser-vs');

        laserShader = gl.createProgram();
        gl.attachShader(laserShader, vertexShader);
        gl.attachShader(laserShader, fragmentShader);
        gl.linkProgram(laserShader);

        if (!gl.getProgramParameter(laserShader, gl.LINK_STATUS)) {
            alert('Could not initialise shaders');
        }

        gl.useProgram(laserShader);
        laserShader.vertexPositionAttribute = gl.getAttribLocation(laserShader, 'aVertexPosition');
        gl.enableVertexAttribArray(laserShader.vertexPositionAttribute);

        laserShader.pMatrixUniform = gl.getUniformLocation(laserShader, 'uPMatrix');
        laserShader.mvMatrixUniform = gl.getUniformLocation(laserShader, 'uMVMatrix');
		
		laserShader.tElapsedU = gl.getUniformLocation(laserShader, 'uTE');
		}

    function handleLoadedTexture(texture) {
        gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
        gl.bindTexture(gl.TEXTURE_2D, texture);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, texture.image);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR_MIPMAP_NEAREST);
        gl.generateMipmap(gl.TEXTURE_2D);

        gl.bindTexture(gl.TEXTURE_2D, null);
    }


    var moonTexture;

    function initTexture() {
        moonTexture = gl.createTexture();
        moonTexture.image = new Image();
        moonTexture.image.onload = function () {
            handleLoadedTexture(moonTexture)
        }

        moonTexture.image.src = 'moon.gif';
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

    function setMatrixUniforms(selProgram) {
        gl.uniformMatrix4fv(selProgram.pMatrixUniform, false, pMatrix);
        gl.uniformMatrix4fv(selProgram.mvMatrixUniform, false, mvMatrix);

        var normalMatrix = mat3.create();
        mat4.toInverseMat3(mvMatrix, normalMatrix);
        mat3.transpose(normalMatrix);
        gl.uniformMatrix3fv(selProgram.nMatrixUniform, false, normalMatrix);	
    }


    function degToRad(degrees) {
        return degrees * Math.PI / 180;
    }


    var mouseDown = false;
    var lastMouseX = null;
    var lastMouseY = null;

    var moonRotationMatrix = mat4.create();
    mat4.identity(moonRotationMatrix);

    function handleMouseDown(event) {
        mouseDown = true;
        lastMouseX = event.clientX;
        lastMouseY = event.clientY;
    }


    function handleMouseUp(event) {
        mouseDown = false;
    }


    function handleMouseMove(event) {
        if (!mouseDown) {
            return;
        }
        var newX = event.clientX;
        var newY = event.clientY;

        var deltaX = newX - lastMouseX
        var newRotationMatrix = mat4.create();
        mat4.identity(newRotationMatrix);
        mat4.rotate(newRotationMatrix, degToRad(deltaX / 10), [0, 1, 0]);

        var deltaY = newY - lastMouseY;
        mat4.rotate(newRotationMatrix, degToRad(deltaY / 10), [1, 0, 0]);

        mat4.multiply(newRotationMatrix, moonRotationMatrix, moonRotationMatrix);

        lastMouseX = newX
        lastMouseY = newY;
    }

    var moonVertexPositionBuffer;
    var moonVertexNormalBuffer;
    var moonVertexTextureCoordBuffer;
    var moonVertexIndexBuffer;
	var laserLn;
	var laserCol;
	
	var axis_pt = [];
	var axis_col =[];
	
	var satellitePos;
	var satelliteCol;
	
	var elapsed = 0.0;

    function initBuffers() {
		axis_pt[0] = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, axis_pt[0]);
		gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([1.0, 0.0, 0.0, -1.0, 0.0, 0.0]), gl.STATIC_DRAW);
		
		axis_col[0] = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, axis_col[0]);
		gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([1.0, 0.0, 0.0, 1.0, 1.0, 0.0, 0.0, 1.0]), gl.STATIC_DRAW);	
		
		axis_pt[1] = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, axis_pt[1]);
		gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([0.0, 1.0, 0.0, 0.0, -1.0, 0.0]), gl.STATIC_DRAW);
		
		axis_col[1] = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, axis_col[1]);
		gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([0.0, 1.0, 0.0, 1.0, 0.0, 1.0, 0.0, 1.0]), gl.STATIC_DRAW);	
		
		axis_pt[2] = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, axis_pt[2]);
		gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([0.0, 0.0, 1.0, 0.0, 0.0, -1.0]), gl.STATIC_DRAW);
		
		axis_col[2] = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, axis_col[2]);
		gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([0.0, 0.0, 1.0, 1.0, 0.0, 0.0, 1.0, 1.0]), gl.STATIC_DRAW);	
	
	
        var latitudeBands = 30;
        var longitudeBands = 30;
        var radius = 0.5;

        var vertexPositionData = [];
        var normalData = [];
        var textureCoordData = [];
        for (var latNumber=0; latNumber <= latitudeBands; latNumber++) {
            var theta = latNumber * Math.PI / latitudeBands;
            var sinTheta = Math.sin(theta);
            var cosTheta = Math.cos(theta);

            for (var longNumber=0; longNumber <= longitudeBands; longNumber++) {
                var phi = longNumber * 2 * Math.PI / longitudeBands;
                var sinPhi = Math.sin(phi);
                var cosPhi = Math.cos(phi);

                var x = cosPhi * sinTheta;
                var y = cosTheta;
                var z = sinPhi * sinTheta;
                var u = 1 - (longNumber / longitudeBands);
                var v = 1 - (latNumber / latitudeBands);

                normalData.push(x);
                normalData.push(y);
                normalData.push(z);
                textureCoordData.push(u);
                textureCoordData.push(v);
                vertexPositionData.push(radius * x);
                vertexPositionData.push(radius * y);
                vertexPositionData.push(radius * z);
            }
        }

        var indexData = [];
        for (var latNumber=0; latNumber < latitudeBands; latNumber++) {
            for (var longNumber=0; longNumber < longitudeBands; longNumber++) {
                var first = (latNumber * (longitudeBands + 1)) + longNumber;
                var second = first + longitudeBands + 1;
                indexData.push(first);
                indexData.push(second);
                indexData.push(first + 1);

                indexData.push(second);
                indexData.push(second + 1);
                indexData.push(first + 1);
            }
        }

        moonVertexNormalBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, moonVertexNormalBuffer);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(normalData), gl.STATIC_DRAW);
        moonVertexNormalBuffer.itemSize = 3;
        moonVertexNormalBuffer.numItems = normalData.length / 3;

        moonVertexTextureCoordBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, moonVertexTextureCoordBuffer);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(textureCoordData), gl.STATIC_DRAW);
        moonVertexTextureCoordBuffer.itemSize = 2;
        moonVertexTextureCoordBuffer.numItems = textureCoordData.length / 2;

        moonVertexPositionBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, moonVertexPositionBuffer);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertexPositionData), gl.STATIC_DRAW);
        moonVertexPositionBuffer.itemSize = 3;
        moonVertexPositionBuffer.numItems = vertexPositionData.length / 3;

        moonVertexIndexBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, moonVertexIndexBuffer);
        gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, new Uint16Array(indexData), gl.STATIC_DRAW);
        moonVertexIndexBuffer.itemSize = 1;
        moonVertexIndexBuffer.numItems = indexData.length;
		
		satellitePos = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, satellitePos);
		gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(
						[-0.2, 0.1, 0.1, 
						-0.2, -0.1, 0.1,
						0.2, 0.1, 0.1, 
						0.2, -0.1, 0.1
						]), gl.STATIC_DRAW);
		
		satelliteCol = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, satelliteCol);
		gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([1.0, 0.0, 0.0, 1.0, 
			1.0, 0.0, 0.0, 1.0,
			1.0, 0.0, 0.0, 1.0,
			1.0, 0.0, 0.0, 1.0]), gl.STATIC_DRAW);	
		}
	
	function degToRad(degrees) {
        return degrees * Math.PI / 180;
		}

    function drawScene() {
        gl.viewport(0, 0, gl.viewportWidth, gl.viewportHeight);
        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

        mat4.perspective(45, gl.viewportWidth / gl.viewportHeight, 0.1, 100.0, pMatrix);
		mat4.identity(mvMatrix);
		
		gl.disable(gl.BLEND);
		gl.useProgram(textureShader);

        //if (lighting) {
            gl.uniform3f(textureShader.ambientColorUniform, 0.0, 0.5, 0.5);

            var lightingDirection = [-1.0, -1.0, -1.0];
            var adjustedLD = vec3.create();
            vec3.normalize(lightingDirection, adjustedLD);
            vec3.scale(adjustedLD, -1);
            gl.uniform3fv(textureShader.lightingDirectionUniform, adjustedLD);

            gl.uniform3f(textureShader.directionalColorUniform,1.0,0.0,0.0);
		//	}       

        mat4.translate(mvMatrix, [0, 0, -25]);	
		mat4.rotate(mvMatrix, degToRad(yRot*2), [0, 1, 0]);
		//gl.useProgram(textureShader);

		mvPushMatrix();
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, moonTexture);
        gl.uniform1i(textureShader.samplerUniform, 0);

        gl.bindBuffer(gl.ARRAY_BUFFER, moonVertexPositionBuffer);
        gl.vertexAttribPointer(textureShader.vertexPositionAttribute, moonVertexPositionBuffer.itemSize, gl.FLOAT, false, 0, 0);

        gl.bindBuffer(gl.ARRAY_BUFFER, moonVertexTextureCoordBuffer);
        gl.vertexAttribPointer(textureShader.textureCoordAttribute, moonVertexTextureCoordBuffer.itemSize, gl.FLOAT, false, 0, 0);

        gl.bindBuffer(gl.ARRAY_BUFFER, moonVertexNormalBuffer);
        gl.vertexAttribPointer(textureShader.vertexNormalAttribute, moonVertexNormalBuffer.itemSize, gl.FLOAT, false, 0, 0);

        gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, moonVertexIndexBuffer);
        setMatrixUniforms(textureShader);
        gl.drawElements(gl.TRIANGLES, moonVertexIndexBuffer.numItems, gl.UNSIGNED_SHORT, 0);
		mvPopMatrix();
		
		gl.useProgram(colorShader);
		
		for (var ax=0; ax<3; ax++) {
			gl.bindBuffer(gl.ARRAY_BUFFER, axis_pt[ax]);
			gl.vertexAttribPointer(colorShader.vertexPositionAttribute, 3, gl.FLOAT, false, 0, 0);

			gl.bindBuffer(gl.ARRAY_BUFFER, axis_col[ax]);
			gl.vertexAttribPointer(colorShader.vertexColorAttribute, 4, gl.FLOAT, false, 0, 0);
			setMatrixUniforms(colorShader);
			gl.drawArrays(gl.LINES, 0, 2);
			}
		
		for (var i=0; i<objectList.length; i++) {
			mvPushMatrix();
			mat4.translate(mvMatrix, [objectList[i][0], objectList[i][1], objectList[i][2]]);
			mat4.rotate(mvMatrix, -Math.acos(objectList[i][9]), vec3.cross([objectList[i][9], objectList[i][10], objectList[i][11]],[1, 0, 0]));
			
			gl.bindBuffer(gl.ARRAY_BUFFER, satellitePos);
			gl.vertexAttribPointer(colorShader.vertexPositionAttribute, 3, gl.FLOAT, false, 0, 0);

			gl.bindBuffer(gl.ARRAY_BUFFER, satelliteCol);
			gl.vertexAttribPointer(colorShader.vertexColorAttribute, 4, gl.FLOAT, false, 0, 0);
			setMatrixUniforms(colorShader);
			gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
			
			for (var ax=0; ax<3; ax++) {
				gl.bindBuffer(gl.ARRAY_BUFFER, axis_pt[ax]);
				gl.vertexAttribPointer(colorShader.vertexPositionAttribute, 3, gl.FLOAT, false, 0, 0);

				gl.bindBuffer(gl.ARRAY_BUFFER, axis_col[ax]);
				gl.vertexAttribPointer(colorShader.vertexColorAttribute, 4, gl.FLOAT, false, 0, 0);
				setMatrixUniforms(colorShader);
				//gl.drawArrays(gl.LINES, 0, 2);
				}
			mvPopMatrix();	
			}

		gl.enable(gl.BLEND);
		gl.useProgram(laserShader);
		var kill_list = [];
		for (var i=0; i <laserList.length; i++) {
			if (laserList[i].dur*1000 + laserList[i].start >= lastTime) {
				laserUniforms(laserList[i].dur, laserList[i].start);
				
				gl.bindBuffer(gl.ARRAY_BUFFER, laserList[i]);
				gl.vertexAttribPointer(laserShader.vertexPositionAttribute, 3, gl.FLOAT, false, 0, 0);
				
				gl.drawArrays(gl.LINES, 0, 2);
				}
			else {
				laserList.splice(i, 1);		
				kill_list.push(i);
				}
			}
		for (var i=kill_list.length; i>0; i--) {
			//laserList.splice(kill_list[i], 1);		
			}
		
		}
	
	var currentlyPressedKeys = {};
	function handleKeyDown(event) {
		currentlyPressedKeys[event.keyCode] = true;
		if (String.fromCharCode(event.keyCode) == 'F') {
			filter += 1;
			if (filter == 3) {
				filter = 0;
				}
			}
		}

	function handleKeyUp(event) {
		currentlyPressedKeys[event.keyCode] = false;
		}
	
	var yRotVel;
	function handleKeys() {
		if (currentlyPressedKeys[37] || currentlyPressedKeys[65]) {
			// Left cursor key or A
			yRotVel = 0.01;
		} else if (currentlyPressedKeys[39] || currentlyPressedKeys[68]) {
			// Right cursor key or D
			yRotVel = -0.01;
		} else {
			yRotVel = 0;
		}
	}
	
	/*
	0, 1, 2 = centerpoint of object
	3, 4, 5 = centerpoint of target
	6 = shot duration
	7 = shot frequency
	8 = last update
	9, 10, 11 = velocity vector (current facing)
	12, 13, 14, 15 = wX, wY, wZ, ticks (wX max)
	*/
	objectList = [];
	function initObjects() {
		var newObj = [-3, -3, 0, 
					0.0, 0.0, 0.0, 
					3.0, 3.0, 0, 
					1.0, 0.0, 0.0, 
					0, 0, 0, 0, 0];
		newObj.rot = [0, 0, 0];
		objectList.push(newObj);
		var newObj = [3, 3, 0, 
					0.0, 0.0, 0.0, 
					3.0, 3.0, 0,
					1.0, 0.0, 0.0,
					0, 0, 0, 0, 0];
		newObj.rot = [0, 0, 0];
		objectList.push(newObj);
		
		for (var i=1; i<10; i++) {
			var newObj = [i, i, i,
			0.0, 0.0, 0.0,
			3.0, 3.0, 0,
			1.0, 0.0, 0.0, 
			0, 0, 0, 0, 0];
			objectList.push(newObj);
			}
		}
	
	var startTime = new Date().getTime();
	var lastTime = 0;
	var yRot = 0;
	
	function animate() {
		var timeNow = new Date().getTime();
		if (lastTime != 0) {
		elapsed = timeNow - lastTime;

		yRot += (yRotVel*elapsed);
		}
		lastTime = timeNow;
	}
	
	function findTargets(i) {
		//for (var i=0; i<objectList.length; i++) {
			var aTarg = [(objectList[i][3] - objectList[i][0]), (objectList[i][4] - objectList[i][1]), (objectList[i][5] - objectList[i][2])];
			var tMag = Math.pow(aTarg[0]*aTarg[0] + aTarg[1]*aTarg[1] + aTarg[2]*aTarg[2],0.5);
			aTarg[0] /= tMag;
			aTarg[1] /= tMag;
			aTarg[2] /= tMag;
			
			angle = Math.acos(objectList[i][9]*aTarg[0] + objectList[i][10]*aTarg[1] + objectList[i][11]*aTarg[2]);
			dRot = angle;
			objectList[i][12] = (aTarg[0] - objectList[i][9])/dRot;
			objectList[i][13] = (aTarg[1] - objectList[i][10])/dRot;
			objectList[i][14] = (aTarg[2] - objectList[i][11])/dRot;
			objectList[i][15] = new Date().getTime() + dRot*1000;
		//	}
		}
	
	function moveObjects() {
		for (var i=0; i<objectList.length; i++) {
			dt = elapsed/1000;
			if (objectList[i][15]>lastTime) {
				var aTarg = [objectList[i][9]+objectList[i][12]*dt, objectList[i][10]+objectList[i][13]*dt, objectList[i][11]+objectList[i][14]*dt];
				var mag = Math.pow(aTarg[0]*aTarg[0] + aTarg[1]*aTarg[1] + aTarg[2]*aTarg[2],0.5);
				objectList[i][9] = aTarg[0]/mag;
				objectList[i][10] = aTarg[1]/mag;
				objectList[i][11] = aTarg[2]/mag;
				}
			else findTargets(i);
		
			objectList[i][0] += objectList[i][9]*dt;
			objectList[i][1] += objectList[i][10]*dt;
			objectList[i][2] += objectList[i][11]*dt;			
			}
		}
	
	function shootTargets() {
		for (var i=0; i<objectList.length; i++) {
			if (lastTime - objectList[i][8] >=  objectList[i][7]*1000) {
				if (vec3.dotAng([objectList[i][9], objectList[i][10], objectList[i][11]], [objectList[i][3]-objectList[i][0], objectList[i][4]-objectList[i][1], objectList[i][5]-objectList[i][2]]) > 0.97) {
					var laserItem = gl.createBuffer();
					gl.bindBuffer(gl.ARRAY_BUFFER, laserItem);
					gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([objectList[i][0], objectList[i][1], objectList[i][2], objectList[i][3], objectList[i][4], objectList[i][5]]), gl.STATIC_DRAW);
					laserItem.dur = objectList[i][6];
					//laserItem.dur = 0.5;
					laserItem.start = lastTime;
					laserList.push(laserItem);
					objectList[i][8] = lastTime;
					}
				
				}
			}
		}
	
	function tick() {
        requestAnimFrame(tick);
		handleKeys();
		animate();
		
		moveObjects();
		shootTargets();
        drawScene();
		}


	var laserList = [];
											
	
    function webGLStart() {
		var canvas = document.getElementById('spaceCanvas');
		canvas.width = 500;
		canvas.height = 500;
        initGL(canvas);
        initShaders();
		initLasers();
		initColShaders();
        initBuffers();
        initTexture();
		initObjects();
		//findTargets();

        gl.clearColor(0.0, 0.0, 0.0, 1.0);
        gl.enable(gl.DEPTH_TEST);
		gl.blendFunc(gl.SRC_ALPHA, gl.ONE);

        document.onkeydown = handleKeyDown;
		document.onkeyup = handleKeyUp;

        tick();
		}

	enableGL = true;
</script></head>
<body>";
include ('navBody.php');
echo "<div class='content'>
    <canvas id='spaceCanvas' style='position:absolute; top:30'></canvas>
    <br/>
	<div style='position:absolute; top:30; left:725;'>
	This demo shows some rectangles flying around a planet.  They even have lasers that they shoot at the planet!  
	Not quite the death star but still fun!
	
	<p>Navagate with the following keys:
	A/D: rotate around Y axis<br>
	
	</div>
</div>
</body>

</html>";


?>