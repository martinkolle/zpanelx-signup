<?php

/**
 * @package zpanelx
 * @subpackage modules->billing
 * @author Martin Kollerup
 * @copyright martinkole
 * @link http://www.kmweb.dk/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */

class webservice extends ws_xmws {

	public function PackageList(){
		$response="";
		$row = module_controller::getPackages();
		foreach($row as &$value) {
			if($value['hosting']) {
				$response .= ws_xmws::NewXMLTag('package', 	
				ws_xmws::NewXMLTag('name',$value['name']).
				ws_xmws::NewXMLTag('id', $value['id']).
				ws_xmws::NewXMLTag('reseller', $value['reseller']).
				ws_xmws::NewXMLTag('domain', $value['domain']).
				ws_xmws::NewXMLTag('hosting', $value['hosting'])
				);
			}
		}

		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', $response);
		return $dataobject->getDataObject();
	}

	/**
	* Create the invoice
	* @return 1: succes
	* @return 2: Account invoice failed
	* @return 0: Invoice creation failed
	*/

	public function CreateInvoice(){

		$response = NULL;
		$request_data = $this->RawXMWSToArray($this->wsdata);
		$contenttags = $this->XMLDataToArray($request_data['content']);

		if(module_controller::ExecuteCreateInvoice( 
			ws_generic::GetTagValue('user_id', $request_data['content']), 
			ws_generic::GetTagValue('amount', $request_data['content']),
			ws_generic::GetTagValue('type', $request_data['content']),
			ws_generic::GetTagValue('desc', $request_data['content']),
			ws_generic::GetTagValue('token', $request_data['content'])
			)){
				$response = "1";
		} else{
			$response = "0";
		}
		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', ws_xmws::NewXMLTag('code', $response));
		return $dataobject->getDataObject();
	}

	/**
	* Get the invoice informations
	* @return amount - id - payment id - user_id
	*/

	public function Invoice(){

		$request_data 	= $this->RawXMWSToArray($this->wsdata);
		$contenttags 	= $this->XMLDataToArray($request_data['content']);
		$response 		= null;
		$row = module_controller::ApiInvoice(ws_generic::GetTagValue('token', $request_data['content']));

		if ($row != false){
			$response = ws_xmws::NewXMLTag('code','1');
			$response .= ws_xmws::NewXMLTag('invoice', 	
				ws_xmws::NewXMLTag('user',$row['inv_user']).
				ws_xmws::NewXMLTag('desc', $row['inv_desc']).
				ws_xmws::NewXMLTag('amount', $row['inv_amount']).
				ws_xmws::NewXMLTag('id', $row['inv_id']).
				ws_xmws::NewXMLTag('status', $row['inv_status'])
			);
		} else{
			$response = ws_xmws::NewXMLTag('code','0');
		}
	  		$dataobject = new runtime_dataobject();
			$dataobject->addItemValue('response', '');
			$dataobject->addItemValue('content', $response);
		return $dataobject->getDataObject();
	}


	//check if the username exits
	public function UsernameExits() {

		$request_data 	= $this->RawXMWSToArray($this->wsdata);
		$contenttags 	= $this->XMLDataToArray($request_data['content']);
		$response 		= null;
		$human 			= null;
		$UsernameExits 	= module_controller::getUserExits($contenttags['username']);

		switch ($UsernameExits) {
			case 1:
				$human = "Username is not valid";
			break;
			case 2:
				$human = "Username allready exits";
			break;
			case 3: 
				$human = "Username is available";
			break;
			case 4:
				$human = "Username is empty";
			break;
		}

		if(isset($UsernameExits)){
			$response = $UsernameExits;
		} 

		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', ws_xmws::NewXMLTag('code', $response) . ws_xmws::NewXMLTag('human', $human));

		return $dataobject->getDataObject();
	}
	
	/**
	* Get the settings value
	* @return xml tag
	*/

	public function Setting(){
		
		$request_data 	= $this->RawXMWSToArray($this->wsdata);
		$contenttags 	= $this->XMLDataToArray($request_data['content']);
		$response 		= null;
		$settings 		= (is_array($contenttags['settings']['setting'])) ? $contenttags['settings']['setting'] : $contenttags['settings'];
		
		if(is_array($settings)){
			foreach($settings as $key => $setting){
				$response .= ws_xmws::NewXMLTag($setting, module_controller::getConfig($setting));
			}
		}

		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', ws_xmws::NewXMLTag('settings',$response));
	return $dataobject->getDataObject();
	}


	/**
	* get the package informations and return in xml
	* @return price - name - id
	*/
	public function Package(){

		$request_data 	= $this->RawXMWSToArray($this->wsdata);
		$contenttags 	= $this->XMLDataToArray($request_data['content']);

		$row = module_controller::ApiPackage($contenttags['pk_id']);
		$response = ws_xmws::NewXMLTag('package', 	
			ws_xmws::NewXMLTag('name',$row['pk_name_vc']).
			ws_xmws::NewXMLTag('id', $row['pk_id_pk']).
			ws_xmws::NewXMLTag('domain', $row['pkp_domain']).
			ws_xmws::NewXMLTag('hosting', $row['pkp_hosting'])
		);

		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', $response);
		return $dataobject->getDataObject();

	}

	public function Pay(){

		$request_data 	= $this->RawXMWSToArray($this->wsdata);
		$contenttags 	= $this->XMLDataToArray($request_data['content']);
		$response 		= null;

		if(ws_generic::GetTagValue('account_id', $request_data['content'])){
			$row = module_controller::ApiAccount(ws_generic::GetTagValue('account_id', $request_data['content']));
			$response .= ws_xmws::NewXMLTag('account', 	
				ws_xmws::NewXMLTag('alias',$row['ac_user_vc']).
				ws_xmws::NewXMLTag('id', $row['ac_id_pk']).
				ws_xmws::NewXMLTag('email', $row['email'])
			);
		}

		if(ws_generic::GetTagValue('payment', $request_data['content'])){
			$rows = module_controller::ApiPaymentMethods(ws_generic::GetTagValue('payment', $request_data['content']));
			$payment_method = null;

			foreach ($rows as $row){

				$row['pm_data'] = str_replace('{{business}}', module_controller::getConfig('payment.email_paypal'), $row['pm_data']);
				$row['pm_data'] = str_replace('{{country}}', module_controller::getConfig('payment.country'), $row['pm_data']);
				$row['pm_data'] = str_replace('{{logo}}', module_controller::getConfig('payment.logo'), $row['pm_data']);
				$row['pm_data'] = str_replace('{{notify_url}}', module_controller::getConfig('payment.notify_url'), $row['pm_data']);
				$row['pm_data'] = str_replace('{{cs}}', module_controller::getConfig('payment.cs'), $row['pm_data']);
				$row['pm_data'] = str_replace('{{return_url}}', module_controller::getConfig('payment.return_url'), $row['pm_data']);

				$payment_method .= ws_xmws::NewXMLTag('payment',
					ws_xmws::NewXMLTag('id',$row['pm_id']).
					ws_xmws::NewXMLTag('name',$row['pm_name']).
					ws_xmws::NewXMLTag('data',"<![CDATA[".$row['pm_data']."]]>")
				);
			}
			$response .= ws_xmws::NewXMLTag('payments', $payment_method);
		}

		if(ws_generic::GetTagValue('profile_id', $request_data['content'])){
			$row = module_controller::ApiProfile(ws_generic::GetTagValue('profile_id', $request_data['content']));
			$response .= ws_xmws::NewXMLTag('profile', 	
				ws_xmws::NewXMLTag('fullname',$row['ud_fullname_vc'])
			);
		}

		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', $response);
		return $dataobject->getDataObject();
	}

	public function Payment(){
		$request_data 	= $this->RawXMWSToArray($this->wsdata);
		$contenttags 	= $this->XMLDataToArray($request_data['content']);
		$response 		= null;

		$response = module_controller::ApiPayment(
			ws_generic::GetTagValue('method', $request_data['content']),
			ws_generic::GetTagValue('user_id', $request_data['content']),
			ws_generic::GetTagValue('txn_id', $request_data['content']),
			ws_generic::GetTagValue('token', $request_data['content'])
		);

		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', ws_xmws::NewXMLTag('code',$response));
		return $dataobject->getDataObject();
	}

	/**
	 * Lets create the user
	 * @return 0: User creation fail
	 * @return 1: User created
	*/

	public function CreateClient() {
		$request_data = $this->RawXMWSToArray($this->wsdata);
		$contenttags 	= $this->XMLDataToArray($request_data['content']);
		$response_xml = NULL;

		//Check that a reseller have been set else use get from settings
		if (ws_generic::GetTagValue('resellerid', $request_data['content']) == "0"){
			$reseller_id = module_controller::getConfig("user.reseller_id");
		} else {
			$reseller_id = ws_generic::GetTagValue('resellerid', $request_data['content']);
		}

		//Check that a group id have been set else use get from settings
		if (ws_generic::GetTagValue('groupid', $request_data['content']) == "0"){
			$group_id = module_controller::getConfig("user.group_id");
		} else {
			$group_id = ws_generic::GetTagValue('groupid', $request_data['content']);
		}

        module_controller::ApiCreateClient($reseller_id, ws_generic::GetTagValue('username', $request_data['content']), ws_generic::GetTagValue('packageid', $request_data['content']), $group_id, ws_generic::GetTagValue('fullname', $request_data['content']), ws_generic::GetTagValue('email', $request_data['content']), ws_generic::GetTagValue('address', $request_data['content']), ws_generic::GetTagValue('postcode', $request_data['content']), ws_generic::GetTagValue('phone', $request_data['content']), ws_generic::GetTagValue('password', $request_data['content']));
            
		$response_xml = ws_xmws::NewXMLTag('uid', module_controller::getUsernameId(ws_generic::GetTagValue('username', $request_data['content'])));
		$response_xml .= ws_xmws::NewXMLTag('code', '1');
        
		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', $response_xml);
		return $dataobject->getDataObject();
	}
}