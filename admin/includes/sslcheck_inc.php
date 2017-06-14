<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return \Andyftw\SSLLabs\Model\Host
 */
function sslcheckGetData ()
{
    $host = 'andyfront.de'; // Shop::getUrl()
    $api  = new \Andyftw\SSLLabs\Api();
    $info = $api->analyze($host);

    if ($info->getStatus() === 'READY' && $endpoints = $info->getEndpoints()) {
        if (count($endpoints) > 0) {
            $endpoint = $endpoints[0];
            $details  = $api->getEndpointData($host, $endpoint->getIpAddress());
            $info->setEndpoints([$details]);
        }
    }

    return $info;
}

/**
 * @param $data
 * @return mixed
 */
function rebuildData ($data)
{
    $serializer = JMS\Serializer\SerializerBuilder::create()->build();

    return json_decode($serializer->serialize($data, 'json'));
}

/**
 * @return array|IOError
 */
function getSSLCheck()
{
    \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
    $smarty = Shop::Smarty();

    try {
        $data = sslcheckGetData();
        $smarty->assign('data', $data);
        $content = $smarty->fetch('tpl_inc/sslcheck.tpl');
        $result  = [
            'tpl' => $content,
            'data' => rebuildData($data),
            'type' => 'check'
        ];
    } catch (\Andyftw\SSLLabs\Exception\ApiException $e) {
        $result = new IOError($e->getMessage());
    }

    return $result;
}
