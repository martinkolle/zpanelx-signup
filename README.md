Zpanelx auto signup
==============

This is a auto signup module for Zpanel x which supports paypal integration as payment.

For users
==============
To install this module, please downlaod the files and upload it to your server. 
Go to: yourdomain.com/adm/install.php and follow the guides.


How to test
==============
Please make a test account to paypal aka. sandbox. 
In this test account make a business account and a buyer account. 

Please upload the files to you server, and install the module aka. yourdomain.com/adm/install.php. (For security reason please delete this file when install is finish.)

Go to yourdomain.com/adm and add prices for the packages you have made in zpanelx.
When you have added prices, please go to Add payments method: I have made this example for paypal: (NB: sandbox testing!!):
<pre>
<form name="form1" method="post" action="https://www.sandbox.paypal.com/cgi-bin/webscr">
<input name="last_name" value="kwldqm" type="hidden">
<input name="invoice" value="$invid" type="hidden">
<input name="first_name" value="kol" type="hidden">
<input name="charset" value="utf-8" type="hidden">

<input name="email" value="kolle@kolle.dk" type="hidden">
<input name="return" value="http://kmweb.dk/betaling-godkendt" type="hidden">
<input name="business" value="martin_1339196217_biz@gmail.com" type="hidden">
<input name="item_name" value="Webhosting" type="hidden">
<input name="quantity" value="1" type="hidden">
<input name="country" value="DK" type="hidden">
<input name="cmd" value="_xclick" type="hidden">
<input name="upload" value="1" type="hidden">
<input name="amount" value="$itmvalue" type="hidden">
<input name="currency_code" value="$cs" type="hidden">
<input name="image_url" value="http://billing.kmweb.dk/logo.png" type="hidden">
 <input type="hidden" name="notify_url" value="http://billing.kmweb.dk/ipn.php" />
<input value="Forsæt til betaling »" class="defbtn" type="submit">
</form>
</pre>
License
==============
THIS MODULE IS RELEASED UNDER GNU/GPL V.3 