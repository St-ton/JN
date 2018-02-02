<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @package jtl-shop
 * @since
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
        $this->customerFields = [];
        $this->langID         = $langID;
        $customerFields       = Shop::DB()->selectAll('tkundenfeld', 'kSprache', $langID, '*', 'nSort ASC');

        if (is_array($customerFields)) {
            foreach ($customerFields as $item) {
                $this->prepare($item);
                $this->customerFields[$item->kKundenfeld] = $item;
            }
        }
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
        return array_key_exists($kCustomerField, $this->customerFields) ? $this->customerFields[$kCustomerField] : null;
    }

    /**
     * @param object $customerField
     * @return null|object[]
     */
    public function getCustomerFieldValues($customerField)
    {
        $this->prepare($customerField);

        if ($customerField->cTyp === 'auswahl') {
            return Shop::DB()->selectAll(
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
     */
    public function delete($kCustomerField)
    {
        $kCustomerField = (int)$kCustomerField;

        if ($kCustomerField !== 0) {
            Shop::DB()->delete('tkundenattribut', 'kKundenfeld', $kCustomerField);
            Shop::DB()->delete('tkundenfeldwert', 'kKundenfeld', $kCustomerField);
            Shop::DB()->delete('tkundenfeld', 'kKundenfeld', $kCustomerField);

            unset($this->customerFields[$kCustomerField]);
        }
    }

    /**
     * @param int $kCustomerField
     * @param array $customerFieldValues
     */
    protected function updateCustomerFieldValues($kCustomerField, $customerFieldValues)
    {
        Shop::DB()->delete('tkundenfeldwert', 'kKundenfeld', $kCustomerField);

        foreach ($customerFieldValues as $customerFieldValue) {
            $entitie              = new stdClass();
            $entitie->kKundenfeld = $kCustomerField;
            $entitie->cWert       = $customerFieldValue['cWert'];
            $entitie->nSort       = (int)$customerFieldValue['nSort'];

            Shop::DB()->insert('tkundenfeldwert', $entitie);
        }

        // Delete all customer values that are not in value list
        Shop::DB()->executeQueryPrepared(
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
            NiceDB::RET_AFFECTED_ROWS
        );
    }

    /**
     * @param object $customerField
     * @param null|array $customerFieldValues
     */
    public function update($customerField, $customerFieldValues = null)
    {
        $this->prepare($customerField);
        $key = isset($customerField->kKundenfeld) ? $customerField->kKundenfeld : null;

        if ($key !== null && isset($this->customerFields[$key])) {
            // update...
            $oldType                    = $this->customerFields[$key]->cTyp;
            $this->customerFields[$key] = clone $customerField;
            // this entities are not changeable
            unset($customerField->kKundenfeld);
            unset($customerField->kSprache);
            unset($customerField->cWawi);

            Shop::DB()->update('tkundenfeld', 'kKundenfeld', $key, $customerField);

            if ($oldType !== $customerField->cTyp) {
                // cTyp has been changed
                switch ($oldType) {
                    case 'auswahl':
                        // cTyp changed from "auswahl" to something else - delete values for the customer field
                        Shop::DB()->delete('tkundenfeldwert', 'kKundenfeld', $key);
                        break;
                    default:
                        // actually nothing to do...
                        break;
                }

                switch ($customerField->cTyp) {
                    case 'zahl':
                        // all customer values will be changed to numbers if possible
                        Shop::DB()->executeQueryPrepared(
                            "UPDATE tkundenattribut SET
	                            cWert =	CAST(CAST(cWert AS DOUBLE) AS CHAR)
                                WHERE tkundenattribut.kKundenfeld = :kKundenfeld",
                            ['kKundenfeld' => $key],
                            NiceDB::RET_AFFECTED_ROWS
                        );
                        break;
                    case 'datum':
                        // all customer values will be changed to date if possible
                        Shop::DB()->executeQueryPrepared(
                            "UPDATE tkundenattribut SET
	                            cWert =	DATE_FORMAT(STR_TO_DATE(cWert, '%d.%m.%Y'), '%d.%m.%Y')
                                WHERE tkundenattribut.kKundenfeld = :kKundenfeld",
                            ['kKundenfeld' => $key],
                            NiceDB::RET_AFFECTED_ROWS
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
            $key = Shop::DB()->insert('tkundenfeld', $customerField);

            $customerField->kKundenfeld = $key;
            $this->customerFields[$key] = $customerField;
        }

        if ($customerField->cTyp === 'auswahl' && is_array($customerFieldValues)) {
            $this->updateCustomerFieldValues($key, $customerFieldValues);
        }
    }
}
