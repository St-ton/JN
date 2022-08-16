<?php

namespace JTL;

use Exception;
use JTL\DB\DbInterface;
use stdClass;

/**
 * Class Emailhistory
 * @package JTL
 */
class Emailhistory
{
    /**
     * @var int
     */
    public $kEmailhistory;

    /**
     * @var int
     */
    public $kEmailvorlage;

    /**
     * @var string
     */
    public $cSubject;

    /**
     * @var string
     */
    public $cFromName;

    /**
     * @var string
     */
    public $cFromEmail;

    /**
     * @var string
     */
    public $cToName;

    /**
     * @var string
     */
    public $cToEmail;

    /**
     * @var string - date
     */
    public $dSent;

    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * Emailhistory constructor.
     * @param null|int         $id
     * @param null|object      $data
     * @param null|DbInterface $db
     */
    public function __construct(int $id = null, $data = null, DbInterface $db = null)
    {
        $this->db = $db ?? Shop::Container()->getDB();
        if ($id > 0) {
            $this->loadFromDB($id);
        } elseif ($data !== null && \is_object($data)) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                $methodName = 'set' . \mb_substr($member, 1);
                if (\method_exists($this, $methodName)) {
                    $this->$methodName($data->$member);
                }
            }
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    protected function loadFromDB(int $id): self
    {
        $data = $this->db->select('temailhistory', 'kEmailhistory', $id);
        if (isset($data->kEmailhistory) && $data->kEmailhistory > 0) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                $this->$member = $data->$member;
            }
        }

        return $this;
    }

    /**
     * @param bool $primary
     * @return bool|int
     * @throws Exception
     */
    public function save(bool $primary = true)
    {
        if ($this->kEmailhistory > 0) {
            return $this->update();
        }
        $ins                = new stdClass();
        $ins->kEmailvorlage = $this->kEmailvorlage;
        $ins->cSubject      = $this->cSubject;
        $ins->cFromName     = $this->cFromName;
        $ins->cFromEmail    = $this->cFromEmail;
        $ins->cToName       = $this->cToName;
        $ins->cToEmail      = $this->cToEmail;
        $ins->dSent         = $this->dSent;

        $kPrim = $this->db->insert('temailhistory', $ins);
        if ($kPrim > 0) {
            return $primary ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function update(): int
    {
        $upd                = new stdClass();
        $upd->kEmailhistory = $this->kEmailhistory;
        $upd->kEmailvorlage = $this->kEmailvorlage;
        $upd->cSubject      = $this->cSubject;
        $upd->cFromName     = $this->cFromName;
        $upd->cFromEmail    = $this->cFromEmail;
        $upd->cToName       = $this->cToName;
        $upd->cToEmail      = $this->cToEmail;
        $upd->dSent         = $this->dSent;

        return $this->db->updateRow('temailhistory', 'kEmailhistory', $this->getEmailhistory(), $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return $this->db->delete('temailhistory', 'kEmailhistory', $this->getEmailhistory());
    }

    /**
     * @param string $limitSQL
     * @return array
     */
    public function getAll(string $limitSQL = ''): array
    {
        $historyData = $this->db->getObjects(
            'SELECT * 
                FROM temailhistory 
                ORDER BY dSent DESC' . $limitSQL
        );
        $history     = [];
        foreach ($historyData as $item) {
            $item->kEmailhistory = (int)$item->kEmailhistory;
            $item->kEmailvorlage = (int)$item->kEmailvorlage;
            $history[]           = new self(null, $item, $this->db);
        }

        return $history;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return (int)$this->db->getSingleObject('SELECT COUNT(*) AS cnt FROM temailhistory')->cnt;
    }

    /**
     * @param array $ids
     * @return bool|int
     */
    public function deletePack(array $ids)
    {
        if (\count($ids) === 0) {
            return false;
        }

        return $this->db->getAffectedRows(
            'DELETE 
                FROM temailhistory 
                WHERE kEmailhistory IN (' . \implode(',', \array_map('\intval', $ids)) . ')'
        );
    }

    /**
     * truncate the email-history-table
     * @return int
     */
    public function deleteAll(): int
    {
        Shop::Container()->getLogService()->notice('eMail-History gelöscht');
        $res = $this->db->getAffectedRows('DELETE FROM temailhistory');
        $this->db->query('TRUNCATE TABLE temailhistory');

        return $res;
    }

    /**
     * @return int
     */
    public function getEmailhistory(): int
    {
        return (int)$this->kEmailhistory;
    }

    /**
     * @param int $kEmailhistory
     * @return $this
     */
    public function setEmailhistory(int $kEmailhistory): self
    {
        $this->kEmailhistory = $kEmailhistory;

        return $this;
    }

    /**
     * @return int
     */
    public function getEmailvorlage(): int
    {
        return (int)$this->kEmailvorlage;
    }

    /**
     * @param int $kEmailvorlage
     * @return $this
     */
    public function setEmailvorlage(int $kEmailvorlage): self
    {
        $this->kEmailvorlage = $kEmailvorlage;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->cSubject;
    }

    /**
     * @param string $cSubject
     * @return $this
     */
    public function setSubject($cSubject): self
    {
        $this->cSubject = $cSubject;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFromName(): ?string
    {
        return $this->cFromName;
    }

    /**
     * @param string $cFromName
     * @return $this
     */
    public function setFromName($cFromName): self
    {
        $this->cFromName = $cFromName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFromEmail(): ?string
    {
        return $this->cFromEmail;
    }

    /**
     * @param string $cFromEmail
     * @return $this
     */
    public function setFromEmail($cFromEmail): self
    {
        $this->cFromEmail = $cFromEmail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getToName(): ?string
    {
        return $this->cToName;
    }

    /**
     * @param string $cToName
     * @return $this
     */
    public function setToName($cToName): self
    {
        $this->cToName = $cToName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getToEmail(): ?string
    {
        return $this->cToEmail;
    }

    /**
     * @param string $cToEmail
     * @return $this
     */
    public function setToEmail($cToEmail): self
    {
        $this->cToEmail = $cToEmail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSent(): ?string
    {
        return $this->dSent;
    }

    /**
     * @param string $dSent
     * @return $this
     */
    public function setSent($dSent): self
    {
        $this->dSent = $dSent;

        return $this;
    }
}
