<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Newsletter\Newsletter;
use JTL\Optin\Optin;
use JTL\Optin\OptinNewsletter;
use JTL\Optin\OptinRefData;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class NewsletterImportController
 * @package JTL\Router\Controller\Backend
 */
class NewsletterImportController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::IMPORT_NEWSLETTER_RECEIVER_VIEW);
        $this->getText->loadAdminLocale('pages/newsletterimport');

        if (isset($_FILES['csv']['tmp_name'])
            && Request::postInt('newsletterimport') === 1
            && Form::validateToken()
            && \mb_strlen($_FILES['csv']['tmp_name']) > 0
        ) {
            $file = \fopen($_FILES['csv']['tmp_name'], 'rb');
            if ($file !== false) {
                $format    = ['cAnrede', 'cVorname', 'cNachname', 'cEmail'];
                $row       = 0;
                $fmt       = [];
                $importMsg = '';
                while ($data = \fgetcsv($file, 2000, ';', '"')) {
                    if ($row === 0) {
                        $importMsg .= \__('checkHead');
                        $fmt        = $this->checkformat($data, $format);
                        if ($fmt === -1) {
                            $this->alertService->addError(\__('errorFormatUnknown'), 'errorFormatUnknown');
                            break;
                        }
                        $importMsg .= '<br /><br />' . \__('importPending') . '<br />';
                    } else {
                        $importMsg .= '<br />' . \__('row') . $row . ': ' . $this->processImport($fmt, $data);
                    }
                    $row++;
                }
                $this->alertService->addNotice($importMsg, 'importMessage');
                \fclose($file);
            }
        }

        return $smarty->assign('kundengruppen', $this->db->getObjects(
            'SELECT * FROM tkundengruppe ORDER BY cName'
        ))
            ->assign('route', $this->route)
            ->getResponse('newsletterimport.tpl');
    }

    /**
     * @param string $email
     * @return bool
     */
    private function checkBlacklist(string $email): bool
    {
        $blacklist = $this->db->select(
            'tnewsletterempfaengerblacklist',
            'cMail',
            $email
        );

        return $blacklist !== null && !empty($blacklist->cMail);
    }

    /**
     * @param array $data
     * @param array $formats
     * @return array|int
     */
    private function checkformat(array $data, array $formats): int|array
    {
        $fmt = [];
        $cnt = \count($data);
        for ($i = 0; $i < $cnt; $i++) {
            if (!empty($data[$i]) && \in_array($data[$i], $formats, true)) {
                $fmt[$i] = $data[$i];
            }
        }

        return \in_array('cEmail', $fmt, true) ? $fmt : -1;
    }

    /**
     * @param array $fmt
     * @param array $data
     * @return string
     */
    private function processImport(array $fmt, array $data): string
    {
        $recipient = new class
        {
            /**
             * @var string
             */
            public $cAnrede;

            /**
             * @var string
             */
            public $cEmail;

            /**
             * @var string
             */
            public $cVorname;

            /**
             * @var string
             */
            public $cNachname;

            /**
             * @var int
             */
            public $kKunde = 0;

            /**
             * @var int
             */
            public $customerGroupID = 0;

            /**
             * @var int
             */
            public $kSprache;

            /**
             * @var string
             */
            public $cOptCode;

            /**
             * @var string
             */
            public $cLoeschCode;

            /**
             * @var string
             */
            public $dEingetragen;

            /**
             * @var int
             */
            public $nAktiv = 1;
        };
        $cnt       = \count($fmt); // only columns that have no empty header jtl-shop/issues#296
        for ($i = 0; $i < $cnt; $i++) {
            if (!empty($fmt[$i])) {
                $recipient->{$fmt[$i]} = $data[$i];
            }
        }

        if (Text::filterEmailAddress($recipient->cEmail) === false) {
            return \sprintf(\__('errorEmailInvalid'), $recipient->cEmail);
        }
        if ($this->checkBlacklist($recipient->cEmail)) {
            return \sprintf(\__('errorEmailInvalidBlacklist'), $recipient->cEmail);
        }
        if (!$recipient->cNachname) {
            return \__('errorSurnameMissing');
        }
        $instance = new Newsletter($this->db, []);
        $oldMail  = $this->db->select('tnewsletterempfaenger', 'cEmail', $recipient->cEmail);
        if ($oldMail !== null && $oldMail->kNewsletterEmpfaenger > 0) {
            return \sprintf(\__('errorEmailExists'), $recipient->cEmail);
        }

        if ($recipient->cAnrede === 'f') {
            $recipient->cAnrede = 'Frau';
        }
        if ($recipient->cAnrede === 'm' || $recipient->cAnrede === 'h') {
            $recipient->cAnrede = 'Herr';
        }
        $recipient->cOptCode     = $instance->createCode('cOptCode', $recipient->cEmail);
        $recipient->cLoeschCode  = $instance->createCode('cLoeschCode', $recipient->cEmail);
        $recipient->dEingetragen = 'NOW()';
        $recipient->kSprache     = (int)$_POST['kSprache'];
        $recipient->kKunde       = 0;

        $customerData = $this->db->select('tkunde', 'cMail', $recipient->cEmail);
        if ($customerData !== null && $customerData->kKunde > 0) {
            $recipient->kKunde          = (int)$customerData->kKunde;
            $recipient->kSprache        = (int)$customerData->kSprache;
            $recipient->customerGroupID = (int)$customerData->kKundengruppe;
        }
        $rowData               = new stdClass();
        $rowData->cAnrede      = $recipient->cAnrede;
        $rowData->cVorname     = $recipient->cVorname;
        $rowData->cNachname    = $recipient->cNachname;
        $rowData->kKunde       = $recipient->kKunde;
        $rowData->cEmail       = $recipient->cEmail;
        $rowData->dEingetragen = $recipient->dEingetragen;
        $rowData->kSprache     = $recipient->kSprache;
        $rowData->cOptCode     = $recipient->cOptCode;
        $rowData->cLoeschCode  = $recipient->cLoeschCode;
        $rowData->nAktiv       = $recipient->nAktiv;
        if ($this->db->insert('tnewsletterempfaenger', $rowData)) {
            unset($rowData->nAktiv);
            $rowData->cAktion = 'Daten-Import';
            $res              = $this->db->insert('tnewsletterempfaengerhistory', $rowData);

            try {
                $refData = (new OptinRefData())
                    ->setSalutation($rowData->cAnrede ?? '')
                    ->setFirstName($rowData->cVorname ?? '')
                    ->setLastName($rowData->cNachname ?? '')
                    ->setEmail($rowData->cEmail ?? '')
                    ->setLanguageID($rowData->kSprache)
                    ->setCustomerGroupID($recipient->customerGroupID)
                    ->setRealIP(Request::getRealIP());
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                (new Optin(OptinNewsletter::class))
                    ->getOptinInstance()
                    ->bypassSendingPermission()
                    ->createOptin($refData)
                    ->activateOptin();
            } catch (Exception $e) {
                Shop::Container()->getLogService()->notice(
                    'Optin creation failed during import for {mail}. Cause: {msg}',
                    ['mail' => $rowData->cEmail, 'msg' => $e->getMessage()]
                );
            }
            if ($res) {
                return \__('successImport')
                    . $recipient->cVorname
                    . ' ' . $recipient->cNachname
                    . ' (' . $recipient->cEmail . ')';
            }
        }

        return \__('errorImportRow');
    }
}
