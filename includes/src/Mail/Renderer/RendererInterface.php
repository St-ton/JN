<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Renderer;

use JTL\Mail\Mail\MailInterface;
use JTL\Mail\Template\TemplateInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Interface RendererInterface
 * @package JTL\Mail\Renderer
 */
interface RendererInterface
{
    /**
     * @param array                 $params
     * @param \JTL\Smarty\JTLSmarty $smarty
     * @return string
     */
    public function includeMailTemplate($params, $smarty): string;

    /**
     * @return JTLSmarty
     */
    public function getSmarty(): JTLSmarty;

    /**
     * @param TemplateInterface $template
     * @param int               $languageID
     * @throws \SmartyException
     */
    public function renderTemplate(TemplateInterface $template, int $languageID): void;

    /**
     * @param MailInterface $mail
     * @throws \SmartyException
     */
    public function renderMail(MailInterface $mail): void;
}
