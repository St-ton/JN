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
    protected string $type = 'forgotpassword';

    /**
     * @inheritdoc
     */
    public function check(?array $args = null): bool
    {
        $items = $this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
                FROM tfloodprotect
                WHERE cIP = :ip
                    AND cTyp = :tpe
                    AND TIMESTAMPDIFF(MINUTE, dErstellt, NOW()) < :td',
            [
                'ip'  => $this->ip,
                'tpe' => $this->type,
                'td'  => $this->getFloodMinutes()
            ]
        );

        return ($items->cnt ?? 0) <= $this->getLimit();
    }
}
