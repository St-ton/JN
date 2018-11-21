<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

use JTLShop\SemVer\Version;
use GuzzleHttp\Client;

/**
 * Class VueUpdater
 */
class VueUpdater
{
    /**
     * @var string
     */
    private $task;

    /**
     * @var array
     */
    private $post;

    /**
     * @var array
     */
    private $payload = [];

    /**
     * Updater constructor.
     *
     * @param string     $task
     * @param array|null $post
     */
    public function __construct($task, $post = null)
    {
        $this->task  = $task;
        $this->post  = $post;
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        switch ($this->task) {
            case 'setStepFromSession':
                $this->setStepFromSession();
                break;
            case 'getApplicationVersion':
                $this->getApplicationVersion();
                break;
            case 'getAvailableUpdates':
                $this->getAvailableUpdates();
                break;
            case 'getJTLToken':
                $this->getJTLToken();
                break;
            case 'login':
                $this->login();
                break;
            default:
                break;
        }
        $this->output();
    }

    /**
     * @return self
     */
    private function setStepFromSession(): self
    {
        $step = 0;

        if (isset($_SESSION['loginIsValid']) && $_SESSION['loginIsValid']
            && \Session\AdminSession::getInstance()->isValid()) {
            $step = 1;
        }

        $this->payload['step'] = $step;

        return $this;
    }

    /**
     * @return self
     */
    private function getApplicationVersion(): self
    {
        $this->payload['version'] = APPLICATION_VERSION;

        return $this;
    }

    /**
     * @return self
     */
    private function getAvailableUpdates(): self
    {
        $applicationVersion = Version::parse(APPLICATION_VERSION);
        $client             = new Client(['base_uri' => 'https://api.jtl-shop.de']);
        $responseBuildsDev  = $client->get('versions-dev');
        $responsePatches    = $client->get('patches');
        $builds             = json_decode($responseBuildsDev->getBody()->getContents());
        $patches            = json_decode($responsePatches->getBody()->getContents());
        $availableUpdates   = [
            'builds'  => [],
            'patches' => []
        ];

        foreach ($builds as $build) {
            try {
                if ($applicationVersion->smallerThan($build->reference)) {
                    // Changelog https://issues.jtl-software.de/int/issues?offset=0&limit=20&sort=created&dir=desc&filter_project=JTL-Shop&filter_component[]=all&filter_version=4.06.10&filter_status[]=all&filter_user[]=all
                    $id             = str_replace(['v','.'], '', $build->reference);
                    $availableBuild = (object)[
                        'id'       => $id,
                        'ref'      => $build->reference,
                        'filename' => $build->filename
                    ];

                    $availableUpdates['builds'][] = $availableBuild;
                }
            } catch (Exception $e) {
                continue;
            }
        }
        foreach ($patches as $patch) {
            try {
                if ($applicationVersion->equals($patch->reference)) {
                    $value          = str_replace('.zip', '', $patch->filename);
                    $ref            = str_replace('shop-', '', $value);
                    $ref            = str_replace('-t', ' t', $ref);
                    $ref            = str_replace('-v', ' v', $ref);
                    $ref            = str_replace('-', '.', $ref);
                    $id             = $patch->reference.'-'.$patch->build->reference;
                    $id             = str_replace(['v','.'], '', $id);
                    $availablePatch = (object)[
                        'id'       => $id,
                        'ref'      => $ref,
                        'filename' => $patch->filename
                    ];

                    $availableUpdates['patches'][] = $availablePatch;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        $this->payload['updates'] = $availableUpdates;

        return $this;
    }

    /**
     * @return self
     * @throws Exception
     */
    private function getJTLToken(): self
    {
        $this->payload['jtl_token'] = $_SESSION['jtl_token'];

        return $this;
    }

    /**
     * @return self
     * @throws Exception
     */
    private function login(): self
    {
        /** @global AdminAccount $oAccount */
        $errors   = [];
        $success  = false;
        $oAccount = new AdminAccount();
        if (isset($this->post['adminlogin']) && (int)$this->post['adminlogin'] === 1) {
            $csrfOK = true;
            // Check if shop version is new enough for csrf validation
            if (Shop::getShopDatabaseVersion()->equals(Version::parse('4.0.0'))
                || Shop::getShopDatabaseVersion()->greaterThan(Version::parse('4.0.0'))
            ) {
                $csrfOK = Shop::Container()
                    ->getCryptoService()
                    ->stableStringEquals($_SESSION['jtl_token'], $this->post['jtl_token']);
            }
            $loginName = isset($this->post['user'])
                ? StringHandler::filterXSS(Shop::Container()->getDB()->escape($this->post['user']))
                : '---';

            if ($csrfOK === true) {
                $cLogin  = $this->post['user'];
                $cPass   = $this->post['password'];
                $nReturn = $oAccount->login($cLogin, $cPass);
                switch ($nReturn) {
                    case AdminLoginStatus::ERROR_LOCKED:
                    case AdminLoginStatus::ERROR_INVALID_PASSWORD_LOCKED:
                        $lockTime = $oAccount->getLockedMinutes();
                        $errors['locked'] = 'Gesperrt für ' . $lockTime . ' Minute' . ($lockTime !== 1 ? 'n' : '');
                        break;

                    case AdminLoginStatus::ERROR_USER_NOT_FOUND:
                        $errors['user'] = 'Benutzername falsch';
                        if (isset($_SESSION['AdminAccount']->TwoFA_expired)
                            && $_SESSION['AdminAccount']->TwoFA_expired === true
                        ) {
                            $errors['2faktor'] = '2-Faktor-Auth-Code abgelaufen';
                        }
                        break;
                    case AdminLoginStatus::ERROR_INVALID_PASSWORD:
                        $errors['password'] = 'Passwort falsch';
                        if (isset($_SESSION['AdminAccount']->TwoFA_expired)
                            && $_SESSION['AdminAccount']->TwoFA_expired === true
                        ) {
                            $errors['2faktor'] = '2-Faktor-Auth-Code abgelaufen';
                        }
                        break;

                    case AdminLoginStatus::ERROR_USER_DISABLED:
                        $errors['disabled'] = 'Anmeldung zur Zeit nicht möglich';
                        break;

                    case AdminLoginStatus::ERROR_LOGIN_EXPIRED:
                        $errors['expired'] = 'Anmeldedaten nicht mehr gültig';
                        break;

                    case AdminLoginStatus::ERROR_TWO_FACTOR_AUTH_EXPIRED:
                        if (isset($_SESSION['AdminAccount']->TwoFA_expired)
                            && $_SESSION['AdminAccount']->TwoFA_expired === true
                        ) {
                            $errors['2kfaktorExpired'] = '2-Faktor-Authentifizierungs-Code abgelaufen';
                        }
                        break;

                    case AdminLoginStatus::ERROR_NOT_AUTHORIZED:
                        $errors['noAuthorized'] = 'Keine Berechtigungen vorhanden';
                        break;

                    case AdminLoginStatus::LOGIN_OK:
                        if ($oAccount->permission('SHOP_UPDATE_VIEW')) {
                            \Session\AdminSession::getInstance()->reHash();
                            $success                  = true;
                            $_SESSION['loginIsValid'] = true; // "enable" the "header.tpl"-navigation again
                        }

                        break;
                }
            } elseif ($csrfOK !== true) {
                $errors['csrfFailed'] = 'Cross site request forgery! Sind Cookies aktiviert?';
            }
        }

        if (!empty($errors)) {
            $this->payload['errors'] = $errors;

            echo json_encode($errors);
            http_response_code(422);
            exit(1);
        } else {
            $this->payload['success'] = $success;
        }

        return $this;
    }

    /**
     * @return void
     */
    private function output(): void
    {
        echo json_encode($this->payload);
        exit(0);
    }

    /**
     * @return self
     */
    public function getDirectoryCheck(): self
    {
        $oFS = new Systemcheck_Platform_Filesystem(PFAD_ROOT);
        $this->payload['testresults'] = $oFS->getFoldersChecked();

        return $this;
    }
}