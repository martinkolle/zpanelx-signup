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


	public function CreateInvoice(){

		$response = null;
	    $request_data = $this->RawXMWSToArray($this->wsdata);
		$contenttags = $this->XMLDataToArray($request_data['content']);
		
		if(module_controller::ExecuteCreateInvoice( ws_generic::GetTagValue('user_id', $request_data['content']), 
													ws_generic::GetTagValue('price', $request_data['content']),
													ws_generic::GetTagValue('token', $request_data['content']),
													ws_generic::GetTagValue('invoice_nextdue', $request_data['content']),
													ws_generic::GetTagValue('invoice_period', $request_data['content'])
												)){
			if(module_controller::ExecuteCreateAccountInvoice(ws_generic::GetTagValue('price', $request_data['content']),
															  ws_generic::GetTagValue('invoice_nextdue', $request_data['content']),
															  ws_generic::GetTagValue('invoice_period', $request_data['content']),
															  ws_generic::GetTagValue('user_id', $request_data['content'])
															)){
				$response = '1';
			} else{
				$response = "Creating account invoice failed!";
			}
		} else{
			$response = "Creating invoice failed";
		}
		$dataobject = new runtime_dataobject();
		$dataobject->addItemValue('response', '');
		$dataobject->addItemValue('content', $response);
		return $dataobject->getDataObject();

	}
}