<?php

require_once __DIR__. '/ipl_xml_api.php';

/**
 * class ipl_xml_request
 *
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial
 */
class ipl_xml_request
{
    /**
     * @var string
     */
    private $request_xml = '';

    /**
     * @var string
     */
    private $response_xml = '';

    /**
     * @var string
     */
    protected $_ipl_request_url = '';

    /**
     * @var array
     */
    protected $_default_params = [];

    /**
     * @var array
     */
    protected $_status_info = [];

    /**
     * @var array
     */
    protected $_validation_errors = [];

    /**
     * @var
     */
    public $status;

    /**
     * used for extended logging purpose
     * @var array
     */
    protected $aTraceData = [];

    /**
     * @var
     */
    private $_username;

    /**
     * @var
     */
    private $_password;

    /**
     * @return bool
     */
    public function has_error()
    {
        return $this->_status_info['error_code'] > 0;
    }

    /**
     * @return mixed
     */
    public function get_error_code()
    {
        return $this->_status_info['error_code'];
    }

    /**
     * @return mixed
     */
    public function get_customer_error_message()
    {
        return $this->_status_info['customer_message'];
    }

    /**
     * @return mixed
     */
    public function get_merchant_error_message()
    {
        return $this->_status_info['merchant_message'];
    }

    /**
     * @return bool
     */
    public function has_validation_errors()
    {
        return count($this->_validation_errors['customer']) > 0;
    }

    /**
     * Returns an array of validation errors that can be visible to customer.
     * @return array
     */
    public function get_customer_validation_errors()
    {
        return $this->_validation_errors['customer'];
    }

    /**
     * Returns an array of validation errors that should be only visible to merchant.
     * @return array
     */
    public function get_merchant_validation_errors()
    {
        return $this->_validation_errors['merchant'];
    }

    /**
     * @return string
     */
    public function get_request_xml()
    {
        return $this->request_xml;
    }

    /**
     * @return string
     */
    public function get_response_xml()
    {
        return $this->response_xml;
    }

    /**
     * ipl_xml_request constructor.
     * @param $ipl_request_url
     */
    public function __construct($ipl_request_url)
    {
        $this->_ipl_request_url = $ipl_request_url;
    }

    /**
     * @param $mid
     * @param $pid
     * @param $bpsecure
     */
    public function set_default_params($mid, $pid, $bpsecure)
    {
        $this->_default_params['mid']      = $mid;
        $this->_default_params['pid']      = $pid;
        $this->_default_params['bpsecure'] = $bpsecure;
    }

    /**
     * @param $username
     * @param $password
     */
    public function set_basic_auth_params($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    /**
     * @param $sTraceId
     * @return $this
     */
    public function setTraceId($sTraceId)
    {
        $this->aTraceData['trace_id'] = $sTraceId;

        return $this;
    }

    /**
     * set trace data array
     * <pre>
     *  array(
     *      'shop_type'      => "Name of the Shopsystem",
     *      'shop_version'   => "Version of the Shopsystem",
     *      'shop_domain'    => "Domain the Shop is running on",
     *      'plugin_version' => "Version of the Plugin if one is used",
     *      'trace_id'       => "Unique identifier of the customer e.g. a hash of the customers session id"
     *  );
     * </pre>
     *
     * @param array $aTraceData
     *
     * @return ipl_xml_request
     */
    public function setTraceData($aTraceData)
    {
        $this->aTraceData = array_merge($this->aTraceData, $aTraceData);

        return $this;
    }

    /**
     * @return array
     */
    protected function getTraceData()
    {
        if ($_SESSION !== null && !isset($this->aTraceData['trace_id'])) {
            $this->aTraceData['trace_id'] = ipl_create_hash(session_id());
        }
        ksort($this->aTraceData);

        return $this->aTraceData;
    }

    /**
     * This must be overridden in deriving class
     * @abstract
     * @return bool
     */
    protected function _send()
    {
        return false;
    }

    /**
     * This must be overridden in deriving class
     * @abstract
     * @param $data
     */
    protected function _process_response_xml($data)
    {
    }

    /**
     * This must be overridden in deriving class
     * @abstract
     * @param $data
     */
    protected function _process_error_response_xml($data)
    {
    }

    /**
     * @return string
     */
    public function get_internal_error_msg()
    {
        return ipl_core_get_internal_error_msg();
    }

    /**
     * @return bool If false, there is no error.
     * @throws Exception
     */
    public function send()
    {
        $res = $this->_send();

        if (!$res || ipl_core_has_internal_error()) {
            $errorMsg = ipl_core_get_internal_error_msg();

            if (!empty($errorMsg)) {
                throw new Exception($errorMsg);
            } else {
                throw new Exception('Internal error with unknown cause occurred.');
            }
        }

        // Get status info data structure
        $this->_status_info = ipl_core_get_api_error_info();

        $this->request_xml  = $res[0];
        $this->response_xml = $res[1];

        if (!ipl_core_has_api_error()) {
            $this->_process_response_xml($res[2]);
        } else {
            $this->_process_error_response_xml($res[2]);
        }

        return false; # no error
    }
}
