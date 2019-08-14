<?php

/**
 * HTTP client to communicate with MeinPaket webservice.
 * 
 * @category	Dhl
 * @package		Dhl_MeinPaket
 * @subpackage	Model_Client
 * @version		$Id$
 * @author		Timo Fuchs <timo.fuchs@aoemedia.de>
 */
class Dhl_MeinPaket_Model_Client_XmlOverHttp extends Varien_Object {
	/**
	 * Endpoint for production
	 *
	 * @var string
	 */
	const PRODUCTIVE_ENDPOINT = 'www.meinpaket.de/dealerapi/xml';
	/**
	 * Endpoint for sandbox
	 *
	 * @var string
	 */
	const SANDBOX_ENDPOINT = 'mp-api.mepa-home.de/dealerapi/xml';
	/**
	 * Suffix for async requests
	 *
	 * @var string
	 */
	const ASYNC_SUFFIX = 'Async';
	
	/**
	 * Configuration
	 *
	 * @var array
	 */
	private $config;
	
	/**
	 * Endpoint
	 *
	 * @var string
	 */
	private $endpoint;
	
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->config = array ('useragent' => 'Magento ' . Mage::getVersion() . ' Extension ' . Mage::helper('meinpaket/data')->getExtensionVersion());
		$scheme = Mage::getStoreConfigFlag ( 'meinpaket/endpoint/https' ) ? 'https://' : 'http://';
		$path = Mage::getStoreConfigFlag ( 'meinpaket/endpoint/sandbox' ) ? self::SANDBOX_ENDPOINT : self::PRODUCTIVE_ENDPOINT;
		$this->endpoint = $scheme . $path;
		
		$host = Mage::getStoreConfig ( 'meinpaket/endpoint/proxy_host' );
		$port = Mage::getStoreConfig ( 'meinpaket/endpoint/proxy_port' );
		
		if (Mage::getStoreConfigFlag ( 'meinpaket/endpoint/proxy' )) {
			$this->config ['adapter'] = 'Zend_Http_Client_Adapter_Proxy';
			
			if (strlen ( $host ) > 0) {
				$this->config ['proxy_host'] = $host;
			} else {
				$this->config ['proxy_host'] = '127.0.0.1';
			}
			
			if (strlen ( $port ) > 0) {
				$this->config ['proxy_port'] = $port;
			} else {
				$this->config ['proxy_port'] = 8888;
			}
		}
	}
	
	/**
	 * Sends raw XML and returns the response body.
	 *
	 * @param Dhl_MeinPaket_Model_Xml_AbstractXmlRequest $xml        	
	 * @throws Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException
	 * @throws Dhl_MeinPaket_Model_Client_HttpTimeoutException
	 * @return Dhl_MeinPaket_Model_Xml_Response_Abstract
	 */
	public function send($xml, $async = false) {
		$xmlData = $xml instanceof Dhl_MeinPaket_Model_Xml_AbstractXmlPartial ? $xml->__toString () : $xml;
		
		$url = $this->endpoint . ($async ? self::ASYNC_SUFFIX : '');
		$httpClient = new Zend_Http_Client ( $url, $this->config );
		//Zend_Debug::dump($httpClient);die;
		$httpClient->setMethod ( Zend_Http_Client::POST );
		$httpClient->setRawData ( $xmlData );
		
		/* @var $log Dhl_MeinPaket_Model_Log */
		$log = Mage::getModel ( 'meinpaket/log' );
		$log->setSend ( $xmlData );
		$log->setCreatedAt ( Varien_Date::now () );
		if ($xml instanceof Dhl_MeinPaket_Model_Xml_Request_AsynchronousStatusRequest) {
			$log->setRequestId ( $xml->getRequestId () );
		}
		$log->save ();
		
		try {
			$serverResponse = $httpClient->request ();
		} catch ( Zend_Http_Client_Adapter_Exception $e ) {
			Mage::logException ( $e );
			$log->setError ( $e->getMessage () );
			$log->save ();
			throw new Dhl_MeinPaket_Model_Client_HttpTimeoutException ( $e->getMessage () );
		}
		
		$body = $serverResponse->getBody ();
		
		$body4Mysql = substr ( $body, 0, 1024 * 1024 * 4 );
		$log->setReceived ( $body4Mysql );
		$status = $serverResponse->getStatus ();
		
		if ($status !== 200) {
			$log->setError ( 'FAILURE: Client returned HTTP return code "' . $status . '".' );
			$log->save ();
			throw new Dhl_MeinPaket_Model_Client_BadHttpReturnCodeException ( $status, 'FAILURE: Client returned HTTP return code "' . $status . '".' );
		}
		$log->save ();
		
		$dom = new DOMDocument ();
		$response = null;
		
		try {
			$valid = $dom->loadXML ( $body );
			if (! $valid) {
				throw new Dhl_MeinPaket_Model_Xml_InvalidXmlException ( "Invalid XML" );
			}
			
			/* @var $parser Dhl_MeinPaket_Model_Xml_XmlResponseParser */
			$parser = Mage::getModel ( 'meinpaket/xml_xmlResponseParser' );
			$response = $parser->parseResponse ( $dom );
			
			if ($response instanceof Dhl_MeinPaket_Model_Xml_Response_AsynchronousStatusResponse) {
				$log->setRequestId ( $response->getRequestId () );
			}
			
			$log->setError ( $response->getErrorString () );
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			$log->setError ( $e->getMessage () );
		}
		$log->save ();
		
		return $response;
	}
}
