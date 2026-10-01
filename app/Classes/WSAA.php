<?php

namespace App\Classes;

use SoapClient;
use SimpleXMLElement;

$produccion = false;
dd("llegue a afip");

if ($produccion) {
	define("CERT", "keys/DonAgustin_Prod.crt");
	define("URL", "https://wsaa.afip.gov.ar/ws/services/LoginCms"); // produccion
	define("WSDL", "wsaaProd.wsdl");  			# WSDL	produccion: https://wsaa.afip.gov.ar/ws/services/LoginCms?WSDL
} else {
	define("CERT", "keys/DonAgustin_Test.crt");        	//Certificado AFIP
	define("URL", "https://wsaahomo.afip.gov.ar/ws/services/LoginCms"); // homologacion (testing)
	define("WSDL", "wsaa.wsdl"); 
}

class WSAA {

	const PRIVATEKEY = "keys/MiClavePrivada.key";  	//Clave privada con la que se genero el requerimiento de cerificado
	const PASSPHRASE = "";         				
	const PROXY_ENABLE = false;
	const TA 	= "xml/TA.xml";        			# Ticket de Acceso
	
	//Path completo
	//private $path = './'; //linux
	private $path = 'C:/Xampp/htdocs/JABlack Soft/Control-Soft/app/Classes/'; // En windows poner path real con barras como en linux /
	
	/*
	* manejo de errores
	*/
	public $error = '';

	//Cliente SOAP
	private $client;
	
	private $service; 
  
  	public function __construct($service = 'wsfe') 
	{
		$this->service = $service;    
		
		// seteos en php
		ini_set("soap.wsdl_cache_enabled", "0");    
		
		// validar archivos necesarios
		if (!file_exists($this->path.CERT)) $this->error .= " Failed to open ".CERT;
		if (!file_exists($this->path.self::PRIVATEKEY)) $this->error .= " Failed to open ".self::PRIVATEKEY;
		if (!file_exists($this->path.WSDL)) $this->error .= " Failed to open ".WSDL;
		
		if(!empty($this->error)) {
			dd('WSAA class. Faltan archivos necesarios para el funcionamiento: ' . $this->error);
			//throw new Exception('WSAA class. Faltan archivos necesarios para el funcionamiento');
		}
		
		$this->client = new SoapClient($this->path.WSDL, array(
					'soap_version'   => SOAP_1_2,
					'location'       => URL,
					'trace'          => 1,
					'exceptions'     => 0
					)
		);
	}
  
	/*
	* Crea el archivo xml de TRA
	*/
	private function create_TRA()
	{
		$TRA = new SimpleXMLElement(
				'<?xml version="1.0" encoding="UTF-8"?>' .
				'<loginTicketRequest version="1.0">'.
				'</loginTicketRequest>');
		$TRA->addChild('header');
		$TRA->header->addChild('uniqueId', date('U'));
		$TRA->header->addChild('generationTime', date('c',date('U')-60));
		$TRA->header->addChild('expirationTime', date('c',date('U')+60));
		$TRA->addChild('service', $this->service);
		$TRA->asXML($this->path.'xml/TRA.xml');
	}
  
	/*
	* This functions makes the PKCS#7 signature using TRA as input file, CERT and
	* PRIVATEKEY to sign. Generates an intermediate file and finally trims the 
	* MIME heading leaving the final CMS required by WSAA.
	* 
	* devuelve el CMS
	*/
	private function sign_TRA()
	{
		$STATUS = openssl_pkcs7_sign($this->path . "xml/TRA.xml", $this->path . "xml/TRA.tmp", "file://" . $this->path.CERT,
			array("file://" . $this->path.self::PRIVATEKEY, self::PASSPHRASE),
			array(),
			!PKCS7_DETACHED
		);
		
		if (!$STATUS)
			dd("ERROR generating PKCS#7 signature");
		
		$inf = fopen($this->path."xml/TRA.tmp", "r");
		$i = 0;
		$CMS = "";
		while (!feof($inf)) { 
			$buffer = fgets($inf);
			if ( $i++ >= 4 ) $CMS .= $buffer;
		}
		
		fclose($inf);
		unlink($this->path."xml/TRA.tmp");
		
		return $CMS;
	}
  
	//Obtengo token y Sign

	private function call_WSAA($cms)
	{     
		$results = $this->client->loginCms(array('in0' => $cms));
		// para logueo
		file_put_contents($this->path."request-loginCms.xml", $this->client->__getLastRequest());
		file_put_contents($this->path."response-loginCms.xml", $this->client->__getLastResponse());

		if (is_soap_fault($results)) 
			dd("SOAP Fault: ".$results->faultcode.': '.$results->faultstring);
		return $results->loginCmsReturn;
	}
  
	// Array a XML
	private function xml2array($xml) 
	{    
		$json = json_encode( simplexml_load_string($xml));
		return json_decode($json, TRUE);
	}    
  
	//Genero TA
	public function generar_TA()
	{
		$this->create_TRA();
		$TA = $this->call_WSAA( $this->sign_TRA() );
						
		if (!file_put_contents($this->path.self::TA, $TA))
			dd("Error al generar al archivo TA.xml");

		$this->TA = $this->xml2Array($TA);
		
		return true;
	}
  
	//Expiracion del Tiket de Acceso TA
	public function get_expiration() 
	{    
		// Primero buscamos que exista por lo menos
		$ruta_TA_file = $this->path.self::TA;
		// Chequeamos que existe en la carpeta
		if (file_exists($ruta_TA_file)) 
		{ // si existe y no esta en memoria abrirlo
			if(empty($this->TA)) 
			{
				$TA_file = file($ruta_TA_file, FILE_IGNORE_NEW_LINES);
				if($TA_file) 
				{
					$TA_xml = '';
					for($i=0; $i < sizeof($TA_file); $i++)
						$TA_xml.= $TA_file[$i];        
					$this->TA = $this->xml2Array($TA_xml);
					$r = $this->TA['header']['expirationTime'];
				} 
				else 
				{
					$r = false;
				}
			} 
			else 
			{
				$r = $this->TA['header']['expirationTime'];
			}
		} 
		else // no existe
		{
			$r = false;
		}

		if ($r) {
			$r = str_replace("T"," ",substr($this->TA['header']['expirationTime'],0,19));
		}
		
		return $r;
	}
}
?>