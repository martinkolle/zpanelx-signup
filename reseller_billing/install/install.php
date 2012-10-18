<!-- <?php

/**
 * @package zpanelx
 * @subpackage modules->reseller_billing->install
 * @author Martin Kollerup
 * @copyright martinkole
 * @link http://www.kmweb.dk/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */
?> -->

<!DOCTYPE html>
<html>
<head>
<title>Reseller Billing Installation</title>
  <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <style type="text/css">
	  body{background-color: #0C1021;
-webkit-transition: background-color 0.5s ease-out;
-moz-transition: background-color 0.5s ease-out;
-ms-transition: background-color 0.5s ease-out;
-o-transition: background-color 0.5s ease-out;
transition: background-color 0.5s ease-out;
font: 13px/1.5 'Lucida Grande','Lucida Sans Unicode',Arial,sans-serif;}
	  body:before {content: "";position: fixed;top: -10px;left: -10px;width: 110%;height: 10px;-webkit-box-shadow: 0px 0px 10px rgba(0,0,0,.8);-moz-box-shadow: 0px 0px 10px rgba(0,0,0,.8);-ms-box-shadow: 0px 0px 10px rgba(0,0,0,.8);-o-box-shadow: 0px 0px 10px rgba(0,0,0,.8);box-shadow: 0px 0px 10px rgba(0,0,0,.8);z-index: 100;}
	  /* green */
	  #start-install{text-align: center;margin-top:20px; margin: 0 auto;width:400px}
	  .green {
	  	color: #e8f0de;
	  	border: solid 1px #538312;
	  	background: #64991e;
	  	background: -webkit-gradient(linear, left top, left bottom, from(#7db72f), to(#4e7d0e));
	  	background: -moz-linear-gradient(top,  #7db72f,  #4e7d0e);
	  	filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#7db72f', endColorstr='#4e7d0e');
	  }
	  .green:hover {
	  	background: #538018;
	  	background: -webkit-gradient(linear, left top, left bottom, from(#6b9d28), to(#436b0c));
	  	background: -moz-linear-gradient(top,  #6b9d28,  #436b0c);
	  	filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#6b9d28', endColorstr='#436b0c');
	  }
	  .green:active {
	  	color: #a9c08c;
	  	background: -webkit-gradient(linear, left top, left bottom, from(#4e7d0e), to(#7db72f));
	  	background: -moz-linear-gradient(top,  #4e7d0e,  #7db72f);
	  	filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#4e7d0e', endColorstr='#7db72f');
	  }

	  .button {
	  	display: inline-block;
	  	zoom: 1; /* zoom and *display = ie7 hack for display:inline-block */
	  	*display: inline;
	  	vertical-align: baseline;
	  	margin: 0 2px;
	  	outline: none;
	  	cursor: pointer;
	  	text-align: center;
	  	text-decoration: none;
	  	font: 14px/100% Arial, Helvetica, sans-serif;
	  	padding: .5em 2em .55em;
	  	text-shadow: 0 1px 1px rgba(0,0,0,.3);
	  	-webkit-border-radius: .5em; 
	  	-moz-border-radius: .5em;
	  	border-radius: .5em;
	  	-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.2);
	  	-moz-box-shadow: 0 1px 2px rgba(0,0,0,.2);
	  	box-shadow: 0 1px 2px rgba(0,0,0,.2);
	  }
	  .button:hover {
	  	text-decoration: none;
	  }
	  .button:active {
	  	position: relative;
	  	top: 1px;
	  }
	  #center{width:960px;}

	  #log{
		  color: rgba(0, 136, 255, .9);
		  white-space: pre;
		  margin: 0;
		  position: relative;
		  z-index: 1;
		  width:95%;
		  height:250px;
		  margin:25px;
		  font: 16px/20px Consolas,"Andale Mono WT","Andale Mono","Lucida Console","Lucida Sans Typewriter","DejaVu Sans Mono","Bitstream Vera Sans Mono","Liberation Mono","Nimbus Mono L",Monaco,"Courier New",Courier,monospace;
	  }
	  #log .line{color:#61CE3C;}
  </style>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

  <script>
jQuery.fn.center = function () {
	this.css("position","absolute");
	this.css("top", Math.max(0, (($(window).height() - this.outerHeight()) / 2) + 
												$(window).scrollTop()) + "px");
	this.css("left", Math.max(0, (($(window).width() - this.outerWidth()) / 2) + 
												$(window).scrollLeft()) + "px");
	return this;
}

  $(document).ready(function(){ 

	$('#log-all-button').click(function() { $('#slickbox').show('slide'); return false; });
	$('#log-all-button').button({label: 'View full log', icons: {primary: 'ui-icon-custom', secondary: null}});
	$('#start-install').button({label: 'Start install', icons: {primary: 'ui-icon-custom', secondary: null}}).center();
	$("#progressbar").progressbar({ disabled: true });

  	//attach a jQuery live event to the button
  	$('#start-install').live('click', function(){
	  	$('#log').append('>> <span class="line">Installation started</span><br />');
	  	$("#start-install").hide("slide", { direction: "down" }, 300);
	  	$('#log').append('>> <span class="line">Finding current version<br />');
	  	$.getJSON('function.php?function=findVersion', function(data) {
  			//alert(data); //uncomment this for debug
  			//alert (data.success+" "+data.log+" "+data.error); //further debug
			if(data.version && data.version_new){
				$('#log').append("Database version: "+data.version+"<br />");
				$('#log').append("File version: "+data.version_new+"<br />");
			} else {
		  		$('#log').append("ERROR: Could not determine version");
	  	  	}
	  	  	$( "#progressbar" ).progressbar( "option", "value", 50 );
	  	  	$('#log').append('>> <span class="line">Starting database insert</span><br />').delay(1);
	  	});
  		$.getJSON('function.php?function=installFtp&from=&to=', function(data) {	
  			//alert(data); //uncomment this for debug
  			//alert (data.success+" "+data.log+" "+data.error); //further debug
  			if(data.status == "1"){
	  			$('#log').append("Database inserted or updated<br />");
	  			$('#log').append("Log have been saved to"+window.location.pathname+"/"+data.log);
	  			$('#log-all').append('>> <span class="line">Sql queries<br />');
	  			$('#log-all').append(data.log);
  			} else{
	  			$('#log').append("ERROR: Update or insert to database returned false<br />");
	  			$('#log').append("You can find the MYSQL dump in the modules folder/install<br />");
  			}
			$( "#progressbar" ).progressbar( "option", "value", 100 );
  		});
  	});
  });
</script>
</head>
<body>

<div id="progressbar"></div>
<div id="center"><div class="button green" id="start-install"></div></div>
<div id="log"></div>
<div id="error"></div>
<div id="log-all-button"></div>
<div id="log-all" style="display:none"></div>

</body>
</html>