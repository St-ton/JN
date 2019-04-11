<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Renderer;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Mail\Mail\MailInterface;
use JTL\Mail\Template\Plugin;
use JTL\Mail\Template\TemplateInterface;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\SmartyResourceNiceDB;

/**
 * Class SmartyRenderer
 * @package JTL\Mail\Renderer
 */
class SmartyRenderer implements RendererInterface
{
    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Smarty constructor.
     * @param DbInterface $db
     * @throws \SmartyException
     */
    public function __construct(DbInterface $db)
    {
        $this->db     = $db;
        $this->smarty = new JTLSmarty(true, ContextType::MAIL);
        $this->smarty->registerResource('db', new SmartyResourceNiceDB($db, ContextType::MAIL))
            ->registerPlugin(\Smarty::PLUGIN_FUNCTION, 'includeMailTemplate', [$this, 'includeMailTemplate'])
            ->setCaching(0)
            ->setDebugging(0)
            ->setCompileDir(\PFAD_ROOT . \PFAD_COMPILEDIR)
            ->setTemplateDir(\PFAD_ROOT . \PFAD_EMAILTEMPLATES);
        if (\MAILTEMPLATE_USE_SECURITY) {
            $this->smarty->activateBackendSecurityMode();
        }
    }

    /**
     * @param array     $params
     * @param JTLSmarty $smarty
     * @return string
     */
    public function includeMailTemplate($params, $smarty): string
    {
        if (isset($params['template'], $params['type']) && $smarty->getTemplateVars('int_lang') !== null) {
            $res  = null;
            $lang = null;
            $tpl  = $this->db->select(
                'temailvorlageoriginal',
                'cDateiname',
                $params['template']
            );
            if (isset($tpl->kEmailvorlage) && $tpl->kEmailvorlage > 0) {
                $lang = $smarty->getTemplateVars('int_lang');
                $row  = $params['type'] === 'html' ? 'cContentHtml' : 'cContentText';
                $res  = $this->db->query(
                    'SELECT ' . $row . ' AS content
                    FROM temailvorlagesprache
                    WHERE kSprache = ' . (int)$lang->kSprache .
                    ' AND kEmailvorlage = ' . (int)$tpl->kEmailvorlage,
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($res->content)) {
                    if ($params['type'] === 'plain') {
                        $params['type'] = 'text';
                    }

                    return $smarty->fetch('db:' . $params['type'] . '_' . $tpl->kEmailvorlage . '_' . $lang->kSprache);
                }
            }
        }

        return '';
    }

    /**
     * @return JTLSmarty
     */
    public function getSmarty(): JTLSmarty
    {
        return $this->smarty;
    }

    /**
     * @inheritdoc
     */
    public function renderTemplate(TemplateInterface $template, int $languageID): void
    {
        if ($template === null) {
            return;
        }
        $model = $template->getModel();
        if ($model === null) {
            return;
        }
        $tplID = $model->getID() . '_' . $languageID . ($template instanceof Plugin ? '_' . $model->getPluginID() : '');
        $type  = $model->getType();
        \executeHook(\HOOK_MAILTOOLS_INC_SWITCH, [
            'mailsmarty'    => $this->getSmarty(),
            'mail'          => null,
            'kEmailvorlage' => $model->getID(),
            'kSprache'      => $languageID,
            'cPluginBody'   => '',
            'template'      => $template,
            'model'         => $model,
            'Emailvorlage'  => $model
        ]);
        $html = $type === 'text/html' || $type === 'html' ? $this->renderHTML($tplID) : '';
        $text = $this->renderText($tplID);
        $html = $this->renderLegalDataHTML($template, $languageID, $html);
        $text = $this->renderLegalDataText($template, $languageID, $text);

        $template->setHTML($html);
        $template->setText($text);
        $template->setSubject($this->renderSubject($template));
    }

    /**
     * @param TemplateInterface $template
     * @param int               $languageID
     * @param string            $html
     * @return string
     * @throws \SmartyException
     */
    private function renderLegalDataHTML(TemplateInterface $template, int $languageID, string $html): string
    {
        $legalData = $template->getLegalData();
        $model     = $template->getModel();
        if ($model === null || \mb_strlen($html) === 0) {
            return $html;
        }
        if ($model->getShowAKZ()) {
            $tplID    = 'core_jtl_anbieterkennzeichnung_' . $languageID;
            $rendered = $this->renderHTML($tplID);
            if (mb_strlen($rendered) > 0) {
                $html .= '<br /><br />' . $rendered;
            }
        }
        if ($model->getShowWRB() && \mb_strlen($legalData['wrb']->cContentHtml) > 0) {
            $html .= '<br /><br /><h3>' . Shop::Lang()->get('wrb') . '</h3>' . $legalData['wrb']->cContentHtml;
        }
        if ($model->getShowWRBForm() && \mb_strlen($legalData['wrbform']->cContentHtml) > 0) {
            $html .= '<br /><br /><h3>' . Shop::Lang()->get('wrbform') . '</h3>' . $legalData['wrbform']->cContentHtml;
        }
        if ($model->getShowAGB() && \mb_strlen($legalData['agb']->cContentHtml) > 0) {
            $html .= '<br /><br /><h3>' . Shop::Lang()->get('agb') . '</h3>' . $legalData['agb']->cContentHtml;
        }
        if ($model->getShowDSE() && \mb_strlen($legalData['dse']->cContentHtml) > 0) {
            $html .= '<br /><br /><h3>' . Shop::Lang()->get('dse') . '</h3>' . $legalData['dse']->cContentHtml;
        }

        return $html;
    }

    /**
     * @param TemplateInterface $template
     * @param int               $languageID
     * @param string            $text
     * @return string
     * @throws \SmartyException
     */
    private function renderLegalDataText(TemplateInterface $template, int $languageID, string $text): string
    {
        $legalData = $template->getLegalData();
        $model     = $template->getModel();
        if ($model === null) {
            return $text;
        }
        if ($model->getShowAKZ()) {
            $tplID = 'core_jtl_anbieterkennzeichnung_' . $languageID;
            $text  = $this->renderText($tplID) . "\n\n";
        }
        if ($model->getShowWRB() && \mb_strlen($legalData['wrb']->cContentText) > 0) {
            $text .= "\n\n" . Shop::Lang()->get('wrb') . "\n\n" . $legalData['wrb']->cContentText;
        }
        if ($model->getShowWRBForm() && \mb_strlen($legalData['wrbform']->cContentText) > 0) {
            $text .= "\n\n" . Shop::Lang()->get('wrbform') . "\n\n" . $legalData['wrbform']->cContentText;
        }
        if ($model->getShowAGB() && \mb_strlen($legalData['agb']->cContentText) > 0) {
            $text .= "\n\n" . Shop::Lang()->get('agb') . "\n\n" . $legalData['agb']->cContentText;
        }
        if ($model->getShowDSE() && \mb_strlen($legalData['dse']->cContentText) > 0) {
            $text .= "\n\n" . Shop::Lang()->get('dse') . "\n\n" . $legalData['dse']->cContentText;
        }

        return $text;
    }

    /**
     * @inheritDoc
     */
    public function renderHTML(string $id): string
    {
        return $this->smarty->fetch('db:html_' . $id);
    }

    /**
     * @inheritDoc
     */
    public function renderText(string $id): string
    {
        return $this->smarty->fetch('db:text_' . $id);
    }

    /**
     * @inheritdoc
     */
    public function renderMail(MailInterface $mail): void
    {
        $model    = null;
        $template = $mail->getTemplate();
        if ($template !== null) {
            $model = $template->getModel();
        }
        if ($model === null) {
            $mail->setBodyText($this->smarty->fetch('string:' . $mail->getBodyText()));
            $mail->setBodyHTML($this->smarty->fetch('string:' . $mail->getBodyHTML()));
            $mail->setSubject($this->smarty->fetch('string:' . $mail->getSubject()));
        } else {
            $this->renderTemplate($template, $mail->getLanguageID());
        }
    }

    /**
     * mail template subjects support a special syntax like "#smartyobject.value#"
     * this only works for #var# or #var.value# - not for deeper hierarchies
     *
     * @param TemplateInterface $template
     * @return string|null
     */
    private function renderSubject(TemplateInterface $template): ?string
    {
        $model = $template->getModel();
        if ($model === null) {
            return null;
        }
        $subject = $model->getSubject();
        $matches = \preg_match_all('/#(.*?)#/', $subject, $hits);
        if ($matches === 0) {
            return $subject;
        }
        $search  = [];
        $replace = [];
        foreach ($hits[0] as $i => $match) {
            $varName = $hits[1][$i];
            $parts   = \explode('.', $varName);
            $count   = \count($parts);
            if ($count === 0 || $count > 2) {
                continue;
            }
            $value = $this->getAssignedVar($parts[0]);
            if ($value === null) {
                continue;
            }
            if (\is_object($value) && isset($parts[1])) {
                $value = $this->getAssignedValue($value, $parts[1]);
            }
            if ($value !== null) {
                $search[]  = $match;
                $replace[] = $value;
            }
        }

        return \str_replace($search, $replace, $subject);
    }

    /**
     * @param object $object
     * @param string $name
     * @return mixed
     */
    private function getAssignedValue(object $object, string $name)
    {
        foreach (\get_object_vars($object) as $var => $value) {
            if ($var === $name) {
                return $value;
            }
            if (\mb_convert_case(\mb_substr($var, 1), \MB_CASE_LOWER) === $name) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function getAssignedVar(string $name)
    {
        return $this->smarty->getTemplateVars($name) ?? $this->smarty->getTemplateVars(\ucfirst($name));
    }
}
