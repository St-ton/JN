<?php

use Andyftw\Faker\ImageProvider;
use Cocur\Slugify\Slugify;
use Faker\Factory as Fake;
use ShopCli\Faker\de_DE\Commerce;

/**
 * Class DemoDataInstaller
 */
class DemoDataInstaller
{
    /**
     * number of categories to create.
     */
    const NUM_CATEGORIES = 10;

    /**
     * number of articles to create.
     */
    const NUM_ARTICLES = 50;

    /**
     * number of manufacturers to create.
     */
    const NUM_MANUFACTURERS = 10;

    /**
     * number of customers to create.
     */
    const NUM_CUSTOMERS = 100;

    /**
     * font file.
     */
    const FONT_FILE = 'OpenSans-Regular.ttf';

    /**
     * @var \ShopCli\Controller\ShopController
     */
    protected $shop;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var \Cocur\Slugify\Slugify
     */
    private $slugify;

    /**
     * @var \NiceDB
     */
    private $pdo;

    /**
     * @var array
     */
    private static $_defaultConfig = [
        'manufacturers' => self::NUM_MANUFACTURERS,
        'categories'    => self::NUM_CATEGORIES,
        'articles'      => self::NUM_ARTICLES,
        'customers'     => self::NUM_CUSTOMERS,
    ];

    /**
     * DemoData constructor.
     * @param NiceDB $DB
     * @param array  $config
     */
    public function __construct(\NiceDB $DB, array $config = [])
    {
        $this->pdo    = $DB;
        $this->config = array_merge(static::$_defaultConfig, $config);

        $this->faker = Fake::create('de_DE');
        $this->faker->addProvider(new Commerce($this->faker));
        $this->faker->addProvider(new ImageProvider($this->faker));

        $this->slugify = new Slugify([
            'lowercase' => false,
            'rulesets'  => ['default', 'german'],
        ]);
    }

    protected function execute()
    {
//        $io = $this->getIO();
//        $shop = $this->getController('shop');
//        $targetDirectory = $this->getOption('target-dir');

//        $shop->setBasePath($targetDirectory);
//        $shop->validateConfig();

        $config = [
            'manufacturers' => max(0, (int)$this->config['manufacturers']),
            'categories'    => max(0, (int)$this->config['categories']),
            'articles'      => max(0, (int)$this->config['articles']),
            'customers'     => max(0, (int)$this->config['customers']),
        ];

        $steps = count(array_filter($config));
        $step  = 1;

//        foreach ($config as $kindName => $kindCount) {
//            if ($kindCount > 0) {
//                $io->setStep($step, $steps, 'Creating '.$kindCount.' '.Text::singular($kindName, $kindCount));
//                $io->progress(
//                    function ($mycb) use (&$demoData, $kindName) {
//                        $demoData->{Text::camelize('create_'.$kindName)}(
//                            function ($index, $limit, $success, $name) use (&$mycb) {
//                                $mycb(round($index * 100 / $limit), $limit, $index, $name);
//                            }
//                        );
//                    }, '  %percent:-3s%% [%bar%] %message%'
//                );
//                ++$step;
//            }
//        }

        $this->updateRatingsAvg()->updateGlobals();
    }

    /**
     * @param null $callback
     * @return $this
     */
    public function run($callback = null): self
    {
        $this->cleanup()
             ->addCompanyData()
             ->createManufacturers($callback)
             ->createCategories($callback)
             ->createArticles($callback)
             ->updateRatingsAvg()
             ->setConfig()
             ->updateGlobals();

        return $this;
    }

    /**
     * @return $this
     */
    public function setConfig(): self
    {
        $this->pdo->query(
            "UPDATE `teinstellungen` SET `cWert`='Y' WHERE `kEinstellungenSektion`='107' AND cName = 'bewertung_anzeigen';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `teinstellungen` SET `cWert`='10' WHERE `kEinstellungenSektion`='2' AND cName = 'startseite_bestseller_anzahl';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `teinstellungen` SET `cWert`='10' WHERE `kEinstellungenSektion`='2' AND cName = 'startseite_neuimsortiment_anzahl';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `teinstellungen` SET `cWert`='10' WHERE `kEinstellungenSektion`='2' AND cName = 'startseite_sonderangebote_anzahl';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `teinstellungen` SET `cWert`='10' WHERE `kEinstellungenSektion`='2' AND cName = 'startseite_topangebote_anzahl';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen` SET `cWert`='Y' WHERE `cTemplate`='Evo' AND `cSektion`='megamenu' AND `cName`='show_pages';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen` SET `cWert`='Y' WHERE `cTemplate`='Evo' AND `cSektion`='megamenu' AND `cName`='show_manufacturers';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen` SET `cWert`='Y' WHERE `cTemplate`='Evo' AND `cSektion`='footer' AND `cName`='newsletter_footer';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen` SET `cWert`='Y' WHERE `cTemplate`='Evo' AND `cSektion`='footer' AND `cName`='socialmedia_footer';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen` SET `cWert`='https://www.facebook.com/JTLSoftware/' WHERE `cTemplate`='Evo' AND `cSektion`='footer' AND `cName`='facebook';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen` SET `cWert`='https://twitter.com/JTLSoftware' WHERE `cTemplate`='Evo' AND `cSektion`='footer' AND `cName`='twitter';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen` SET `cWert`='https://www.youtube.com/user/JTLSoftwareGmbH' WHERE `cTemplate`='Evo' AND `cSektion`='footer' AND `cName`='youtube';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen` SET `cWert`='https://www.xing.com/companies/jtl-softwaregmbh' WHERE `cTemplate`='Evo' AND `cSektion`='footer' AND `cName`='xing';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `tlinksprache` SET `cTitle`='Startseite!', `cContent`='" . $this->faker->text(500) . "' WHERE `kLink`='3' AND `cISOSprache`='ger';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `tlinksprache` SET `cTitle`='Home!', `cContent`='" . $this->faker->text(500) . "' WHERE `kLink`='3' AND `cISOSprache`='eng';",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `teinheit` (`kEinheit`, `kSprache`, `cName`) VALUES (1,1,'kg'),(1,2,'kg'),(2,1,'ml'),(2,2,'ml'),(3,1,'Stk'),(3,2,'Piece');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlink` (`kLink`,`kVaterLink`,`kPlugin`,`cName`,`nLinkart`,`cNoFollow`,`cKundengruppen`,`cSichtbarNachLogin`,`cDruckButton`,`nSort`,`bSSL`,`bIsFluid`,`cIdentifier`) 
                VALUES (100,0,0,'NurEndkunden',1,'N','1;','N','N',0,0,0,'');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlink` (`kLink`,`kVaterLink`,`kPlugin`,`cName`,`nLinkart`,`cNoFollow`,`cKundengruppen`,`cSichtbarNachLogin`,`cDruckButton`,`nSort`,`bSSL`,`bIsFluid`,`cIdentifier`) 
                VALUES (101,0,0,'NurHaendler',1,'N','2;','N','N',0,0,0,'');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlink` (`kLink`,`kVaterLink`,`kPlugin`,`cName`,`nLinkart`,`cNoFollow`,`cKundengruppen`,`cSichtbarNachLogin`,`cDruckButton`,`nSort`,`bSSL`,`bIsFluid`,`cIdentifier`) 
                VALUES (102,0,9,0,'Beispiel',1,'N',NULL,'N','N',0,0,0,'');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlink` (`kLink`,`kVaterLink`,`kPlugin`,`cName`,`nLinkart`,`cNoFollow`,`cKundengruppen`,`cSichtbarNachLogin`,`cDruckButton`,`nSort`,`bSSL`,`bIsFluid`,`cIdentifier`) 
                VALUES (103,102,0,'Kindseite1',1,'N',NULL,'N','N',0,0,0,'');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlink` (`kLink`,`kVaterLink`,`kPlugin`,`cName`,`nLinkart`,`cNoFollow`,`cKundengruppen`,`cSichtbarNachLogin`,`cDruckButton`,`nSort`,`bSSL`,`bIsFluid`,`cIdentifier`) 
                VALUES (104,102,0,'Kindseite2',1,'N',NULL,'N','N',0,0,0,'');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            'INSERT INTO `tlinkgroupassociations` (`linkID`,`linkGroupID`) 
                VALUES (100, 9), (101, 9), (102, 9), (103, 9), (104, 9);',
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,`cMetaTitle`,`cMetaKeywords`,`cMetaDescription`) 
                VALUES (100,'customers-only','eng','Customers only','Customers only','" . $this->faker->text(500) . "','','','');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,`cMetaTitle`,`cMetaKeywords`,`cMetaDescription`) 
                VALUES (100,'nur-kunden','ger','Nur Endkunden','Nur Endkunden','" . $this->faker->text(500) . "','','','');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,`cMetaTitle`,`cMetaKeywords`,`cMetaDescription`) 
                VALUES (101,'retailers-only','eng','Retailers only','Retailers only','" . $this->faker->text(500) . "','','','');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,`cMetaTitle`,`cMetaKeywords`,`cMetaDescription`) 
                VALUES (101,'nur-haendler','ger','Nur Haendler','Nur Haendler','" . $this->faker->text(500) . "','','','');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,`cMetaTitle`,`cMetaKeywords`,`cMetaDescription`) 
                VALUES (102,'beispiel-seite','ger','Beispielseite','Beispielseite','" . $this->faker->text(500) . "','','','');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,`cMetaTitle`,`cMetaKeywords`,`cMetaDescription`) 
                VALUES (103,'kindseite-eins','ger','Kindseite1','Kindseite1','" . $this->faker->text(500) . "','','','');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,`cMetaTitle`,`cMetaKeywords`,`cMetaDescription`) 
                VALUES (104,'kindseite-zwei','ger','Kindseite2','Kindseite2','" . $this->faker->text(500) . "','','','');",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('nur-endkunden', 'kLink', 100, 3);",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('customers-only', 'kLink', 100, 2);",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('nur-haendler', 'kLink', 101, 3);",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('retailers-only', 'kLink', 101, 2);",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('beispiel-seite', 'kLink', 102, 3);",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('kindseite-eins', 'kLink', 103, 3);",
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('kindseite-zwei', 'kLink', 104, 3);",
            \DB\ReturnType::DEFAULT
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function cleanup(): self
    {
        $this->pdo->query(
            'TRUNCATE TABLE tkategorie; TRUNCATE TABLE tartikel; TRUNCATE TABLE tartikelpict; ' .
            'TRUNCATE TABLE tkategorieartikel; TRUNCATE TABLE tbewertung; TRUNCATE TABLE tartikelext; ' .
            'TRUNCATE TABLE tkategoriepict; TRUNCATE TABLE thersteller; TRUNCATE TABLE tpreise; ' .
            'TRUNCATE TABLE tpreis; TRUNCATE TABLE tpreisdetail; TRUNCATE TABLE teinheit; TRUNCATE TABLE tkunde;',
            \DB\ReturnType::DEFAULT
        );
        $this->pdo->query('DELETE FROM tlink WHERE kLink > 99;', \DB\ReturnType::DEFAULT);
        $this->pdo->query('DELETE FROM tlinksprache WHERE kLink > 99;', \DB\ReturnType::DEFAULT);
        $this->pdo->query("DELETE FROM tseo WHERE cKey = 'kLink' AND kKey > 99;", \DB\ReturnType::DEFAULT);
        $this->pdo->query("DELETE FROM tseo WHERE cKey = 'kArtikel' OR cKey = 'kKategorie' OR cKey = 'kHersteller'",
            \DB\ReturnType::DEFAULT);

        return $this;
    }

    /**
     * @return DemoDataInstaller
     */
    public function addCompanyData(): self
    {
        $ins                = new stdClass();
        $ins->cName         = 'Beispiel GmbH';
        $ins->cUnternehmer  = 'Max Mustermann';
        $ins->cStrasse      = 'Zufallsstraße';
        $ins->cHausnummer   = 42;
        $ins->cPLZ          = '12345';
        $ins->cOrt          = 'Beispielshausen';
        $ins->cLand         = 'Deutschland';
        $ins->cTel          = '01234 123456789';
        $ins->cFax          = '01234 123456788';
        $ins->cEMail        = 'info@example.com';
        $ins->cWWW          = 'www.example.com';
        $ins->cKontoinhaber = 'Beispiel GmbH';
        $ins->cBLZ          = '1112250000';
        $ins->cKontoNr      = '1337133713';
        $ins->cBank         = 'Sparkasse Entenhausen';
        $ins->cIBAN         = 'DE257864472';
        $ins->cBIC          = 'FOOOBAR';
        $this->pdo->insert('tfirma', $ins);

        return $this;
    }

    /**
     * @return int
     */
    public function updateGlobals(): int
    {
        return $this->pdo->query('UPDATE tglobals SET dLetzteAenderung = now()', \DB\ReturnType::AFFECTED_ROWS);
    }

    /**
     * @return $this
     */
    public function updateRatingsAvg(): self
    {
        $this->pdo->query('TRUNCATE TABLE tartikelext', \DB\ReturnType::DEFAULT);
        $this->pdo->query(
            'INSERT INTO tartikelext(kArtikel, fDurchschnittsBewertung) SELECT kArtikel, AVG(nSterne) FROM tbewertung GROUP BY kArtikel',
            \DB\ReturnType::DEFAULT
        );

        return $this;
    }

    /**
     * @param null $callback
     * @return $this
     */
    public function createManufacturers($callback = null): self
    {
        $maxPk      = (int)$this->pdo->query(
            'SELECT max(kHersteller) AS maxPk FROM thersteller',
            \DB\ReturnType::SINGLE_OBJECT
        )->maxPk;
        $limit      = $this->config['manufacturers'];
        $name_index = 0;

        for ($i = 1; $i <= $limit; ++$i) {
            try {
                $_name = $this->faker->unique()->company;
                $res   = $this->pdo->query('SELECT kHersteller FROM thersteller WHERE cName = "' . $_name . '"',
                    \DB\ReturnType::ARRAY_OF_OBJECTS);
                if (is_array($res) && count($res) > 0) {
                    throw new \OverflowException();
                }
            } catch (\OverflowException $e) {
                $_name = $this->faker->unique(true)->company . '_' . ++$name_index;
            }

            $_manufacturer              = new \stdClass();
            $_manufacturer->kHersteller = $maxPk + $i;
            $_manufacturer->cName       = $_name;
            $_manufacturer->cSeo        = $this->slug($_name);
            $_manufacturer->cHomepage   = $this->faker->unique()->url;
            $_manufacturer->nSortNr     = 0;
            $_manufacturer->cBildpfad   = $this->createManufacturerImage($_manufacturer->kHersteller, $_name);
            $res                        = $this->pdo->insert('thersteller', $_manufacturer);
            if ($res > 0) {
                $_seoEntry       = new \stdClass();
                $_seoEntry->cKey = 'kHersteller';
                $_seoEntry->cSeo = $_manufacturer->cSeo;

                $seo_index = 0;
                while (($data = $this->pdo->select('tseo', 'cKey', $_seoEntry->cKey, 'cSeo', $_seoEntry->cSeo)) !== false && is_array($data) && count($data) > 0) {
                    $_seoEntry->cSeo = $_manufacturer->cSeo . '_' . ++$seo_index;
                }

                $_seoEntry->kKey     = $_manufacturer->kHersteller;
                $_seoEntry->kSprache = 1;
                $this->pdo->insert('tseo', $_seoEntry);

                $_seoEntry->cSeo     = $_seoEntry->cSeo . '-en';
                $_seoEntry->kSprache = 2;
                $this->pdo->insert('tseo', $_seoEntry);
            }

            $this->callback($callback, $i, $limit, $res > 0, $_name);
        }

        return $this;
    }

    /**
     * @param null $callback
     * @return $this
     */
    public function createCategories($callback = null): self
    {
        $maxPk      = (int)$this->pdo->query(
            'SELECT max(kKategorie) AS maxPk FROM tkategorie',
            \DB\ReturnType::SINGLE_OBJECT
        )->maxPk;
        $limit      = $this->config['categories'];
        $name_index = 0;
        for ($i = 1; $i <= $limit; ++$i) {
            try {
                $_name = $this->faker->unique()->department;
                $res   = $this->pdo->query(
                    'SELECT kKategorie FROM tkategorie WHERE cName = "' . $_name . '"',
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                if (is_array($res) && count($res) > 0) {
                    throw new \OverflowException();
                }
            } catch (\OverflowException $e) {
                $_name = $this->faker->unique(true)->department . '_' . ++$name_index;
            }
            $_category                        = new \stdClass();
            $_category->kKategorie            = $maxPk + $i;
            $_category->cName                 = $_name;
            $_category->cSeo                  = $this->slug($_name);
            $_category->cBeschreibung         = $this->faker->text(200);
            $_category->kOberKategorie        = rand(0, $_category->kKategorie - 1);
            $_category->nSort                 = 0;
            $_category->dLetzteAktualisierung = 'now()';
            $_category->lft                   = 0;
            $_category->rght                  = 0;
            $res                              = $this->pdo->insert('tkategorie', $_category);
            if ($res > 0) {
                $_seoEntry       = new \stdClass();
                $_seoEntry->cKey = 'kKategorie';
                $_seoEntry->cSeo = $_category->cSeo;

                $seo_index = 0;
                while (($data = $this->pdo->select('tseo', 'cKey', $_seoEntry->cKey, 'cSeo', $_seoEntry->cSeo)) !== false && is_array($data) && count($data) > 0) {
                    $_seoEntry->cSeo = $_category->cSeo . '_' . ++$seo_index;
                }

                $_seoEntry->kKey     = $_category->kKategorie;
                $_seoEntry->kSprache = 1;
                $this->pdo->insert('tseo', $_seoEntry);

                $_seoEntry->cSeo     = $_seoEntry->cSeo . '-en';
                $_seoEntry->kSprache = 2;
                $this->pdo->insert('tseo', $_seoEntry);

                $this->createCategoryImage($_category->kKategorie, $_name);
            }

            $this->callback($callback, $i, $limit, $res > 0, $_name);
        }
        $this->rebuildCategoryTree(0, 1);

        return $this;
    }

    /**
     * @param null $callback
     * @return $this
     */
    public function createArticles($callback = null): self
    {
        $maxPk             = (int)$this->pdo->query(
            'SELECT max(kArtikel) AS maxPk FROM tartikel',
            \DB\ReturnType::SINGLE_OBJECT
        )->maxPk;
        $manufacturesCount = (int)$this->pdo->query(
            'SELECT count(kHersteller) AS mCount FROM thersteller',
            \DB\ReturnType::SINGLE_OBJECT
        )->mCount;
        $categoryCount     = (int)$this->pdo->query(
            'SELECT count(kKategorie) AS mCount FROM tkategorie',
            \DB\ReturnType::SINGLE_OBJECT
        )->mCount;

        if ($categoryCount === 0) {
            return $this;
        }

        $unitCount = (int)$this->pdo->query(
            'SELECT max(groupCount) AS unitCount
                FROM (
                    SELECT count(*) AS groupCount
                    FROM teinheit
                    GROUP BY kSprache
                ) x',
            \DB\ReturnType::SINGLE_OBJECT
        )->unitCount;

        $limit      = $this->config['articles'];
        $name_index = 0;
        $_taxRate   = 19.00;

        for ($i = 1; $i <= $limit; ++$i) {
            try {
                $_name = $this->faker->unique()->productName;
                $res   = $this->pdo->query(
                    'SELECT kArtikel FROM tartikel WHERE cName = "' . $_name . '"',
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                if (is_array($res) && count($res) > 0) {
                    throw new \OverflowException();
                }
            } catch (\OverflowException $e) {
                $_name = $this->faker->unique(true)->productName . '_' . ++$name_index;
            }

            $_articlePrice                      = rand(1, 2999);
            $_article                           = new \stdClass();
            $_article->kArtikel                 = $maxPk + $i;
            $_article->kHersteller              = rand(0, $manufacturesCount);
            $_article->kLieferstatus            = 0;
            $_article->kSteuerklasse            = 1;
            $_article->kEinheit                 = (10 === rand(0, 10)) && $unitCount > 0 ? rand(1, $unitCount) : 0;
            $_article->kVersandklasse           = 1;
            $_article->kEigenschaftKombi        = 0;
            $_article->kVaterArtikel            = 0;
            $_article->kStueckliste             = 0;
            $_article->kWarengruppe             = 0;
            $_article->kVPEEinheit              = 0;
            $_article->kMassEinheit             = 0;
            $_article->kGrundpreisEinheit       = 0;
            $_article->cName                    = $_name;
            $_article->cSeo                     = $this->slug($_name);
            $_article->cArtNr                   = $this->faker->ean8();
            $_article->cBeschreibung            = $this->faker->text(300);
            $_article->cAnmerkung               = '';
            $_article->fLagerbestand            = (float)rand(0, 1000);
            $_article->fStandardpreisNetto      = $_articlePrice / 19.00;
            $_article->fMwSt                    = $_taxRate;
            $_article->fMindestbestellmenge     = (5 < rand(0, 10)) ? rand(0, 5) : 0;
            $_article->fLieferantenlagerbestand = 0;
            $_article->fLieferzeit              = 0;
            $_article->cBarcode                 = $this->faker->ean13;
            $_article->cTopArtikel              = (10 === rand(0, 10)) ? 'Y' : 'N';
            $_article->fGewicht                 = (float)rand(0, 10);
            $_article->fArtikelgewicht          = $_article->fGewicht;
            $_article->fMassMenge               = 0; //@todo?
            $_article->fGrundpreisMenge         = 0;
            $_article->fBreite                  = 0;
            $_article->fHoehe                   = 0;
            $_article->fLaenge                  = 0;
            $_article->cNeu                     = (10 === rand(0, 10)) ? 'Y' : 'N';
            $_article->cKurzBeschreibung        = $this->faker->text(50);
            $_article->fUVP                     = (10 === rand(0, 10)) ? ($_articlePrice / 2) : 0;
            $_article->cLagerBeachten           = (10 === rand(0, 10)) ? 'Y' : 'N';
            $_article->cLagerKleinerNull        = $_article->cLagerBeachten;
            $_article->cLagerVariation          = 'N';
            $_article->cTeilbar                 = 'N';
            $_article->fPackeinheit             = (10 === rand(0, 10)) ? rand(1, 12) : 1;
            $_article->fAbnahmeintervall        = 0;
            $_article->fZulauf                  = 0;
            $_article->cVPE                     = 'N';
            $_article->fVPEWert                 = 0;
            $_article->nSort                    = 0;
            $_article->dErscheinungsdatum       = 'now()';
            $_article->dErstellt                = 'now()';
            $_article->dLetzteAktualisierung    = 'now()';
            $articleID                          = $this->pdo->insert('tartikel', $_article); //@todo!
            if ($articleID > 0) {
                $_maxImages = $this->faker->numberBetween(1, 3);
                for ($k = 0; $k < $_maxImages; ++$k) {
                    $this->createArticleImage($_article->kArtikel, $_name, $k + 1);
                }
                $_numRatings = $this->faker->numberBetween(0, 6);
                for ($j = 0; $j < $_numRatings; ++$j) {
                    $this->createRating($_article->kArtikel);
                }

                $_articleCategory                    = new \stdClass();
                $_articleCategory->kKategorieArtikel = $_article->kArtikel;
                $_articleCategory->kArtikel          = $_article->kArtikel;
                $_articleCategory->kKategorie        = rand(1, $categoryCount);
                $this->pdo->insert('tkategorieartikel', $_articleCategory);

                $_seoEntry       = new \stdClass();
                $_seoEntry->cKey = 'kArtikel';
                $_seoEntry->cSeo = $_article->cSeo;

                $seo_index = 0;
                while (($data = $this->pdo->select('tseo', 'cKey', $_seoEntry->cKey, 'cSeo',
                        $_seoEntry->cSeo)) !== false && is_array($data) && count($data) > 0) {
                    $_seoEntry->cSeo = $_article->cSeo . '_' . ++$seo_index;
                }

                $_seoEntry->kKey     = $_article->kArtikel;
                $_seoEntry->kSprache = 1;
                $this->pdo->insert('tseo', $_seoEntry);

                $_seoEntry->cSeo     = $_seoEntry->cSeo . '-en';
                $_seoEntry->kSprache = 2;
                $this->pdo->insert('tseo', $_seoEntry);

                $_price                = new \stdClass();
                $_price->kKundengruppe = 1;
                $_price->kArtikel      = $_article->kArtikel;
                $_price->fVKNetto      = $_articlePrice / 19.00;
                $this->pdo->insert('tpreise', $_price);

                $_price->kKundengruppe = 2;
                $_price->fVKNetto      = (rand(0, 1) === 0) ? $_price->fVKNetto : ($_price->fVKNetto * 0.9);
                $this->pdo->insert('tpreise', $_price);

                $_price2                = new \stdClass();
                $_price2->kArtikel      = $_article->kArtikel;
                $_price2->kKundengruppe = 1;
                $idxKg1                 = $this->pdo->insert('tpreis', $_price2);
                if ($idxKg1 > 0) {
                    $_price3            = new \stdClass();
                    $_price3->kPreis    = $idxKg1;
                    $_price3->nAnzahlAb = 0;
                    $_price3->fVKNetto  = $_articlePrice / 19.00;
                    $this->pdo->insert('tpreisdetail', $_price3);
                }

                $_price2->kKundengruppe = 2;
                $idxKg2                 = $this->pdo->insert('tpreis', $_price2);
                if ($idxKg2 > 0) {
                    $_price3            = new \stdClass();
                    $_price3->kPreis    = $idxKg2;
                    $_price3->nAnzahlAb = 0;
                    $_price3->fVKNetto  = $_articlePrice / 19.00;
                    $this->pdo->insert('tpreisdetail', $_price3);
                }
            }

            $this->callback($callback, $i, $limit, $articleID > 0, $_name);
        }

        return $this;
    }

    /**
     * @param null $callback
     * @return $this
     */
    public function createCustomers($callback = null): self
    {
        $limit  = $this->config['customers'];
        $fake   = $this->faker;
        $pdo    = $this->pdo;
        $secret = BLOWFISH_KEY;
        $oXTEA  = new \XTEA($secret);

        for ($i = 1; $i <= $limit; ++$i) {
            if (rand(0, 1) === 0) {
                $firstName = $fake->firstNameMale;
                $gender    = 'm';
            } else {
                $firstName = $fake->firstNameFemale;
                $gender    = 'w';
            }
            $firstName     = $firstName;
            $lastName      = $fake->lastName;
            $streetName    = $fake->streetName;
            $houseNr       = rand(1, 200);
            $cityName      = $fake->city;
            $postcode      = $fake->postcode;
            $email         = $fake->email;
            $dateofbirth   = $fake->date('Y-m-d', '1998-12-31');
            $password      = password_hash('pass', PASSWORD_DEFAULT);
            $streetNameEnc = $oXTEA->encrypt($streetName);
            $lastNameEnc   = $oXTEA->encrypt($lastName);
            $lastName      = $fake->lastName;

            $insertObj = (object)[
                'kKundengruppe'      => 1,
                'kSprache'           => 1,
                'cKundenNr'          => '',
                'cPasswort'          => $password,
                'cAnrede'            => $gender,
                'cTitel'             => '',
                'cVorname'           => $firstName,
                'cNachname'          => $lastNameEnc,
                'cFirma'             => '',
                'cZusatz'            => '',
                'cStrasse'           => $streetNameEnc,
                'cHausnummer'        => $houseNr,
                'cAdressZusatz'      => '',
                'cPLZ'               => $postcode,
                'cOrt'               => $cityName,
                'cBundesland'        => '',
                'cLand'              => 'DE',
                'cTel'               => '',
                'cMobil'             => '',
                'cFax'               => '',
                'cMail'              => $email,
                'cUSTID'             => '',
                'cWWW'               => '',
                'cSperre'            => 'N',
                'fGuthaben'          => 0.0,
                'cNewsletter'        => '',
                'dGeburtstag'        => $dateofbirth,
                'fRabatt'            => 0.0,
                'dErstellt'          => 'now()',
                'dVeraendert'        => 'now()',
                'cAktiv'             => 'Y',
                'cAbgeholt'          => 'N',
                'nRegistriert'       => 1,
                'nLoginversuche'     => 0,
                'cResetPasswordHash' => '',
            ];

            $res = $pdo->insert('tkunde', $insertObj);
            $this->callback($callback, $i, $limit, $res > 0, $firstName . ' ' . $lastName);
        }

        return $this;
    }

    /**
     * @param string      $path
     * @param null|string $string
     * @param int         $width
     * @param int         $height
     * @return bool
     */
    private function _createImage($path, $string = null, int $width = 500, int $height = 500): bool
    {
        $font     = $this->getFontFile();
        $filepath = $this->faker->imageFile(null, $width, $height, 'jpg', true, $string, null, null, $font);

        return $filepath !== null && rename($filepath, $path);
    }

    /**
     * @param int    $manufacturerID
     * @param string $string
     * @return string
     */
    private function createManufacturerImage(int $manufacturerID, $string): string
    {
        if ($manufacturerID > 0) {
            $file       = $this->slug($string) . '.jpg';
            $pathNormal = PFAD_ROOT . 'bilder/hersteller/normal/' . $file;
            $pathSmall  = PFAD_ROOT . 'bilder/hersteller/klein/' . $file;

            return ($this->_createImage($pathNormal, $string) === true
                && $this->_createImage($pathSmall, $string, 100, 100) === true)
                ? $file
                : '';
        }

        return '';
    }

    /**
     * @param int    $articleID
     * @param string $string
     * @param int    $imageNumber
     */
    private function createArticleImage(int $articleID, $string, $imageNumber)
    {
        $maxPk = (int)$this->pdo->query(
            'SELECT max(kArtikelPict) AS maxPk FROM tartikelpict',
            \DB\ReturnType::SINGLE_OBJECT
        )->maxPk;

        if ($articleID > 0) {
            $file = '1024_1024_' . md5($string . $articleID . $imageNumber) . '.jpg';
            $path = PFAD_ROOT . 'media/image/storage/' . $file;

            if ($this->_createImage($path, $string, 1024, 1024) === true) {
                $_image                   = new \stdClass();
                $_image->cPfad            = $file;
                $_image->kBild            = $this->pdo->insert('tbild', $_image);
                $_image->kArtikelPict     = $maxPk + 1;
                $_image->kMainArtikelBild = 0;
                $_image->kArtikel         = $articleID;
                $_image->nNr              = $imageNumber;
                $this->pdo->insert('tartikelpict', $_image);
            }
        }
    }

    /**
     * @param int    $categoryID
     * @param string $string
     */
    private function createCategoryImage(int $categoryID, $string)
    {
        if ($categoryID > 0) {
            $file = $this->slug($string) . '.jpg';
            $path = PFAD_ROOT . 'bilder/kategorien/' . $file;

            if ($this->_createImage($path, $string, 200, 200) === true) {
                $_image             = new \stdClass();
                $_image->kKategorie = $categoryID;
                $_image->cPfad      = $file;
                $this->pdo->insert('tkategoriepict', $_image);
            }
        }
    }

    /**
     * @param int $articleID
     * @return bool
     */
    private function createRating(int $articleID): bool
    {
        if ($articleID > 0) {
            $_rating                  = new \stdClass();
            $_rating->kArtikel        = $articleID;
            $_rating->kKunde          = 0;
            $_rating->kSprache        = 1; //@todo: rand(0, 1)?
            $_rating->cName           = $this->faker->name;
            $_rating->cTitel          = addcslashes($this->faker->realText(75), '\'"');
            $_rating->cText           = $this->faker->text(100);
            $_rating->nHilfreich      = rand(0, 10);
            $_rating->nNichtHilfreich = rand(0, 10);
            $_rating->nSterne         = rand(1, 5);
            $_rating->nAktiv          = 1;
            $_rating->dDatum          = 'now()';

            return $this->pdo->insert('tbewertung', $_rating) > 0;
        }

        return false;
    }

    /**
     * update lft/rght values for categories in the nested set model.
     *
     * @param int $parentId
     * @param int $left
     * @param int $level
     * @return int
     */
    private function rebuildCategoryTree(int $parentId, int $left, int $level = 0): int
    {
        // the right value of this node is the left value + 1
        $right = $left + 1;
        // get all children of this node
        $result = $this->pdo->query(
            'SELECT kKategorie FROM tkategorie WHERE kOberKategorie = ' . $parentId . ' ORDER BY nSort, cName',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($result as $_res) {
            $right = $this->rebuildCategoryTree($_res->kKategorie, $right, $level + 1);
        }
        // we've got the left value, and now that we've processed the children of this node we also know the right value
        $this->pdo->query(
            'UPDATE tkategorie SET lft = ' . $left . ', rght = ' . $right . ', nLevel = ' . $level . ' WHERE kKategorie = ' . $parentId,
            \DB\ReturnType::DEFAULT
        );

        // return the right value of this node + 1
        return $right + 1;
    }

    /**
     * @param $text
     * @return mixed
     */
    private function slug($text)
    {
        return $this->slugify->slugify($text);
    }

    /**
     *
     */
    private function callback()
    {
        $arguments = func_get_args();
        $cb        = array_shift($arguments);

        if ($cb !== null && is_callable($cb)) {
            call_user_func_array($cb, $arguments);
        }
    }

    /**
     * @return string
     */
    private function getFontFile(): string
    {
        return PFAD_ROOT . PFAD_TEMPLATES . 'Evo/fonts/opensans/OpenSans-Regular.ttf';
    }
}
