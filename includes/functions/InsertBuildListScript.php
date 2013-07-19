<?php

function InsertBuildListScript($CallProgram)
{
	global $_Lang;

	$BuildListScript = "<script type=\"text/javascript\">\n";
	$BuildListScript .= "<!--\n";
	$BuildListScript .= "function t() {\n";
	$BuildListScript .= "	v = new Date();\n";
	$BuildListScript .= "	var blc = document.getElementById('blc');\n";
	$BuildListScript .= "	var del_link = document.getElementById('dlink');\n";
	$BuildListScript .= "	var timeout = 1;\n";
	$BuildListScript .= "	n = new Date();\n";
	$BuildListScript .= "	ss = pp;\n";
	$BuildListScript .= "	aa = Math.round( (n.getTime() - v.getTime() ) / 1000. );\n";
	$BuildListScript .= "	s = ss - aa;\n";
	$BuildListScript .= "	m = 0;\n";
	$BuildListScript .= "	h = 0;\n\n";
	$BuildListScript .= "	if((ss + 3) < aa){\n";
	$BuildListScript .= "		blc.innerHTML = \"". $_Lang['completed'] ."\";\n";
	$BuildListScript .= "		del_link.innerHTML = \"<a href=". $CallProgram .".php?planet=\" + pl + \">". $_Lang['continue'] ."</a>\";\n";
	$BuildListScript .= "		if((ss + 6) >= aa){\n";
	$BuildListScript .= "			window.setTimeout('document.location.href=\"". $CallProgram .".php?planet=' + pl + '\";', 1000);\n";
	$BuildListScript .= "		}\n";
	$BuildListScript .= "	} else {\n";
	$BuildListScript .= "		if(s < 0){\n";
	$BuildListScript .= "		 blc.innerHTML = \"". $_Lang['completed'] ."\";\n";
	$BuildListScript .= "		 del_link.innerHTML = \"<a href=". $CallProgram .".php?planet=\" + pl + \">". $_Lang['continue'] ."</a>\";\n";
	$BuildListScript .= "		 window.setTimeout('document.location.href=\"". $CallProgram .".php?planet=' + pl + '\";', 1000);\n";
	$BuildListScript .= "		} else {\n";
	$BuildListScript .= "			if(s > 59){\n";
	$BuildListScript .= "				m = Math.floor( s / 60);\n";
	$BuildListScript .= "				s = s - m * 60;\n";
	$BuildListScript .= "			}\n";
	$BuildListScript .= "			if( m > 59){\n";
	$BuildListScript .= "				h = Math.floor( m / 60);\n";
	$BuildListScript .= "				m = m - h * 60;\n";
	$BuildListScript .= "			}\n";
	$BuildListScript .= "			if(s < 10){\n";
	$BuildListScript .= "				s = \"0\" + s;\n";
	$BuildListScript .= "			}\n";
	$BuildListScript .= "			if(m < 10){\n";
	$BuildListScript .= "				m = \"0\" + m;\n";
	$BuildListScript .= "			}\n";
	$BuildListScript .= "			blc.innerHTML = h + \":\" + m + \":\" + s;\n";
	$BuildListScript .= "		}\n";
	$BuildListScript .= "		pp = pp - 1;\n";
	$BuildListScript .= "		window.setTimeout(\"t();\", 999);\n";
	$BuildListScript .= "	}\n";
	$BuildListScript .= "}\n";
	$BuildListScript .= "//-->\n";
	$BuildListScript .= "</script>\n";

	return $BuildListScript;
}

?>