<?php

/**
 * @package zpanelx
 * @subpackage modules
 * @author Martin Kollerup
 * @copyright martinkole
 * @link http://www.zpanelcp.com/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */

class webservice extends ws_xmws {

	/**
	* Create the invoice
	* @return 1: succes
	* @return 2: Account invoice failed
	* @return 0: Invoice creation failed
	*/

	public function CreateInvoice(){

		$response = null;
	    $request_data = $this->RawXMWSToArray($this->wsdata);
		$contenttags = $this->XMLDataToArray($request_data['content']);
		
		if(module_controller::ExecuteCreateInvoice( 
			ws_generic::GetTagValue('user_id', $request_data['content']), 
			ws_generic::GetTagValue('price', $request_data['content']),
			ws_generic::GetTagValue('token', $request_data['content']),
			ws_generic::GetTagValue('invoice_nextdue', $request_data['content']),
			ws_generic::GetTagValue('invoice_period', $request_data['content'])
			)){
			if(module_controller::ExecuteCreateAccountInvoice(
				ws_generic::GetTagValue('price', $request_data['content']),
				ws_generic::GetTagValue('invoice_nextdue', $request_data['content']),
				ws_generic::GetTagValue('invoice_period', $request_data['content']),
				ws_generic::GetTagValue('user_id', $request_data['content'])
				)){
				$response = "1";
			} else{
				$response = "2";
			}
		} else{
			$response = "0";
		}
		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', ws_xmws::NewXMLTag('code', $response));
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
			ws_xmws::NewXMLTag('pm', $row['pk_price_pm']).
			ws_xmws::NewXMLTag('pq', $row['pk_price_pq']).
			ws_xmws::NewXMLTag('py', $row['pk_price_py'])
		);

		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', $response);
		return $dataobject->getDataObject();

    }

    /**
    * Get the invoice informations
    * @return amount - id - payment id - user_id
    */

    public function Invoice(){

    	$request_data 	= $this->RawXMWSToArray($this->wsdata);
		$contenttags 	= $this->XMLDataToArray($request_data['content']);

		$row = module_controller::ApiInvoice($contenttags['token']);
		if ($row != false){
			$response = ws_xmws::NewXMLTag('code','1');
			$response .= ws_xmws::NewXMLTag('invoice', 	
				ws_xmws::NewXMLTag('user',$row['inv_user']).
				ws_xmws::NewXMLTag('amount', $row['inv_amount']).
				ws_xmws::NewXMLTag('id', $row['inv_id']).
				ws_xmws::NewXMLTag('payment_id', $row['inv_payment_id'])
			);
		} else{
			$response = ws_xmws::NewXMLTag('code','0');
		}

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
				ws_xmws::NewXMLTag('email', $row['ac_email_vc']).
				ws_xmws::NewXMLTag('package_id', $row['ac_package_fk']).
				ws_xmws::NewXMLTag('payperiod', $row['ac_invoice_period'])
			);
		}

		if(ws_generic::GetTagValue('payment', $request_data['content'])){
			$rows = module_controller::ApiPayment_method(ws_generic::GetTagValue('payment', $request_data['content']));
			$payment_method = null;

			foreach ($rows as $row){
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
			ws_generic::GetTagValue('user_id', $request_data['content']),
			ws_generic::GetTagValue('txn_id', $request_data['content']),
			ws_generic::GetTagValue('token', $request_data['content'])
		);

		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', ws_xmws::NewXMLTag('code',$response));
		return $dataobject->getDataObject();
    }
}