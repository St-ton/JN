<?php declare(strict_types=1);

namespace JTL\Mail\Validator;

use Exception;
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
use JTL\Network\Communication;
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
            // strip smarty output if fatal error occurs
            $fatalPos = \strpos($text, 'Fatal error:');
            if ($fatalPos !== false) {
                $text = \substr($text, $fatalPos);
            }
            // strip possible call stack
            $callstackPos = \strpos($text, 'Call Stack:');
            if ($callstackPos !== false) {
                $text = \substr($text, 0, $callstackPos);
            }
            $result = (object)[
                'result'  => 'failure',
                'message' => $text,
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
                try {
                    \session_write_close();
                    $res = Communication::getContent($testUrl, null, $_COOKIE);
                    \session_start();
                } catch (Exception $e) {
                    $res = $this->finishSyntaxcheck($e->getMessage(), $model);
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
                    \PFAD_ROOT
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
            $this->hydrator->hydrate(null, $lang);
            $html = $this->renderer->renderHTML($templateID);
            $text = $this->renderer->renderText($templateID);
            if ((\mb_strlen(trim($html)) === 0 || \mb_strlen(trim($text)) === 0)
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
