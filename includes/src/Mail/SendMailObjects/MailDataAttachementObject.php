<?php declare(strict_types=1);

namespace JTL\Mail\SendMailObjects;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;

class MailDataAttachementObject extends AbstractDataObject implements DataTableObjectInterface
{
    private string $primarykey = 'id';
    protected int $id          = 0;
    protected int $mailID      = 0;
    protected string $mime     = '';
    protected string $dir      = '';
    protected string $fileName = '';
    protected string $name     = '';
    protected string $encoding = 'base64';

    private array $mapping = [];

    private array $columnMapping = [
        'primarykey' => 'primarykey',
        'id'         => 'id',
        'mailID'     => 'mailID',
        'mime'       => 'mime',
        'dir'        => 'dir',
        'fileName'   => 'fileName',
        'name'       => 'name',
        'encoding'   => 'encoding',
    ];

    /**
     * @return string
     */
    public function getPrimarykey(): string
    {
        return $this->primarykey;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return MailDataAttachementObject
     */
    public function setId(int $id): MailDataAttachementObject
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getMailID(): int
    {
        return $this->mailID;
    }

    /**
     * @param int $mailID
     * @return MailDataAttachementObject
     */
    public function setMailID(int $mailID): MailDataAttachementObject
    {
        $this->mailID = $mailID;

        return $this;
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * @param string $mime
     * @return MailDataAttachementObject
     */
    public function setMime(string $mime): MailDataAttachementObject
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     * @return MailDataAttachementObject
     */
    public function setDir(string $dir): MailDataAttachementObject
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return MailDataAttachementObject
     */
    public function setFileName(string $fileName): MailDataAttachementObject
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MailDataAttachementObject
     */
    public function setName(string $name): MailDataAttachementObject
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     * @return MailDataAttachementObject
     */
    public function setEncoding(string $encoding): MailDataAttachementObject
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return \array_merge($this->mapping, $this->columnMapping);
    }

    /**
     * @return array
     */
    public function getReverseMapping(): array
    {
        return \array_flip($this->mapping);
    }

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return \array_flip($this->columnMapping);
    }
}
