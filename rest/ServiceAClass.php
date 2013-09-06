<?php

require('AbstractClass.php');

class ServiceAClass extends AbstractClass
{
	
    public function __construct($config)
    {
		$this->config = $config;
	}
    
    /**
     * Initiate send Order details to webservice
     * 
     * @param  Order $order_data
     * @param  Order_Items $items_data
     * @return Response Webservice Order ID
     */
    public function initiate($order_data)
    {
		try {
			$config = $this->config;
			$config_data = explode(';',$config);
			foreach($config_data as $sys) {
				//search for Webservice URL
				if(!$sys) continue;
				$val = explode('=',$sys);
				$url = $val[0]=='url' ? $val[1] : false;
			}
			if($url) {
				$datas = array('content'=>$order_data);
				$ch = curl_init ($url);
				curl_setopt ($ch, CURLOPT_POST, true);
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $datas);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
				$returndata = curl_exec ($ch);
				return $returndata;
			}
		} catch(Exception $e) {
			//print errors
		}
    }
    
    /**
     * 
     * @param Returned API Order data $order_api_data
     * @return Response API Order data
     */
    public function getOrderStatus($order_api_data)
    {
		try {
			$config = $this->config;
			$config_data = explode(';',$config);
			foreach($config_data as $sys) {
				//search for Webservice URL
				if(!$sys) continue;
				$val = explode('=',$sys);
				$url = $val[0]=='url' ? $val[1] : false;
			}
			$order_id = json_decode($order_api_data);
			$url = $url.'?orderid='.$order_id;
			if($url) {
				$ch = curl_init ($url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
				$returndata = curl_exec ($ch);
				return $returndata;
			}
		} catch(Exception $e) {
			//print errors
		}
	}

}
