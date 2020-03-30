<?php

namespace JTL\Plugin\Payment;

/**
 * Class ServerMethod
 * @package JTL\Plugin\Payment
 */
class ServerMethod extends Method
{
    /**
     * e.g. ssl://www.moneybookers.com
     *
     * @var string
     */
    public $hostname;

    /**
     * e.g. www.moneybookers.com
     *
     * @var string
     */
    public $host;

    /**
     * e.g. /app/test_payment.pl
     *
     * @var string
     */
    public $path;

    /**
     * @inheritDoc
     */
    public function init(int $nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->hostname = '';
        $this->host     = '';
        $this->path     = '';

        return $this;
    }

    /**
     * @param array  $fields - associated array (Key=>Value)
     * @param bool   $bUTF8
     * @param bool   $bLogging
     * @param string $cLogPfad
     * @return array - array('status', 'header', 'body') status = error|success
     */
    public function postRequest(array $fields, bool $bUTF8 = true, bool $bLogging = false, string $cLogPfad = ''): array
    {
        // Workaround: http://bugs.php.net/bug.php?id=39039 (see last line of Method)
        $tempErrorLevel = \error_reporting(0);

        $socket = \fsockopen($this->hostname, \SPM_PORT, $errNo, $errStr, \SPM_TIMEOUT);

        // Socket Error
        if (!$socket) {
            //echo $errStr;
            return ['status' => 'error'];
        }

        // Request
        $request   = '';
        $cEncoding = 'UTF-8';
        if ($bUTF8) {
            $fields = \array_map('\urlencode', \array_map('\utf8_encode', $fields));
        } else {
            $fields    = \array_map('\urlencode', $fields);
            $cEncoding = 'ISO-8859-1';
        }

        foreach ($fields as $key => $value) {
            $request .= '&' . $key . '=' . $value;
        }
        // Send
        $header = 'POST ' . $this->path . " HTTP/1.1\r\n"
            . 'Host: ' . $this->host . "\r\n"
            . 'Content-Type: application/x-www-form-urlencoded;charset=' . $cEncoding . "\r\n"
            . 'Content-Length: ' . \strlen($request) . "\r\n"
            . "Connection: close\r\n\r\n";
        \fwrite($socket, $header);
        \fwrite($socket, $request);
        // Recieve
        $reponseHeader = '';
        $reponseBody   = '';
        $isBody        = false;
        $isChunked     = false;
        while (\feof($socket) === false) {
            $line = \fgetss($socket, 256);
            $line = \trim($line);

            if (($isBody === false) && ($line === '')) {
                $isBody = true;
            }

            if ($isBody) {
                if ($isChunked && $line === '') {
                    // Read Control Sequence
                    \fgetss($socket, 256);
                } else {
                    $reponseBody .= $line;
                }
            } else {
                $reponseHeader .= $line;
                if (($isChunked === false) && \preg_match('/Transfer-Encoding:[\s*]chunked/is', $line)) {
                    $isChunked = true;
                }
            }
        }
        \fclose($socket);
        // Workaround: http://bugs.php.net/bug.php?id=39039 (see first line of Method)
        \error_reporting($tempErrorLevel);

        return [
            'status' => 'success',
            'header' => $reponseHeader,
            'body'   => $reponseBody
        ];
    }
}
