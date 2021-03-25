<?php declare(strict_types=1);

namespace JTL\Export;

use JTL\Helpers\Text;
use JTL\Smarty\ExportSmarty;

/**
 * Class FileWriter
 * @package JTL\Export
 */
class FileWriter
{
    /**
     * @var ExportSmarty
     */
    private $smarty;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $tmpFileName;

    /**
     * @var resource
     */
    private $tmpFile;

    /**
     * FileWriter constructor.
     * @param ExportSmarty $smarty
     * @param Model        $model
     * @param array        $config
     */
    public function __construct(ExportSmarty $smarty, Model $model, array $config)
    {
        $this->smarty      = $smarty;
        $this->model       = $model;
        $this->config      = $config;
        $this->tmpFileName = 'tmp_' . $this->model->getFilename();
    }

    public function start(): void
    {
        $this->tmpFile = \fopen(\PFAD_ROOT . \PFAD_EXPORT . $this->tmpFileName, 'ab');
    }

    /**
     * @return string
     */
    private function getNewLine(): string
    {
        return ($this->config['exportformate_line_ending'] ?? 'LF') === 'LF' ? "\n" : "\r\n";
    }

    /**
     * @param resource $handle
     * @return int
     */
    public function writeHeader($handle = null): int
    {
        $handle = $handle ?? $this->tmpFile;
        $header = $this->smarty->fetch('string:' . $this->model->getHeader());
        if (\mb_strlen($header) === 0) {
            return 0;
        }
        $encoding = $this->model->getEncoding();
        if ($encoding === 'UTF-8') {
            \fwrite($handle, "\xEF\xBB\xBF");
        }
        if ($encoding === 'UTF-8' || $encoding === 'UTF-8noBOM') {
            $header = Text::convertUTF8($header);
        }

        return \fwrite($handle, $header . $this->getNewLine());
    }

    /**
     * @param resource|null $handle
     * @return int
     * @throws \SmartyException
     */
    public function writeFooter($handle = null): int
    {
        $handle = $handle ?? $this->tmpFile;
        $footer = $this->smarty->fetch('string:' . $this->model->getFooter());
        if (\mb_strlen($footer) === 0) {
            return 0;
        }
        $encoding = $this->model->getEncoding();
        if ($encoding === 'UTF-8' || $encoding === 'UTF-8noBOM') {
            $footer = Text::convertUTF8($footer);
        }

        return \fwrite($handle, $footer);
    }

    /**
     * @param string        $data
     * @param resource|null $handle
     * @return int
     */
    public function writeContent(string $data, $handle = null): int
    {
        $handle = $handle ?? $this->tmpFile;

        return \fwrite($handle, (($this->model->getEncoding() === 'UTF-8' || $this->model->getEncoding() === 'UTF-8noBOM')
            ? Text::convertUTF8($data)
            : $data));
    }

    /**
     * @param resource|null $handle
     * @return bool
     */
    public function close($handle = null): bool
    {
        $handle = $handle ?? $this->tmpFile;

        return \fclose($handle);
    }

    /**
     * @param resource|null $handle
     * @return bool
     */
    public function finish($handle = null): bool
    {
        $handle = $handle ?? $this->tmpFile;
        $this->close($handle);
        if (\copy(
            \PFAD_ROOT . \PFAD_EXPORT . $this->tmpFileName,
            \PFAD_ROOT . \PFAD_EXPORT . $this->model->getFilename()
        )
        ) {
            \unlink(\PFAD_ROOT . \PFAD_EXPORT . $this->tmpFileName);

            return true;
        }

        return false;
    }

    /**
     * @param string $fileName
     * @param string $fileNameSplit
     * @return $this
     */
    private function cleanupFiles(string $fileName, string $fileNameSplit): self
    {
        if (\is_dir(\PFAD_ROOT . \PFAD_EXPORT) && ($dir = \opendir(\PFAD_ROOT . \PFAD_EXPORT)) !== false) {
            while (($fdir = \readdir($dir)) !== false) {
                if ($fdir !== $fileName && \mb_strpos($fdir, $fileNameSplit) !== false) {
                    \unlink(\PFAD_ROOT . \PFAD_EXPORT . $fdir);
                }
            }
            \closedir($dir);
        }

        return $this;
    }

    public function deleteOldExports(): void
    {
        if (\file_exists(\PFAD_ROOT . \PFAD_EXPORT . $this->model->getFilename())) {
            \unlink(\PFAD_ROOT . \PFAD_EXPORT . $this->model->getFilename());
        }
    }

    public function deleteOldTempFile(): void
    {
        if (\file_exists(\PFAD_ROOT . \PFAD_EXPORT . $this->tmpFileName)) {
            \unlink(\PFAD_ROOT . \PFAD_EXPORT . $this->tmpFileName);
        }
    }

    /**
     * @param array $splits
     * @param int   $fileCounter
     * @return string
     */
    private function getFileName($splits, $fileCounter): string
    {
        $fn = (\is_array($splits) && \count($splits) > 1)
            ? $splits[0] . $fileCounter . $splits[1]
            : $splits[0] . $fileCounter;

        return \PFAD_ROOT . \PFAD_EXPORT . $fn;
    }

    /**
     * @return $this
     */
    public function splitFile(): self
    {
        $file = $this->model->getFilename();
        if ((int)$this->model->getSplitSize() <= 0 || !\file_exists(\PFAD_ROOT . \PFAD_EXPORT . $file)) {
            return $this;
        }
        $fileCounter = 1;
        $splits      = [];
        $fileTypeIdx = \mb_strrpos($file, '.');
        // Dateiname splitten nach Name + Typ
        if ($fileTypeIdx === false) {
            $splits[0] = $file;
        } else {
            $splits[0] = \mb_substr($file, 0, $fileTypeIdx);
            $splits[1] = \mb_substr($file, $fileTypeIdx);
        }
        // Ist die angelegte Datei größer als die Einstellung im Exportformat?
        \clearstatcache();
        if (\filesize(\PFAD_ROOT . \PFAD_EXPORT . $file) >= ($this->model->getSplitSize() * 1024 * 1024 - 102400)) {
            \sleep(2);
            $this->cleanupFiles($file, $splits[0]);
            $handle    = \fopen(\PFAD_ROOT . \PFAD_EXPORT . $file, 'rb');
            $row       = 1;
            $newHandle = \fopen($this->getFileName($splits, $fileCounter), 'wb');
            $filesize  = 0;
            while (($content = \fgets($handle)) !== false) {
                if ($row > 1) {
                    $nSizeZeile = \mb_strlen($content) + 2;
                    // Schwelle erreicht?
                    if ($filesize <= ($this->model->getSplitSize() * 1024 * 1024 - 102400)) {
                        // Schreibe Content
                        \fwrite($newHandle, $content);
                        $filesize += $nSizeZeile;
                    } else {
                        // neue Datei
                        $this->writeFooter($newHandle);
                        \fclose($newHandle);
                        ++$fileCounter;
                        $newHandle = \fopen($this->getFileName($splits, $fileCounter), 'wb');
                        $this->writeHeader($newHandle);
                        // Schreibe Content
                        \fwrite($newHandle, $content);
                        $filesize = $nSizeZeile;
                    }
                } elseif ($row === 1) {
                    $this->writeHeader($newHandle);
                }
                ++$row;
            }
            \fclose($newHandle);
            \fclose($handle);
            \unlink(\PFAD_ROOT . \PFAD_EXPORT . $file);
        }

        return $this;
    }
}
