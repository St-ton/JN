<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Marketplace
 */
final class Marketplace
{
    const API_URL = 'https://api.jtl-software.de/';

    const API_TOKEN = '438ghKLb';

    /**
     * Fetching marketplace api extension data
     *
     * @param MarketplaceQuery $query
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @return mixed - Returns the value encoded in json in appropriate PHP type. Values true,
     * false and null (case-insensitive) are returned as TRUE, FALSE and NULL respectively.
     * NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
     */
    public function fetch($query)
    {
        if (!$query instanceof MarketplaceQuery) {
            throw new InvalidArgumentException('Paramter query must be an instance of MarketplaceQuery');
        }
        $url      = sprintf("%s?s=%s&c=marketplace%s", self::API_URL, self::API_TOKEN, $query);
        $response = Communication::postData($url, [], false);
        if (!$response) {
            throw new UnexpectedValueException('Empty api response');
        }

        return json_decode($response);
    }
}
