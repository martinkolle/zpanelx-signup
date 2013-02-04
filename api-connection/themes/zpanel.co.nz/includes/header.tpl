<?php
$crawltsite=1;
require_once("/var/zpanel/hostdata/zadmin/public_html/zpanel_co_nz/crawler/crawltrack.php");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{title}} - {{company}}</title>
<meta name="description" content="New Zealand's Only Official ZPanelCP Support Site - ZPanel is a free and complete web hosting control panel for Microsoft® Windows™ and POSIX (Linux, UNIX and MacOSX) based servers." />
<meta name="keywords" content="{{title}} - {{company}}, zpanel, web hosting panel, open-source hosting panel, ZPanel Xystems, Open Source Control Panel, Open Source Hosting, Open Source Web Hosting, Open-Source Control Panel, Open-Source Hosting, Open-Source Web Hosting, Cheap Web Hosting, New Zealand, Hawkes Bay, Napier, New Zealand Web Hosting, ZPanel, Hosting, Systems, Xystems, PS2Guy Productions, " />
<meta http-equiv="cache-control" content="cache" />
<meta http-equiv="Content-Language" content="en" />
<meta http-equiv="Copyright" content="Nigel Caldwell, 2013" />
<link rel='index' title='ZPanel Xystems' href='https://billing.zpanel.co.nz/' />
<meta name="robots" content="index" />
<meta name="robots" content="all/follow"  />
<meta content="general" name="rating" />
<meta content="7days" name="revisit" />
<link rel="shortcut icon" type="image/x-icon" href="themes/{{theme}}/images/favicon.ico" />
<link href="themes/{{theme}}/css/reset.css" rel="stylesheet" type="text/css" media="all">
<link href="themes/{{theme}}/css/layout.css" rel="stylesheet" type="text/css" media="all">
<link href="themes/{{theme}}/css/style.css" rel="stylesheet" type="text/css" media="all">
<link href="themes/{{theme}}/css/billing.css" rel="stylesheet" type="text/css" media="all">
<script type="text/javascript" src="themes/{{theme}}/js/maxheight.js"></script>
<script type="text/javascript" src="themes/{{theme}}/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="themes/{{theme}}/js/cufon-yui.js"></script>
<script type="text/javascript" src="themes/{{theme}}/js/cufon-replace.js"></script>
<script type="text/javascript" src="themes/{{theme}}/js/Myriad_Pro_300.font.js"></script>
<script type="text/javascript" src="themes/{{theme}}/js/Myriad_Pro_400.font.js"></script>
<script type="text/javascript" src="themes/{{theme}}/js/jquery.faded.js"></script>
<script type="text/javascript" src="themes/{{theme}}/js/jquery.jqtransform.js"></script>
<script type="text/javascript" src="themes/{{theme}}/js/script.js"></script>
<!--[if lt IE 7]>
<script type="text/javascript" src="http://info.template-help.com/files/ie6_warning/ie6_script_other.js"></script>
<![endif]-->
<!--[if lt IE 9]>
<script type="text/javascript" src="themes/{{theme}}/js/html5.js"></script>
<![endif]-->
<script language="javascript" type="text/javascript">
   var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-36174427-1']);
  _gaq.push(['_setDomainName', 'zpanel.co.nz']);
  _gaq.push(['_setAllowLinker', true]);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
		  $(document).ready(function(){
				$('#login-trigger').click(function(){
					$(this).next('#login-content').slideToggle();
					$(this).toggleClass('active');					
					
					if ($(this).hasClass('active')) $(this).find('span').html('&#x25B2;')
						else $(this).find('span').html('&#x25BC;')
					})
		  });

function clearText(field)
{
    if (field.defaultValue == field.value) field.value = '';
    else if (field.value == '') field.value = field.defaultValue;
}
</script>	
	{{head}}
</head>
