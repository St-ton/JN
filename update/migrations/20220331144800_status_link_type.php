<?php declare(strict_types=1);

use JTL\Language\LanguageHelper;
use JTL\Link\Admin\LinkAdmin;
use JTL\Shop;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220331144800
 */
class Migration_20220331144800 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Add status page';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $linkGroup = $this->getDB()->getSingleObject(
            "SELECT kLinkgruppe 
                FROM tlinkgruppe
                WHERE cTemplatename = 'hidden'"
        );
        if ($linkGroup === null) {
            return;
        }

        $linkAdmin = new LinkAdmin($this->getDB(), Shop::Container()->getCache());

        $link = [
            'kLinkgruppe'    => (int)$linkGroup->kLinkgruppe,
            'kLink'          => 0,
            'kPlugin'        => 0,
            'cName'          => 'Bestellstatus',
            'nLinkart'       => 3,
            'nSpezialseite'  => LINKTYP_BESTELLSTATUS,
            'cKundengruppen' => ['-1'],
            'bIsActive'      => 1,
            'bSSL'           => 0,
            'nSort'          => '',
            'cIdentifier'    => ''
        ];
        foreach (LanguageHelper::getAllLanguages() as $language) {
            $code                              = $language->getIso();
            $link['cName_' . $code]            = '';
            $link['cSeo_' . $code]             = '';
            $link['cTitle_' . $code]           = '';
            $link['cContent_' . $code]         = '';
            $link['cMetaTitle_' . $code]       = '';
            $link['cMetaKeywords_' . $code]    = '';
            $link['cMetaDescription_' . $code] = '';
        }

        if ($this->getDB()->select('tlink', 'nLinkart', LINKTYP_BESTELLSTATUS) === null) {
            $linkAdmin->createOrUpdateLink($link);
        }
        $this->execute(
            "INSERT INTO `tspezialseite` (`kPlugin`, `cName`, `cDateiname`, `nLinkart`, `nSort`) 
                VALUES ('0', 'Bestellstatus', 'status.php', 38, 38);"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
