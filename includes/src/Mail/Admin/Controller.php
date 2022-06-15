<?php declare(strict_types=1);

namespace JTL\Mail\Admin;

use JTL\DB\DbInterface;
use JTL\Mail\Mailer;
use JTL\Mail\Template\TemplateFactory;

/**
 * Class Controller
 * @package JTL\Mail\Admin
 * @deprecated since 5.2.0
 */
final class Controller
{
    /**
     * Controller constructor.
     * @param DbInterface     $db
     * @param Mailer          $mailer
     * @param TemplateFactory $factory
     */
    public function __construct(DbInterface $db, Mailer $mailer, TemplateFactory $factory)
    {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
    }
}
