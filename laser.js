
		
 function laserUniforms(time, start) {
        gl.uniformMatrix4fv(laserShader.pMatrixUniform, false, pMatrix);
        gl.uniformMatrix4fv(laserShader.mvMatrixUniform, false, mvMatrix);

		gl.uniform1f(laserShader.tElapsedU, (1000*time + start - lastTime)/(1000*time));
		//gl.uniform1f(laserShader.tElapsedU, (1.0-(time - lastTime)/time));
		}

