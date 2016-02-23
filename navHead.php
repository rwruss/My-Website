<?php

echo "
<html>
<meta http-equiv='content-type' content='text/html; charset=ISO-8859-1'>
<head>
<style>

body {
	height:100%;
	width:99%}
.blockImg {
	position:relative;
	float:left;
	padding:1%;
}
.blockContent{
	position:relative;
	float:left;
	padding:1%;
	width:50%;
	}
.content {
	-webkit-transform: translate3d(0,0,0);
}
.contentDiv {
	font:arial;
	font-size:14;
	left:10%;
	margin-left: -30%;
	width:80%;
	zIndex:2;
}
.imgPrev {
	width:250;
	height:250;
	cursor:pointer;
}
.navBar {
	position:fixed;
	background:green;
	color:white;
	width:100%
	top:0;
	zIndex = 100;
}
#baseNav {
	margin-top:0px;
	zIndex = 100;
}
#baseNav ul
{
	list-style:none;
	position:relative;
	float:left;
	margin:0;
	padding:0

}
#baseNav ul a {
	display:block;
	color:#333;
	text-decoration:none;
	font-weight:700;
	font-size:12px;
	line-height:32px;
	padding:0 15px;
	font-family:'HelveticaNeue','Helvetica Neue',Helvetica,Arial,sans-serif
}
#baseNav ul li {
	display:block;
	position:relative;
	float:left;
	margin:0;
	padding:0;
	padding:0px 5px
}

#baseNav ul li.current-menu-item {
	background:#fff000
}
#baseNav ul li:hover {
	background:#A0A0A0;
	}
#baseNav ul ul {
	display:none;
	position:absolute;
	top:100%;
	left:0;
	background:#fff000;
	padding:0
}
#baseNav ul ul li {
	float:none;
	color:red;
}
#baseNav ul ul a {
	line-height:120%;
	padding:5px 0px
}
#baseNav ul ul ul {
	top:0;
	left:100%
	background:black;
}
#baseNav ul li:hover > ul
{
	display:block
}


</style>
<script type='text/javascript'>
//alert('It is working');

function findPos(obj) {
		var curleft = curtop = 0;
		if (obj.offsetParent) {
			do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
			} while (obj = obj.offsetParent);
			return [curleft,curtop];
			}
		}

function openNav(menu_id) {
	params = 'val1='+menu_id;
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open('POST', './navMenu.php', true);

	parent = document.getElementById('menu_'+menu_id);
	parentLoc = findPos(parent);
	target = document.getElementById('subMenu');

	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlhttp.setRequestHeader('Content-length', params.length);
	xmlhttp.setRequestHeader('Connection', 'close');

	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			target.innerHTML =  xmlhttp.response;
			target.style.left = parentLoc[0];
			target.style.padding = 5;
			pointMenu(target)
			}
		}

	xmlhttp.send(params);
	}

function pointMenu(target) {
	for (var i=0; i<target.children.length; i++) {
		id = target.children[i].id;
		target.children[i].onclick = movePage;
	}
}

function movePage() {
	window.location = this.id+'.php';
	}

function loadPage(trg) {
	window.location = trg;
}

function killMenu() {
	trg = document.getElementById('subMenu');
	trg.style.padding = 0;
	trg.innerHTML = '';
	}
function init() {
	document.getElementById('index').onclick = movePage;
}
window.addEventListener('load', init, false);

</script>
";

?>
