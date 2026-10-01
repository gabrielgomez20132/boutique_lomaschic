<?php

namespace App\Classes;

use SoapClient;

error_reporting(0);

$produccion = false;

if ($produccion) {
	define("WSFEWSDL", "wsfev1Prod.wsdl");    # WSFFEV1 WSDL --- Produccion https://servicios1.afip.gov.ar/wsfev1/service.asmx?WSDL
	define("WSFEURL", "https://servicios1.afip.gov.ar/wsfev1/service.asmx"); // produccion  
} else {
	define("WSFEWSDL" ,"wsfev1.wsdl");    # WSFFEV1 WSDL --- Testing
	define("WSFEURL", "https://wswhomo.afip.gov.ar/wsfev1/service.asmx"); // homologacion wsfev1 (testing)
}

class WSFEV1 {
	const CUIT 	= 20303538950 ;    # CUIT del emisor
	
	const TA 	= "xml/TA.xml";    # Ubicacion Ticket de Acceso
	
	const LOG_XMLS = true;         
	
	//Path completo
	//private $path = '/www/afipfev1/'; //linux
	private $path = 'C:/Xampp/htdocs/JABlack Soft/Control-Soft/app/Classes/'; //caso windows (no importa que las barras esten como en linux)
	
	/*
	* manejo de errores
	*/
	public $error = '';
	public $ObsCode = '';
	public $ObsMsg = '';
	public $Code = '';
	public $Msg = '';
	

// Cliente SOAP
	private $client;
  	private $TA;
  
	
	public function __construct()
	{
    
    	ini_set("soap.wsdl_cache_enabled", "0");    
    
		// validar archivos necesarios
		if (!file_exists($this->path.WSFEWSDL)) $this->error .= " Failed to open ".WSFEWSDL;
		
		if(!empty($this->error)) {
			dd('WSFE class. Faltan archivos necesarios para el funcionamiento: ' . $this->error);
		}        
		
		$this->client = new SoapClient($this->path.WSFEWSDL, array( 
					'soap_version' => SOAP_1_2,
					'location'     => WSFEURL,
					'exceptions'   => 0,
					'trace'        => 1)
		); 
	}
  
	// Chequea errores

	private function _checkErrors($results, $method)
	{
		if (self::LOG_XMLS) {
			file_put_contents("xml/request-".$method.".xml",$this->client->__getLastRequest());
			file_put_contents("xml/response-".$method.".xml",$this->client->__getLastResponse());
		}
		
		if (is_soap_fault($results)) {
			dd('SOAP error. WSFE class. FaultString: ' . $results->faultcode.' '.$results->faultstring);
		}
		
		if ($method == 'FEDummy') {
			return;
		}
		
		$XXX = $method.'Result';
		
		if ($results->$XXX->Errors->Err->Code != 0) {
			$this->error = "Method=$method errcode=".$results->$XXX->Errors->Err->Code." errmsg=".$results->$XXX->Errors->Err->Msg;
		}
    	
	
		//asigna error a variable
		if ($method == 'FECAESolicitar') {
			if ($results->$XXX->FeDetResp->FECAEDetResponse->Observaciones->Obs->Code){	
				$this->ObsCode = $results->$XXX->FeDetResp->FECAEDetResponse->Observaciones->Obs->Code;
				$this->ObsMsg = $results->$XXX->FeDetResp->FECAEDetResponse->Observaciones->Obs->Msg;
			}
			
			// if ($results->$XXX->FeDetResp->FECAEDetResponse->Observaciones->Obs[0]->Code){	
			// 	$this->ObsCode = $results->$XXX->FeDetResp->FECAEDetResponse->Observaciones->Obs[0]->Code;
			// 	$this->ObsMsg = $results->$XXX->FeDetResp->FECAEDetResponse->Observaciones->Obs[0]->Msg;
			// }
		}
		$this->Code = $results->$XXX->Errors->Err->Code;
		$this->Msg = $results->$XXX->Errors->Err->Msg;	
		//fin asigna error a variable
		
		return $results->$XXX->Errors->Err->Code != 0 ? true : false;
	}

	
	//Abre TA
	public function openTA()
	{
		$this->TA = simplexml_load_file($this->path.self::TA);

		return $this->TA == false ? false : true;
	}
  
	//Funcion para solicitar ultimo comp autorizado

	public function FECompUltimoAutorizado($ptovta, $tipo_cbte)
	{
		$results = $this->client->FECompUltimoAutorizado(
			array('Auth'=>array('Token' => $this->TA->credentials->token,
								'Sign' => $this->TA->credentials->sign,
								'Cuit' => self::CUIT),
				'PtoVta' => $ptovta,
				'CbteTipo' => $tipo_cbte));
				
		$e = $this->_checkErrors($results, 'FECompUltimoAutorizado');
		
		return $e == false ? $results->FECompUltimoAutorizadoResult->CbteNro : false;
	} //end function FECompUltimoAutorizado
  
	/*
	* Retorna el ultimo comprobante autorizado para el tipo de comprobante /cuit / punto de venta ingresado.
	*/ 
	public function recuperaLastCMP($ptovta, $tipo_cbte)
	{
		$results = $this->client->FERecuperaLastCMPRequest(
			array('argAuth' =>  array('Token' => $this->TA->credentials->token,
									'Sign' => $this->TA->credentials->sign,
									'cuit' => self::CUIT),
				'argTCMP' => array('PtoVta' => $ptovta,
									'TipoCbte' => $tipo_cbte)));
		$e = $this->_checkErrors($results, 'FERecuperaLastCMPRequest');
		
		return $e == false ? $results->FERecuperaLastCMPRequestResult->cbte_nro : false;
	} //end function recuperaLastCMP

	
	/*
	* Solicitud CAE y fecha de vencimiento 
	*/	
	public function FECAESolicitar($cbte, $ptovta, $regfe, $regfeasoc, $regfetrib, $regfeiva)
	{
		if ($cbte == "0") { // para 
			$cbte = "1";
			
		}
		
		$params = array(
			'Auth' =>
			array(
				'Token' => $this->TA->credentials->token,
				'Sign' => $this->TA->credentials->sign,
				'Cuit' => self::CUIT
			),
			'FeCAEReq' =>
			array(
				'FeCabReq' =>
				array(
					'CantReg' => 1,
					'PtoVta' => $ptovta,
					'CbteTipo' => $regfe['CbteTipo']
				),
				'FeDetReq' =>
				array(
					'FECAEDetRequest' =>
					array(
						'Concepto' => $regfe['Concepto'],
						'DocTipo' => $regfe['DocTipo'],
						'DocNro' => $regfe['DocNro'],
						'CbteDesde' => $cbte,
						'CbteHasta' => $cbte,
						'CbteFch' => $regfe['CbteFch'],
						'ImpNeto' => $regfe['ImpNeto'],
						'ImpTotConc' => $regfe['ImpTotConc'],
						'ImpIVA' => $regfe['ImpIVA'],
						'ImpTrib' => $regfe['ImpTrib'],
						'ImpOpEx' => $regfe['ImpOpEx'],
						'ImpTotal' => $regfe['ImpTotal'],
						'FchServDesde' => $regfe['FchServDesde'], //null
						'FchServHasta' => $regfe['FchServHasta'], //null
						'FchVtoPago' => $regfe['FchVtoPago'], //null
						'MonId' => $regfe['MonId'], //PES 
						'MonCotiz' => $regfe['MonCotiz'], //1 
						'CbtesAsoc' =>
						array(
							'CbteAsoc' =>
							array(
								'Tipo' =>  $regfeasoc['Tipo'],
								'PtoVta' => $regfeasoc['PtoVta'],
								'Nro' => $regfeasoc['Nro']
							),
						),
						'Tributos' =>
						array(
							'Tributo' =>
							array(
								'Id' =>  $regfetrib['Id'],
								'Desc' => $regfetrib['Desc'],
								'BaseImp' => $regfetrib['BaseImp'],
								'Alic' => $regfetrib['Alic'],
								'Importe' => $regfetrib['Importe']
							),
						),
						'Iva' =>
						array(
							'AlicIva' =>
							array(
								'Id' => $regfeiva['Id'],
								'BaseImp' => $regfeiva['BaseImp'],
								'Importe' => $regfeiva['Importe']
							),
						),
					),
				),
			),
		);
		//Para Facturas, elimino el objeto CbtesAsoc (los null los puse yo para esto nada más)
		if ($regfeasoc['Tipo'] == null && $regfeasoc['PtoVta'] == null && $regfeasoc['Nro'] == null) 
		{
			unset($params['FeCAEReq']['FeDetReq']['FECAEDetRequest']['CbtesAsoc']);
		}
		
		//Para Monotributo, elimino el objeto IVA (los null los puse yo para esto nada más)
		if ($regfeiva['Id'] == null && $regfeiva['BaseImp'] == null && $regfeiva['Importe'] == null)
		{
			unset($params['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Iva']);
		}
	
		$results = $this->client->FECAESolicitar($params);

		$e = $this->_checkErrors($results, 'FECAESolicitar');
		
		//asigno respuesta 
		$resp_cae = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE;
		$resp_caefvto = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto;

		return $e == false ? Array( 'cae' => $resp_cae, 'fecha_vencimiento' => $resp_caefvto ): false;
	} //end function FECAESolicitar
	
} // class

?>