<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PHPSettingsHelper
 */
class PHPSettingsHelper
{
    use SingletonTrait;
    
    /**
     * @param string $shorthand
     * @return int
     */
    private function shortHandToInt($shorthand): int
    {
        switch (substr($shorthand, -1)) {
            case 'M':
            case 'm':
                return (int)$shorthand * 1048576;
            case 'K':
            case 'k':
                return (int)$shorthand * 1024;
            case 'G':
            case 'g':
                return (int)$shorthand * 1073741824;
            default:
                return (int)$shorthand;
        }
    }
    
    /**
     * @return int
     */
    public function limit(): int
    {
        return $this->shortHandToInt(ini_get('memory_limit'));
    }
    
    /**
     * @return string
     */
    public function version(): string
    {
        return PHP_VERSION;
    }
    
    /**
     * @return int
     */
    public function executionTime(): int
    {
        return (int)ini_get('max_execution_time');
    }

    /**
     * @return int
     */
    public function postMaxSize(): int
    {
        return $this->shortHandToInt(ini_get('post_max_size'));
    }

    /**
     * @return int
     */
    public function uploadMaxFileSize(): int
    {
        return $this->shortHandToInt(ini_get('upload_max_filesize'));
    }

    /**
     * @return bool
     */
    public function safeMode(): bool
    {
        return false;
    }
    
    /**
     * @return string
     */
    public function tempDir(): string
    {
        return sys_get_temp_dir();
    }
    
    /**
     * @return bool
     */
    public function fopenWrapper(): bool
    {
        return (bool)ini_get('allow_url_fopen');
    }
    
    /**
     * @param int $limit - in bytes
     * @return bool
     */
    public function hasMinLimit(int $limit): bool
    {
        $value = $this->limit();

        return $value === -1 || $value === 0 || $value >= $limit;
    }
    
    /**
     * @param int $limit - in S
     * @return bool
     */
    public function hasMinExecutionTime(int $limit): bool
    {
        return ($this->executionTime() >= $limit || $this->executionTime() === 0);
    }
    
    /**
     * @param int $limit - in bytes
     * @return bool
     */
    public function hasMinPostSize(int $limit): bool
    {
        return $this->postMaxSize() >= $limit;
    }
    
    /**
     * @param int $limit - in bytes
     * @return bool
     */
    public function hasMinUploadSize(int $limit): bool
    {
        return $this->uploadMaxFileSize() >= $limit;
    }
    
    /**
     * @return bool
     */
    public function isTempWriteable(): bool
    {
        return is_writable($this->tempDir());
    }
}
