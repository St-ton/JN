<?php declare(strict_types=1);

namespace JTL\Mail;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Class SmtpTest
 * @package JTL\Mail
 */
class SmtpTest
{
    /**
     * @param array $config
     * @return bool
     */
    public function run(array $config): bool
    {
        $smtp           = new SMTP();
        $smtp->do_debug = SMTP::DEBUG_CONNECTION;
        try {
            if (!$smtp->connect($config['email_smtp_hostname'], $config['email_smtp_port'])) {
                throw new Exception('Connect failed');
            }
            if (!$smtp->hello(\gethostname())) {
                throw new Exception('EHLO failed: ' . $smtp->getError()['error']);
            }
            $e = $smtp->getServerExtList();
            if (\is_array($e) && \array_key_exists('STARTTLS', $e)) {
                $tlsok = $smtp->startTLS();
                if (!$tlsok) {
                    throw new Exception('Failed to start encryption: ' . $smtp->getError()['error']);
                }
                if (!$smtp->hello(\gethostname())) {
                    throw new Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
                }
                $e = $smtp->getServerExtList();
            } elseif ($config['email_smtp_verschluesselung'] === 'tls') {
                throw new Exception('TLS not supported');
            }
            if (\is_array($e) && \array_key_exists('AUTH', $e)) {
                if ($smtp->authenticate($config['email_smtp_user'], $config['email_smtp_pass'])) {
                    echo 'Connected ok!';
                } else {
                    throw new Exception('Authentication failed: ' . $smtp->getError()['error']);
                }
            } else {
                throw new Exception('No authentication supported');
            }
        } catch (Exception $e) {
            echo 'SMTP error: ' . $e->getMessage(), "\n";
        }

        return $smtp->quit();
    }
}
