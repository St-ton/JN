<?php declare(strict_types=1);

namespace JTL\Export;

use JTL\Helpers\Text;
use JTL\Smarty\ExportSmarty;

/**
 * Class TestWriter
 * @package JTL\Export
 */
class TestWriter implements ExportWriterInterface
{
    /**
     * @var string
     */
    private string $header = '';

    /**
     * @var string
     */
    private string $footer = '';

    /**
     * @var string
     */
    private string $content = '';

    /**
     * @inheritdoc
     */
    public function __construct(private Model $model, private array $config, private ?ExportSmarty $smarty = null)
    {
    }

    public function start(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function writeHeader(): int
    {
        $this->header = $this->smarty->fetch('string:' . $this->model->getHeader());
        if (\mb_strlen($this->header) === 0) {
            return 0;
        }
        $encoding = $this->model->getEncoding();
        if ($encoding === 'UTF-8' || $encoding === 'UTF-8noBOM') {
            $this->header = Text::convertUTF8($this->header);
        }
        $this->header .= $this->getNewLine();

        return \mb_strlen($this->header);
    }

    /**
     * @inheritdoc
     */
    public function writeFooter(): int
    {
        $this->footer = $this->smarty->fetch('string:' . $this->model->getFooter());
        if (\mb_strlen($this->footer) === 0) {
            return 0;
        }
        $encoding = $this->model->getEncoding();
        if ($encoding === 'UTF-8' || $encoding === 'UTF-8noBOM') {
            $this->footer = Text::convertUTF8($this->footer);
        }

        return \mb_strlen($this->footer);
    }

    /**
     * @inheritdoc
     */
    public function writeContent(string $data): int
    {
        $utf8 = ($this->model->getEncoding() === 'UTF-8' || $this->model->getEncoding() === 'UTF-8noBOM');

        $this->content = ($utf8 ? Text::convertUTF8($data) : $data);

        return \mb_strlen($this->content);
    }

    /**
     * @inheritdoc
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function finish(): bool
    {
        return true;
    }

    public function deleteOldExports(): void
    {
    }

    public function deleteOldTempFile(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function split(): ExportWriterInterface
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getNewLine(): string
    {
        return ($this->config['exportformate_line_ending'] ?? 'LF') === 'LF' ? "\n" : "\r\n";
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * @param string $header
     */
    public function setHeader(string $header): void
    {
        $this->header = $header;
    }

    /**
     * @return string
     */
    public function getFooter(): string
    {
        return $this->footer;
    }

    /**
     * @param string $footer
     */
    public function setFooter(string $footer): void
    {
        $this->footer = $footer;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
