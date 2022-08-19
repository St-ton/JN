<?php declare(strict_types=1);

namespace JTL\Checkout;

use JTL\Customer\Customer;
use JTL\DB\DbInterface;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use stdClass;

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
     * @var int
     */
    public int $nIstStandardLieferadresse = 0;

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
        $data = $this->db->select('tlieferadressevorlage', 'kLieferadresse', $id);
        if ($data === null || $data->kLieferadresse < 1) {
            return null;
        }
        $this->kKunde                    = (int)$data->kKunde;
        $this->cAnrede                   = $data->cAnrede;
        $this->cVorname                  = $data->cVorname;
        $this->cNachname                 = $data->cNachname;
        $this->cTitel                    = $data->cTitel;
        $this->cFirma                    = $data->cFirma;
        $this->cZusatz                   = $data->cZusatz;
        $this->cStrasse                  = $data->cStrasse;
        $this->cHausnummer               = $data->cHausnummer;
        $this->cAdressZusatz             = $data->cAdressZusatz;
        $this->cPLZ                      = $data->cPLZ;
        $this->cOrt                      = $data->cOrt;
        $this->cBundesland               = $data->cBundesland;
        $this->cLand                     = $data->cLand;
        $this->cTel                      = $data->cTel;
        $this->cMobil                    = $data->cMobil;
        $this->cFax                      = $data->cFax;
        $this->cMail                     = $data->cMail;
        $this->kLieferadresse            = $id;
        $this->nIstStandardLieferadresse = (int)$data->nIstStandardLieferadresse;
        $this->cAnredeLocalized          = Customer::mapSalutation($this->cAnrede, 0, $this->kKunde);
        // Workaround for WAWI-39370
        $this->cLand           = self::checkISOCountryCode($this->cLand);
        $this->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($this->cLand);
        $this->decrypt();

        \executeHook(\HOOK_LIEFERADRESSE_CLASS_LOADFROMDB, ['address' => $this]);

        return $this;
    }

    /**
     * @return int
     */
    public function persist(): int
    {
        $this->encrypt();
        $ins                            = new stdClass();
        $ins->kKunde                    = $this->kKunde;
        $ins->cAnrede                   = $this->cAnrede;
        $ins->cVorname                  = $this->cVorname;
        $ins->cNachname                 = $this->cNachname;
        $ins->cTitel                    = $this->cTitel;
        $ins->cFirma                    = $this->cFirma;
        $ins->cZusatz                   = $this->cZusatz;
        $ins->cStrasse                  = $this->cStrasse;
        $ins->cHausnummer               = $this->cHausnummer;
        $ins->cAdressZusatz             = $this->cAdressZusatz;
        $ins->cPLZ                      = $this->cPLZ;
        $ins->cOrt                      = $this->cOrt;
        $ins->cBundesland               = $this->cBundesland;
        $ins->cLand                     = self::checkISOCountryCode($this->cLand);
        $ins->cTel                      = $this->cTel;
        $ins->cMobil                    = $this->cMobil;
        $ins->cFax                      = $this->cFax;
        $ins->cMail                     = $this->cMail;
        $ins->nIstStandardLieferadresse = $this->nIstStandardLieferadresse;

        $this->kLieferadresse = $this->db->insert('tlieferadressevorlage', $ins);
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
        $upd                            = new stdClass();
        $upd->kLieferadresse            = $this->kLieferadresse;
        $upd->kKunde                    = $this->kKunde;
        $upd->cAnrede                   = $this->cAnrede;
        $upd->cVorname                  = $this->cVorname;
        $upd->cNachname                 = $this->cNachname;
        $upd->cTitel                    = $this->cTitel;
        $upd->cFirma                    = $this->cFirma;
        $upd->cZusatz                   = $this->cZusatz;
        $upd->cStrasse                  = $this->cStrasse;
        $upd->cHausnummer               = $this->cHausnummer;
        $upd->cAdressZusatz             = $this->cAdressZusatz;
        $upd->cPLZ                      = $this->cPLZ;
        $upd->cOrt                      = $this->cOrt;
        $upd->cBundesland               = $this->cBundesland;
        $upd->cLand                     = self::checkISOCountryCode($this->cLand);
        $upd->cTel                      = $this->cTel;
        $upd->cMobil                    = $this->cMobil;
        $upd->cFax                      = $this->cFax;
        $upd->cMail                     = $this->cMail;
        $upd->nIstStandardLieferadresse = $this->nIstStandardLieferadresse;

        $res = $this->db->update('tlieferadressevorlage', 'kLieferadresse', $upd->kLieferadresse, $upd);
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

        return $this->db->delete(
            'tlieferadressevorlage',
            ['kLieferadresse', 'kKunde'],
            [$this->kLieferadresse, $this->kKunde]
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
     * @param object $data
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
