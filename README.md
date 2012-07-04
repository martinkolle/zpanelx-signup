Zpanelx Auto Signup
=============================

Please follow the guide lines @ http://forums.zpanelcp.com/showthread.php?7687-TESTING-Auto-signup-%28Zpanelx%29&p=61194#post61194

Samples for Payment_method
================
Remove pre when using it in the code
<pre>
<form name="form" method="post" action="{{action}}">
<input name="charset" value="utf-8" type="hidden">
<input name="cmd" value="_xclick" type="hidden">
<input name="upload" value="1" type="hidden">

<input name="first_name" value="{{user_firstname}}" type="hidden">
<input name="invoice" value="{{invoice}}" type="hidden">
<input name="email" value="{{email}}" type="hidden">
<input name="return" value="{{return_url}}" type="hidden">
<input name="business" value="{{business}}" type="hidden">
<input name="item_name" value="{{item_name}}" type="hidden">
<input name="quantity" value="1" type="hidden">
<input name="country" value="{{country}}" type="hidden">
<input name="amount" value="{{amount}}" type="hidden">
<input name="currency_code" value="{{cs}}" type="hidden">
<input name="image_url" value="{{logo}}" type="hidden">
<input type="hidden" name="notify_url" value="{{notify_url}}" />
<input value="Forsæt til betaling »" class="defbtn" type="submit">
</form>
</pre>