<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class VueInstaller
 */
class VueInstaller
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
     * @var NiceDB
     */
    private $db;

    /**
     * @var bool
     */
    private $responseStatus = true;

    /**
     * @var array
     */
    private $responseMessage = [];

    /**
     * @var array
     */
    private $payload = [];

    /**
     * Installer constructor.
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
     *
     */
    public function run()
    {
        switch ($this->task) {
            case 'installedcheck':
                $this->getIsInstalled();
                break;
            case 'systemcheck' :
                $this->getSystemCheck();
                break;
            case 'dircheck' :
                $this->getDirectoryCheck();
                break;
            case 'credentialscheck' :
                $this->getDBCredentialsCheck();
                break;
            case 'doinstall' :
                $this->doInstall();
                break;
            case 'installdemodata' :
                $this->installDemoData();
                break;
            case 'wizard' :
                $this->executeWizard();
            default:
                break;
        }
        $this->output();
    }

    /**
     * @return $this
     */
    private function executeWizard() : VueInstaller
    {
        if ($this->initNiceDB($this->post['db'])) {
            $step   = isset($this->post['stepId']) ? (int)$this->post['stepId'] : 0;
            $wizard = new \jtl\Wizard\Shop4Wizard($step);
            if (isset($this->post['action']) && $this->post['action'] === 'setData') {
                foreach ($wizard->getQuestions() as $idx => $question) {
                    $questionId = $question->getID();
                    if ($question->getType() === \jtl\Wizard\Question::TYPE_BOOL) {
                        $wizard->answerQuestionByID(
                            $questionId,
                            isset($this->post['data'][$questionId]) && $this->post['data'][$questionId] === 'true'
                        );
                    } elseif (isset($this->post['data'][$questionId])) {
                        $wizard->answerQuestionByID($questionId, $this->post['data'][$questionId]);
                    }
                }
                $wizard->getStep()->finishStep(false);
            }
            if ($step > 0) {
                $this->payload['questions'] = $wizard->getFilteredQuestions();
            } else {
                $this->payload['isSynced'] = $wizard->getStep()->isSync();
                $this->payload['step']     = $wizard->getStep();
            }
        }
        $this->sendResponse();

        return $this;
    }

    /**
     *
     */
    private function output()
    {
        echo json_encode($this->payload);
        exit(0);
    }

    /**
     *
     */
    private function sendResponse()
    {
        if ($this->responseStatus === true && empty($this->responseMessage)) {
            $this->responseMessage[] = 'Erfolgreich ausgeführt';
        }
        echo json_encode([
            'ok'      => $this->responseStatus,
            'payload' => $this->payload,
            'msg'     => implode('<br>', $this->responseMessage)
        ]);
        exit(0);
    }

    /**
     * @param array $credentials
     * @return bool
     */
    private function initNiceDB(array $credentials) : bool
    {
        if (isset($credentials['host'], $credentials['user'], $credentials['pass'], $credentials['name'])) {
            try {
                if (!empty($credentials['socket'])) {
                    define('DB_SOCKET', $credentials['socket']);
                }
                $this->db = new NiceDB(
                    $credentials['host'],
                    $credentials['user'],
                    $credentials['pass'],
                    $credentials['name']
                );
            } catch (Exception $e) {
                $this->responseMessage[] = $e->getMessage();
                $this->responseStatus    = false;

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return $this
     */
    private function doInstall() : VueInstaller
    {
        if ($this->initNiceDB($this->post['db'])) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=0', \DB\ReturnType::DEFAULT);
            $this->parse_mysql_dump(__DIR__ . '/initial_schema.sql');
            $this->insertUsers();
            $blowfishKey = $this->getUID(30);
            $this->writeConfigFile($this->post['db'], $blowfishKey);
            $this->payload['secretKey'] = $blowfishKey;
            $this->db->query('SET FOREIGN_KEY_CHECKS=1', \DB\ReturnType::DEFAULT);
        }
        $this->sendResponse();

        return $this;
    }

    /**
     * @return $this
     */
    private function installDemoData() : VueInstaller
    {
        if ($this->initNiceDB($this->post['db'])) {
            $demoData = new DemoDataInstaller($this->db);
            $demoData->run();
            $this->responseStatus = true;
        }

        $this->sendResponse();

        return $this;
    }

    /**
     * @param array  $credentials
     * @param string $blowfishKey
     * @return bool
     */
    private function writeConfigFile(array $credentials, string $blowfishKey) : bool
    {
        if (isset($credentials['host'], $credentials['user'], $credentials['pass'], $credentials['name'])) {
            $socket = '';
            if (!empty($credentials['socket'])) {
                $socket = "\ndefine('DB_SOCKET', '" . $credentials['host'] . "');";
            }
            $cPfadRoot = PFAD_ROOT;
            if (strpos(PFAD_ROOT, '\\') !== false) {
                $cPfadRoot = str_replace('\\', '\\\\', $cPfadRoot);
            }
            $cConfigFile = "<?php
define('PFAD_ROOT', '" . $cPfadRoot . "');
define('URL_SHOP', '" . substr(URL_SHOP, 0, strlen(URL_SHOP) - 1) . "');" .
                $socket . "
define('DB_HOST','" . $credentials['host'] . "');
define('DB_NAME','" . $credentials['name'] . "');
define('DB_USER','" . $credentials['user'] . "');
define('DB_PASS','" . $credentials['pass'] . "');

define('BLOWFISH_KEY', '" . $blowfishKey . "');

//enables printing of warnings/infos/errors for the shop frontend
define('SHOP_LOG_LEVEL', 0);
//enables printing of warnings/infos/errors for the dbeS sync
define('SYNC_LOG_LEVEL', 0);
//enables printing of warnings/infos/errors for the admin backend
define('ADMIN_LOG_LEVEL', 0);
//enables printing of warnings/infos/errors for the smarty templates
define('SMARTY_LOG_LEVEL', 0);
//excplicitly show/hide errors
ini_set('display_errors', 0);" . "\n";
            //file speichern
            $file = fopen(PFAD_ROOT . PFAD_INCLUDES . 'config.JTL-Shop.ini.php', 'w');
            fwrite($file, $cConfigFile);
            fclose($file);

            return true;
        }

        return false;
    }

    /**
     * @param string $url
     * @return string
     */
    private function parse_mysql_dump(string $url) : string
    {
        if ($this->db === null) {
            return 'NiceDB nicht initialisiert.';
        }
        $file_content = file($url);
        $errors       = '';
        $query        = '';
        foreach ($file_content as $i => $sql_line) {
            $tsl = trim($sql_line);
            if ($sql_line !== ''
                && substr($tsl, 0, 2) !== '/*'
                && substr($tsl, 0, 2) !== '--'
                && substr($tsl, 0, 1) !== '#'
            ) {
                $query .= $sql_line;
                if (preg_match('/;\s*$/', $sql_line)) {
                    $result = $this->db->executeQuery($query, \DB\ReturnType::QUERYSINGLE);
                    if (!$result) {
                        $this->responseStatus    = false;
                        $this->responseMessage[] = $this->db->getErrorMessage() .
                            ' Nr: ' . $this->db->getErrorCode() . ' in Zeile ' . $i . '<br>' . $query . '<br>';
                    }
                    $query = '';
                }
            }
        }

        return $errors;
    }

    /**
     * @return VueInstaller
     * @throws \Exceptions\InvalidEntityNameException
     */
    private function insertUsers() : VueInstaller
    {
        $adminLogin                    = new stdClass();
        $adminLogin->cLogin            = $this->post['admin']['name'];
        $adminLogin->cPass             = md5($this->post['admin']['pass']);
        $adminLogin->cName             = 'Admin';
        $adminLogin->cMail             = '';
        $adminLogin->kAdminlogingruppe = 1;
        $adminLogin->nLoginVersuch     = 0;
        $adminLogin->bAktiv            = 1;

        if (!$this->db->insertRow('tadminlogin', $adminLogin)) {
            $error                   = $this->db->getError();
            $this->responseMessage[] = 'Fehler Nr: ' . $this->db->getErrorCode();
            if (!is_array($error)) {
                $this->responseMessage[] = $error;
            }
            $this->responseStatus = false;
        }

        $syncLogin        = new stdClass();
        $syncLogin->cMail = '';
        $syncLogin->cName = $this->post['wawi']['name'];
        $syncLogin->cPass = password_hash($this->post['wawi']['pass'], PASSWORD_DEFAULT);

        if (!$this->db->insertRow('tsynclogin', $syncLogin)) {
            $error                   = $this->db->getError();
            $this->responseMessage[] = 'Fehler Nr: ' . $this->db->getErrorCode();
            if (!is_array($error)) {
                $this->responseMessage[] = $error;
            }
            $this->responseStatus = false;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function getDBCredentialsCheck() : VueInstaller
    {
        $res        = new stdClass();
        $res->error = false;
        $res->msg   = 'Erfolgreich verbunden';
        if (isset($this->post['host'], $this->post['user'], $this->post['pass'], $this->post['name'])) {
            if (!empty($this->post['socket'])) {
                define('DB_SOCKET', $this->post['socket']);
            }
            try {
                $db = new NiceDB($this->post['host'], $this->post['user'], $this->post['pass'], $this->post['name']);
                if (!$db->isConnected()) {
                    $res->error = true;
                    $res->msg   = 'Keine Verbindung möglich';
                }
                $obj = $db->executeQuery("SHOW TABLES LIKE 'tsynclogin'", 1);
                if ($obj !== false) {
                    $res->error = true;
                    $res->msg   = 'Es existiert bereits eine Shopinstallation in dieser Datenbank';
                }
            } catch (Exception $e) {
                $res->error = true;
                $res->msg   = 'Datenbankfehler: ' . $e->getMessage();
            }
        } else {
            $res->msg   = 'Keine Zugangsdaten übermittelt';
            $res->error = true;
        }
        $this->payload['msg']   = $res->msg;
        $this->payload['error'] = $res->error;

        return $this;
    }

    /**
     * @return $this
     */
    private function getIsInstalled() : VueInstaller
    {
        $res = false;
        if (file_exists(PFAD_ROOT . PFAD_INCLUDES . 'config.JTL-Shop.ini.php')) {
            //use buffer to avoid redeclaring constants errors
            ob_start();
            require_once PFAD_ROOT . PFAD_INCLUDES . 'config.JTL-Shop.ini.php';
            ob_end_clean();

            $res = defined('BLOWFISH_KEY');
        }
        $this->payload['shopURL']   = URL_SHOP;
        $this->payload['installed'] = $res;

        return $this;
    }

    /**
     * @return $this
     */
    public function getSystemCheck() : VueInstaller
    {
        $oSC    = new Systemcheck_Environment();
        $vTests = $oSC->executeTestGroup('Shop4');
        $this->payload['testresults'] = $vTests;

        return $this;
    }

    /**
     * @return $this
     */
    public function getDirectoryCheck() : VueInstaller
    {
        $oFS = new Systemcheck_Platform_Filesystem(PFAD_ROOT);
        $this->payload['testresults'] = $oFS->getFoldersChecked();

        return $this;
    }

    /**
     * @param int    $length
     * @param string $cString
     * @return bool|string
     */
    private function getUID(int $length = 40, string $cString = '') : string
    {
        $cUID            = '';
        $cSalt           = '';
        $cSaltBuchstaben = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789';
        // Gen SALT
        for ($j = 0; $j < 30; $j++) {
            $cSalt .= substr($cSaltBuchstaben, mt_rand(0, strlen($cSaltBuchstaben) - 1), 1);
        }
        $cSalt = md5($cSalt);
        mt_srand();
        // Wurde ein String übergeben?
        if (strlen($cString) > 0) {
            // Hat der String Elemente?
            list($cString_arr) = explode(';', $cString);
            if (is_array($cString_arr) && count($cString_arr) > 0) {
                foreach ($cString_arr as $string) {
                    $cUID .= md5($string . md5(PFAD_ROOT . (time() - mt_rand())));
                }

                $cUID = md5($cUID . $cSalt);
            } else {
                $sl = strlen($cString);
                for ($i = 0; $i < $sl; $i++) {
                    $nPos = mt_rand(0, strlen($cString) - 1);
                    if (((int)date('w') % 2) <= strlen($cString)) {
                        $nPos = (int)date('w') % 2;
                    }
                    $cUID .= md5(substr($cString, $nPos, 1) . $cSalt . md5(PFAD_ROOT . (microtime(true) - mt_rand())));
                }
            }
            $cUID = $this->cryptPasswort($cUID . $cSalt);
        } else {
            $cUID = $this->cryptPasswort(md5(M_PI . $cSalt . md5(time() - mt_rand())));
        }

        return $length > 0 ? substr($cUID, 0, $length) : $cUID;
    }

    /**
     * @param string      $cPasswort
     * @param null|string $cHashPasswort
     * @return bool|string
     */
    private function cryptPasswort(string $cPasswort, $cHashPasswort = null)
    {
        $cSalt   = sha1(uniqid(mt_rand(), true));
        $nLaenge = strlen($cSalt);
        $nLaenge = max($nLaenge >> 3, ($nLaenge >> 2) - strlen($cPasswort));
        $cSalt   = $cHashPasswort
            ? substr($cHashPasswort, min(strlen($cPasswort), strlen($cHashPasswort) - $nLaenge), $nLaenge)
            : strrev(substr($cSalt, 0, $nLaenge));
        $cHash   = sha1($cPasswort);
        $cHash   = sha1(substr($cHash, 0, strlen($cPasswort)) . $cSalt . substr($cHash, strlen($cPasswort)));
        $cHash   = substr($cHash, $nLaenge);
        $cHash   = substr($cHash, 0, strlen($cPasswort)) . $cSalt . substr($cHash, strlen($cPasswort));

        return $cHashPasswort && $cHashPasswort !== $cHash ? false : $cHash;
    }
}
