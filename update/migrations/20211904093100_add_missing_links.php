<?php declare(strict_types=1);

use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Link\Admin\LinkAdmin;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20211904093100
 */
class Migration_20211904093100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add missing links';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $linkGroup = $this->getDB()->query(
            "SELECT kLinkgruppe FROM tlinkgruppe WHERE cTemplatename='hidden'",
            ReturnType::SINGLE_OBJECT
        );
        if (empty($linkGroup)) {
            return;
        }

        $linkAdmin = new LinkAdmin(Shop::Container()->getDB(), Shop::Container()->getCache());

        $link = [
            'kLinkgruppe' => $linkGroup->kLinkgruppe,
            'kLink' => 0,
            'kPlugin' => 0,
            'cName' => 'Hersteller',
            'nLinkart' => 3,
            'nSpezialseite' => \LINKTYP_HERSTELLER,
            'cKundengruppen' => ['-1'],
            'bIsActive' => 1,
            'bSSL' => 0,
            'nSort' => '',
            'cIdentifier' => ''
        ];
        foreach (LanguageHelper::getAllLanguages() as $language) {
            $code = $language->getIso();
            $link['cName_' . $code] = '';
            $link['cSeo_' . $code] = '';
            $link['cTitle_' . $code] = '';
            $link['cContent_' . $code] = '';
            $link['cMetaTitle_' . $code] = '';
            $link['cMetaKeywords_' . $code] = '';
            $link['cMetaDescription_' . $code] = '';
        }

        if ($this->getDB()->select('tlink', 'nLinkart', \LINKTYP_HERSTELLER) === null) {
            $linkAdmin->createOrUpdateLink($link);
        }

        if ($this->getDB()->select('tlink', 'nLinkart', \LINKTYP_WRB_FORMULAR) === null) {
            $link['cName']         = 'WR-Formular';
            $link['nSpezialseite'] = \LINKTYP_WRB_FORMULAR;
            $linkAdmin->createOrUpdateLink($link);
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {

    }
}
