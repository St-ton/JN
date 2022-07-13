<?php declare(strict_types=1);

namespace JTL\Checkout;

use JTL\Customer\Customer;
use JTL\DB\DbInterface;
use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * Class DeliveryAddressTemplate
 * @package JTL\Checkout
 */
class DeliveryAddressTemplate extends Adresse
{
    /**
     * @var int
     */
    public $kLieferadresse;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cAnredeLocalized;

    /**
     * @var string
     */
    public $angezeigtesLand;

    /**
     * @param DbInterface $db
     * @param int         $id
     */
    public function __construct(private DbInterface $db, int $id = 0)
    {
        if ($id > 0) {
            $this->load($id);
        }
    }

    /**
     * @param int $id
     * @return $this|null
     */
    public function load(int $id): ?self
    {
        $obj = $this->db->select('tlieferadressevorlage', 'kLieferadresse', $id);
        if ($obj === null || $obj->kLieferadresse < 1) {
            return null;
        }
        $this->fromObject($obj);
        if ($this->kKunde > 0) {
            $this->kKunde = (int)$this->kKunde;
        }
        if ($this->kLieferadresse > 0) {
            $this->kLieferadresse = (int)$this->kLieferadresse;
        }
        $this->cAnredeLocalized = Customer::mapSalutation($this->cAnrede, 0, $this->kKunde);
        // Workaround for WAWI-39370
        $this->cLand           = self::checkISOCountryCode($this->cLand);
        $this->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($this->cLand);
        if ($this->kLieferadresse > 0) {
            $this->decrypt();
        }

        \executeHook(\HOOK_LIEFERADRESSE_CLASS_LOADFROMDB, ['address' => $this]);

        return $this;
    }

    /**
     * @return int
     */
    public function persist(): int
    {
        $this->encrypt();
        $obj = $this->toObject();

        $obj->cLand = self::checkISOCountryCode($obj->cLand);

        unset($obj->kLieferadresse, $obj->angezeigtesLand, $obj->cAnredeLocalized);

        $this->kLieferadresse = $this->db->insert('tlieferadressevorlage', $obj);
        $this->decrypt();
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $this->kLieferadresse;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $this->encrypt();
        $obj = $this->toObject();

        $obj->cLand = self::checkISOCountryCode($obj->cLand);
        unset($obj->angezeigtesLand, $obj->cAnredeLocalized);
        $res = $this->db->update('tlieferadressevorlage', 'kLieferadresse', $obj->kLieferadresse, $obj);
        $this->decrypt();
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $res;
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        $this->encrypt();
        $obj = $this->toObject();

        return $this->db->delete(
            'tlieferadressevorlage',
            ['kLieferadresse', 'kKunde'],
            [$obj->kLieferadresse, $obj->kKunde]
        );
    }

    /**
     * get shipping address
     *
     * @return array
     */
    public function gibLieferadresseAssoc(): array
    {
        return $this->kLieferadresse > 0
            ? $this->toArray()
            : [];
    }

    /**
     * @return DeliveryAddressTemplate
     */
    public static function createFromObject($data): DeliveryAddressTemplate
    {
        $address                = new self(Shop::Container()->getDB());
        $address->cVorname      = $data->cVorname;
        $address->cNachname     = $data->cNachname;
        $address->cFirma        = $data->cFirma ?? null;
        $address->cZusatz       = $data->cZusatz ?? null;
        $address->kKunde        = $data->kKunde;
        $address->cAnrede       = $data->cAnrede ?? null;
        $address->cTitel        = $data->cTitel;
        $address->cStrasse      = $data->cStrasse;
        $address->cHausnummer   = $data->cHausnummer;
        $address->cAdressZusatz = $data->cAdressZusatz ?? null;
        $address->cPLZ          = $data->cPLZ;
        $address->cOrt          = $data->cOrt;
        $address->cLand         = $data->cLand;
        $address->cBundesland   = $data->cBundesland ?? null;
        $address->cTel          = $data->cTel ?? null;
        $address->cMobil        = $data->cMobil ?? null;
        $address->cFax          = $data->cFax ?? null;
        $address->cMail         = $data->cMail ?? null;

        return $address;
    }

    /**
     * @return Lieferadresse
     */
    public function getDeliveryAddress(): Lieferadresse
    {
        $address                = new Lieferadresse();
        $address->cVorname      = $this->cVorname;
        $address->cNachname     = $this->cNachname;
        $address->cFirma        = $this->cFirma ?? null;
        $address->cZusatz       = $this->cZusatz ?? null;
        $address->kKunde        = $this->kKunde;
        $address->cAnrede       = $this->cAnrede ?? null;
        $address->cTitel        = $this->cTitel;
        $address->cStrasse      = $this->cStrasse;
        $address->cHausnummer   = $this->cHausnummer;
        $address->cAdressZusatz = $this->cAdressZusatz ?? null;
        $address->cPLZ          = $this->cPLZ;
        $address->cOrt          = $this->cOrt;
        $address->cLand         = $this->cLand;
        $address->cBundesland   = $this->cBundesland ?? null;
        $address->cTel          = $this->cTel ?? null;
        $address->cMobil        = $this->cMobil ?? null;
        $address->cFax          = $this->cFax ?? null;
        $address->cMail         = $this->cMail ?? null;

        return $address;
    }
}
