ZpanalX - Auto Billing Module

Version 10.0.5
================================================================
Edit config.php

 Read the comments in config.php for help.
 
================================================================
Global Place Holders ( Can be used in all .tpl files )

{{theme}} - Folder name of theme
{{company}} - Business Name or Website Name
{{panel_url}} - Your Zpanel URL
{{webmail_url}} - Your Webmail URL

================================================================
index.tpl Place Holders ( these are all set in your Zpanel->Reseller->Package Manager)

{{packagename}}
{{packageid}}
{{price}}
{{space}} - Disk space quota ( returns value + MB or GB (example 1GB) )
{{bandwidth}} - Bandwidth quota ( returns value + MB or GB (example 1GB) )
{{mailboxes}}
{{mysql}}
{{ftp}}
{{domains}}
{{subdomains}}
{{parkeddomains}}
{{forwarders}}
{{distlists}}

================================================================

** I have created a freehosting.php for people that would like to offer free hosting. 
If you do not want to offer free hosting delete this file as it is possible for someone 
to get an invoice number and post it to freehosting.php and they will have an active account.

To add the payment option go to the zpanel => billing module => Payment Options and add

Name :	Free ( this can be whatever you would like it to be )

Data :	<form name="form" method="post" action="freehosting.php">
	<input name="invoice" value="{{invoice}}" type="hidden">
	<input name="first_name" value="{{user_firstname}}" type="hidden">
	</form>
