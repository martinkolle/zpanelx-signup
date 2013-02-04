	<form method="POST" action="{{action}}">	
		<div id="main-inner">
			<div id="left">
				<div id="box">
					<h2>Your information</h2>
					<div id="login">
						<h3>Login </h3>
						<input type="text" name="username" value="{{username}}" placeholder="Username"><br />
						<input type="email" name="email" value="{{email}}" placeholder="Email">
					</div>
					<div id="information">
						<h3>Customer</h3>
						<input type="text" name="fullname" value="{{fullname}}" placeholder="Full Name">
						<textarea name="address" cols="25" placeholder="Address">{{address}}</textarea>
						<input type="number" name="postcode" size="20" value="{{postcode}}" placeholder="Postcode">
						<input type="tel" name="telephone" value="{{telephone}}" placeholder="Telephone">
					</div>
				</div>
			</div>

			<div id="right" class="pay">
				<div id="box" style="padding: 5px;">
					<h2>Payment period</h2>
						<input type="hidden" name="packagename" size="20" disabled="true" value="{{packagename}}">
						<input type="hidden" name="packageid" size="1" value="{{pid}}">
						{{payoptions}}
				</div>
			</div>
			<div id="right" class="trans">
				<div id="box" style="padding: 5px;">
					<h2>Transfer website</h2>
						<input type="text" name="website" value="{{transfer_website}}" placeholder="www.yourdomain.com"/>
						<br />
						<input type="checkbox" name="website_help" value="dns">I want help to DNS transfer <br />
						<input type="checkbox" name="website_help" value="no">I don't have a domain.
				</div>
			</div>		

			<div id="right" class="recapctha">
				<div id="box" style="padding: 5px;">
				    <h2>Captcha Challenge</h2>
			     {{recaptcha}}				    
                   
                    </div>
			</div>

		</div>
		<div id="bottom">
			<input type="submit" value="Select Payment Option >>" name="submit" />
		</div>
	</form>
