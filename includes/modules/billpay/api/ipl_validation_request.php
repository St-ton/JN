<?php

require_once __DIR__ . '/ipl_xml_request.php';

/**
 * class ipl_validation_request
 *
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial
 */
class ipl_validation_request extends ipl_xml_request
{
    /**
     * @var array
     */
    private $_customer_details = [];

    /**
     * @var array
     */
    private $_shippping_details = [];

    /**
     * @param $customer_id
     * @param $customer_type
     * @param $salutation
     * @param $title
     * @param $first_name
     * @param $last_name
     * @param $street
     * @param $street_no
     * @param $address_addition
     * @param $zip
     * @param $city
     * @param $country
     * @param $email
     * @param $phone
     * @param $cell_phone
     * @param $birthday
     * @param $language
     * @param $ip
     */
    public function set_customer_details(
        $customer_id,
        $customer_type,
        $salutation,
        $title,
        $first_name,
        $last_name,
        $street,
        $street_no,
        $address_addition,
        $zip,
        $city,
        $country,
        $email,
        $phone,
        $cell_phone,
        $birthday,
        $language,
        $ip
    ) {
        $this->_customer_details['customerid']      = $customer_id;
        $this->_customer_details['customertype']    = $customer_type;
        $this->_customer_details['salutation']      = $salutation;
        $this->_customer_details['title']           = $title;
        $this->_customer_details['firstName']       = $first_name;
        $this->_customer_details['lastName']        = $last_name;
        $this->_customer_details['street']          = $street;
        $this->_customer_details['streetNo']        = $street_no;
        $this->_customer_details['addressAddition'] = $address_addition;
        $this->_customer_details['zip']             = $zip;
        $this->_customer_details['city']            = $city;
        $this->_customer_details['country']         = $country;
        $this->_customer_details['email']           = $email;
        $this->_customer_details['phone']           = $phone;
        $this->_customer_details['cellPhone']       = $cell_phone;
        $this->_customer_details['birthday']        = $birthday;
        $this->_customer_details['language']        = $language;
        $this->_customer_details['ip']              = $ip;
    }

    /**
     * @param      $use_billing_address
     * @param null $salutation
     * @param null $title
     * @param null $first_name
     * @param null $last_name
     * @param null $street
     * @param null $street_no
     * @param null $address_addition
     * @param null $zip
     * @param null $city
     * @param null $country
     * @param null $phone
     * @param null $cell_phone
     */
    public function set_shipping_details(
        $use_billing_address,
        $salutation = null,
        $title = null,
        $first_name = null,
        $last_name = null,
        $street = null,
        $street_no = null,
        $address_addition = null,
        $zip = null,
        $city = null,
        $country = null,
        $phone = null,
        $cell_phone = null
    ) {
        $this->_shippping_details['useBillingAddress'] = $use_billing_address ? '1' : '0';
        $this->_shippping_details['salutation']        = $salutation;
        $this->_shippping_details['title']             = $title;
        $this->_shippping_details['firstName']         = $first_name;
        $this->_shippping_details['lastName']          = $last_name;
        $this->_shippping_details['street']            = $street;
        $this->_shippping_details['streetNo']          = $street_no;
        $this->_shippping_details['addressAddition']   = $address_addition;
        $this->_shippping_details['zip']               = $zip;
        $this->_shippping_details['city']              = $city;
        $this->_shippping_details['country']           = $country;
        $this->_shippping_details['phone']             = $phone;
        $this->_shippping_details['cellPhone']         = $cell_phone;
    }

    /**
     * @return array|bool
     */
    protected function _send()
    {
        return ipl_core_send_validation_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_customer_details,
            $this->_shippping_details
        );
    }

    /**
     * @param $data
     */
    protected function _process_response_xml($data)
    {
        // Nothing to do here
    }
}
