<?php

/**
    * Generate the token the payment should be specified with.
    * There is fallback to mt:rand() if openssl not is supported
    * @credit http://www.php.net/manual/en/function.openssl-random-pseudo-bytes.php#96812
    * @return token
*/

function generateToken($length = 24) {
        if(function_exists('openssl_random_pseudo_bytes')) {
            $password = base64_encode(openssl_random_pseudo_bytes($length, $strong));
            if($strong == TRUE)
                return substr($password, 0, $length); //base64 is about 33% longer, so we need to truncate the result
        }
        //fall back to mt_rand
        $characters = '0123456789';
        $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz/+'; 
        $charactersLength = strlen($characters)-1;
        $password = '';

        //select some random characters
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[mt_rand(0, $charactersLength)];
        }        
        
        return $password;
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $error = null;

    $firstname      = $_POST['firstname'];
    $lastname       = $_POST['lastname'];
    $email          = $_POST['email'];
    $telephone      = $_POST['telephone'];
    $postnumber     = $_POST['postnumber'];
    $city           = $_POST['city'];
    $adress         = $_POST['adress'];
    $host_type      = $_POST['host_type'];
    $host_period    = $_POST['host_period'];
    $info           = $_POST['info'];

    //we hate empty fields
    if (empty($firstname)){
        $error .= "Please enter a Name";
    }
    if (empty($lastname)){
        $error .= "Please enter a Last name";
    }    
    if (empty($email)){
        $error .= "Please enter a Email";
    }    
    if (empty($telephone)){
        $error .= "Please enter Telephone number";
    }    
    if (empty($postnumber)){
        $error .= "Please enter postnumber";
    }    
    if (empty($city)){
        $error .= "Please enter a city";
    }    
    if (empty($adress)){
        $error .= "Please enter your adress";
    }
    if (empty($host_type)){
        $error .= "Please enter your host type";
    }
    if (empty($host_period)){
        $error .= "Please enter your host period";
    }

if ($error) {

    echo $error;
    return false;
}
else{
  try
  {
    //your user information for the database
    $user = "billing2";
    $pass = "my4epamuz";
 
    $db = new PDO("mysql:host=kmweb.dk;dbname=zadmin_billing", $user, $pass);
 
    //make pdo request
    $stmt = $db->prepare("INSERT INTO user (firstname, lastname, email, telephone, postnumber, city, adress, host_type, host_period, info, date, price, token) value (:firstname, :lastname, :email, :telephone, :postnumber, :city, :adress, :host_type, :host_period, :info, :daten, :price, :token)");
 
    $firstname      = "";
    $lastname       = "";
    $email          = "";
    $telephone      = "";
    $postnumber     = "";
    $city           = "";
    $adress         = "";
    $host_type      = "";
    $host_period    = "";
    $info           = "";
    $payment        = "";
    $return         = "";
    $date           = "";
    $mc_gross       = "";
    $txn_id         = "";
    $price          = "";
    $token          = "";

    //set the params
    $stmt->bindParam(':firstname', $firstname);
    $stmt->bindParam(':lastname', $lastname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':telephone', $telephone);
    $stmt->bindParam(':postnumber', $postnumber);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':adress', $adress);
    $stmt->bindParam(':host_type', $host_type);
    $stmt->bindParam(':host_period', $host_period);   
    $stmt->bindParam(':info', $info);
    $stmt->bindParam(':daten', $date);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':token', $token);

$_POST['name'] = filter_var($_POST['name'], FILTER_SANITIZE_STRING);  

    //get the params
    $firstname      = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
    $lastname       = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
    $email          = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telephone      = ctype_digit($_POST['telephone']);
    $postnumber     = ctype_digit($_POST['postnumber']);
    $city           = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
    $adress         = filter_var($_POST['adress'], FILTER_SANITIZE_STRING);
    $host_type      = filter_var($_POST['host_type'], FILTER_SANITIZE_STRING);
    $host_period    = filter_var($_POST['host_period'], FILTER_SANITIZE_STRING);
    $info           = filter_var($_POST['info'], FILTER_SANITIZE_STRING);
    $payment        = null;
    $return         = null; //return from paypal. ipn.php
    $date           = date('Y-m-d H:i:s');
    $mc_gross       = null ; //used by paypal. ipn.php
    $txn_id         = null; //used by paypal. (ipn.php)
    $token          = generateToken();

 /**
   * Find the price for the webhosts (products)
   * @return price or undifined
 */
    switch ($host_type){

        case 'web9':
            switch ($host_period){

                case '3';
                    $price = '27';
                break;
                case '6';
                    $price = '54';
                break;
                case '12';
                    $price = '108';
                break;
                default:
                    $price = 'undefined';
                break;
            }
        break;

        case 'web49':
            switch ($host_period){

                case '3';
                    $price = '147';
                break;
                case '6';
                    $price = '294';
                break;
                case '12';
                    $price = '588';
                break;
                default:
                    $price = 'undefined';
                break;
            }
        break;

        default:
            $price = 'undefined';
        break;
    }
    $price = $price;
    $stmt->execute();
 
    echo "<p>".$firstname." Id: " . $db->lastInsertId() . "</p>";    

    //close pdo connection
    $db = null;
  }
    catch(PDOException $pdoex)
    {
        //catch error/message
        echo $pdoex->getMessage();
    }
}//else end
}

?>

<!DOCTYPE html>
<htmL>
<head>
<title>Billing | KMweb.dk</title>
<link rel="stylesheet" type="text/css" media="All" href="css/style.css" />
</head>
<body>

    <div id="wrap">
        <h2>Opret webhotel ved KMweb.dk</h2>
        <p id="info">
            DU SKAL BRUGE DEN SAMME EMAIL SOM DU BRUGER TIL PAYPAL - ellers vil der gå længere tid til din bestilling bliver aktiveret
        </p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <input name="firstname" type="text" />
            <input name="lastname" type="text" value="Efternavn" />
            <input name="telephone" type="text" value="Telefon" />
            <input name="email" type="text" value="email" />
            <input name="adress" type="text" value="Adresse" />
            <input name="postnumber" type="text" value="Postnummer" />
            <input name="city" type="text" value="By" />
            <select name="country" id="country"><option value="AF">Afghanistan</option><option value="AX">Aland Islands</option><option value="AL">Albania</option><option value="DZ">Algeria</option><option value="AS">American Samoa</option><option value="AD">Andorra</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AQ">Antarctica</option><option value="AG">Antigua And Barbuda</option><option value="AR">Argentina</option><option value="AM">Armenia</option><option value="AW">Aruba</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AZ">Azerbaijan</option><option value="BS">Bahamas</option><option value="BH">Bahrain</option><option value="BD">Bangladesh</option><option value="BB">Barbados</option><option value="BY">Belarus</option><option value="BE">Belgium</option><option value="BZ">Belize</option><option value="BJ">Benin</option><option value="BM">Bermuda</option><option value="BT">Bhutan</option><option value="BO">Bolivia</option><option value="BA">Bosnia And Herzegovina</option><option value="BW">Botswana</option><option value="BV">Bouvet Island</option><option value="BR">Brazil</option><option value="IO">British Indian Ocean Territory</option><option value="BN">Brunei Darussalam</option><option value="BG">Bulgaria</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="KH">Cambodia</option><option value="CM">Cameroon</option><option value="CA">Canada</option><option value="CV">Cape Verde</option><option value="KY">Cayman Islands</option><option value="CF">Central African Republic</option><option value="TD">Chad</option><option value="CL">Chile</option><option value="CN">China</option><option value="CX">Christmas Island</option><option value="CC">Cocos (Keeling) Islands</option><option value="CO">Colombia</option><option value="KM">Comoros</option><option value="CG">Congo</option><option value="CD">Congo, Democratic Republic</option><option value="CK">Cook Islands</option><option value="CR">Costa Rica</option><option value="CI">Cote D'Ivoire</option><option value="HR">Croatia</option><option value="CU">Cuba</option><option value="CY">Cyprus</option><option value="CZ">Czech Republic</option><option value="DK" selected="selected">Denmark</option><option value="DJ">Djibouti</option><option value="DM">Dominica</option><option value="DO">Dominican Republic</option><option value="EC">Ecuador</option><option value="EG">Egypt</option><option value="SV">El Salvador</option><option value="GQ">Equatorial Guinea</option><option value="ER">Eritrea</option><option value="EE">Estonia</option><option value="ET">Ethiopia</option><option value="FK">Falkland Islands (Malvinas)</option><option value="FO">Faroe Islands</option><option value="FJ">Fiji</option><option value="FI">Finland</option><option value="FR">France</option><option value="GF">French Guiana</option><option value="PF">French Polynesia</option><option value="TF">French Southern Territories</option><option value="GA">Gabon</option><option value="GM">Gambia</option><option value="GE">Georgia</option><option value="DE">Germany</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GR">Greece</option><option value="GL">Greenland</option><option value="GD">Grenada</option><option value="GP">Guadeloupe</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GG">Guernsey</option><option value="GN">Guinea</option><option value="GW">Guinea-Bissau</option><option value="GY">Guyana</option><option value="HT">Haiti</option><option value="HM">Heard Island &amp; Mcdonald Islands</option><option value="VA">Holy See (Vatican City State)</option><option value="HN">Honduras</option><option value="HK">Hong Kong</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IR">Iran, Islamic Republic Of</option><option value="IQ">Iraq</option><option value="IE">Ireland</option><option value="IM">Isle Of Man</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="JE">Jersey</option><option value="JO">Jordan</option><option value="KZ">Kazakhstan</option><option value="KE">Kenya</option><option value="KI">Kiribati</option><option value="KR">Korea</option><option value="KW">Kuwait</option><option value="KG">Kyrgyzstan</option><option value="LA">Lao People's Democratic Republic</option><option value="LV">Latvia</option><option value="LB">Lebanon</option><option value="LS">Lesotho</option><option value="LR">Liberia</option><option value="LY">Libyan Arab Jamahiriya</option><option value="LI">Liechtenstein</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MO">Macao</option><option value="MK">Macedonia</option><option value="MG">Madagascar</option><option value="MW">Malawi</option><option value="MY">Malaysia</option><option value="MV">Maldives</option><option value="ML">Mali</option><option value="MT">Malta</option><option value="MH">Marshall Islands</option><option value="MQ">Martinique</option><option value="MR">Mauritania</option><option value="MU">Mauritius</option><option value="YT">Mayotte</option><option value="MX">Mexico</option><option value="FM">Micronesia, Federated States Of</option><option value="MD">Moldova</option><option value="MC">Monaco</option><option value="MN">Mongolia</option><option value="ME">Montenegro</option><option value="MS">Montserrat</option><option value="MA">Morocco</option><option value="MZ">Mozambique</option><option value="MM">Myanmar</option><option value="NA">Namibia</option><option value="NR">Nauru</option><option value="NP">Nepal</option><option value="NL">Netherlands</option><option value="AN">Netherlands Antilles</option><option value="NC">New Caledonia</option><option value="NZ">New Zealand</option><option value="NI">Nicaragua</option><option value="NE">Niger</option><option value="NG">Nigeria</option><option value="NU">Niue</option><option value="NF">Norfolk Island</option><option value="MP">Northern Mariana Islands</option><option value="NO">Norway</option><option value="OM">Oman</option><option value="PK">Pakistan</option><option value="PW">Palau</option><option value="PS">Palestinian Territory, Occupied</option><option value="PA">Panama</option><option value="PG">Papua New Guinea</option><option value="PY">Paraguay</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PN">Pitcairn</option><option value="PL">Poland</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="QA">Qatar</option><option value="RE">Reunion</option><option value="RO">Romania</option><option value="RU">Russian Federation</option><option value="RW">Rwanda</option><option value="BL">Saint Barthelemy</option><option value="SH">Saint Helena</option><option value="KN">Saint Kitts And Nevis</option><option value="LC">Saint Lucia</option><option value="MF">Saint Martin</option><option value="PM">Saint Pierre And Miquelon</option><option value="VC">Saint Vincent And Grenadines</option><option value="WS">Samoa</option><option value="SM">San Marino</option><option value="ST">Sao Tome And Principe</option><option value="SA">Saudi Arabia</option><option value="SN">Senegal</option><option value="RS">Serbia</option><option value="SC">Seychelles</option><option value="SL">Sierra Leone</option><option value="SG">Singapore</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="SB">Solomon Islands</option><option value="SO">Somalia</option><option value="ZA">South Africa</option><option value="GS">South Georgia And Sandwich Isl.</option><option value="ES">Spain</option><option value="LK">Sri Lanka</option><option value="SD">Sudan</option><option value="SR">Suriname</option><option value="SJ">Svalbard And Jan Mayen</option><option value="SZ">Swaziland</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="SY">Syrian Arab Republic</option><option value="TW">Taiwan</option><option value="TJ">Tajikistan</option><option value="TZ">Tanzania</option><option value="TH">Thailand</option><option value="TL">Timor-Leste</option><option value="TG">Togo</option><option value="TK">Tokelau</option><option value="TO">Tonga</option><option value="TT">Trinidad And Tobago</option><option value="TN">Tunisia</option><option value="TR">Turkey</option><option value="TM">Turkmenistan</option><option value="TC">Turks And Caicos Islands</option><option value="TV">Tuvalu</option><option value="UG">Uganda</option><option value="UA">Ukraine</option><option value="AE">United Arab Emirates</option><option value="GB">United Kingdom</option><option value="US">United States</option><option value="UM">United States Outlying Islands</option><option value="UY">Uruguay</option><option value="UZ">Uzbekistan</option><option value="VU">Vanuatu</option><option value="VE">Venezuela</option><option value="VN">Viet Nam</option><option value="VG">Virgin Islands, British</option><option value="VI">Virgin Islands, U.S.</option><option value="WF">Wallis And Futuna</option><option value="EH">Western Sahara</option><option value="YE">Yemen</option><option value="ZM">Zambia</option><option value="ZW">Zimbabwe</option></select>
            <textarea name="info"></textarea>
<br />
            <input type="radio" name="host_type" value="web9" /> Web 9,-<br />
            <input type="radio" name="host_type" value="web49" /> Web 49,- <br />

<h2>Antal måneder </h2>
            <input type="radio" name="host_period" value="3" /> 3 måneder<br />
            <input type="radio" name="host_period" value="6" /> 6 måneder <br />
            <input type="radio" name="host_period" value="12" /> 12 måneder<br />


            <input type="submit" value="Submit og gå videre til betaling" /> 
        </form>
    </div>

</body>
</html>
