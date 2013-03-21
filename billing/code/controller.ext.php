<?php

/**
 * @package zpanelx
 * @subpackage modules->billing
 * @author Martin Kollerup
 * @copyright martinkole
 * @link http://www.kmweb.dk/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */

class module_controller {

    static $delete;
    static $noId;
    static $noDelete;
    static $resetform;
    static $deletedPaymentMethod;
    static $deletedPaymentMethodFail;
    static $editPaymentGood;
    static $editPaymentBad;
    static $editPaymentNoId;
    static $editedPackage;
    static $SavedPackage;
    static $editPackage;
    static $editPackageNeedid;
    static $editInvoiceGood;
    static $editInvoiceBad;
    static $editInvoiceNoId;
    static $dbInstall;
    static $username;
    static $allreadyexists; //maybe remove?!
    static $updatedSettings;
    static $editedEmail;
    static $editEmail;

    /**
    * Yeah, "must have" functions for the module.
    */
    static function getModuleName(){
    	return ui_module::GetModuleName();
    }

    static function getModuleDesc(){
    	return ui_language::translate(ui_module::GetModuleDescription());
    }

	static function getModuleIcon() {
		global $controller;
		$module_icon = "modules/" . $controller->GetControllerRequest('URL', 'module') . "/assets/icon.png";
        return $module_icon;
    }
    static function getModuleDir(){
        global $controller;
        $name = $controller->GetControllerRequest('URL', 'module');
        return "/modules/".$name;
    }

    /**
    * LET ME SAY... DIFFERENT VIEWS IN MODULES! BUUUUH JAH!!
    */
    function getView(){
        global $controller;
        $url = $controller->GetAllControllerRequests('URL');
        $url = (array_key_exists('view', $url)) ? $url['view'] : 'invoice';
        return  $url;
    }
    function getViewInvoice(){
        $url = self::getView();
        return ($url == 'invoice') ? true : false;
    }
    function getViewPayment(){
        $url = self::getView();
        return ($url == 'payment_method') ? true : false;
    }
    function getViewPackage(){
        $url = self::getView();
        return ($url == 'package') ? true : false;
    }
    function getViewSetting(){
        $url = self::getView();
        return ($url == 'setting') ? true : false;
    }
    function getViewEmail(){
        $url = self::getView();
        return ($url == 'email') ? true : false;
    }

/******
* PAYMENT
******/

    static function getPayments(){
        global $zdbh;
        $sql = "SELECT * FROM x_rb_payment";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($row = $sql->fetch()) {
                $active = ($row["pm_active"] == "1") ? "selected" : "";
                $disable = ($row["pm_active"] == "0") ? "selected" : "";
                array_push($res, array(
                    'pm_id' => $row['pm_id'],
                    'name' => $row['pm_name'],
                    'data' => $row['pm_data'],
                    'active' => $row['pm_active'],
                    'select' =>'<option value="1" '.$active.'>Activate</option><option value="0" '.$disable.'>Disable</option>'
                ));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function doCreatePayment() {
        global $controller;

        $formvars = $controller->GetAllControllerRequests('FORM');
        if (self::ExecuteCreatePayment($formvars['newName'], $formvars['newData'], $formvars['newActive'])) {
            return true;
        } else {
            return false;
        }
    }

    static function doEditPayment() {
        global $controller;
        $url = $controller->GetAllControllerRequests('URL');

        $id     = (isset($url['id'])) ? $url['id'] : null;
        $name   = (isset($url['name'])) ? $url['name'] : null;
        $data   = (isset($url['data'])) ? $url['data'] : null;
        $active = (isset($url['active'])) ? $url['active'] : null;

        if ((!empty($id)) && (!empty($data))) {
            if (self::ExecuteEditPayment($id, $name, $data, $active)){
                self::$editPaymentGood = true;
                return true;
            }
            else{
                self::$editPaymentBad = true;
                return false;
            }
        }
        else{
            self::$editPaymentNoId = true;
            return false;
        }
    }

   static function doDeletePayment() {
        global $controller;
        $id = $controller->GetAllControllerRequests('URL');

        if (isset($id['deleteId'])) {
            if (self::ExecuteDeletePayment($id['deleteId'])){
                self::$deletedPaymentMethod = true;
                return true;
            }
            else{
                self::$deletedPaymentMethodFail = true;
                return false;
            }
        }
        else{
            self::$noId = true;
            return false;
        }
    }

    static function ExecuteCreatePayment($name, $data, $active){
        global $zdbh;
        // Check for errors before we continue...
        if (fs_director::CheckForEmptyValue($name, $data, $active)) {
            return false;
        }
        $sql = $zdbh->prepare("INSERT INTO x_rb_payment (pm_name,pm_data,pm_active) VALUES(:name, :data, :active)");
        $query = array(':name'=>$name, ':data'=>$data,':active'=>$active);
        
        $sql->execute($query);
        return true;
    }

    static function ExecuteEditPayment($id, $name, $data, $active){
        global $zdbh;
        // Check for errors before we continue...
        if (fs_director::CheckForEmptyValue($id, $name, $data, $active)) {
            return false;
        }
        $sql = $zdbh->prepare("UPDATE x_rb_payment SET pm_name = :name, pm_data = :data, pm_active = :active WHERE pm_id = :id");
        $query = array(':id'=>$id, ':name'=>$name, ':data'=>$data,':active'=>$active);
        
        $sql->execute($query);
        return true;
    }
    static function ExecuteDeletePayment($id){
        global $zdbh;
        // Check for errors before we continue...
        if (fs_director::CheckForEmptyValue($id)) {
            return false;
        }
        $sql = $zdbh->prepare("DELETE FROM x_rb_payment WHERE pm_id = ?");        
        $sql->execute(array($id));
        return true;
    }

    /**
    * Get the form values on post
    */
    static function getFormName() {
        global $controller;
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (isset($formvars['newName']) && fs_director::CheckForEmptyValue(self::$resetform)) {
            return $formvars['newName'];
        }
        return;
    }
    static function getFormData() {
        global $controller;
        $formvars = $controller->GetAllControllerRequests('FORM');
        if (isset($formvars['newData']) && fs_director::CheckForEmptyValue(self::$resetform)) {
            return $formvars['newData'];
        }
        return;
    }

/*****
* START INVOICES
*****/

    function getInvoices() {
        global $zdbh;
        $sql = "SELECT * FROM x_rb_invoice WHERE inv_user IS NOT NULL";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($row = $sql->fetch()) {
                $pending = ($row["inv_status"] == "2") ? "selected" : "";
                $disable = ($row["inv_status"] == "0") ? "selected" : "";
                $accepted = ($row["inv_status"] == "1") ? "selected" : "";

                array_push($res, array(
                    'id' => $row['inv_id'],
                    'user_id' => $row['inv_user'],
                    'amount' => $row['inv_amount'],
                    'type' => $row['inv_type'],
                    'date' => $row['inv_date'],
                    'payment' => $row['inv_payment'],
                    'payment_id' => $row['inv_payment_id'],                    
                    'desc' => $row['inv_desc'],
                    'status' => $row['inv_status'],
                    'token' => $row['inv_token'],
                    'select' =>'<option value="1" '.$accepted.'>Accepted</option><option value="2" '.$pending.'>Pending</option><option value="0" '.$disable.'>Disabled</option>'
                ));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function doDeleteInvoice() {
        global $controller;
        $id = $controller->GetAllControllerRequests('URL');

        if (isset($id['deleteId'])) {
            if (self::ExecuteDeleteInvoice($id['deleteId'])){
                self::$delete = true;
                return true;
            }
            else{
                self::$noDelete = true;
                return false;
            }
        }
        else{
            self::$noId = true;
            return false;
        }
    }

    static function ExecuteDeleteInvoice($id) {
        global $zdbh;
        $sql = "DELETE FROM x_rb_invoice WHERE inv_id = ?";
        $sql = $zdbh->prepare($sql);
        $sql->execute(array($id));
        return true;
    }

    static function doEditInvoice() {
        global $controller;
        $url = $controller->GetAllControllerRequests('URL');

        $id             = (isset($url['id'])) ? $url['id'] : null;
        $user           = (isset($url['user'])) ? $url['user'] : null;
        $amount         = (isset($url['amount'])) ? $url['amount'] : null;
        $type           = (isset($url['type'])) ? $url['type'] : null;
        $date           = (isset($url['date'])) ? $url['date'] : null;
        $payment        = (isset($url['payment'])) ? $url['payment'] : null;
        $payment_id     = (isset($url['payment_id'])) ? $url['payment_id'] : null;
        $desc           = (isset($url['desc'])) ? $url['desc'] : null;
        $status         = (isset($url['status'])) ? $url['status'] : null;
        $token          = (isset($url['token'])) ? $url['token'] : null;

        if ((!empty($id)) && (!empty($user))) {
            if (self::ExecuteEditInvoice($id, $user, $amount, $type, $date, $payment, $payment_id, $desc, $status, $token)){
                self::$editInvoiceGood = true;
                return true;
            }
            else{
                self::$editInvoiceBad = true;
                return false;
            }
        }
        else{
            self::$editInvoiceNoId = true;
            return false;
        }
    }

    static function ExecuteEditInvoice($id, $user, $amount, $type, $date, $payment, $payment_id, $desc, $status, $token){
        global $zdbh;

        /*if (fs_director::CheckForEmptyValue($id, $amount, $token)) {
          return false;
        } */
        $sql = $zdbh->prepare("UPDATE x_rb_invoice SET inv_user = :inv_user, inv_amount = :amount, inv_type = :type, inv_date = :date, inv_payment = :payment, inv_payment_id = :payment_id, inv_desc = :desc, inv_status = :status, inv_token = :token  WHERE inv_id = :id LIMIT 1");
        
        $query = array(':id'=>$id, ':inv_user'=>$user, ':amount'=>$amount, ':type'=>urldecode($type), ':date'=>$date, ':payment'=>urldecode($payment), ':payment_id'=>$payment_id, ':desc'=>urldecode($desc), ':status'=>$status, ':token'=>$token);
        
        $sql->execute($query);
        return true;
    }

/******
* PACKAGES
******/
    function getPackages(){
        global $zdbh;
        $sql = "SELECT * FROM x_packages 
				LEFT JOIN x_rb_price ON x_packages.pk_id_pk = x_rb_price.pk_id
				LEFT JOIN x_quotas ON x_packages.pk_id_pk = x_quotas.qt_package_fk
				WHERE x_packages.pk_deleted_ts IS NULL";
				
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($row = $sql->fetch()) {
                array_push($res, array(
                    'id' => $row['pk_id_pk'],
                    'name' => $row['pk_name_vc'],
                    'reseller' => $row['pk_reseller_fk'],
                    'hosting' => $row['pkp_hosting'],
                    'domain' => $row['pkp_domain'],
                    'qdomain' => $row['qt_domains_in'],
                    'qsubdomain' => $row['qt_subdomains_in'],
                    'qparkdomain' => $row['qt_parkeddomains_in'],
                    'qmailboxes' => $row['qt_mailboxes_in'],
                    'qforwarders' => $row['qt_fowarders_in'],
                    'qdistlist' => $row['qt_distlists_in'],
                    'qftp' => $row['qt_ftpaccounts_in'],
                    'qmysql' => $row['qt_mysql_in'],
                    'qspace' => $row['qt_diskspace_bi'],
                    'qband' => $row['qt_bandwidth_bi']
                ));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function doSavePackage() {
        global $controller;
        global $zdbh;

        $url = $controller->GetAllControllerRequests('URL');

        $id         = (isset($url['id'])) ? $url['id'] : null;
        $name       = (isset($url['name'])) ? urldecode($url['name']) : null;
        $reseller   = (isset($url['reseller'])) ? $url['reseller'] : null;

        $domain     = (isset($url['domain'])) ? $url['domain'] : null;
        $hosting    = (isset($url['hosting'])) ? $url['hosting'] : null;

        $stmt = $zdbh->prepare('SELECT pkp_id FROM x_rb_price WHERE pk_id=?');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ((!empty($id)) && (!empty($name))) {

            if(!$row)
            {
                if (self::ExecuteSavePackage($id, $name, $domain, $hosting)){
                    self::$SavedPackage = true;
                    return true;
                }else{
                    self::$editPackage = true;
                    return false;
                }
            }
            else{
                if (self::ExecuteEditPackage($id, $name, $domain, $hosting)){
                    self::$editedPackage = true;
                    return true;
                }else{
                    self::$editPackage = true;
                    return false;
                }
            }

        }else{
            self::$editPackageNeedid = true;
            return false;
        }
    }
    static function ExecuteEditPackage($id, $name, $domain, $hosting){
        global $zdbh;
        // Check for errors before we continue...
        if (fs_director::CheckForEmptyValue($id, $name)) {
            return false;
        }
        $sql = $zdbh->prepare("UPDATE x_packages SET pk_name_vc = :name WHERE pk_id_pk = :id LIMIT 1");
        $query = array(':id'=>$id, ':name'=>$name);
        $sql->execute($query);

        $stmt = $zdbh->prepare("UPDATE x_rb_price SET 
            pkp_domain = :domain, 
            pkp_hosting = :hosting 
            WHERE pk_id = :id");
        $query2 = array(':id'=>$id, ':domain'=>$domain, ':hosting'=>$hosting);
        $stmt->execute($query2);

        return true;
    }

    static function ExecuteSavePackage($id, $name, $domain, $hosting){
        global $zdbh;
        // Check for errors before we continue...
        if (fs_director::CheckForEmptyValue($id)) {
            return false;
        }

        $stmt = $zdbh->prepare("
            INSERT INTO x_rb_price(
                pkp_domain, 
                pkp_hosting,
                pk_id) 
            VALUES (
                :domain,
                :hosting,
                :pk_id
            )");

        $query = array(':pk_id'=>$id, ':domain'=>$domain, ':hosting'=>$hosting);
        $stmt->execute($query);

        return true;
    }

/******
* SETTINGS VIEW
******/

    static function getConfigs() {
        global $zdbh;
        $currentuser = ctrl_users::GetUserDetail();
        $sql = "SELECT * FROM x_rb_settings ORDER BY title";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($rowsettings = $sql->fetch()) {
                $fieldhtml = ctrl_options::OutputSettingTextArea(str_replace(".", "_", $rowsettings['name']), $rowsettings['value']);
                
                array_push($res, array('cleanname' => ui_language::translate($rowsettings['name']),
                    'name' => $rowsettings['title'],
                    'description' => ui_language::translate($rowsettings['desc']),
                    'value' => $rowsettings['value'],
                    'fieldhtml' => $fieldhtml));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function doUpdateConfigs() {
        global $zdbh;
        global $controller;
        $sql = "SELECT * FROM x_rb_settings";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $sql->execute();
            while ($row = $sql->fetch()) {
                if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', str_replace(".", "_", $row['name'])))) {
                    $updatesql = $zdbh->prepare("UPDATE x_rb_settings SET value = :value WHERE name = '" . $row['name'] . "'");
                    $updatesql->bindParam(':value', $controller->GetControllerRequest('FORM', str_replace(".", "_", $row['name'])));
                    $updatesql->execute();
                }
            }
        }
        self::$updatedSettings = true;
    }


/******
* EMAIL VIEW
******/

static function getEmail() {
        global $zdbh;
        $currentuser = ctrl_users::GetUserDetail();
        $sql = "SELECT * FROM x_rb_mail ORDER BY name";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($row = $sql->fetch()) { 
            $name       = ctrl_options::OutputSettingTextField("name", $row['name']);
            $message    = ctrl_options::OutputSettingTextArea("message", $row['message']);
            $subject    = ctrl_options::OutputSettingTextField("subject", $row['subject']);

                array_push($res, array('id' => $row['id'],
                    'name' => $name,
                    'subject' => $subject,
                    'message' => $message,
                    'header' => $row['header']));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function doEditEmail() {
        global $controller;
        $url = $controller->GetAllControllerRequests('URL');
    
        $name         = (isset($url['name'])) ? urldecode($url['name']) : null;
        $subject     = (isset($url['subject'])) ? $url['subject'] : null;
    
        $message       = (isset($url['message'])) ? $url['message'] : null;
        $header      = (isset($url['header'])) ? $url['header'] : null;
    
        if ((!empty($name)) && (!empty($subject)) && (!empty($message))) {
            if (self::ExecuteEditEmail($name, $subject, $message, $header)){
                self::$editedEmail = true;
                return true;
            }
            else{
                self::$editEmail = true;
                return false;
            }
        }
    }
    
    static function ExecuteEditEmail($name, $subject, $message, $header){
        global $zdbh;
        // Check for errors before we continue...
        if (fs_director::CheckForEmptyValue($name, $subject, $message)) {
            return false;
        }
        $sql = $zdbh->prepare("UPDATE x_rb_mail SET subject = :subject, message = :message WHERE name = :name LIMIT 1");
        $query = array(':subject'=>$subject, ':message'=>$message, ':name' => $name);
        $sql->execute($query);
    
        return true;
    }


/******
* ALL AROUND FUNCTONS
******/

    static function getMail($name){
        global $zdbh;

        $stmt = $zdbh->prepare('SELECT * FROM x_rb_mail WHERE name=? LIMIT 1');
        $stmt->execute(array($name));
        $row = $stmt->fetch();

        return $row;
    }
    static function getUserEmail($id){
        global $zdbh;
        $stmt = $zdbh->prepare('SELECT ac_email_vc FROM x_accounts WHERE ac_id_pk=? LIMIT 1');
        $stmt->execute(array($id));
        $row = $stmt->fetchColumn();

        return $row;
    }

    /**
    * PHP mail function to send mail in UTF-8.
    * @author Martinkolle
    * @return true on success and false on fail
    */
    static function sendemail($emailto, $emailsubject, $emailbody) {

        $fromEmail = self::getConfig('email.from');
        $fromEmailName = self::getConfig('email.fromname');
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= "From: ". $fromEmailName ." <". $fromEmail .">\r\n";

        $send = mail($emailto,$emailsubject,$emailbody,$headers);

        return ($send) ? true : false;
    }

    static function getConfig($name){
        global $zdbh;
        $stmt = $zdbh->prepare('SELECT value FROM x_rb_settings WHERE name=? LIMIT 1');
        $stmt->execute(array($name));
        $row = $stmt->fetchColumn();

        return $row;
    }
    /**
        * Generate the token the payment should be specified with.
        * @link http://www.php.net/manual/en/function.openssl-random-pseudo-bytes.php#96812
        * @return token
    */
    static function generateToken($length = 24) {
        $characters = '0123456789';
        $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
        $charactersLength = strlen($characters)-1;
        $token = '';
        //select some random characters
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[mt_rand(0, $charactersLength)];
        }        
        return $token;
    }

/*****
* API CONNECTIONS
*****/

    static function ExecuteCreateInvoice($user, $amount, $type, $desc, $token, $email = true){
        global $zdbh;
        $date = date("Y-m-d");// current date

        $stmt = $zdbh->prepare("INSERT INTO x_rb_invoice(inv_user, inv_amount, inv_type, inv_date, inv_desc, inv_token) VALUES (:user, :amount, :type, :date, :desc, :token)");
		$stmt->bindParam(':user', $user);
		$stmt->bindParam(':amount', $amount);
		$stmt->bindParam(':type', $type);
		$stmt->bindParam(':date', $date);
		$stmt->bindParam(':desc', $desc);
		$stmt->bindParam(':token', $token);
		if(!$stmt->execute()){
		return false;
        } else{
            if($email){
                $profile = self::ApiProfile($user);
                $email = self::getMail("user_payment");
                $emailtext = $email['message'];
                $emailtext = str_replace('{{fullname}}',$profile['ud_fullname_vc'],$emailtext);
                $emailtext = str_replace('{{billing_url}}',self::getConfig('system.url_billing'),$emailtext);
                $emailtext = str_replace('{{token}}',$token,$emailtext);
                $emailtext = str_replace('{{firm}}',self::getConfig('firm'),$emailtext);
            
                self::sendemail(self::getUserEmail($user), $email['subject'], $emailtext);
            }
            else{
                $profile = self::ApiProfile($user);
                $email = self::getMail("user_expire");
                $emailtext = $email['message'];
                $emailtext = str_replace('{{fullname}}',$profile['ud_fullname_vc'],$emailtext);
                $emailtext = str_replace('{{billing_url}}',self::getConfig('system.url_billing'),$emailtext);
                $emailtext = str_replace('{{days}}',self::getConfig('user_expireDays'),$emailtext);
                $emailtext = str_replace('{{token}}',$token,$emailtext);
                $emailtext = str_replace('{{firm}}',self::getConfig('firm'),$emailtext);
            
                self::sendemail(self::getUserEmail($user), $email['subject'], $emailtext);
            }

            return true;
        }
    }

    static function IsValidUserName($username) {
        if (!preg_match('/^[a-z\d][a-z\d-]{0,62}$/i', $username) || preg_match('/-$/', $username)) {
            return false;
        }
        return true;
    }

    static function IsValidEmail($email) {
        if (!preg_match('/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i', $email)) {
            return false;
        }
        return true;
    }

    /**
    * We need to check if the user exits, and return in the xml.
    * @return
    * 1: Username is not valid
    * 2: Username allready exits
    * 3: Username is available
    * 4: Username is empty
    */
    static function getUserExits($username){
        global $zdbh;
        if (!self::IsValidUserName($username)) {
            return 1;
        }
        if (!fs_director::CheckForEmptyValue($username)) {
            $sql = "SELECT COUNT(*) FROM x_accounts WHERE UPPER(ac_user_vc)='" . strtoupper($username)."' LIMIT 1";
            if ($numrows = $zdbh->query($sql)) {
                if ($numrows->fetchColumn() <> 0) {
                    return 2;
                } else {
                    return 3;
                }
            }
        } else{
            return 4;
        }
    }

/******
* API FUNCTONS
******/

    /**
    * Get the package informations
    * @return query
    */
    static function ApiPackage($pk_id){
        global $zdbh;
        //select package informations
        $stmt = $zdbh->prepare("
            SELECT *
            FROM x_packages a
              LEFT JOIN x_rb_price b
                ON a.pk_id_pk = b.pk_id
                WHERE a.pk_id_pk = ? AND a.pk_deleted_ts IS NULL
        ");
        $stmt->execute(array($pk_id));
        return $stmt->fetch();
    }

    static function ApiInvoice($token){
        global $zdbh;
        //select package informations
        $stmt = $zdbh->prepare("SELECT * FROM x_rb_invoice WHERE inv_token= ? LIMIT 1");
        if($stmt->execute(array($token))){
            return $stmt->fetch();
        } else {
            return false;
        }
    }

    static function ApiAccount($id){
        global $zdbh;
        //select package informations
        $stmt = $zdbh->prepare("
            SELECT a.ac_user_vc, a.ac_id_pk, a.ac_email_vc as email, b.inv_desc
            FROM x_accounts a
              LEFT JOIN x_rb_invoice b
                ON a.ac_id_pk = b.inv_user
                AND b.inv_desc IS NOT NULL
                AND b.inv_status = '2'
            WHERE a.ac_id_pk = ? AND ac_deleted_ts IS NULL
        ");
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    static function ApiPaymentMethods(){
        global $zdbh;
        //select payment informations
        $stmt = $zdbh->prepare("SELECT * FROM x_rb_payment WHERE pm_active='1'");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    static function ApiProfile($user_id){
        global $zdbh;
        //select profile informations
        $stmt = $zdbh->prepare("SELECT * FROM x_profiles WHERE ud_user_fk= ?");
        $stmt->execute(array($user_id));
        return $stmt->fetch();
    }
	
	static function ApiDetails($user_id){
        global $zdbh;
        //select profile informations
        $stmt = $zdbh->prepare("SELECT * FROM x_accounts WHERE ac_id_pk= ?");
        $stmt->execute(array($user_id));
        return $stmt->fetch();
    }

    static function ApiPayment($method, $user_id, $txn_id, $token){
        global $zdbh;
        $response   = "1";
        $desc       = NULL;

        // Set that we have received the payment from paypal
        $stmt = $zdbh->prepare("UPDATE x_rb_invoice SET inv_payment = :method, inv_payment_id = :txn_id, inv_status = '1' WHERE inv_token = :token LIMIT 1");
        $stmt->bindParam(':method', $method);
		$stmt->bindParam(':txn_id', $txn_id);
		$stmt->bindParam(':token', $token);
		
        if(!$stmt->execute()) {
           $response = "2";
        }

        //set new account password
        
        $characters = '0123456789';
		$characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$characters .= 'abcdefghijklmnopqrstuvwxyz'; 
		$charactersLength = strlen($characters)-1;
		$newpassword = '';
		$length = '9';

		//select some random characters
		for ($i = 0; $i < $length; $i++) {
			$newpassword .= $characters[mt_rand(0, $charactersLength)];
		}
		
        $crypto = new runtime_hash;
        $crypto->SetPassword($newpassword);
        $randomsalt = $crypto->RandomSalt();
        $crypto->SetSalt($randomsalt);
        $secure_password = $crypto->CryptParts($crypto->Crypt())->Hash;
						
        $stmt = $zdbh->prepare("UPDATE x_accounts SET ac_enabled_in = '1',ac_pass_vc = :pass, ac_passsalt_vc = :salt WHERE ac_id_pk = :user_id LIMIT 1");
		$stmt->bindParam(':user_id', $user_id);
		$stmt->bindParam(':pass', $secure_password);
		$stmt->bindParam(':salt', $randomsalt);
		
        if(!$stmt->execute()) {
           $response = "4";
        }
		
		//Update the hosting time - when should the user expire
        $stmt = $zdbh->prepare("SELECT inv_desc FROM x_rb_invoice WHERE inv_token = ?");

        if($stmt->execute(array($token))){
            $desc = $stmt->fetchColumn();
            $obj = json_decode($desc);   
            $period = $obj->{'period'};
        }
        else{
            $response = "3";
        }

        $date           = date('Y-m-d');
        $remind_date    = strtotime( $period." month", strtotime($date));
        $remind_date    = date('Y-m-d', $remind_date);
		$remind_date2    = strtotime( "-1 month", strtotime($remind_date));
		$remind_date2    = date('Y-m-d', $remind_date2);
		
        $stmt = $zdbh->prepare("INSERT INTO x_rb_billing (blg_user, blg_inv_id, blg_duedate, blg_remind, blg_create, blg_desc) VALUES (:user_id, :inv_id, :duedate, :remind, :create, :desc)");
		$stmt->bindParam(':user_id', $user_id);
		$stmt->bindParam(':inv_id', $token);
		$stmt->bindParam(':duedate', $remind_date);
		$stmt->bindParam(':remind', $remind_date2);
		$stmt->bindParam(':create', $date);
		$stmt->bindParam(':desc', $desc);
		
        if(!$stmt->execute()) {
           $response = "5";
        }
		        $profile = self::ApiProfile($user_id);
				$details = self::ApiDetails($user_id);
                $email = self::getMail("user_welcome");
                $emailtext = $email['message'];
                $emailtext = str_replace('{{fullname}}',$profile['ud_fullname_vc'],$emailtext);
                $emailtext = str_replace('{{zpanel_url}}',self::getConfig('system.url_billing'),$emailtext);
				$emailtext = str_replace('{{ftp}}',self::getConfig('system.url_billing'),$emailtext);
				$emailtext = str_replace('{{username}}',$details['ac_user_vc'],$emailtext);
                $emailtext = str_replace('{{password}}',$newpassword,$emailtext);
                $emailtext = str_replace('{{ns1}}',self::getConfig('domain.ns1'),$emailtext);
                $emailtext = str_replace('{{ns2}}',self::getConfig('domain.ns2'),$emailtext);
                $emailtext = str_replace('{{firm}}',self::getConfig('system.firm'),$emailtext);
            	$emailsubject = $email['subject'];
            	$emailsubject = str_replace('{{firm}}',self::getConfig('system.firm'),$emailsubject);
            	
                self::sendemail(self::getUserEmail($user_id), $emailsubject, $emailtext);
                
			return $response;
    }

    static function ApiCreateClient($reseller_id, $username, $packageid, $groupid, $fullname, $email, $address, $post, $phone, $password, $domain, $transfer_help, $buy_domain) {
        global $zdbh;
		        	
        // Check for spaces and remove if found...
        $username = strtolower(str_replace(' ', '', $username));
        
      
        if(empty($reseller_id)){
            $reseller_id = self::getConfig("user.reseller_id");
        }

        if(empty($groupid)){
            $groupid = self::getConfig("user.group_id");
        }
        $reseller = ctrl_users::GetUserDetail($reseller_id);
        // Check for errors before we continue...
			
        if (fs_director::CheckForEmptyValue(self::ApiCreateClientCheckError($username, $packageid, $groupid, $email, $password))) {
            return false;
        }
        runtime_hook::Execute('OnBeforeCreateClient');
        // No errors found, so we can add the user to the database...
        $crypto = new runtime_hash;
        $crypto->SetPassword($password);
        $randomsalt = $crypto->RandomSalt();
        $crypto->SetSalt($randomsalt);
        $secure_password = $crypto->CryptParts($crypto->Crypt())->Hash;
        
        $sql = $zdbh->prepare("INSERT INTO x_accounts (ac_user_vc, ac_pass_vc, ac_passsalt_vc, ac_email_vc, ac_package_fk, ac_group_fk, ac_usertheme_vc, ac_usercss_vc, ac_reseller_fk, ac_created_ts, ac_enabled_in) VALUES (
:username, :password, :passsalt, :email, :packageid, :groupid, :resellertheme, :resellercss, :resellerid, :time, 0)");
        $sql->bindParam(':resellerid', $reseller_id);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->bindParam(':username', $username);
        $sql->bindParam(':password', $secure_password);
        $sql->bindParam(':passsalt', $randomsalt);
        $sql->bindParam(':email', $email);
        $sql->bindParam(':packageid', $packageid);
        $sql->bindParam(':groupid', $groupid);
        $sql->bindParam(':resellertheme', $reseller['usertheme']);
        $sql->bindParam(':resellercss', $reseller['usercss']);
        $sql->execute();
        
		$numrows = $zdbh->prepare("SELECT * FROM x_accounts WHERE ac_reseller_fk=:resellerid ORDER BY ac_id_pk DESC");
        $numrows->bindParam(':resellerid', $reseller_id);
        $numrows->execute();
        $client = $numrows->fetch();
        
        
         // Now lets pull back the client ID so that we can add their personal address details etc...
        $sql = $zdbh->prepare("INSERT INTO x_profiles (ud_user_fk, ud_fullname_vc, ud_group_fk, ud_package_fk, ud_address_tx, ud_postcode_vc, ud_phone_vc, ud_created_ts) VALUES (:userid, :fullname, :packageid, :groupid, :address, :postcode, :phone, :time)");
        $sql->bindParam(':userid', $client['ac_id_pk']);
        $sql->bindParam(':fullname', $fullname);
        $sql->bindParam(':packageid', $packageid);
        $sql->bindParam(':groupid', $groupid);
        $sql->bindParam(':address', $address);
        $sql->bindParam(':postcode', $post);
        $sql->bindParam(':phone', $phone);
        $time = time();
        $sql->bindParam(':time', $time);
        $sql->execute();
        
        // Now we add an entry into the bandwidth table, for the user for the upcoming month.
		$sql = $zdbh->prepare("INSERT INTO x_bandwidth (bd_acc_fk, bd_month_in, bd_transamount_bi, bd_diskamount_bi) VALUES (:ac_id_pk, :date, 0, 0)");
        $date = date("Ym", time());
        $sql->bindParam(':date', $date);
        $sql->bindParam(':ac_id_pk', $client['ac_id_pk']);
        $sql->execute();
		
		// Lets create the client diectories
        fs_director::CreateDirectory(ctrl_options::GetSystemOption('hosted_dir') . $username);
        fs_director::SetFileSystemPermissions(ctrl_options::GetSystemOption('hosted_dir') . $username, 0777);
        fs_director::CreateDirectory(ctrl_options::GetSystemOption('hosted_dir') . $username . "/public_html");
        fs_director::SetFileSystemPermissions(ctrl_options::GetSystemOption('hosted_dir') . $username . "/public_html", 0777);
        fs_director::CreateDirectory(ctrl_options::GetSystemOption('hosted_dir') . $username . "/backups");
        fs_director::SetFileSystemPermissions(ctrl_options::GetSystemOption('hosted_dir') . $username . "/backups", 0777);
        
        if ($domain == false ) {
        	$domain = 'No Domain Specified';
        }
        if ($transfer_help === 'yes') {
        	$transfer_help = 'The customer would like help to transfer the domain';
        } else {
        	$transfer_help = 'No help is required to transfer the domain';
        }
		
		if ($buy_domain === 'yes') {
			$buy_domain = 'The Customer would like to buy a domain';
		} else {
			$buy_domain = 'No domain purchase required';
		}
		
        $email = self::getMail("user_signup");
        $emailtext = $email['message'];
        $emailtext = str_replace('{{fullname}}',$fullname,$emailtext);
		$emailtext = str_replace('{{username}}',$username,$emailtext);
        $emailtext = str_replace('{{reseller_id}}',$reseller_id,$emailtext);
        $emailtext = str_replace('{{packageid}}',$packageid,$emailtext);
		$emailtext = str_replace('{{groupid}}',$groupid,$emailtext);
		$emailtext = str_replace('{{email}}',$email,$emailtext);
		$emailtext = str_replace('{{address}}',$address,$emailtext);
		$emailtext = str_replace('{{post}}',$post,$emailtext);
		$emailtext = str_replace('{{phone}}',$phone,$emailtext);
		$emailtext = str_replace('{{domain}}',$domain,$emailtext);
		$emailtext = str_replace('{{phone}}',$phone,$emailtext);
		$emailtext = str_replace('{{transfer_help}}',$transfer_help,$emailtext);
		$emailtext = str_replace('{{buy_domain}}',$buy_domain,$emailtext);
		$emailtext = str_replace('{{firm}}',self::getConfig('system.firm'),$emailtext);
        $emailsubject = $email['subject'];
            	
        self::sendemail(self::getConfig('email.contact_email'), $emailsubject, $emailtext);
				
				    
        runtime_hook::Execute('OnAfterCreateClient');
        self::$username = $username;
        return true;
    }

    static function ApiCreateClientCheckError($username, $packageid, $groupid, $email, $password="") {
        global $zdbh;
        $username = strtolower(str_replace(' ', '', $username));
        // Check to make sure the username is not blank or exists before we go any further...
        if (!fs_director::CheckForEmptyValue($username)) {
            $sql = "SELECT COUNT(*) FROM x_accounts WHERE UPPER(ac_user_vc)='" . strtoupper($username) . "' AND ac_deleted_ts IS NULL";
            if ($numrows = $zdbh->query($sql)) {
                if ($numrows->fetchColumn() <> 0) {
                    self::$allreadyexists = true;
                    return false;
                }
            }
            if (!self::IsValidUserName($username)) {
                return false;
            }
        } else {
            return false;
        }
        // Check to make sure the packagename is not blank and exists before we go any further...
        if (!fs_director::CheckForEmptyValue($packageid)) {
            $sql = "SELECT COUNT(*) FROM x_packages WHERE pk_id_pk='" . $packageid . "' AND pk_deleted_ts IS NULL";
            if ($numrows = $zdbh->query($sql)) {
                if ($numrows->fetchColumn() == 0) {
                    return false;
                }
            }
        } else {
            return false;
        }
        // Check to make sure the groupname is not blank and exists before we go any further...
        if (!fs_director::CheckForEmptyValue($groupid)) {
            $sql = "SELECT COUNT(*) FROM x_groups WHERE ug_id_pk='" . $groupid . "'";
            if ($numrows = $zdbh->query($sql)) {
                if ($numrows->fetchColumn() == 0) {
                    return;
                }
            }
        } else {
            return false;
        }
        // Check for invalid characters in the email and that it exists...
        if (!fs_director::CheckForEmptyValue($email)) {
            if (!self::IsValidEmail($email)) {
                return false;
            }
        } else {
            return false;
        }
        // Check for password length...
        if (!fs_director::CheckForEmptyValue($password)) {
            if (strlen($password) < ctrl_options::GetOption('password_minlength')) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    static function getUsernameId($username){
        global $zdbh;
        $sql='SELECT ac_id_pk FROM x_accounts WHERE ac_user_vc=?';
        $sql = $zdbh->prepare($sql);
        $sql->execute(array($username));
        $result=$sql->fetchColumn();

        return $result;
    }

    static function getShouldInstall(){
        global $zdbh;
        $result = $zdbh->query("show tables like 'x_rb_%'")->fetch(PDO::FETCH_NUM);
        if (is_array($result)){
            return false;
        } else{
            return true;
        }
    }
    static function DoinstallDatabase(){
    global $zdbh;
    $html = null;

        $query = $zdbh->prepare("CREATE TABLE IF NOT EXISTS `x_rb_invoice` (
                `inv_id` int(9) NOT NULL AUTO_INCREMENT,
                `inv_user` varchar(100) NOT NULL,
                `inv_amount` varchar(50) NOT NULL,
                `inv_type` varchar(100) NOT NULL,
                `inv_date` date NOT NULL COMMENT 'Created date',
                `inv_payment` varchar(50) DEFAULT NULL,
                `inv_payment_id` varchar(255) DEFAULT NULL,
                `inv_desc` text NOT NULL COMMENT 'json with domain, hosting, period',
                `inv_token` varchar(255) NOT NULL,
                `inv_status` int(9) NOT NULL DEFAULT '2',PRIMARY KEY (`inv_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
        if($query->execute()){
            $html .= "Created x_rb_invoice <br/>";
        } else{
            $html .= "Error x_rb_invoice<br/>";
        }

        $query = $zdbh->prepare("INSERT INTO `x_rb_invoice` (`inv_id`, `inv_user`, `inv_amount`, `inv_type`, `inv_date`, `inv_payment`, `inv_payment_id`, `inv_desc`, `inv_token`, `inv_status`) VALUES(1, '69', '488', 'Initial signup', '2012-08-28', 'paypal','','{\"pk_id\":4, \"price\":480, \"period\":12}', '7834ur9hoiu3rkewol', 2);");
        
        if($query->execute()){
            $html .= "Insert into x_rb_invoice<br/>";
        } else{
            $html .= "Insert into error x_rb_invoice<br/>";
        }

        $query = $zdbh->prepare("
CREATE TABLE IF NOT EXISTS `x_rb_billing` (
  `blg_id` int(9) NOT NULL AUTO_INCREMENT,
  `blg_user` varchar(100) NOT NULL,
  `blg_create` date NOT NULL,
  `blg_duedate` date NOT NULL COMMENT 'due date',
  `blg_inv_id` varchar(255) NOT NULL COMMENT 'invoice id',
  `blg_remind` varchar(100) NOT NULL,
  `blg_desc` text NOT NULL,
  PRIMARY KEY (`blg_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");
        
        if($query->execute()){
            $html .= "Created x_rb_billing<br/>";
        } else{
            $html .= "Error x_rb_billing<br/>";
        }

        $query = $zdbh->prepare("INSERT INTO `x_rb_billing` (`blg_id`, `blg_user`, `blg_create`, `blg_duedate`, `blg_inv_id`, `blg_remind`, `blg_desc`) VALUES
(1, '88', '0000-00-00', '2012-09-19', '1', '', '{\"pk_id\":\"4\", \"price\":\"488\", \"period\":\"12\", \"domain\":\"http://kmweb.dk\"}');
");
        
        if($query->execute()){
            $html .= "Insert into x_rb_billing<br/>";
        } else{
            $html .= "Insert into error x_rb_billing<br/>";
        }

        $query = $zdbh->prepare("CREATE TABLE IF NOT EXISTS `x_rb_mail` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(350) NOT NULL,
  `subject` varchar(350) NOT NULL,
  `message` text NOT NULL,
  `header` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;");

        if($query->execute()){
            $html .= "Create x_rb_mail";
        } else{
             $html .= "Error x_rb_mail";
        }

        $query->prepare("INSERT INTO `x_rb_mail` (`id`, `name`, `subject`, `message`, `header`) VALUES
(1, 'user_payment', 'Pay for hosting', '<p>{{fullname}}, we''re very pleased you''ve created an account with us.</p>\r\n<p>If for some reason you was unable to complete your order, please follow the \r\nfollowing link:</p>\r\n<p><a href=\"{{billing_url}}/pay.php?id={{token}}\">{{billing_url}}/pay.php?id={{token}}</a></p>\r\n<p>Once your payment is accepted, we will send your login credentails.</p>\r\n\r\nRegards, {{firm}}', ''),
(2, 'user_welcome', 'Welcome at {{firm}}', '<p><b>Welcome, {{username}}!</b></p>\r\n<p>Thank you for choosing us for your web hosting services. This email contains \r\nall the information you need to get started.</p>\r\n<p><b>Your control panel:</b></p>\r\n<p>{{zpanel_url}}<br>\r\nUsername: {{username}}<br>\r\npassword: {{password}}<br />\r\n</p>\r\n<p><b>Adding your domain name:</b></p>\r\n<p>This should be the first thing you do.</p>\r\n<ul>\r\n <li>Login to your control panel</li>\r\n    <li>Click ''Domains''</li>\r\n  <li>This page allows you to add new domain names</li>\r\n   <li>Then go to Advanced --&gt; DNS Settings --&gt; Select your domain name --&gt; \r\n  Click ''add default records''</li>\r\n</ul>\r\n<p>The last thing to do is change the name server settings with your domain \r\nregistrar to the following:</p>\r\n<p>{{ns1}}<br>\r\n{{ns2}}</p>\r\n<p>Domain names should resolve to our service within 1 hour, however can take up \r\nto 48 hours.</p>\r\n<p><b>FTP:</b></p>\r\n<ul>\r\n  <li>Login to your control panel</li>\r\n    <li>Click ''FTP Accounts''</li>\r\n <li>Create a username and password</li>\r\n</ul>\r\n<p>FTP can then be accessed using a range of free programs (we recommend \r\nFileZilla) with the address line:</p>\r\n<p>ftp.[your-domain-name].[ext] <br /> \r\nor {{ftp}}</p>\r\n\r\nRegards, {{firm}}', ''),
(3, 'user_expire', 'Account is going to be disabled', 'Dear {{fullname}}<br />\r\n\r\n<b>Your account at {{firm}} will expire in {{days}} days.</b>\r\n<br />\r\nIf you want to renew, please follow the link below. \r\n<br />\r\n{{billing_url}}/pay.php?id={{token}}\r\n<br />\r\nIf you don''t want, we will say thanks for the partnership. \r\n</br>\r\nRegards, {{firm}}', ''),
(4, 'user_disabled', '{{firm}}: {{username}} have been disabled', 'Dear {{fullname}},\r\n\r\nYour hosting account at {{firm}} have been disabled because you do not have  paid. \r\n\r\nIf you want to reactivate your account, please contact us at {{contact_mail}} <br /><br />\r\n\r\nRegards, {{firm}}', '');
");

        if($query->execute()){
            $html .= "Insert into x_rb_mail";
        } else{
             $html .= "Insert into error x_rb_mail";
        }  


        $query = $zdbh->prepare("CREATE TABLE IF NOT EXISTS `x_rb_payment` (
  `pm_id` int(9) NOT NULL AUTO_INCREMENT,
  `pm_name` varchar(255) NOT NULL,
  `pm_data` text NOT NULL,
  `pm_active` int(9) NOT NULL,
  PRIMARY KEY (`pm_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
        
        if($query->execute()){
            $html .= "Created x_rb_payment<br/>";
        } else{
            $html .= "Error x_rb_payment<br/>";
        }

        $query = $zdbh->prepare("INSERT INTO `x_rb_payment` (`pm_id`, `pm_name`, `pm_data`, `pm_active`) VALUES
(1, 'paypal', '<form name=\"form\" method=\"post\" action=\"{{action}}\">\n<input name=\"charset\" value=\"utf-8\" type=\"hidden\">\n<input name=\"cmd\" value=\"_xclick\" type=\"hidden\">\n<input name=\"upload\" value=\"1\" type=\"hidden\">\n\n<input name=\"first_name\" value=\"{{user_firstname}}\" type=\"hidden\">\n<input name=\"invoice\" value=\"{{invoice}}\" type=\"hidden\">\n<input name=\"email\" value=\"{{email}}\" type=\"hidden\">\n<input name=\"return\" value=\"{{return_url}}\" type=\"hidden\">\n<input name=\"business\" value=\"{{business}}\" type=\"hidden\">\n<input name=\"item_name\" value=\"{{item_name}}\" type=\"hidden\">\n<input name=\"quantity\" value=\"1\" type=\"hidden\">\n<input name=\"country\" value=\"{{country}}\" type=\"hidden\">\n<input name=\"amount\" value=\"{{amount}}\" type=\"hidden\">\n<input name=\"currency_code\" value=\"{{cs}}\" type=\"hidden\">\n<input name=\"image_url\" value=\"{{logo}}\" type=\"hidden\">\n<input type=\"hidden\" name=\"notify_url\" value=\"{{notify_url}}\" />\n<input value=\"Payment\" class=\"defbtn\" type=\"submit\">\n</form>', 1);
");
        
        if($query->execute()){
            $html .= "Insert into x_rb_payment<br/>";
        } else{
            $html .= "Insert into error x_rb_payment<br/>";
        }


        $query = $zdbh->prepare("CREATE TABLE IF NOT EXISTS `x_rb_price` (
  `pkp_id` int(9) NOT NULL AUTO_INCREMENT,
  `pk_id` varchar(9) NOT NULL COMMENT 'Package id',
  `pkp_domain` text COMMENT 'domain price -json',
  `pkp_hosting` text COMMENT 'hosting price -json',
  PRIMARY KEY (`pkp_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
        
        if($query->execute()){
            $html .= "Created x_rb_payment<br/>";
        } else{
            $html .= "Error x_rb_payment<br/>";
        }

        $query = $zdbh->prepare("INSERT INTO `x_rb_price` (`pkp_id`, `pk_id`, `pkp_domain`, `pkp_hosting`) VALUES
(1, '4', NULL, '{\"hosting\":[{\"month\":12,\"price\":499},{\"month\":6,\"price\":200}]}');");
        
        if($query->execute()){
            $html .= "Insert into x_rb_payment<br/>";
        } else{
            $html .= "Insert into error x_rb_payment<br/>";
        }
        self::$dbInstall = $html;
    }

    /**
    * Get error messages or return
    */
    static function getResult(){
        if(!fs_director::CheckForEmptyValue(self::$noId)){
            return ui_sysmessage::shout(ui_language::translate("No ID specified to delete"), "zannounceerror");
        } 
        if(!fs_director::CheckForEmptyValue(self::$noDelete)){
            return ui_sysmessage::shout(ui_language::translate("Could not delete invoice! Unknown error."), "zannounceerror");
        }
        if(!fs_director::CheckForEmptyValue(self::$delete)){
            return ui_sysmessage::shout(ui_language::translate("Invoice have been deleted"), "zannounceerror");
        }
        if(!fs_director::CheckForEmptyValue(self::$deletedPaymentMethod)){
            return ui_sysmessage::shout(ui_language::translate("Payment method have been deleted"), "zannounceok");
        }
        if(!fs_director::CheckForEmptyValue(self::$deletedPaymentMethodFail)){
            return ui_sysmessage::shout(ui_language::translate("Payment method have not been deleted. Failed"), "zannounceerror");
        }
        if(!fs_director::CheckForEmptyValue(self::$editedPackage)){
            return ui_sysmessage::shout(ui_language::translate("Package have been edited"), "zannounceok");
        }
        if(!fs_director::CheckForEmptyValue(self::$SavedPackage)){
            return ui_sysmessage::shout(ui_language::translate("Package have been saved"), "zannounceok");
        }

        if(!fs_director::CheckForEmptyValue(self::$editPackage)){
            return ui_sysmessage::shout(ui_language::translate("Failed editing package"), "zannounceerror");
        }
        if(!fs_director::CheckForEmptyValue(self::$editPackageNeedid)){
            return ui_sysmessage::shout(ui_language::translate("Not all values is added"), "zannounceerror");
        }
        if(!fs_director::CheckForEmptyValue(self::$editInvoiceGood)){
            return ui_sysmessage::shout(ui_language::translate("Invoice have been edited"), "zannounceok");
        }
        if(!fs_director::CheckForEmptyValue(self::$editInvoiceBad)){
            return ui_sysmessage::shout(ui_language::translate("Invoice could not be edited"), "zannounceerror");
        }
        if(!fs_director::CheckForEmptyValue(self::$editInvoiceNoId)){
            return ui_sysmessage::shout(ui_language::translate("Invoice id or user have not been specified"), "zannounceerror");
        }
        if(!fs_director::CheckForEmptyValue(self::$editPaymentGood)){
            return ui_sysmessage::shout(ui_language::translate("Payment have been edited"), "zannounceok");
        }
        if(!fs_director::CheckForEmptyValue(self::$editPaymentGood)){
            return ui_sysmessage::shout(ui_language::translate("Payment could not be edited"), "zannounceerror");
        }
        if(!fs_director::CheckForEmptyValue(self::$editPaymentGood)){
            return ui_sysmessage::shout(ui_language::translate("Payment id have not been specified"), "zannounceerror");
        }
        if(!fs_director::CheckForEmptyValue(self::$dbInstall)){
            return ui_sysmessage::shout(self::$dbInstall, "zannounceok");
        }
        if (!fs_director::CheckForEmptyValue(self::$updatedSettings)) {
            return ui_sysmessage::shout(ui_language::translate("Changes to your settings have been saved successfully!"),"zannounceok");
        }
        if (!fs_director::CheckForEmptyValue(self::$editedEmail)) {
            return ui_sysmessage::shout(ui_language::translate("Changes to email have been saved successfully!"),"zannounceok");
        }
        if (!fs_director::CheckForEmptyValue(self::$editEmail)) {
            return ui_sysmessage::shout(ui_language::translate("Could not save email"),"zannounceerror");
        }
    }
}

?>