<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

use JTL\DB\NiceDB;
use JTL\DB\ReturnType;

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
     * @var bool
     */
    private $cli;

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
     * @param bool       $cli
     */
    public function __construct($task, $post = null, $cli = false)
    {
        $this->task = $task;
        $this->post = $post;
        $this->cli  = $cli;
    }

    /**
     *
     */
    public function run(): ?array
    {
        switch ($this->task) {
            case 'installedcheck':
                $this->getIsInstalled();
                break;
            case 'systemcheck':
                $this->getSystemCheck();
                break;
            case 'dircheck':
                $this->getDirectoryCheck();
                break;
            case 'credentialscheck':
                $this->getDBCredentialsCheck();
                break;
            case 'doinstall':
                $this->doInstall();
                break;
            case 'installdemodata':
                $this->installDemoData();
                break;
            case 'wizard':
                $this->executeWizard();
                break;
            default:
                break;
        }

        return $this->output();
    }

    /**
     * @return $this
     */
    private function executeWizard(): self
    {
        if ($this->initNiceDB($this->post['db'])) {
            $step   = isset($this->post['stepId']) ? (int)$this->post['stepId'] : 0;
            $wizard = new \jtl\Wizard\ShopWizard($step);
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
    private function output(): ?array
    {
        if (!$this->cli) {
            echo json_encode($this->payload);
            exit(0);
        }

        return $this->payload;
    }

    /**
     *
     */
    private function sendResponse(): void
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
    private function initNiceDB(array $credentials): bool
    {
        if (isset($credentials['host'], $credentials['user'], $credentials['pass'], $credentials['name'])) {
            try {
                if (!empty($credentials['socket'])) {
                    define('DB_SOCKET', $credentials['socket']);
                }
                ifndef('DB_HOST', $credentials['host']);
                ifndef('DB_USER', $credentials['user']);
                ifndef('DB_PASS', $credentials['pass']);
                ifndef('DB_NAME', $credentials['name']);
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
     * @return VueInstaller
     * @throws \JTL\Exceptions\InvalidEntityNameException
     */
    private function doInstall(): self
    {
        if ($this->initNiceDB($this->post['db'])) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=0', ReturnType::DEFAULT);
            $this->parseMysqlDump(__DIR__ . '/initial_schema.sql');
            $this->insertUsers();
            $blowfishKey = $this->getUID(30);
            $this->writeConfigFile($this->post['db'], $blowfishKey);
            $this->payload['secretKey'] = $blowfishKey;
            $this->db->query('SET FOREIGN_KEY_CHECKS=1', ReturnType::DEFAULT);
        }

        if (!$this->cli) {
            $this->sendResponse();
        } else {
            $this->payload['error'] = !$this->responseStatus;
            $this->payload['msg']   = $this->responseStatus === true && empty($this->responseMessage)
                ? 'Erfolgreich ausgeführt'
                : $this->responseMessage;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function installDemoData(): self
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
    private function writeConfigFile(array $credentials, string $blowfishKey): bool
    {
        if (isset($credentials['host'], $credentials['user'], $credentials['pass'], $credentials['name'])) {
            $socket = '';
            if (!empty($credentials['socket'])) {
                $socket = "\ndefine('DB_SOCKET', '" . $credentials['host'] . "');";
            }
            $rootPath = PFAD_ROOT;
            if (strpos(PFAD_ROOT, '\\') !== false) {
                $rootPath = str_replace('\\', '\\\\', $rootPath);
            }
            $config = "<?php
define('PFAD_ROOT', '" . $rootPath . "');
define('URL_SHOP', '" . substr(URL_SHOP, 0, strlen(URL_SHOP) - 1) . "');" .
                $socket . "
define('DB_HOST','" . $credentials['host'] . "');
define('DB_NAME','" . $credentials['name'] . "');
define('DB_USER','" . $credentials['user'] . "');
define('DB_PASS','" . $credentials['pass'] . "');

define('BLOWFISH_KEY', '" . $blowfishKey . "');

//enables printing of warnings/infos/errors for the shop frontend
define('SHOP_LOG_LEVEL', E_ALL);
//enables printing of warnings/infos/errors for the dbeS sync
define('SYNC_LOG_LEVEL', E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
//enables printing of warnings/infos/errors for the admin backend
define('ADMIN_LOG_LEVEL', E_ALL);
//enables printing of warnings/infos/errors for the smarty templates
define('SMARTY_LOG_LEVEL', E_ALL);
//excplicitly show/hide errors
ini_set('display_errors', 0);" . "\n";
            $file        = fopen(PFAD_ROOT . PFAD_INCLUDES . 'config.JTL-Shop.ini.php', 'w');
            fwrite($file, $config);
            fclose($file);

            return true;
        }

        return false;
    }

    /**
     * @param string $url
     * @return string
     */
    private function parseMysqlDump(string $url): string
    {
        if ($this->db === null) {
            return 'NiceDB nicht initialisiert.';
        }
        $content = file($url);
        $errors  = '';
        $query   = '';
        foreach ($content as $i => $line) {
            $tsl = trim($line);
            if ($line !== ''
                && substr($tsl, 0, 2) !== '/*'
                && substr($tsl, 0, 2) !== '--'
                && substr($tsl, 0, 1) !== '#'
            ) {
                $query .= $line;
                if (preg_match('/;\s*$/', $line)) {
                    $result = $this->db->executeQuery($query, ReturnType::QUERYSINGLE);
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
     * @throws \JTL\Exceptions\InvalidEntityNameException
     */
    private function insertUsers(): self
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
    private function getDBCredentialsCheck(): self
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
    private function getIsInstalled(): self
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
    public function getSystemCheck(): self
    {
        $environment                  = new Systemcheck_Environment();
        $this->payload['testresults'] = $environment->executeTestGroup('Shop5');

        return $this;
    }

    /**
     * @return $this
     */
    public function getDirectoryCheck(): self
    {
        $fsCheck                      = new Systemcheck_Platform_Filesystem(PFAD_ROOT);
        $this->payload['testresults'] = $fsCheck->getFoldersChecked();

        return $this;
    }

    /**
     * @param int    $length
     * @param string $seed
     * @return bool|string
     */
    private function getUID(int $length = 40, string $seed = ''): string
    {
        $uid      = '';
        $salt     = '';
        $saltBase = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789';
        // Gen SALT
        for ($j = 0; $j < 30; $j++) {
            $salt .= substr($saltBase, mt_rand(0, strlen($saltBase) - 1), 1);
        }
        $salt = md5($salt);
        mt_srand();
        // Wurde ein String übergeben?
        if (strlen($seed) > 0) {
            // Hat der String Elemente?
            list($strings) = explode(';', $seed);
            if (is_array($strings) && count($strings) > 0) {
                foreach ($strings as $string) {
                    $uid .= md5($string . md5(PFAD_ROOT . (time() - mt_rand())));
                }

                $uid = md5($uid . $salt);
            } else {
                $sl = strlen($seed);
                for ($i = 0; $i < $sl; $i++) {
                    $nPos = mt_rand(0, strlen($seed) - 1);
                    if (((int)date('w') % 2) <= strlen($seed)) {
                        $nPos = (int)date('w') % 2;
                    }
                    $uid .= md5(substr($seed, $nPos, 1) . $salt . md5(PFAD_ROOT . (microtime(true) - mt_rand())));
                }
            }
            $uid = $this->cryptPasswort($uid . $salt);
        } else {
            $uid = $this->cryptPasswort(md5(M_PI . $salt . md5(time() - mt_rand())));
        }

        return $length > 0 ? substr($uid, 0, $length) : $uid;
    }

    /**
     * @param string      $pass
     * @param null|string $hashPass
     * @return bool|string
     */
    private function cryptPasswort(string $pass, $hashPass = null)
    {
        $salt   = sha1(uniqid(mt_rand(), true));
        $length = strlen($salt);
        $length = max($length >> 3, ($length >> 2) - strlen($pass));
        $salt   = $hashPass
            ? substr($hashPass, min(strlen($pass), strlen($hashPass) - $length), $length)
            : strrev(substr($salt, 0, $length));
        $hash   = sha1($pass);
        $hash   = sha1(substr($hash, 0, strlen($pass)) . $salt . substr($hash, strlen($pass)));
        $hash   = substr($hash, $length);
        $hash   = substr($hash, 0, strlen($pass)) . $salt . substr($hash, strlen($pass));

        return $hashPass && $hashPass !== $hash ? false : $hash;
    }
}
