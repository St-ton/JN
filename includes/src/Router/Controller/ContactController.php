<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\CheckBox;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class ContactController
 * @package JTL\Router\Controller
 */
class ContactController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty
    ): ResponseInterface {
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_KONTAKT);
        $linkHelper         = Shop::Container()->getLinkService();
        $link               = $linkHelper->getSpecialPage(\LINKTYP_KONTAKT);
        $this->canonicalURL = $linkHelper->getStaticRoute('kontakt.php');

        if (Form::checkSubject()) {
            $this->assignForms();
        } else {
            Shop::Container()->getLogService()->error('Kein Kontaktbetreff vorhanden! Bitte im Backend unter '
                . 'Einstellungen -> Kontaktformular -> Betreffs einen Betreff hinzuf&uuml;gen.');
            $this->alertService->addNotice(Shop::Lang()->get('noSubjectAvailable', 'contact'), 'noSubjectAvailable');
            $this->smarty->assign('Spezialcontent', new stdClass());
        }
        $this->smarty->assign('Link', $link);

        $this->preRender();
        \executeHook(\HOOK_KONTAKT_PAGE);

        return $this->smarty->getResponse('contact/index.tpl');
    }

    protected function assignForms(): void
    {
        $specialContent = new stdClass();
        $lang           = Shop::getLanguageCode();
        $step           = 'formular';
        $missingData    = [];
        if (Request::postInt('kontakt') === 1 && Form::validateToken()) {
            $missingData = Form::getMissingContactFormData();
            $checkBox    = new CheckBox(0, $this->db);
            $missingData = \array_merge(
                $missingData,
                $checkBox->validateCheckBox(\CHECKBOX_ORT_KONTAKT, $this->customerGroupID, $_POST, true)
            );
            $ok          = Form::eingabenKorrekt($missingData);
            $this->smarty->assign('cPost_arr', Text::filterXSS($_POST));
            \executeHook(\HOOK_KONTAKT_PAGE_PLAUSI);

            if ($ok) {
                $step = 'floodschutz';
                if (!Form::checkFloodProtection($this->config['kontakt']['kontakt_sperre_minuten'])) {
                    $msg = Form::baueKontaktFormularVorgaben();
                    $checkBox->triggerSpecialFunction(
                        \CHECKBOX_ORT_KONTAKT,
                        $this->customerGroupID,
                        true,
                        $_POST,
                        ['oKunde' => $msg, 'oNachricht' => $msg]
                    )->checkLogging(\CHECKBOX_ORT_KONTAKT, $this->customerGroupID, $_POST, true);
                    Form::editMessage();
                    $step = 'nachricht versendet';
                }
            }
        }

        $contents = $this->db->selectAll(
            'tspezialcontentsprache',
            ['nSpezialContent', 'cISOSprache'],
            [(int)\SC_KONTAKTFORMULAR, $lang]
        );
        foreach ($contents as $content) {
            $specialContent->{$content->cTyp} = $content->cContent;
        }
        $subjects = $this->db->getObjects(
            "SELECT *
                FROM tkontaktbetreff
                WHERE (cKundengruppen = 0
                    OR FIND_IN_SET(:customerGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)
                ORDER BY nSort",
            ['customerGroupID' => $this->customerGroupID]
        );
        foreach ($subjects as $subject) {
            $localization             = $this->db->select(
                'tkontaktbetreffsprache',
                'kKontaktBetreff',
                (int)$subject->kKontaktBetreff,
                'cISOSprache',
                $lang
            );
            $subject->AngezeigterName = $localization->cName ?? $subject->cName;
        }
        if ($step === 'nachricht versendet') {
            $this->alertService->addSuccess(Shop::Lang()->get('messageSent', 'contact'), 'messageSent');
        } elseif ($step === 'floodschutz') {
            $this->alertService->addDanger(
                Shop::Lang()->get('youSentUsAMessageShortTimeBefore', 'contact'),
                'youSentUsAMessageShortTimeBefore'
            );
        }

        $this->smarty->assign('step', $step)
            ->assign('code', false)
            ->assign('Spezialcontent', $specialContent)
            ->assign('betreffs', $subjects)
            ->assign('Vorgaben', Form::baueKontaktFormularVorgaben($step === 'nachricht versendet'))
            ->assign('fehlendeAngaben', $missingData)
            ->assign('nAnzeigeOrt', \CHECKBOX_ORT_KONTAKT);
    }
}
