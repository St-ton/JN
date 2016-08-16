<?php
/**
 * phpQuery is a server-side, chainable, CSS3 selector driven
 * Document Object Model (DOM) API based on jQuery JavaScript Library.
 *
 * @version 0.9.5
 * @link http://code.google.com/p/phpquery/
 * @link http://phpquery-library.blogspot.com/
 * @link http://jquery.com/
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package phpQuery
 */

// class names for instanceof
// TODO move them as class constants into phpQuery
define('DOMDOCUMENT', 'DOMDocument');
define('DOMELEMENT', 'DOMElement');
define('DOMNODELIST', 'DOMNodeList');
define('DOMNODE', 'DOMNode');

require_once dirname(__FILE__) . '/phpQuery/DOMEvent.php';
require_once dirname(__FILE__) . '/phpQuery/DOMDocumentWrapper.php';
require_once dirname(__FILE__) . '/phpQuery/phpQueryEvents.php';
require_once dirname(__FILE__) . '/phpQuery/phpQueryObject.php';
require_once dirname(__FILE__) . '/phpQuery/Callback.php';
require_once dirname(__FILE__) . '/phpQuery/compat/mbstring.php';

/**
 * Static namespace for phpQuery functions.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 */
abstract class phpQuery
{
    /**
     * XXX: Workaround for mbstring problems
     *
     * @var bool
     */
    public static $mbstringSupport = true;

    /**
     * @var bool
     */
    public static $debug = false;

    /**
     * @var array
     */
    public static $documents = array();

    /**
     * @var null
     */
    public static $defaultDocumentID = null;

    /**
     * Applies only to HTML.
     *
     * @var string
     */
    public static $defaultDoctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';

    /**
     * @var string
     */
    public static $defaultCharset = 'UTF-8';

    /**
     * Static namespace for plugins.
     *
     * @var object
     */
    public static $plugins = array();

    /**
     * List of loaded plugins.
     *
     * @var array
     */
    public static $pluginsLoaded = array();

    /**
     * @var array
     */
    public static $pluginsMethods = array();

    /**
     * @var array
     */
    public static $pluginsStaticMethods = array();

    /**
     * @var array
     */
    public static $extendMethods = array();

    /**
     * @TODO implement
     * @var array
     */
    public static $extendStaticMethods = array();

    /**
     * Hosts allowed for AJAX connections.
     * Dot '.' means $_SERVER['HTTP_HOST'] (if any).
     *
     * @var array
     */
    public static $ajaxAllowedHosts = array(
        '.'
    );

    /**
     * AJAX settings.
     *
     * @var array
     * XXX should it be static or not ?
     */
    public static $ajaxSettings = array(
        'url'         => '',//TODO
        'global'      => true,
        'type'        => 'GET',
        'timeout'     => null,
        'contentType' => 'application/x-www-form-urlencoded',
        'processData' => true,
        'data'        => null,
        'username'    => null,
        'password'    => null,
        'accepts'     => array(
            'xml'      => 'application/xml, text/xml',
            'html'     => 'text/html',
            'script'   => 'text/javascript, application/javascript',
            'json'     => 'application/json, text/javascript',
            'text'     => 'text/plain',
            '_default' => '*/*'
        )
    );

    /**
     * @var null
     */
    public static $lastModified = null;

    /**
     * @var int
     */
    public static $active = 0;

    /**
     * @var int
     */
    public static $dumpCount = 0;

    /**
     * Multi-purpose function.
     * Use pq() as shortcut.
     *
     * In below examples, $pq is any result of pq(); function.
     *
     * 1. Import markup into existing document (without any attaching):
     * - Import into selected document:
     *   pq('<div/>')                // DOESNT accept text nodes at beginning of input string !
     * - Import into document with ID from $pq->getDocumentID():
     *   pq('<div/>', $pq->getDocumentID())
     * - Import into same document as DOMNode belongs to:
     *   pq('<div/>', DOMNode)
     * - Import into document from phpQuery object:
     *   pq('<div/>', $pq)
     *
     * 2. Run query:
     * - Run query on last selected document:
     *   pq('div.myClass')
     * - Run query on document with ID from $pq->getDocumentID():
     *   pq('div.myClass', $pq->getDocumentID())
     * - Run query on same document as DOMNode belongs to and use node(s)as root for query:
     *   pq('div.myClass', DOMNode)
     * - Run query on document from phpQuery object
     *   and use object's stack as root node(s) for query:
     *   pq('div.myClass', $pq)
     *
     * @param string|DOMNode|DOMNodeList|array $arg1 HTML markup, CSS Selector, DOMNode or array of DOMNodes
     * @param string|phpQueryObject|DOMNode    $context DOM ID from $pq->getDocumentID(), phpQuery object (determines also query root) or DOMNode (determines also query root)
     * @throws Exception
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery|QueryTemplatesPhpQuery|false
     * phpQuery object or false in case of error.
     */
    public static function pq($arg1, $context = null)
    {
        if ($arg1 instanceof DOMNode && !isset($context)) {
            foreach (phpQuery::$documents as $documentWrapper) {
                $compare = $arg1 instanceof DOMDocument
                    ? $arg1 : $arg1->ownerDocument;
                if ($documentWrapper->document->isSameNode($compare)) {
                    $context = $documentWrapper->id;
                }
            }
        }
        if (!$context) {
            $domId = self::$defaultDocumentID;
            if (!$domId) {
                throw new Exception("Can't use last created DOM, because there isn't any. Use phpQuery::newDocument() first.");
            }
        } else {
            if (is_object($context) && $context instanceof phpQueryObject) {
                $domId = $context->getDocumentID();
            } else {
                if ($context instanceof DOMDocument) {
                    $domId = self::getDocumentID($context);
                    if (!$domId) {
                        //throw new Exception('Orphaned DOMDocument');
                        $domId = self::newDocument($context)->getDocumentID();
                    }
                } else {
                    if ($context instanceof DOMNode) {
                        $domId = self::getDocumentID($context);
                        if (!$domId) {
                            throw new Exception('Orphaned DOMNode');
                        }
                    } else {
                        $domId = $context;
                    }
                }
            }
        }
        if ($arg1 instanceof phpQueryObject) {
            /**
             * Return $arg1 or import $arg1 stack if document differs:
             * pq(pq('<div/>'))
             */
            if ($arg1->getDocumentID() == $domId) {
                return $arg1;
            }
            $class = get_class($arg1);
            // support inheritance by passing old object to overloaded constructor
            $phpQuery           = $class !== 'phpQuery'
                ? new $class($arg1, $domId)
                : new phpQueryObject($domId);
            $phpQuery->elements = array();
            foreach ($arg1->elements as $node) {
                $phpQuery->elements[] = $phpQuery->document->importNode($node, true);
            }

            return $phpQuery;
        } else {
            if ($arg1 instanceof DOMNode || (is_array($arg1) && isset($arg1[0]) && $arg1[0] instanceof DOMNode)) {
                $phpQuery = new phpQueryObject($domId);
                if (!($arg1 instanceof DOMNodeList) && !is_array($arg1)) {
                    $arg1 = array($arg1);
                }
                $phpQuery->elements = array();
                foreach ($arg1 as $node) {
                    $sameDocument         = $node->ownerDocument instanceof DOMDocument
                        && !$node->ownerDocument->isSameNode($phpQuery->document);
                    $phpQuery->elements[] = $sameDocument
                        ? $phpQuery->document->importNode($node, true)
                        : $node;
                }

                return $phpQuery;
            } else {
                if (self::isMarkup($arg1)) {
                    /**
                     * Import HTML:
                     * pq('<div/>')
                     */
                    $phpQuery = new phpQueryObject($domId);

                    return $phpQuery->newInstance(
                        $phpQuery->documentWrapper->import($arg1)
                    );
                } else {
                    /**
                     * Run CSS query:
                     * pq('div.myClass')
                     */
                    $phpQuery = new phpQueryObject($domId);
                    if ($context && $context instanceof phpQueryObject) {
                        $phpQuery->elements = $context->elements;
                    } else {
                        if ($context && $context instanceof DOMNodeList) {
                            $phpQuery->elements = array();
                            foreach ($context as $node) {
                                $phpQuery->elements[] = $node;
                            }
                        } else {
                            if ($context && $context instanceof DOMNode) {
                                $phpQuery->elements = array($context);
                            }
                        }
                    }

                    return $phpQuery->find($arg1);
                }
            }
        }
    }

    /**
     * Sets default document to $id. Document has to be loaded prior
     * to using this method.
     * $id can be retrived via getDocumentID() or getDocumentIDRef().
     *
     * @param $id
     */
    public static function selectDocument($id)
    {
        $id                      = self::getDocumentID($id);
        self::$defaultDocumentID = self::getDocumentID($id);
    }

    /**
     * Returns document with id $id or last used as phpQueryObject.
     * $id can be retrived via getDocumentID() or getDocumentIDRef().
     * Chainable.
     *
     * @see phpQuery::selectDocument()
     * @param unknown_type $id
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function getDocument($id = null)
    {
        if ($id) {
            phpQuery::selectDocument($id);
        } else {
            $id = phpQuery::$defaultDocumentID;
        }

        return new phpQueryObject($id);
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param string|null $markup
     * @param string|null $contentType
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocument($markup = null, $contentType = null)
    {
        if (!$markup) {
            $markup = '';
        }
        $documentID = phpQuery::createDocumentWrapper($markup, $contentType);

        return new phpQueryObject($documentID);
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param string|null $markup
     * @param string|null $charset
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentHTML($markup = null, $charset = null)
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocument($markup, "text/html{$contentType}");
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param string|null $markup
     * @param string|null $charset
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentXML($markup = null, $charset = null)
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocument($markup, "text/xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param string|null $markup
     * @param string|null $charset
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentXHTML($markup = null, $charset = null)
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocument($markup, "application/xhtml+xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param string|null $markup
     * @param string      $contentType
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentPHP($markup = null, $contentType = "text/html")
    {
        // TODO pass charset to phpToMarkup if possible (use DOMDocumentWrapper function)
        $markup = phpQuery::phpToMarkup($markup, self::$defaultCharset);

        return self::newDocument($markup, $contentType);
    }

    /**
     * @param        $php
     * @param string $charset
     * @return mixed
     */
    public static function phpToMarkup($php, $charset = 'utf-8')
    {
        $regexes = array(
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(\')([^\']*)<' . '?php?(.*?)(?:\\?>)([^\']*)\'@s',
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(")([^"]*)<' . '?php?(.*?)(?:\\?>)([^"]*)"@s',
        );
        foreach ($regexes as $regex) {
            while (preg_match($regex, $php, $matches)) {
                $php = preg_replace_callback(
                    $regex,
                    array('phpQuery', '_phpToMarkupCallback'),
                    $php
                );
            }
        }
        $regex = '@(^|>[^<]*)+?(<\?php(.*?)(\?>))@s';
        $php   = preg_replace($regex, '\\1<php><!-- \\3 --></php>', $php);

        return $php;
    }

    /**
     * @param        $php
     * @param string $charset
     * @return string
     */
    public static function _phpToMarkupCallback($php, $charset = 'utf-8')
    {
        return $m[1] . $m[2]
        . htmlspecialchars("<?php" . $m[4] . "?>", ENT_QUOTES | ENT_NOQUOTES, $charset)
        . $m[5] . $m[2];
    }

    /**
     * @param $m
     * @return string
     */
    public static function _markupToPHPCallback($m)
    {
        return "<?php " . htmlspecialchars_decode($m[1]) . " ?>";
    }

    /**
     * Converts document markup containing PHP code generated by phpQuery::php()
     * into valid (executable) PHP code syntax.
     *
     * @param string|phpQueryObject $content
     * @return string PHP code.
     */
    public static function markupToPHP($content)
    {
        if ($content instanceof phpQueryObject) {
            $content = $content->markupOuter();
        }
        /* <php>...</php> to <?php...? > */
        $content = preg_replace_callback(
            '@<php>\s*<!--(.*?)-->\s*</php>@s',
            array('phpQuery', '_markupToPHPCallback'),
            $content
        );
        /* <node attr='< ?php ? >'> extra space added to save highlighters */
        $regexes = array(
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(\')([^\']*)(?:&lt;|%3C)\\?(?:php)?(.*?)(?:\\?(?:&gt;|%3E))([^\']*)\'@s',
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(")([^"]*)(?:&lt;|%3C)\\?(?:php)?(.*?)(?:\\?(?:&gt;|%3E))([^"]*)"@s',
        );
        foreach ($regexes as $regex) {
            while (preg_match($regex, $content)) {
                $content = preg_replace_callback(
                    $regex,
                    create_function(
                        '$m',
                        'return $m[1].$m[2].$m[3]."<?php "
                            .str_replace(
                                array("%20", "%3E", "%09", "&#10;", "&#9;", "%7B", "%24", "%7D", "%22", "%5B", "%5D"),
                                array(" ", ">", "	", "\n", "	", "{", "$", "}", \'"\', "[", "]"),
                                htmlspecialchars_decode($m[4])
                            )
                            ." ?>".$m[5].$m[2];'
                    ),
                    $content
                );
            }
        }

        return $content;
    }

    /**
     * Creates new document from file $file.
     * Chainable.
     *
     * @param string $file URLs allowed. See File wrapper page at php.net for more supported sources.
     * @param $contentType
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentFile($file, $contentType = null)
    {
        $documentID = self::createDocumentWrapper(
            file_get_contents($file), $contentType
        );

        return new phpQueryObject($documentID);
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param      $file
     * @param null $charset
     * @return phpQueryObject|QueryTemplatesParse|QueryTemplatesSource|QueryTemplatesSourceQuery
     */
    public static function newDocumentFileHTML($file, $charset = null)
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocumentFile($file, "text/html{$contentType}");
    }

    /**
     * Creates new document from markup.
     *
     * @param string      $file
     * @param string|null $charset
     * @return phpQueryObject|QueryTemplatesParse|QueryTemplatesSource|QueryTemplatesSourceQuery
     */
    public static function newDocumentFileXML($file, $charset = null)
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocumentFile($file, "text/xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param string      $file
     * @param string|null $charset
     * @return phpQueryObject|QueryTemplatesParse|QueryTemplatesSource|QueryTemplatesSourceQuery
     */
    public static function newDocumentFileXHTML($file, $charset = null)
    {
        $contentType = $charset
            ? ";charset=$charset"
            : '';

        return self::newDocumentFile($file, "application/xhtml+xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     *
     * @param string $file
     * @param null   $contentType
     * @return phpQueryObject|QueryTemplatesParse|QueryTemplatesSource|QueryTemplatesSourceQuery
     */
    public static function newDocumentFilePHP($file, $contentType = null)
    {
        return self::newDocumentPHP(file_get_contents($file), $contentType);
    }

    /**
     * Reuses existing DOMDocument object.
     *
     * @param $document DOMDocument
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     * @TODO support DOMDocument
     */
    public static function loadDocument($document)
    {
        // TODO
        die('TODO loadDocument');
    }

    /**
     * @param      $html
     * @param null $contentType
     * @param null $documentID
     * @return null|string
     * @throws Exception
     *
     * @todo support PHP tags in input
     * @todo support passing DOMDocument object from self::loadDocument
     */
    protected static function createDocumentWrapper($html, $contentType = null, $documentID = null)
    {
        if (function_exists('domxml_open_mem')) {
            throw new Exception("Old PHP4 DOM XML extension detected. phpQuery won't work until this extension is enabled.");
        }
        $document = null;
        if ($html instanceof DOMDocument) {
            if (self::getDocumentID($html)) {
                // document already exists in phpQuery::$documents, make a copy
                $wrapper = clone $html;
            } else {
                // new document, add it to phpQuery::$documents
                $wrapper = new DOMDocumentWrapper($html, $contentType, $documentID);
            }
        } else {
            $wrapper = new DOMDocumentWrapper($html, $contentType, $documentID);
        }
        // bind document
        phpQuery::$documents[$wrapper->id] = $wrapper;
        // remember last loaded document
        phpQuery::selectDocument($wrapper->id);

        return $wrapper->id;
    }

    /**
     * Extend class namespace.
     *
     * @param string|array $target
     * @param array        $source
     * @return bool
     * @throws Exception
     * @TODO support string $source
     */
    public static function extend($target, $source)
    {
        switch ($target) {
            case 'phpQueryObject':
                $targetRef  = &self::$extendMethods;
                $targetRef2 = &self::$pluginsMethods;
                break;
            case 'phpQuery':
                $targetRef  = &self::$extendStaticMethods;
                $targetRef2 = &self::$pluginsStaticMethods;
                break;
            default:
                throw new Exception("Unsupported \$target type");
        }
        if (is_string($source)) {
            $source = array($source => $source);
        }
        foreach ($source as $method => $callback) {
            if (isset($targetRef[$method])) {
                continue;
            }
            if (isset($targetRef2[$method])) {
                continue;
            }
            $targetRef[$method] = $callback;
        }

        return true;
    }

    /**
     * Extend phpQuery with $class from $file.
     *
     * @param      $class - Extending class name. Real class name can be prepended phpQuery_.
     * @param null $file - Filename to include. Defaults to "{$class}.php".
     * @return bool
     * @throws Exception
     */
    public static function plugin($class, $file = null)
    {
        if (in_array($class, self::$pluginsLoaded)) {
            return true;
        }
        if (!$file) {
            $file = $class . '.php';
        }
        $objectClassExists = class_exists('phpQueryObjectPlugin_' . $class);
        $staticClassExists = class_exists('phpQueryPlugin_' . $class);
        if (!$objectClassExists && !$staticClassExists) {
            require_once $file;
        }
        self::$pluginsLoaded[] = $class;
        // static methods
        if (class_exists('phpQueryPlugin_' . $class)) {
            $realClass = 'phpQueryPlugin_' . $class;
            $vars      = get_class_vars($realClass);
            $loop      = isset($vars['phpQueryMethods'])
            && !is_null($vars['phpQueryMethods'])
                ? $vars['phpQueryMethods']
                : get_class_methods($realClass);
            foreach ($loop as $method) {
                if ($method == '__initialize') {
                    continue;
                }
                if (!is_callable(array($realClass, $method))) {
                    continue;
                }
                if (isset(self::$pluginsStaticMethods[$method])) {
                    throw new Exception("Duplicate method '{$method}' from plugin '{$class}' conflicts with same method from plugin '" . self::$pluginsStaticMethods[$method] . "'");
                }
                self::$pluginsStaticMethods[$method] = $class;
            }
            if (method_exists($realClass, '__initialize')) {
                call_user_func_array(array($realClass, '__initialize'), array());
            }
        }
        // object methods
        if (class_exists('phpQueryObjectPlugin_' . $class)) {
            $realClass = 'phpQueryObjectPlugin_' . $class;
            $vars      = get_class_vars($realClass);
            $loop      = isset($vars['phpQueryMethods'])
            && !is_null($vars['phpQueryMethods'])
                ? $vars['phpQueryMethods']
                : get_class_methods($realClass);
            foreach ($loop as $method) {
                if (!is_callable(array($realClass, $method))) {
                    continue;
                }
                if (isset(self::$pluginsMethods[$method])) {
                    throw new Exception("Duplicate method '{$method}' from plugin '{$class}' conflicts with same method from plugin '" . self::$pluginsMethods[$method] . "'");
                    continue;
                }
                self::$pluginsMethods[$method] = $class;
            }
        }

        return true;
    }

    /**
     * Unloades all or specified document from memory.
     *
     * @param mixed $id @see phpQuery::getDocumentID() for supported types.
     */
    public static function unloadDocuments($id = null)
    {
        if (isset($id)) {
            if ($id = self::getDocumentID($id)) {
                unset(phpQuery::$documents[$id]);
            }
        } else {
            foreach (phpQuery::$documents as $k => $v) {
                unset(phpQuery::$documents[$k]);
            }
        }
    }

    /**
     * Parses phpQuery object or HTML result against PHP tags and makes them active.
     *
     * @param phpQuery|string $content
     * @deprecated
     * @return string
     */
    public static function unsafePHPTags($content)
    {
        return self::markupToPHP($content);
    }

    /**
     * @param $DOMNodeList
     * @return array
     */
    public static function DOMNodeListToArray($DOMNodeList)
    {
        $array = array();
        if (!$DOMNodeList) {
            return $array;
        }
        foreach ($DOMNodeList as $node) {
            $array[] = $node;
        }

        return $array;
    }

    /**
     * Checks if $input is HTML string, which has to start with '<'.
     *
     * @deprecated
     * @param String $input
     * @return Bool
     */
    public static function isMarkup($input)
    {
        return !is_array($input) && substr(trim($input), 0, 1) == '<';
    }

    /**
     * @param $text
     */
    public static function debug($text)
    {
        if (self::$debug) {
            var_dump($text);
        }
    }

    /**
     * Make an AJAX request
     *
     * @param array $options
     * @param Zend_Http_Client $xhr
     * @return null|Zend_Http_Client
     * @throws Exception
     * @throws Zend_Http_Client_Exception
     * @link http://docs.jquery.com/Ajax/jQuery.ajax
     *
     *  Additional options are:
     * 'document' - document for global events, @see phpQuery::getDocumentID()
     * 'referer' - implemented
     * 'requested_with' - TODO; not implemented (X-Requested-With)
     *
     * @TODO $options['cache']
     * @TODO $options['processData']
     * @TODO $options['xhr']
     * @TODO $options['data'] as string
     * @TODO XHR interface
     */
    public static function ajax($options = array(), $xhr = null)
    {
        $options    = array_merge(
            self::$ajaxSettings, $options
        );
        $documentID = isset($options['document'])
            ? self::getDocumentID($options['document'])
            : null;
        if ($xhr) {
            // reuse existing XHR object, but clean it up
            $client = $xhr;
            $client->setAuth(false);
            $client->setHeaders("If-Modified-Since", null);
            $client->setHeaders("Referer", null);
            $client->resetParameters();
        } else {
            // create new XHR object
            require_once 'phpQuery/Zend/Http/Client.php';
            $client = new Zend_Http_Client();
            $client->setCookieJar();
        }
        if (isset($options['timeout'])) {
            $client->setConfig(
                array(
                    'timeout' => $options['timeout'],
                )
            );
        }
        foreach (self::$ajaxAllowedHosts as $k => $host) {
            if ($host == '.' && isset($_SERVER['HTTP_HOST'])) {
                self::$ajaxAllowedHosts[$k] = $_SERVER['HTTP_HOST'];
            }
        }
        $host = parse_url($options['url'], PHP_URL_HOST);
        if (!in_array($host, self::$ajaxAllowedHosts)) {
            throw new Exception(
                "Request not permitted, host '$host' not present in "
                . "phpQuery::\$ajaxAllowedHosts"
            );
        }
        // JSONP
        $jsre = "/=\\?(&|$)/";
        if (isset($options['dataType']) && $options['dataType'] == 'jsonp') {
            $jsonpCallbackParam = $options['jsonp']
                ? $options['jsonp'] : 'callback';
            if (strtolower($options['type']) == 'get') {
                if (!preg_match($jsre, $options['url'])) {
                    $sep = strpos($options['url'], '?')
                        ? '&' : '?';
                    $options['url'] .= "$sep$jsonpCallbackParam=?";
                }
            } else {
                if ($options['data']) {
                    $jsonp = false;
                    foreach ($options['data'] as $n => $v) {
                        if ($v == '?') {
                            $jsonp = true;
                        }
                    }
                    if (!$jsonp) {
                        $options['data'][$jsonpCallbackParam] = '?';
                    }
                }
            }
            $options['dataType'] = 'json';
        }
        if (isset($options['dataType']) && $options['dataType'] == 'json') {
            $jsonpCallback = 'json_' . md5(microtime());
            $jsonpData     = $jsonpUrl = false;
            if ($options['data']) {
                foreach ($options['data'] as $n => $v) {
                    if ($v == '?') {
                        $jsonpData = $n;
                    }
                }
            }
            if (preg_match($jsre, $options['url'])) {
                $jsonpUrl = true;
            }
            if ($jsonpData !== false || $jsonpUrl) {
                // remember callback name for httpData()
                $options['_jsonp'] = $jsonpCallback;
                if ($jsonpData !== false) {
                    $options['data'][$jsonpData] = $jsonpCallback;
                }
                if ($jsonpUrl) {
                    $options['url'] = preg_replace($jsre, "=$jsonpCallback\\1", $options['url']);
                }
            }
        }
        $client->setUri($options['url']);
        $client->setMethod(strtoupper($options['type']));
        if (isset($options['referer']) && $options['referer']) {
            $client->setHeaders('Referer', $options['referer']);
        }
        $client->setHeaders(
            array(
                'User-Agent'      => 'Mozilla/5.0 (X11; U; Linux x86; en-US; rv:1.9.0.5) Gecko/2008122010 Firefox/3.0.5',
                'Accept-Charset'  => JTL_CHARSET . ',utf-8;q=0.7,*;q=0.7',
                'Accept-Language' => 'en-us,en;q=0.5',
            )
        );
        if ($options['username']) {
            $client->setAuth($options['username'], $options['password']);
        }
        if (isset($options['ifModified']) && $options['ifModified']) {
            $client->setHeaders(
                "If-Modified-Since",
                self::$lastModified
                    ? self::$lastModified
                    : "Thu, 01 Jan 1970 00:00:00 GMT"
            );
        }
        $client->setHeaders(
            "Accept",
            isset($options['dataType'])
            && isset(self::$ajaxSettings['accepts'][$options['dataType']])
                ? self::$ajaxSettings['accepts'][$options['dataType']] . ", */*"
                : self::$ajaxSettings['accepts']['_default']
        );
        // TODO $options['processData']
        if ($options['data'] instanceof phpQueryObject) {
            $serialized      = $options['data']->serializeArray($options['data']);
            $options['data'] = array();
            foreach ($serialized as $r) {
                $options['data'][$r['name']] = $r['value'];
            }
        }
        if (strtolower($options['type']) == 'get') {
            $client->setParameterGet($options['data']);
        } else {
            if (strtolower($options['type']) == 'post') {
                $client->setEncType($options['contentType']);
                $client->setParameterPost($options['data']);
            }
        }
        if (self::$active == 0 && $options['global']) {
            phpQueryEvents::trigger($documentID, 'ajaxStart');
        }
        self::$active++;
        // beforeSend callback
        if (isset($options['beforeSend']) && $options['beforeSend']) {
            phpQuery::callbackRun($options['beforeSend'], array($client));
        }
        // ajaxSend event
        if ($options['global']) {
            phpQueryEvents::trigger($documentID, 'ajaxSend', array($client, $options));
        }
        if (phpQuery::$debug) {
            self::debug("{$options['type']}: {$options['url']}\n");
            self::debug("Options: <pre>" . var_export($options, true) . "</pre>\n");
        }
        // request
        $response = $client->request();
        if (phpQuery::$debug) {
            self::debug('Status: ' . $response->getStatus() . ' / ' . $response->getMessage());
            self::debug($client->getLastRequest());
            self::debug($response->getHeaders());
        }
        if ($response->isSuccessful()) {
            // XXX tempolary
            self::$lastModified = $response->getHeader('Last-Modified');
            $data               = self::httpData($response->getBody(), $options['dataType'], $options);
            if (isset($options['success']) && $options['success']) {
                phpQuery::callbackRun($options['success'], array($data, $response->getStatus(), $options));
            }
            if ($options['global']) {
                phpQueryEvents::trigger($documentID, 'ajaxSuccess', array($client, $options));
            }
        } else {
            if (isset($options['error']) && $options['error']) {
                phpQuery::callbackRun($options['error'],
                    array($client, $response->getStatus(), $response->getMessage()));
            }
            if ($options['global']) {
                phpQueryEvents::trigger(
                    $documentID, 'ajaxError', array(
                        $client, /*$response->getStatus(),*/
                        $response->getMessage(),
                        $options
                    )
                );
            }
        }
        if (isset($options['complete']) && $options['complete']) {
            phpQuery::callbackRun($options['complete'], array($client, $response->getStatus()));
        }
        if ($options['global']) {
            phpQueryEvents::trigger($documentID, 'ajaxComplete', array($client, $options));
        }
        if ($options['global'] && !--self::$active) {
            phpQueryEvents::trigger($documentID, 'ajaxStop');
        }

        return $client;
    }

    /**
     * @param $data
     * @param $type
     * @param $options
     * @return mixed
     */
    protected static function httpData($data, $type, $options)
    {
        if (isset($options['dataFilter']) && $options['dataFilter']) {
            $data = self::callbackRun($options['dataFilter'], array($data, $type));
        }
        if (is_string($data)) {
            if ($type == "json") {
                if (isset($options['_jsonp']) && $options['_jsonp']) {
                    $data = preg_replace('/^\s*\w+\((.*)\)\s*$/s', '$1', $data);
                }
                $data = self::parseJSON($data);
            }
        }

        return $data;
    }

    /**
     * @param array|phpQuery $data
     * @return string
     */
    public static function param($data)
    {
        return http_build_query($data, null, '&');
    }

    /**
     * @param      $url
     * @param null $data
     * @param null $callback
     * @param null $type
     * @return null|Zend_Http_Client
     * @throws Exception
     */
    public static function get($url, $data = null, $callback = null, $type = null)
    {
        if (!is_array($data)) {
            $callback = $data;
            $data     = null;
        }

        return phpQuery::ajax(
            array(
                'type'     => 'GET',
                'url'      => $url,
                'data'     => $data,
                'success'  => $callback,
                'dataType' => $type,
            )
        );
    }

    /**
     * @param      $url
     * @param null $data
     * @param null $callback
     * @param null $type
     * @return Zend_Http_Client
     * @throws Exception
     */
    public static function post($url, $data = null, $callback = null, $type = null)
    {
        if (!is_array($data)) {
            $callback = $data;
            $data     = null;
        }

        return phpQuery::ajax(
            array(
                'type'     => 'POST',
                'url'      => $url,
                'data'     => $data,
                'success'  => $callback,
                'dataType' => $type,
            )
        );
    }

    /**
     * @param      $url
     * @param null $data
     * @param null $callback
     * @return Zend_Http_Client
     * @throws Exception
     */
    public static function getJSON($url, $data = null, $callback = null)
    {
        if (!is_array($data)) {
            $callback = $data;
            $data     = null;
        }

        return phpQuery::ajax(
            array(
                'type'     => 'GET',
                'url'      => $url,
                'data'     => $data,
                'success'  => $callback,
                'dataType' => 'json',
            )
        );
    }

    /**
     * @param $options
     */
    public static function ajaxSetup($options)
    {
        self::$ajaxSettings = array_merge(
            self::$ajaxSettings,
            $options
        );
    }

    /**
     * @param      $host1
     * @param null $host2
     * @param null $host3
     */
    public static function ajaxAllowHost($host1, $host2 = null, $host3 = null)
    {
        $loop = is_array($host1)
            ? $host1
            : func_get_args();
        foreach ($loop as $host) {
            if ($host && !in_array($host, phpQuery::$ajaxAllowedHosts)) {
                phpQuery::$ajaxAllowedHosts[] = $host;
            }
        }
    }

    /**
     * @param      $url1
     * @param null $url2
     * @param null $url3
     */
    public static function ajaxAllowURL($url1, $url2 = null, $url3 = null)
    {
        $loop = is_array($url1)
            ? $url1
            : func_get_args();
        foreach ($loop as $url) {
            phpQuery::ajaxAllowHost(parse_url($url, PHP_URL_HOST));
        }
    }

    /**
     * Returns JSON representation of $data.
     *
     * @static
     * @param mixed $data
     * @return string
     */
    public static function toJSON($data)
    {
        if (function_exists('json_encode')) {
            return json_encode($data);
        }
        require_once 'phpQuery/Zend/Json/Encoder.php';

        return Zend_Json_Encoder::encode($data);
    }

    /**
     * Parses JSON into proper PHP type.
     *
     * @static
     * @param string $json
     * @return mixed
     */
    public static function parseJSON($json)
    {
        if (function_exists('json_decode')) {
            $return = json_decode(trim($json), true);
            // json_decode and UTF8 issues
            if (isset($return)) {
                return $return;
            }
        }
        require_once 'phpQuery/Zend/Json/Decoder.php';

        return Zend_Json_Decoder::decode($json);
    }

    /**
     * Returns source's document ID.
     *
     * @param $source DOMNode|phpQueryObject
     * @return string
     */
    public static function getDocumentID($source)
    {
        if ($source instanceof DOMDocument) {
            foreach (phpQuery::$documents as $id => $document) {
                if ($source->isSameNode($document->document)) {
                    return $id;
                }
            }
        } else {
            if ($source instanceof DOMNode) {
                foreach (phpQuery::$documents as $id => $document) {
                    if ($source->ownerDocument->isSameNode($document->document)) {
                        return $id;
                    }
                }
            } else {
                if ($source instanceof phpQueryObject) {
                    return $source->getDocumentID();
                } else {
                    if (is_string($source) && isset(phpQuery::$documents[$source])) {
                        return $source;
                    }
                }
            }
        }
    }

    /**
     * Get DOMDocument object related to $source.
     * Returns null if such document doesn't exist.
     *
     * @param $source DOMNode|phpQueryObject|string
     * @return string
     */
    public static function getDOMDocument($source)
    {
        if ($source instanceof DOMDocument) {
            return $source;
        }
        $id = self::getDocumentID($source);

        return $id
            ? self::$documents[$id]['document']
            : null;
    }

    /**
     * @param $object
     * @return array
     * @link http://docs.jquery.com/Utilities/jQuery.makeArray
     */
    public static function makeArray($object)
    {
        $array = array();
        if (is_object($object) && $object instanceof DOMNodeList) {
            foreach ($object as $value) {
                $array[] = $value;
            }
        } else {
            if (is_object($object) && !($object instanceof Iterator)) {
                foreach (get_object_vars($object) as $name => $value) {
                    $array[0][$name] = $value;
                }
            } else {
                foreach ($object as $name => $value) {
                    $array[0][$name] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * @param $value
     * @param $array
     * @return bool
     */
    public static function inArray($value, $array)
    {
        return in_array($value, $array);
    }

    /**
     * @param      $object
     * @param      $callback
     * @param null $param1
     * @param null $param2
     * @param null $param3
     * @link http://docs.jquery.com/Utilities/jQuery.each
     */
    public static function each($object, $callback, $param1 = null, $param2 = null, $param3 = null)
    {
        $paramStructure = null;
        if (func_num_args() > 2) {
            $paramStructure = func_get_args();
            $paramStructure = array_slice($paramStructure, 2);
        }
        if (is_object($object) && !($object instanceof Iterator)) {
            foreach (get_object_vars($object) as $name => $value) {
                phpQuery::callbackRun($callback, array($name, $value), $paramStructure);
            }
        } else {
            foreach ($object as $name => $value) {
                phpQuery::callbackRun($callback, array($name, $value), $paramStructure);
            }
        }
    }

    /**
     * @param      $array
     * @param      $callback
     * @param null $param1
     * @param null $param2
     * @param null $param3
     * @return array
     * @link http://docs.jquery.com/Utilities/jQuery.map
     */
    public static function map($array, $callback, $param1 = null, $param2 = null, $param3 = null)
    {
        $result         = array();
        $paramStructure = null;
        if (func_num_args() > 2) {
            $paramStructure = func_get_args();
            $paramStructure = array_slice($paramStructure, 2);
        }
        foreach ($array as $v) {
            $vv = phpQuery::callbackRun($callback, array($v), $paramStructure);
            if (is_array($vv)) {
                foreach ($vv as $vvv) {
                    $result[] = $vvv;
                }
            } else {
                if ($vv !== null) {
                    $result[] = $vv;
                }
            }
        }

        return $result;
    }

    /**
     * @param CallbackParameterToReference|Callback $callback
     * @param $params
     * @param $paramStructure
     * @return bool|mixed|void
     */
    public static function callbackRun($callback, $params = array(), $paramStructure = null)
    {
        if (!$callback) {
            return;
        }
        if ($callback instanceof CallbackParameterToReference) {
            // TODO support ParamStructure to select which $param push to reference
            if (isset($params[0])) {
                $callback->callback = $params[0];
            }

            return true;
        }
        if ($callback instanceof Callback) {
            $paramStructure = $callback->params;
            $callback       = $callback->callback;
        }
        if (!$paramStructure) {
            return call_user_func_array($callback, $params);
        }
        $p = 0;
        foreach ($paramStructure as $i => $v) {
            $paramStructure[$i] = $v instanceof CallbackParam
                ? $params[$p++]
                : $v;
        }

        return call_user_func_array($callback, $paramStructure);
    }

    /**
     * Merge 2 phpQuery objects.
     *
     * @param array $one
     * @param array $two
     * @return array
     * @protected
     * @todo node lists, phpQueryObject
     */
    public static function merge($one, $two)
    {
        $elements = $one->elements;
        foreach ($two->elements as $node) {
            $exists = false;
            foreach ($elements as $node2) {
                if ($node2->isSameNode($node)) {
                    $exists = true;
                }
            }
            if (!$exists) {
                $elements[] = $node;
            }
        }

        return $elements;
    }

    /**
     * @param $array
     * @param $callback
     * @param $invert
     * @return array
     * @link http://docs.jquery.com/Utilities/jQuery.grep
     */
    public static function grep($array, $callback, $invert = false)
    {
        $result = array();
        foreach ($array as $k => $v) {
            $r = call_user_func_array($callback, array($v, $k));
            if ($r === !(bool)$invert) {
                $result[] = $v;
            }
        }

        return $result;
    }

    /**
     * @param $array
     * @return array
     */
    public static function unique($array)
    {
        return array_unique($array);
    }

    /**
     *
     * @param $function
     * @return bool
     * @TODO there are problems with non-static methods, second parameter pass it
     *    but doesnt verify is method is really callable
     */
    public static function isFunction($function)
    {
        return is_callable($function);
    }

    /**
     * @param $str
     * @return string
     */
    public static function trim($str)
    {
        return trim($str);
    }

    /**
     *
     * @param $url
     * @param $callback
     * @param $param1
     * @param $param2
     * @param $param3
     * @return phpQueryObject
     */
    public static function browserGet($url, $callback, $param1 = null, $param2 = null, $param3 = null)
    {
        if (self::plugin('WebBrowser')) {
            $params = func_get_args();

            return self::callbackRun(array(self::$plugins, 'browserGet'), $params);
        } else {
            self::debug('WebBrowser plugin not available...');
        }
    }

    /**
     *
     * @param $url
     * @param $data
     * @param $callback
     * @param $param1
     * @param $param2
     * @param $param3
     * @return phpQueryObject
     */
    public static function browserPost($url, $data, $callback, $param1 = null, $param2 = null, $param3 = null)
    {
        if (self::plugin('WebBrowser')) {
            $params = func_get_args();

            return self::callbackRun(array(self::$plugins, 'browserPost'), $params);
        } else {
            self::debug('WebBrowser plugin not available...');
        }
    }

    /**
     *
     * @param $ajaxSettings
     * @param $callback
     * @param $param1
     * @param $param2
     * @param $param3
     * @return phpQueryObject
     */
    public static function browser($ajaxSettings, $callback, $param1 = null, $param2 = null, $param3 = null)
    {
        if (self::plugin('WebBrowser')) {
            $params = func_get_args();

            return self::callbackRun(array(self::$plugins, 'browser'), $params);
        } else {
            self::debug('WebBrowser plugin not available...');
        }
    }

    /**
     * @param $code
     * @return string
     */
    public static function php($code)
    {
        return self::code('php', $code);
    }

    /**
     * @param $type
     * @param $code
     * @return string
     */
    public static function code($type, $code)
    {
        return "<$type><!-- " . trim($code) . " --></$type>";
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        return call_user_func_array(
            array(phpQuery::$plugins, $method),
            $params
        );
    }

    /**
     * @param $node
     * @param $documentID
     * @return mixed
     */
    protected static function dataSetupNode($node, $documentID)
    {
        // search are return if alredy exists
        foreach (phpQuery::$documents[$documentID]->dataNodes as $dataNode) {
            if ($node->isSameNode($dataNode)) {
                return $dataNode;
            }
        }
        // if doesn't, add it
        phpQuery::$documents[$documentID]->dataNodes[] = $node;

        return $node;
    }

    /**
     * @param $node
     * @param $documentID
     */
    protected static function dataRemoveNode($node, $documentID)
    {
        // search are return if alredy exists
        foreach (phpQuery::$documents[$documentID]->dataNodes as $k => $dataNode) {
            if ($node->isSameNode($dataNode)) {
                unset(self::$documents[$documentID]->dataNodes[$k]);
                unset(self::$documents[$documentID]->data[$dataNode->dataID]);
            }
        }
    }

    /**
     * @param      $node
     * @param      $name
     * @param      $data
     * @param null $documentID
     * @return mixed
     */
    public static function data($node, $name, $data, $documentID = null)
    {
        if (!$documentID) {
            // TODO check if this works

            $documentID = self::getDocumentID($node);
        }
        $document = phpQuery::$documents[$documentID];
        $node     = self::dataSetupNode($node, $documentID);
        if (!isset($node->dataID)) {
            $node->dataID = ++phpQuery::$documents[$documentID]->uuid;
        }
        $id = $node->dataID;
        if (!isset($document->data[$id])) {
            $document->data[$id] = array();
        }
        if (!is_null($data)) {
            $document->data[$id][$name] = $data;
        }
        if ($name) {
            if (isset($document->data[$id][$name])) {
                return $document->data[$id][$name];
            }
        } else {
            return $id;
        }
    }

    /**
     * @param $node
     * @param $name
     * @param $documentID
     */
    public static function removeData($node, $name, $documentID)
    {
        if (!$documentID) {
            // TODO check if this works

            $documentID = self::getDocumentID($node);
        }
        $document = phpQuery::$documents[$documentID];
        $node     = self::dataSetupNode($node, $documentID);
        $id       = $node->dataID;
        if ($name) {
            if (isset($document->data[$id][$name])) {
                unset($document->data[$id][$name]);
            }
            $name = null;
            foreach ($document->data[$id] as $name) {
                break;
            }
            if (!$name) {
                self::removeData($node, $name, $documentID);
            }
        } else {
            self::dataRemoveNode($node, $documentID);
        }
    }
}

/**
 * Plugins static namespace class.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 * @todo move plugin methods here (as statics)
 */
class phpQueryPlugins
{
    /**
     * @param $method
     * @param $args
     * @return mixed|phpQueryPlugins
     * @throws Exception
     */
    public function __call($method, $args)
    {
        if (isset(phpQuery::$extendStaticMethods[$method])) {
            call_user_func_array(
                phpQuery::$extendStaticMethods[$method],
                $args
            );
        } else {
            if (isset(phpQuery::$pluginsStaticMethods[$method])) {
                $class     = phpQuery::$pluginsStaticMethods[$method];
                $realClass = "phpQueryPlugin_$class";
                $return    = call_user_func_array(
                    array($realClass, $method),
                    $args
                );

                return isset($return)
                    ? $return
                    : $this;
            } else {
                throw new Exception("Method '{$method}' doesnt exist");
            }
        }
    }
}

/**
 * Shortcut to phpQuery::pq($arg1, $context)
 * Chainable.
 *
 * @see phpQuery::pq()
 * @param $arg1
 * @param $context
 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 */
function pq($arg1, $context = null)
{
    $args = func_get_args();

    return call_user_func_array(
        array('phpQuery', 'pq'),
        $args
    );
}

// add plugins dir and Zend framework to include path
set_include_path(
    get_include_path()
    . PATH_SEPARATOR . dirname(__FILE__) . '/phpQuery/'
    . PATH_SEPARATOR . dirname(__FILE__) . '/phpQuery/plugins/'
);

phpQuery::$plugins = new phpQueryPlugins();
