<?php declare(strict_types=1);

namespace JTL\Template;

use Exception;
use JTL\Shop;
use Less_Parser;
use RuntimeException;
use ScssPhp\ScssPhp\OutputStyle;
use ScssPhp\ScssPhp\Compiler as BaseCompiler;

class Compiler
{
    /**
     * @var array
     */
    private array $errors = [];

    /**
     * @var array
     */
    private array $compiled = [];

    /**
     * @var string
     */
    private string $customVariables = '';

    /**
     * @var string
     */
    private string $customContent = '';

    private const CACHE_DIR = \PFAD_ROOT . \PFAD_COMPILEDIR . 'tpleditortmp';

    /**
     * @param string $theme
     * @param string $templateDir
     * @return bool
     */
    public function compileSass(string $theme, string $templateDir): bool
    {
        if ($theme === 'base') {
            return true;
        }
        try {
            $themeDir = $this->validateTemplateDir($theme, $templateDir);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        if (\defined('THEME_COMPILE_CACHE') && \THEME_COMPILE_CACHE === true) {
            if (\file_exists(self::CACHE_DIR)) {
                \array_map('\unlink', \glob(self::CACHE_DIR . '/lessphp*'));
            } elseif (!\mkdir(self::CACHE_DIR, 0777) && !\is_dir(self::CACHE_DIR)) {
                throw new RuntimeException(\sprintf('Directory "%s" was not created.', self::CACHE_DIR));
            }
        }
        $input = $themeDir . 'sass/' . $theme . '.scss';
        if (!\file_exists($input)) {
            $this->errors[] = \__(\sprintf('Theme scss file "%s" does not exist.', $input));

            return false;
        }
        try {
            $this->compileSassFile($input, $themeDir . $theme . '.css', $themeDir);
            $critical = $themeDir . 'sass/' . $theme . '_crit.scss';
            if (\file_exists($critical)) {
                $this->compileSassFile($critical, $themeDir . $theme . '_crit.css', $themeDir);
                $this->compiled[] = \__($theme . '_crit.css was compiled successfully.');
            }
            $this->compiled[] = \__(\sprintf('%s.css was compiled successfully.', $theme));

            return true;
        } catch (Exception $e) {
            $this->errors[] = \__($e->getMessage());

            return false;
        }
    }

    /**
     * @param string $file
     * @param string $target
     * @param string $directory
     */
    private function compileSassFile(string $file, string $target, string $directory): void
    {
        $baseDir  = $directory . 'sass/';
        $critical = \strpos($file, '_crit') !== false;
        $compiler = new BaseCompiler();
        if ($critical === true) {
            $compiler->setOutputStyle(OutputStyle::COMPRESSED);
            $compiler->setSourceMap(BaseCompiler::SOURCE_MAP_NONE);
        } else {
            $compiler->setSourceMap(BaseCompiler::SOURCE_MAP_FILE);
            $compiler->setSourceMapOptions([
                'sourceMapURL' => \basename($target) . '.map',
                'sourceMapBasepath' => $directory,
            ]);
        }
        $compiler->addImportPath($baseDir);
        $content = \file_get_contents($file);
        if (\mb_strpos($content, '//#customVariables#') !== false) {
            $content = \str_replace('//#customVariables#', $this->customVariables, $content);
        } else {
            $content = $this->customVariables . "\n" . $content;
        }
        if (\mb_strpos($content, '//#customContent#') !== false) {
            $content = \str_replace('//#customContent#', $this->customContent, $content);
        } else {
            $content .= "\n" . $this->customContent;
        }
        $result = $compiler->compileString($content);
        \file_put_contents($target, $result->getCss());
        if (!$critical) {
            \file_put_contents($target . '.map', $result->getSourceMap());
        }
    }

    /**
     * @param string $theme
     * @param string $templateDir
     * @return bool
     */
    public function compileLess(string $theme, string $templateDir): bool
    {
        try {
            $themeDir = $this->validateTemplateDir($theme, $templateDir);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        $parser = new Less_Parser();
        try {
            $parser->parseFile($themeDir . '/less/theme.less', '/');
            $css = $parser->getCss();
            \file_put_contents($themeDir . '/bootstrap.css', $css);
            $this->compiled[] = \__(\sprintf('%s.css was compiled successfully.', $theme));
            unset($parser);

            return true;
        } catch (Exception $e) {
            $this->errors[] = \__($e->getMessage());

            return false;
        }
    }

    /**
     * @param string $theme
     * @param string $templateDir
     * @return string
     * @throws Exception
     */
    private function validateTemplateDir(string $theme, string $templateDir): string
    {
        $directory  = \realpath(\PFAD_ROOT . $templateDir . $theme);
        $compareDir = \str_replace(['/', '\\'], \DIRECTORY_SEPARATOR, \realpath(\PFAD_ROOT . \PFAD_TEMPLATES));
        if ($directory === false || \strpos($directory . '/', $compareDir) !== 0) {
            throw new Exception(\__(\sprintf('Theme %s does not exist.', $theme)));
        }

        return $directory . '/';
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getCompiled(): array
    {
        return $this->compiled;
    }

    /**
     * @param array $compiled
     */
    public function setCompiled(array $compiled): void
    {
        $this->compiled = $compiled;
    }

    /**
     * @return string
     */
    public function getCustomVariables(): string
    {
        return $this->customVariables;
    }

    /**
     * @param string $customVariables
     */
    public function setCustomVariables(string $customVariables): void
    {
        $this->customVariables = $customVariables;
    }

    /**
     * @return string
     */
    public function getCustomContent(): string
    {
        return $this->customContent;
    }

    /**
     * @param string $customContent
     */
    public function setCustomContent(string $customContent): void
    {
        $this->customContent = $customContent;
    }
}
