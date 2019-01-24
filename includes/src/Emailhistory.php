<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Emailhistory
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
     * @param null|int    $kEmailhistory
     * @param null|object $oObj
     */
    public function __construct($kEmailhistory = null, $oObj = null)
    {
        if ((int)$kEmailhistory > 0) {
            $this->loadFromDB($kEmailhistory);
        } elseif ($oObj !== null && is_object($oObj)) {
            foreach (array_keys(get_object_vars($oObj)) as $member) {
                $cMethod = 'set' . substr($member, 1);
                if (method_exists($this, $cMethod)) {
                    $this->$cMethod($oObj->$member);
                }
            }
        }
    }

    /**
     * @param int $kEmailhistory
     * @return $this
     */
    protected function loadFromDB(int $kEmailhistory): self
    {
        $oObj = Shop::Container()->getDB()->select('temailhistory', 'kEmailhistory', $kEmailhistory);
        if (isset($oObj->kEmailhistory) && $oObj->kEmailhistory > 0) {
            foreach (array_keys(get_object_vars($oObj)) as $member) {
                $this->$member = $oObj->$member;
            }
        }

        return $this;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     * @throws Exception
     */
    public function save(bool $bPrim = true)
    {
        $ins = new stdClass();
        foreach (array_keys(get_object_vars($this)) as $member) {
            $ins->$member = $this->$member;
        }
        if (isset($ins->kEmailhistory) && (int)$ins->kEmailhistory > 0) {
            return $this->update();
        }
        unset($ins->kEmailhistory);
        $kPrim = Shop::Container()->getDB()->insert('temailhistory', $ins);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function update(): int
    {
        $sql     = 'UPDATE temailhistory SET ';
        $set     = [];
        $members = array_keys(get_object_vars($this));
        if (is_array($members) && count($members) > 0) {
            $db = Shop::Container()->getDB();
            foreach ($members as $member) {
                $cMethod = 'get' . substr($member, 1);
                if (method_exists($this, $cMethod)) {
                    $val        = $this->$cMethod();
                    $mValue     = $val === null
                        ? 'NULL'
                        : ("'" . $db->escape($val) . "'");
                    $set[] = "{$member} = {$mValue}";
                }
            }
            $sql .= implode(', ', $set);
            $sql .= ' WHERE kEmailhistory = ' . $this->getEmailhistory();

            return $db->query($sql, \DB\ReturnType::AFFECTED_ROWS);
        }
        throw new Exception('ERROR: Object has no members!');
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('temailhistory', 'kEmailhistory', $this->getEmailhistory());
    }

    /**
     * @param string $limitSQL
     * @return array
     */
    public function getAll(string $limitSQL = ''): array
    {
        $historyData = Shop::Container()->getDB()->query(
            'SELECT * 
                FROM temailhistory 
                ORDER BY dSent DESC' . $limitSQL,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $history     = [];
        foreach ($historyData as $item) {
            $history[] = new self(null, $item);
        }

        return $history;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS nCount FROM temailhistory',
            \DB\ReturnType::SINGLE_OBJECT
        )->nCount;
    }

    /**
     * @param array $ids
     * @return bool|int
     */
    public function deletePack(array $ids)
    {
        if (count($ids) > 0) {
            $ids = array_map(function ($i) {
                return (int)$i;
            }, $ids);

            return Shop::Container()->getDB()->query(
                'DELETE 
                    FROM temailhistory 
                    WHERE kEmailhistory IN (' . implode(',', $ids) . ')',
                \DB\ReturnType::AFFECTED_ROWS
            );
        }

        return false;
    }

    /**
     * truncate the email-history-table
     *
     * @return int
     */
    public function deleteAll(): int
    {
        Shop::Container()->getLogService()->notice('eMail-History gelöscht');
        return !Shop::Container()->getDB()->query('TRUNCATE TABLE temailhistory', \DB\ReturnType::AFFECTED_ROWS);
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
    public function getSubject()
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
    public function getFromEmail()
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
    public function getToName()
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
    public function getToEmail()
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
    public function getSent()
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
