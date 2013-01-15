	<form method="POST" action="{{action}}">	
		<div id="main-inner">
			<div id="left">
				<div id="box">
					<h2>Your information</h2>
					<div id="login">
						<h3>Login </h3>
						<input type="text" name="username" value="{{username}}"><br />
						<input type="text" name="email" value="{{email}}">
					</div>
					<div id="information">
						<h3>Customer</h3>
						<input type="text" name="fullname" value="{{fullname}}">
						<textarea name="address" cols="25">{{address}}</textarea>
						<input type="text" name="postcode" size="20" value="{{postcode}}">
						<input type="text" name="telephone" value="{{telephone}}">
					</div>
				</div>
			</div>

			<div id="right" class="pay">
				<div id="box">
					<h2>Payment period</h2>
						<input type="hidden" name="packagename" size="20" disabled="true" value="{{packagename}}">
						<input type="hidden" name="packageid" size="1" value="{{pid}}">
						{{payoptions}}
				</div>
			</div>
			<div id="right" class="trans">
				<div id="box">
					<h2>Transfer website</h2>
						<input type="text" name="website" value="{{transfer_website}}" />
						<br />
						<input type="checkbox" name="website_help" value="dns">I want help to DNS transfer <br />
						<input type="checkbox" name="website_help" value="no">I don't have a domain.
				</div>
			</div>		

			<div id="right" class="recapctha">
				<div id="box" style="padding: 5px;">
				    <h2>Captcha Challenge</h2>
<script type="text/javascript">
 var RecaptchaOptions = {
    theme : 'white'
 };
 </script>				    
                    <script type="text/javascript"
                       src="http://www.google.com/recaptcha/api/challenge?k={{captcha_pub_key}}">
                    </script>
                    <noscript>
                       <iframe src="http://www.google.com/recaptcha/api/noscript?k={{captcha_pub_key}}"
                           height="300" width="500" frameborder="0"></iframe><br>
                       <textarea name="recaptcha_challenge_field" rows="3" cols="40">
                       </textarea>
                       <input type="hidden" name="recaptcha_response_field"
                           value="manual_challenge">
                    </noscript>
				</div>
			</div>

		</div>
		<div id="bottom">
			<input type="submit" value="Select Payment Option >>" name="submit" />
		</div>
	</form>
