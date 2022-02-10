<?php declare(strict_types=1);

namespace JTL\RateLimit;

/**
 * class ForgotPassword
 * @package JTL\RateLimit
 */
class ForgotPassword extends AbstractRateLimiter
{
    /**
     * @var string
     */
    protected $type = 'forgotpassword';

    /**
     * @inheritdoc
     */
    public function check(?array $args = null): bool
    {
        return $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tfloodprotect
                WHERE cIP = :ip
                    AND cTyp = :tpe
                    AND TIMESTAMPDIFF(MINUTE, dErstellt, NOW()) < :td',
            'cnt',
            [
                'ip'  => $this->ip,
                'tpe' => $this->type,
                'td'  => $this->getFloodMinutes()
            ]
        ) <= $this->getLimit();
    }
}
