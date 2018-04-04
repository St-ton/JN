<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @package jtl-shop
 * @since 4.07
 */

/**
 * Class CustomerFields
 */
class CustomerFields
{
    /**
     * @var static[]
     */
    private static $instances;

    /**
     * @var object[]
     */
    protected $customerFields;

    /**
     * @var int
     */
    protected $langID;

    /**
     * CustomerFields constructor.
     * @param int $langID
     */
    public function __construct($langID)
    {
        $this->langID = $langID;
        $this->loadFields($langID);
    }

    /**
     * @param null|int $langID
     * @return static
     */
    public static function getInstance($langID = null)
    {
        if ($langID === null || (int)$langID === 0) {
            $langID = (int)$_SESSION['kSprache'];
        } else {
            $langID = (int)$langID;
        }

        if (!isset(self::$instances[$langID])) {
            self::$instances[$langID] = new static($langID);
        }

        return self::$instances[$langID];
    }

    /**
     * @param int $langID
     */
    protected function loadFields($langID)
    {
        $this->customerFields = [];
        $customerFields       = Shop::Container()->getDB()->selectAll('tkundenfeld', 'kSprache', $langID, '*', 'nSort ASC');

        foreach ($customerFields as $item) {
            $this->prepare($item);
            $this->customerFields[$item->kKundenfeld] = $item;
        }
    }

    /**
     * @param object $customerField
     * @return object
     */
    protected function prepare($customerField)
    {
        $customerField->kKundenfeld = (int)$customerField->kKundenfeld;
        $customerField->kSprache    = (int)$customerField->kSprache;
        $customerField->nSort       = (int)$customerField->nSort;
        $customerField->nPflicht    = (int)$customerField->nPflicht > 0 ? 1 : 0;
        $customerField->nEditierbar = (int)$customerField->nEditierbar > 0 ? 1 : 0;

        return $customerField;
    }

    /**
     * @return object[]
     */
    public function getCustomerFields()
    {
        return deepCopy($this->customerFields);
    }

    /**
     * @param int $kCustomerField
     * @return null|object
     */
    public function getCustomerField($kCustomerField)
    {
        return $this->customerFields[$kCustomerField] ?? null;
    }

    /**
     * @param object $customerField
     * @return null|object[]
     */
    public function getCustomerFieldValues($customerField)
    {
        $this->prepare($customerField);

        if ($customerField->cTyp === 'auswahl') {
            return Shop::Container()->getDB()->selectAll(
                'tkundenfeldwert',
                'kKundenfeld',
                $customerField->kKundenfeld,
                '*',
                'nSort, kKundenfeldWert ASC'
            );
        }

        return null;
    }

    /**
     * @param int $kCustomerField
     * @return bool
     */
    public function delete($kCustomerField)
    {
        $kCustomerField = (int)$kCustomerField;

        if ($kCustomerField !== 0) {
            $ret = Shop::Container()->getDB()->delete('tkundenattribut', 'kKundenfeld', $kCustomerField) >= 0
                && Shop::Container()->getDB()->delete('tkundenfeldwert', 'kKundenfeld', $kCustomerField) >= 0
                && Shop::Container()->getDB()->delete('tkundenfeld', 'kKundenfeld', $kCustomerField) >= 0;

            if ($ret) {
                unset($this->customerFields[$kCustomerField]);
            } else {
                $this->loadFields($this->langID);
            }

            return $ret;
        }

        return false;
    }

    /**
     * @param int $kCustomerField
     * @param array $customerFieldValues
     */
    protected function updateCustomerFieldValues($kCustomerField, $customerFieldValues)
    {
        Shop::Container()->getDB()->delete('tkundenfeldwert', 'kKundenfeld', $kCustomerField);

        foreach ($customerFieldValues as $customerFieldValue) {
            $entitie              = new stdClass();
            $entitie->kKundenfeld = $kCustomerField;
            $entitie->cWert       = $customerFieldValue['cWert'];
            $entitie->nSort       = (int)$customerFieldValue['nSort'];

            Shop::Container()->getDB()->insert('tkundenfeldwert', $entitie);
        }

        // Delete all customer values that are not in value list
        Shop::Container()->getDB()->executeQueryPrepared(
            "DELETE tkundenattribut
                    FROM tkundenattribut
                    INNER JOIN tkundenfeld ON tkundenfeld.kKundenfeld = tkundenattribut.kKundenfeld
                    WHERE tkundenfeld.cTyp = 'auswahl'
                        AND tkundenfeld.kKundenfeld = :kKundenfeld
                        AND NOT EXISTS (
                            SELECT 1
                            FROM tkundenfeldwert
                            WHERE tkundenfeldwert.kKundenfeld = tkundenattribut.kKundenfeld
                                AND tkundenfeldwert.cWert = tkundenattribut.cWert
                        )",
            ['kKundenfeld' => $kCustomerField],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param object $customerField
     * @param null|array $customerFieldValues
     * @return bool
     */
    public function save($customerField, $customerFieldValues = null)
    {
        $this->prepare($customerField);
        $key = $customerField->kKundenfeld ?? null;
        $ret = false;

        if ($key !== null && isset($this->customerFields[$key])) {
            // update...
            $oldType                    = $this->customerFields[$key]->cTyp;
            $this->customerFields[$key] = clone $customerField;
            // this entities are not changeable
            unset($customerField->kKundenfeld);
            unset($customerField->kSprache);
            unset($customerField->cWawi);

            $ret = Shop::Container()->getDB()->update('tkundenfeld', 'kKundenfeld', $key, $customerField) >= 0;

            if ($oldType !== $customerField->cTyp) {
                // cTyp has been changed
                switch ($oldType) {
                    case 'auswahl':
                        // cTyp changed from "auswahl" to something else - delete values for the customer field
                        Shop::Container()->getDB()->delete('tkundenfeldwert', 'kKundenfeld', $key);
                        break;
                    default:
                        // actually nothing to do...
                        break;
                }

                switch ($customerField->cTyp) {
                    case 'zahl':
                        // all customer values will be changed to numbers if possible
                        Shop::Container()->getDB()->executeQueryPrepared(
                            "UPDATE tkundenattribut SET
	                            cWert =	CAST(CAST(cWert AS DOUBLE) AS CHAR)
                                WHERE tkundenattribut.kKundenfeld = :kKundenfeld",
                            ['kKundenfeld' => $key],
                            \DB\ReturnType::AFFECTED_ROWS
                        );
                        break;
                    case 'datum':
                        // all customer values will be changed to date if possible
                        Shop::Container()->getDB()->executeQueryPrepared(
                            "UPDATE tkundenattribut SET
	                            cWert =	DATE_FORMAT(STR_TO_DATE(cWert, '%d.%m.%Y'), '%d.%m.%Y')
                                WHERE tkundenattribut.kKundenfeld = :kKundenfeld",
                            ['kKundenfeld' => $key],
                            \DB\ReturnType::AFFECTED_ROWS
                        );
                        break;
                    case 'text':
                    default:
                        // changed to text - nothing to do...
                        break;
                }
            }
        } else {
            // insert...
            $key = Shop::Container()->getDB()->insert('tkundenfeld', $customerField);

            if ($key > 0) {
                $customerField->kKundenfeld = $key;
                $this->customerFields[$key] = $customerField;

                $ret = true;
            }
        }

        if ($ret) {
            if ($customerField->cTyp === 'auswahl' && is_array($customerFieldValues)) {
                $this->updateCustomerFieldValues($key, $customerFieldValues);
            }
        } else {
            $this->loadFields($this->langID);
        }

        return $ret;
    }
}
