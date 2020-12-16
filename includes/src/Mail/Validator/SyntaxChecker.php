<?php declare(strict_types=1);

namespace JTL\Mail\Validator;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Mail\Hydrator\HydratorInterface;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Renderer\RendererInterface;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\Model;
use JTL\Mail\Template\TemplateFactory;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\MailSmarty;
use stdClass;
use Symfony\Component\Process\PhpProcess;

/**
 * Class SyntaxChecker
 * @package JTL\Mail\Validator
 */
final class SyntaxChecker
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var TemplateFactory
     */
    private $factory;

    /**
     * SyntaxChecker constructor.
     * @param DbInterface       $db
     * @param TemplateFactory   $factory
     * @param RendererInterface $renderer
     * @param HydratorInterface $hydrator
     */
    public function __construct(
        DbInterface $db,
        TemplateFactory $factory,
        RendererInterface $renderer,
        HydratorInterface $hydrator
    ) {
        $this->db       = $db;
        $this->factory  = $factory;
        $this->hydrator = $hydrator;
        $this->renderer = $renderer;
    }

    /**
     *
     */
    public function checkAll(): void
    {
        $items = $this->db->query(
            'SELECT cModulId AS id, kPlugin AS pluginID FROM temailvorlage',
            ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($items as $template) {
            $this->checkSyntax($template->id, (int)$template->pluginID);
        }
    }

    /**
     * @param int $langID
     * @param string $templateID
     * @param string $moduleID
     * @return stdClass
     * @throws Exception
     */
    public static function ioCheckSyntax(int $langID, string $templateID, string $moduleID): stdClass
    {
        \ini_set('html_errors', '0');
        \ini_set('display_errors', '1');
        \ini_set('log_errors', '0');
        \error_reporting(\E_ALL & ~\E_NOTICE & ~\E_STRICT & ~\E_DEPRECATED);

        $db       = Shop::Container()->getDB();
        $renderer = new SmartyRenderer(new MailSmarty($db));
        $hydrator = new TestHydrator($renderer->getSmarty(), $db, Shopsetting::getInstance());
        $sc       = new self($db, new TemplateFactory($db), $renderer, $hydrator);
        $lang     = LanguageHelper::getInstance($db, Shop::Container()->getCache())->getLanguageByID($langID);
        $check    = $sc->doCheckSyntax($lang, $templateID, $moduleID);
        $res      = new stdClass();
        if ($check === '') {
            $res->result = 'ok';
        } else {
            $res->result  = 'failure';
            $res->message = $check;
        }

        return $res;
    }

    /**
     * @param string $text
     * @param Model $model
     * @return string
     */
    private function finishSyntaxcheck(string $text, Model $model): string
    {
        $result = \json_decode($text, false);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            $text = \strip_tags($text);
            // strip possible call stack
            if (\preg_match('/(Stack trace|Call Stack):/', $text, $hits)) {
                $callstackPos = \mb_strpos($text, 'Call Stack:');
                if ($callstackPos !== false) {
                    $text = \mb_substr($text, 0, $callstackPos);
                }
            }
            $errText  = '';
            $fatalPos = \mb_strlen($text);
            // strip smarty output if fatal error occurs
            if (\preg_match('/((Recoverable )?Fatal error):/ui', $text, $hits)) {
                $fatalPos = \mb_strpos($text, $hits[1]);
                if ($fatalPos !== false) {
                    $errText = \mb_substr($text, $fatalPos);
                }
            }
            // strip possible error position from smarty output
            $text = (string)\preg_replace('/[\t\n]/', ' ', \mb_substr($text, 0, $fatalPos));
            $len  = \mb_strlen($text);
            if ($len > 75) {
                $text = '...' . \mb_substr($text, $len - 75);
            }
            $result = (object)[
                'result'  => 'failure',
                'message' => \htmlentities($errText) . ($len > 0 ? '<br/>on line: ' . \htmlentities($text) : ''),
            ];
        }
        if ($result->result !== 'ok') {
            $error  = '<strong>' . __('Smarty syntax error') . ':</strong><br />';
            $error .= '<pre class="alert-danger">' . $result->message . '</pre>';
            $model->setHasError(true);
            $model->setActive(false);
            $model->save();

            return $error;
        }

        return '';
    }

    /**
     * @param string $templateID
     * @param int    $pluginID
     * @return string[]
     */
    public function checkSyntax(string $templateID, int $pluginID = 0): array
    {
        if ($pluginID > 0) {
            $templateID = 'kPlugin_' . $pluginID . '_' . $templateID;
        }
        $template = $this->factory->getTemplate($templateID);
        $result   = [];
        if ($template === null) {
            return $result;
        }

        foreach (LanguageHelper::getAllLanguages() as $lang) {
            $template->load($lang->getId(), 1);
            $model = $template->getModel();
            if ($model === null) {
                continue;
            }
            $id = $model->getID() . '_' . $lang->getId();

            if (\PHP_SAPI !== 'cli') {
                $testUrl = Shop::getAdminURL() . '/io.php?io=' .
                    \urlencode(
                        \json_encode([
                            'name'   => 'mailvorlageSyntaxCheck',
                            'params' => [$lang->getId(), $id, $model->getModuleID()],
                        ])
                    ) . '&token=' . Text::filterXSS($_SESSION['jtl_token']);
                \session_write_close();
                $client = new Client(['verify' => \DEFAULT_CURL_OPT_VERIFYPEER]);
                $jar    = CookieJar::fromArray($_COOKIE, '.' . \parse_url(\URL_SHOP)['host']);
                try {
                    $res = (string)$client->request('POST', $testUrl, ['cookies' => $jar])->getBody();
                } catch (Exception | GuzzleException $e) {
                    $res = $this->finishSyntaxcheck($e->getMessage(), $model);
                } finally {
                    \session_start();
                }

                $error = $this->finishSyntaxcheck(\is_string($res) ? $res : __('somethingHappend'), $model);
                if ($error !== '') {
                    $result[] = $error;
                }
            } else {
                $phpProcess = new PhpProcess(
                    '<?php declare(strict_types=1);

                    use JTL\Language\LanguageHelper;
                    use JTL\Mail\Hydrator\TestHydrator;
                    use JTL\Mail\Renderer\SmartyRenderer;
                    use JTL\Mail\Template\TemplateFactory;
                    use JTL\Mail\Validator\SyntaxChecker;
                    use JTL\Shop;
                    use JTL\Shopsetting;
                    use JTL\Smarty\MailSmarty;

                    require __DIR__ . \'/includes/globalinclude.php\';
                    ini_set(\'html_errors\', \'0\');
                    ini_set(\'display_errors\', \'1\');
                    ini_set(\'log_errors\', \'0\');
                    error_reporting(\E_ALL & ~\E_NOTICE & ~\E_STRICT & ~\E_DEPRECATED);

                    $langID     = ' . $lang->getId() . ';
                    $templateID = \'' . $id . '\';
                    $moduleID   = \'' . $model->getModuleID() . '\';

                    $db       = Shop::Container()->getDB();
                    $renderer = new SmartyRenderer(new MailSmarty($db));
                    $hydrator = new TestHydrator($renderer->getSmarty(), $db, Shopsetting::getInstance());
                    $sc       = new SyntaxChecker($db, new TemplateFactory($db), $renderer, $hydrator);
                    $lang     = LanguageHelper::getInstance($db, Shop::Container()->getCache())->getLanguageByID($langID);
                    $check    = $sc->doCheckSyntax($lang, $templateID, $moduleID);
                    $res      = new stdClass();
                    if ($check === \'\') {
                        $res->result = \'ok\';
                    } else {
                        $res->result  = \'failure\';
                        $res->message = $check;
                    }

                    echo json_encode($res);',
                    \PFAD_ROOT,
                    null,
                    600
                );
                $phpProcess->run();
                $error = $this->finishSyntaxcheck($phpProcess->getOutput(), $model);
                if ($error !== '') {
                    $result[] = $error;
                }
            }
        }

        return $result;
    }

    /**
     * @param LanguageModel $lang
     * @param string $templateID
     * @param string $moduleID
     * @return string
     */
    public function doCheckSyntax(LanguageModel $lang, string $templateID, string $moduleID): string
    {
        try {
            $this->hydrator->getSmarty()->setErrorReporting(\E_ALL & ~\E_NOTICE & ~\E_STRICT & ~\E_DEPRECATED);
            $this->hydrator->hydrate(null, $lang);
            $html = $this->renderer->renderHTML($templateID);
            $text = $this->renderer->renderText($templateID);
            if ((\mb_strlen(\trim($html)) === 0 || \mb_strlen(\trim($text)) === 0)
                && !\in_array($moduleID, ['core_jtl_footer', 'core_jtl_header'], true)
            ) {
                return __('Empty mail body');
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return '';
    }
}
