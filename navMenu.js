var killTarget;
function openMenu(event) {
	//alert('open');
	if (!event) {
        event = window.event;
     }
	event.stopPropagation();
	var trg = this.getElementsByTagName('ul');
	trg[0].style.display = 'block';
	document.onclick = killMenu;
	killTarget = trg[0];
	
	}
	
function killMenu() {
	//alert('wtf');
	if (killTarget)	killTarget.style.display = 'none';
	}
	
window.onload = function() {	
	var trg = document.getElementById('topList');
	//alert(trg.id + ', ' + trg.getElementsByTagName('li').length);
	for (var i=0; i<document.getElementById('topList').getElementsByTagName('li').length; i++) {
		if (trg.getElementsByTagName('li')[i].parentNode.id == 'topList') {
			trg.getElementsByTagName('li')[i].addEventListener('click',openMenu,true);
			}
		}
	if (enableGL) webGLStart();
	}