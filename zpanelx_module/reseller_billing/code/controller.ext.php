<?php

/**
 *
 * Firewall Configuration Module for ZPX
 * Version : 200
 * Author :  Mudasir Mirza
 * Email : mudasirmirza@gmail.com
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
    static $editPackage;
    static $editPackageNeedid;
    static $editInvoiceGood;
    static $editInvoiceBad;
    static $editInvoiceNoId;

    /**
    *At the end optional fields, but required
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
    * I want to have different views
    */
    function getView(){
        global $controller;
        $url = $controller->GetAllControllerRequests('URL');
        $url = (array_key_exists('view', $url)) ? $url['view'] : false;
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

    /*PAYMENT FUNCTIONS*/

    function getPayments(){
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

    /**END PAYMENT*/

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
    /* USING THE ZPANEL WAY = a lot of reloads because of views
    static function doDeletePayment() {
        global $controller;

        $formvars = $controller->GetAllControllerRequests('FORM');
        if (self::ExecuteDeletePayment($formvars['deleteId'])) {
            self::$deletedPaymentMethod = true;
            return true;
        } else {
            return false;
        }
    }
    */

    /** BILLINGS ***/
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
        $sql = $zdbh->prepare("UPDATE x_rb_invoice SET inv_user = :inv_user, inv_amount = :amount, inv_type = :type, inv_date = :date, inv_payment = :payment, inv_payment_id = :payment_id, inv_desc = :desc, inv_status = :status, inv_token = :token  WHERE inv_id = :id");
        
        $query = array(':id'=>$id, ':inv_user'=>$user, ':amount'=>$amount, ':type'=>urldecode($type), ':date'=>$date, ':payment'=>urldecode($payment), ':payment_id'=>$payment_id, ':desc'=>urldecode($desc), ':status'=>$status, ':token'=>$token);
        
        $sql->execute($query);
        return true;
    }

    /*** PACKAGE ***/

    function getPackages(){
        global $zdbh;
        //$sql = "SELECT * FROM x_packages WHERE x_packages.pk_deleted_ts IS NULL LEFT JOIN x_rb_price ON x_packages.pk_id_pk = x_rb_price.pk_id";

        $sql = "SELECT * FROM x_packages LEFT JOIN x_rb_price ON x_packages.pk_id_pk = x_rb_price.pk_id WHERE x_packages.pk_deleted_ts IS NULL";
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
                    'domain' => $row['pkp_domain']
                ));
            }
            return $res;
        } else {
            return false;
        }
    }
    static function doEditPackage() {
        global $controller;
        $url = $controller->GetAllControllerRequests('URL');

        $id         = (isset($url['id'])) ? $url['id'] : null;
        $name       = (isset($url['name'])) ? urldecode($url['name']) : null;
        $reseller       = (isset($url['reseller'])) ? $url['reseller'] : null;

        $domain   = (isset($url['domain'])) ? $url['domain'] : null;
        $hosting   = (isset($url['hosting'])) ? $url['hosting'] : null;

        if ((!empty($id)) && (!empty($name))) {
            if (self::ExecuteEditPackage($id, $name, $domain, $hosting)){
                self::$editedPackage = true;
                return true;
            }
            else{
                self::$editPackage = true;
                return false;
            }
        }
        else{
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
        $sql = $zdbh->prepare("UPDATE x_packages SET pk_name_vc = :name WHERE pk_id_pk = :id");
        $query = array(':id'=>$id, ':name'=>$name);
        $sql->execute($query);

        $smtp = $zdbh->prepare("UPDATE x_rb_price SET 
            pkp_domain = :domain, 
            pkp_hosting = :hosting 
            WHERE pk_id = :id");
        $query2 = array(':id'=>$id, ':domain'=>$domain, ':hosting'=>$hosting);
        $smtp->execute($esd);

        return true;
    }

    /**
    * API CONNECTIONS
    */

    static function ExecuteCreateInvoice($user, $amount, $type, $desc, $token){
        global $zdbh;
        $date = date("Y-m-d");// current date

        $stmt = $zdbh->prepare("
            INSERT INTO x_rb_invoice(
                inv_user, 
                inv_amount, 
                inv_type, 
                inv_date, 
                inv_desc, 
                inv_token) 
            VALUES (
                :user,
                :amount,
                :type,
                :date,
                :desc,
                :token
            )");
        $query = array(':user'=>$user, ':amount'=>$amount, ':type'=>$type, ':date'=>$date, ':desc'=>$desc, ':token'=>$token);
        if(!$stmt->execute($query)){
            return false;
        } else{
            return true;
        }
    }
    static function ExecuteCreateAccountInvoice($user, $duedate, $invoice, $remind, $desc){
        global $zdbh;
        
        $stmt = $zdbh->prepare("
            INSERT INTO x_rb_billing(
                blg_user, 
                blg_duedate, 
                blg_inv_id, 
                blg_remind, 
                blg_desc
            ) VALUES (
                :user,
                :duedate,
                :invoice,
                :remind,
                :desc
            )");
        $query = array(':user'=>$user, ':duedate'=>$duedate, ':invoice'=>$invoice, ':remind'=>$date, ':desc'=>$desc);

        if(!$stmt->execute($query)){
            return false;
        } else{
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
                AND b.pkp_hosting IS NOT NULL OR b.pkp_domain IS NOT NULL
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

    static function ApiPayment($method, $txn_id, $token, $user_id){
        global $zdbh;
        $response = "1";

        // Set that we have received the payment from paypal
        $stmt = $zdbh->prepare("UPDATE x_rb_invoice SET inv_payment = :method, inv_payment_id = :txn_id, inv_status = :status WHERE inv_token = :token");

        $query = array(':method'=>$method, ':txn_id'=>$txn_id, ':token'=>$token, ':status'=>"1");

        if(!$stmt->execute($query))
        {
           $response = "2";
        }

        //Update the hosting time - when should the user expire
        $stmt = $zdbh->prepare("SELECT inv_desc FROM x_rb_invoice WHERE inv_token = ?");

        if($stmt->execute(array($token))){
           $obj = json_decode($stmt->fetchColumn());   
           $period = $obj->{'month'};
        }
        else{
            $response = "3";
        }

        $date       = date('Y-m-d');
        $nextdue    = strtotime( $period." month", strtotime($date));
        $nextdue    = date('Y-m-d', $nextdue);

        $remind_date= strtotime("2 days", strtotime($date));
        $remind_date= date('Y-m-d', $remind_date);

        $desc = null;
        //activate the account
        $stmt = $zdbh->prepare("UPDATE x_accounts SET ac_enabled_in = '1',  WHERE ac_id_pk = :user_id");
        $stmt .= $zdbh->prepare("
            INSERT INTO x_rb_billing(
                blg_user,
                blg_inv_id,
                blg_remind,
                blg_desc)
            VALUES(
                :user_id,
                :inv_id,
                :remind,
                :desc)");
        $query = array(':user_id'=>$user_id, ':inv_id'=>$token, ':remind'=>$remind_date, ':desc'=>$desc);

        if(!$stmt->execute($query))
        {
           $response = "4";
        }
        return $response;
    }

    static function ApiCreateClient($uid, $username, $packageid, $groupid, $fullname, $email, $address, $post, $phone, $password) {
        global $zdbh;
        // Check for spaces and remove if found...
        $username = strtolower(str_replace(' ', '', $username));
        $reseller = ctrl_users::GetUserDetail($uid);
        // Check for errors before we continue...
        if (fs_director::CheckForEmptyValue(self::ApiCreateClientCheckError($username, $packageid, $groupid, $email, $password))) {
            return false;
        }
        runtime_hook::Execute('OnBeforeCreateClient');
        // No errors found, so we can add the user to the database...
        $sql = $zdbh->prepare("INSERT INTO x_accounts (
                                        ac_user_vc,
                                        ac_pass_vc,
                                        ac_email_vc,
                                        ac_package_fk,
                                        ac_group_fk,
                                        ac_usertheme_vc,
                                        ac_usercss_vc,
                                        ac_reseller_fk,
                                        ac_enabled_in,
                                        ac_created_ts) VALUES (
                                        '" . $username . "',
                                        '" . md5($password) . "',
                                        '" . $email . "',
                                        '" . $packageid . "',
                                        '" . $groupid . "',
                                        '" . $reseller['usertheme'] . "',
                                        '" . $reseller['usercss'] . "',
                                        " . $uid . ",
                                        '0',
                                        " . time() . ")");
        $sql->execute();
        // Now lets pull back the client ID so that we can add their personal address details etc...
        $client = $zdbh->query("SELECT * FROM x_accounts WHERE ac_reseller_fk=" . $uid . " ORDER BY ac_id_pk DESC")->Fetch();
        $sql = $zdbh->prepare("INSERT INTO x_profiles (ud_user_fk,
                                        ud_fullname_vc,
                                        ud_group_fk,
                                        ud_package_fk,
                                        ud_address_tx,
                                        ud_postcode_vc,
                                        ud_phone_vc,
                                        ud_created_ts) VALUES (
                                         " . $client['ac_id_pk'] . ",
                                        '" . $fullname . "',
                                        '" . $packageid . "',
                                        '" . $groupid . "',
                                        '" . $address . "',
                                        '" . $post . "',
                                        '" . $phone . "',
                                         " . time() . ")");
        $sql->execute();
        // Now we add an entry into the bandwidth table, for the user for the upcoming month.
        $sql = $zdbh->prepare("INSERT INTO x_bandwidth (bd_acc_fk, bd_month_in, bd_transamount_bi, bd_diskamount_bi) VALUES (" . $client['ac_id_pk'] . "," . date("Ym", time()) . ", 0, 0)");
        $sql->execute();
        // Lets create the client diectories
        fs_director::CreateDirectory(ctrl_options::GetOption('hosted_dir') . $username);
        fs_director::SetFileSystemPermissions(ctrl_options::GetOption('hosted_dir') . $username, 0777);
        fs_director::CreateDirectory(ctrl_options::GetOption('hosted_dir') . $username . "/public_html");
        fs_director::SetFileSystemPermissions(ctrl_options::GetOption('hosted_dir') . $username . "/public_html", 0777);
        fs_director::CreateDirectory(ctrl_options::GetOption('hosted_dir') . $username . "/backups");
        fs_director::SetFileSystemPermissions(ctrl_options::GetOption('hosted_dir') . $username . "/backups", 0777);

        runtime_hook::Execute('OnAfterCreateClient');
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
                    self::$alreadyexists = true;
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

    static function getUserId($username){
        global $zdbh;
        $sql='SELECT ac_id_pk FROM x_accounts WHERE ac_user_vc=?';
        $sql = $zdbh->prepare($sql);
        $sql->execute(array($username));
        $result=$sql->fetchColumn();

        return $result;
    }

    static function DoinstallDatabase(){
    global $zdbh;

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
            $html .= "Created x_rb_invoice";
        } else{
            $html .= "Error x_rb_invoice";
        }

        $query = $zdbh->prepare("INSERT INTO `x_rb_invoice` (`inv_id`, `inv_user`, `inv_amount`, `inv_type`, `inv_date`, `inv_payment`, `inv_payment_id`, `inv_desc`, `inv_token`, `inv_status`) VALUES
(1, '69', '488', 'Initial signup', '2012-08-28', 'paypal', '', '  {\"pk_id\":4, \"price\":480, \"period\":12}     ', '7834ur9hoiu3rkewol', 2);

");
        
        if($query->execute()){
            $html .= "Insert into x_rb_invoice";
        } else{
            $html .= "Insert into error x_rb_invoice";
        }

        $query = $zdbh->prepare("CREATE TABLE IF NOT EXISTS `x_rb_billing` (
              `blg_id` int(9) NOT NULL AUTO_INCREMENT,
              `blg_user` varchar(100) NOT NULL,
              `blg_duedate` date NOT NULL COMMENT 'due date',
              `blg_inv_id` varchar(255) NOT NULL COMMENT 'invoice id',
              `blg_remind` varchar(100) NOT NULL,
              `blg_desc` text NOT NULL,
              PRIMARY KEY (`blg_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
        
        if($query->execute()){
            $html .= "Created x_rb_billing";
        } else{
            $html .= "Error x_rb_billing";
        }

        $query = $zdbh->prepare("INSERT INTO `x_rb_billing` (`blg_id`, `blg_user`, `blg_duedate`, `blg_inv_id`, `blg_remind`, `blg_desc`) VALUES
(1, '68 ', '0000-00-00', '1', '', '{\"hosting\":web49, \"price\":488, \"period\":12, \"domain\":http://kmweb.dk}');
");
        
        if($query->execute()){
            $html .= "Insert into x_rb_billing";
        } else{
            $html .= "Insert into error x_rb_billing";
        }


        $query = $zdbh->prepare("CREATE TABLE IF NOT EXISTS `x_rb_payment` (
  `pm_id` int(9) NOT NULL AUTO_INCREMENT,
  `pm_name` varchar(255) NOT NULL,
  `pm_data` text NOT NULL,
  `pm_active` int(9) NOT NULL,
  PRIMARY KEY (`pm_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
        
        if($query->execute()){
            $html .= "Created x_rb_payment";
        } else{
            $html .= "Error x_rb_payment";
        }

        $query = $zdbh->prepare("INSERT INTO `x_rb_payment` (`pm_id`, `pm_name`, `pm_data`, `pm_active`) VALUES
(1, 'paypal', '<form name=\"form\" method=\"post\" action=\"{{action}}\">\n<input name=\"charset\" value=\"utf-8\" type=\"hidden\">\n<input name=\"cmd\" value=\"_xclick\" type=\"hidden\">\n<input name=\"upload\" value=\"1\" type=\"hidden\">\n\n<input name=\"first_name\" value=\"{{user_firstname}}\" type=\"hidden\">\n<input name=\"invoice\" value=\"{{invoice}}\" type=\"hidden\">\n<input name=\"email\" value=\"{{email}}\" type=\"hidden\">\n<input name=\"return\" value=\"{{return_url}}\" type=\"hidden\">\n<input name=\"business\" value=\"{{business}}\" type=\"hidden\">\n<input name=\"item_name\" value=\"{{item_name}}\" type=\"hidden\">\n<input name=\"quantity\" value=\"1\" type=\"hidden\">\n<input name=\"country\" value=\"{{country}}\" type=\"hidden\">\n<input name=\"amount\" value=\"{{amount}}\" type=\"hidden\">\n<input name=\"currency_code\" value=\"{{cs}}\" type=\"hidden\">\n<input name=\"image_url\" value=\"{{logo}}\" type=\"hidden\">\n<input type=\"hidden\" name=\"notify_url\" value=\"{{notify_url}}\" />\n<input value=\"Payment\" class=\"defbtn\" type=\"submit\">\n</form>', 1);
");
        
        if($query->execute()){
            $html .= "Insert into x_rb_payment";
        } else{
            $html .= "Insert into error x_rb_payment";
        }


        $query = $zdbh->prepare("CREATE TABLE IF NOT EXISTS `x_rb_price` (
  `pkp_id` int(9) NOT NULL AUTO_INCREMENT,
  `pk_id` varchar(9) NOT NULL COMMENT 'Package id',
  `pkp_domain` text COMMENT 'domain price -json',
  `pkp_hosting` text COMMENT 'hosting price -json',
  PRIMARY KEY (`pkp_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
        
        if($query->execute()){
            $html .= "Created x_rb_payment";
        } else{
            $html .= "Error x_rb_payment";
        }

        $query = $zdbh->prepare("INSERT INTO `x_rb_price` (`pkp_id`, `pk_id`, `pkp_domain`, `pkp_hosting`) VALUES
(1, '4', NULL, '{"hosting":[{"month":12,"price":499},{"month":6,"price":200}]}');");
        
        if($query->execute()){
            $html .= "Insert into x_rb_payment";
        } else{
            $html .= "Insert into error x_rb_payment";
        }
        



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
    }
}

?>