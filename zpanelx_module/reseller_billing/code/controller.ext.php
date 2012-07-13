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
    static $editedPackage;
    static $editPackage;
    static $editPackageNeedid;

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
    static function getEditPayment() {
        global $controller;
        $urlvars = $controller->GetAllControllerRequests('URL');
        if ((isset($urlvars['show'])) && ($urlvars['show'] == "Edit") && ($urlvars['view'] == "payment_method") ) {
            return true;
        } else {
            return false;
        }
    }
    static function getEditPaymentName() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentPayment($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['name'];
        } else {
            return "";
        }
    }
    static function getEditPaymentData() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentPayment($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['data'];
        } else {
            return "";
        }
    }
    static function getEditPaymentActive() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentPayment($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['active'];
        } else {
            return "";
        }
    }
    static function getEditPaymentID() {
        global $controller;
        if ($controller->GetControllerRequest('URL', 'other')) {
            $current = self::ListCurrentPayment($controller->GetControllerRequest('URL', 'other'));
            return $current[0]['id'];
        } else {
            return "";
        }
    }
    function getPayments(){
        global $zdbh;
        $sql = "SELECT * FROM x_payment_methods";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($row = $sql->fetch()) {
                array_push($res, array(
                    'pm_id' => $row['pm_id'],
                    'name' => $row['pm_name'],
                    'data' => $row['pm_data'],
                    'active' => $row['pm_active']
                ));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function ListCurrentPayment($uid) {
        global $zdbh;
        $sql = "SELECT * FROM x_payment_methods WHERE pm_id=" . $uid . "";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($row = $sql->fetch()) {
                    array_push($res, array('name'   => $row['pm_name'],
                                           'id' => $row['pm_id'],
                                           'data'   => $row['pm_data'],
                                           'active'   => $row['pm_active']));
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

        $formvars = $controller->GetAllControllerRequests('FORM');
        if (self::ExecuteEditPayment($formvars['id'],$formvars['name'], $formvars['data'], $formvars['active'])) {
            return true;
        } else {
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

    static function ExecuteCreatePayment($name, $data, $active){
        global $zdbh;
        // Check for errors before we continue...
        if (fs_director::CheckForEmptyValue($name, $data, $active)) {
            return false;
        }
        $sql = $zdbh->prepare("INSERT INTO x_payment_methods (pm_name,pm_data,pm_active) VALUES(:name, :data, :active)");
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
        $sql = $zdbh->prepare("UPDATE x_payment_methods SET pm_name = :name, pm_data = :data, pm_active = :active WHERE pm_id = :id");
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
        $sql = $zdbh->prepare("DELETE FROM x_payment_methods WHERE pm_id = '".$id."'");        
        $sql->execute();
        return true;
    }

    /**END PAYMENT*/

    /** BILLINGS ***/
    function getBillings() {
        global $zdbh;
        $sql = "SELECT * FROM x_invoice WHERE inv_user IS NOT NULL";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $res = array();
            $sql->execute();
            while ($row = $sql->fetch()) {
                array_push($res, array(
                    'user_id' => $row['inv_user'],
                    'amount' => $row['inv_amount'],
                    'description' => $row['inv_description'],
                    'due_date' => $row['inv_duedate'],
                    'createddate' => $row['inv_createddate'],
                    'payment_method' => $row['inv_payment_method'],                    
                    'payment_id' => $row['inv_payment_id'],
                    'act' => $row['inv_act'],
                    'token' => $row['token'],
                    'id' => $row['inv_id']
                ));
            }
            return $res;
        } else {
            return false;
        }
    }

    static function doDeleteBilling() {
        global $controller;
        $id = $controller->GetAllControllerRequests('URL');

        if (isset($id['deleteId'])) {
            if (self::ExecuteDeleteBilling($id['deleteId'])){
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


    static function ExecuteDeleteBilling($id) {
        global $zdbh;
        $sql = "DELETE FROM x_invoice WHERE inv_id = ?";
        $sql = $zdbh->prepare($sql);
        $sql->execute(array($id));
        return true;
    }

    /*** PACKAGE ***/

    function getPackages(){
        global $zdbh;
        $sql = "SELECT * FROM x_packages WHERE pk_deleted_ts IS NULL";
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
                    'price_pm' => $row['pk_price_pm'],
                    'price_pq' => $row['pk_price_pq'],
                    'price_py' => $row['pk_price_py']
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
        $name       = (isset($url['name'])) ? $url['name'] : null;
        $price_pm   = (isset($url['price_pm'])) ? $url['price_pm'] : null;
        $price_pq   = (isset($url['price_pq'])) ? $url['price_pq'] : null;
        $price_py   = (isset($url['price_py'])) ? $url['price_py'] : null;

        if ((!empty($id)) && (!empty($name))) {
            if (self::ExecuteEditPackage($id, $name, $price_pm, $price_pq, $price_py)){
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
    static function ExecuteEditPackage($id, $name, $price_pm, $price_pq, $price_py){
        global $zdbh;
        // Check for errors before we continue...
        if (fs_director::CheckForEmptyValue($id, $name, $price_pm, $price_pq, $price_py)) {
            return false;
        }
        $sql = $zdbh->prepare("UPDATE x_packages SET pk_name_vc = :name, pk_price_pm = :price_pm, pk_price_pq = :price_pq, pk_price_py = :price_py WHERE pk_id_pk = :id");
        $query = array(':id'=>$id, ':name'=>$name, ':price_pm'=>$price_pm,':price_pq'=>$price_pq, ':price_py' =>$price_py);
        
        $sql->execute($query);
        return true;
    }


    /**
    * Webservice functions
    */

    static function ExecuteCreateInvoice($user_id,$selectedpackageprice,$token){
        global $zdbh;
        $todaydate = date("Y-m-d");// current date

        $stmt = $zdbh->prepare("INSERT INTO x_invoice(inv_user, inv_amount, inv_description, inv_duedate, inv_createddate, inv_act, token) VALUES (:user_id,:selectedpackageprice,'Initial Signup',:todaydate,:todaydate,'1',:token)");
        $query = array(':user_id'=>$user_id, ':selectedpackageprice'=>$selectedpackageprice,':todaydate'=>$todaydate, ':todaydate'=>$todaydate, ':token'=>$token);
        if(!$stmt->execute($query)){
            return false;
        } else{
            return true;
        }
    }
    static function ExecuteCreateAccountInvoice($price,$invoice_nextdue,$invoice_period,$user_id){
        global $zdbh;

        $stmt = $zdbh->prepare("UPDATE x_accounts SET ac_price_pm = :price, ac_invoice_nextdue = :invoice_nextdue, ac_invoice_period = :invoice_period WHERE ac_id_pk = :user_id");
        $query = array(':price'=>$price, ':invoice_nextdue'=>$invoice_nextdue,':invoice_period'=>$invoice_period, ':user_id'=>$user_id);
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


    /**
    * We need to check if the user exits, and return in the xml.
    * @return:
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
            $sql = "SELECT COUNT(*) FROM x_accounts WHERE UPPER(ac_user_vc)='" . strtoupper($username)."'";
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
        $stmt = $zdbh->prepare("SELECT * FROM x_packages WHERE pk_id_pk= ? AND pk_price_pm IS NOT NULL AND pk_price_pq IS NOT NULL AND pk_price_py IS NOT NULL AND pk_deleted_ts IS NULL");
        $stmt->execute(array($pk_id));
        return $stmt->fetch();
    }

    static function ApiInvoice($token){
        global $zdbh;
        //select package informations
        $stmt = $zdbh->prepare("SELECT * FROM x_invoice WHERE token= ?");
        $stmt->execute(array($token));
        return $stmt->fetch();
    }

    static function ApiAccount($ac_id){
        global $zdbh;
        //select package informations
        $stmt = $zdbh->prepare("SELECT * FROM x_accounts WHERE ac_id_pk= ? AND ac_deleted_ts IS NULL");
        $stmt->execute(array($ac_id));
        return $stmt->fetch();
    }

    static function ApiPayment_method(){
        global $zdbh;
        //select package informations
        $stmt = $zdbh->prepare("SELECT * FROM x_payment_methods WHERE pm_active='1'");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    static function ApiProfile($user_id){
        global $zdbh;
        //select package informations
        $stmt = $zdbh->prepare("SELECT * FROM x_profiles WHERE ud_user_fk= ?");
        $stmt->execute(array($user_id));
        return $stmt->fetch();
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

    }
}

?>