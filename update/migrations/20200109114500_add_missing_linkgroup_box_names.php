<?php
/**
 * Add missing linkgroup box names
 *
 * @author mh
 * @created Thu, 9 Jan 2020 11:45:00 +0200
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;
use function Functional\group;
use function Functional\reindex;

/**
 * Class Migration_20200109114500
 */
class Migration_20200109114500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add missing linkgroup box names';

    /**
     * @return mixed|void
     */
    public function up()
    {
        $this->execute('ALTER TABLE tboxsprache ADD UNIQUE KEY `kBox_cISO` (kBox, cISO)');
        $langs = ['ger', 'eng'];
        $boxes = $this->getDB()->query(
            'SELECT kBox, kCustomID FROM tboxen WHERE kCustomID != 0',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $linkGroupLang = group(
            $this->getDB()->query('SELECT * FROM tlinkgruppesprache', ReturnType::ARRAY_OF_OBJECTS),
            function ($e) {
                return $e->kLinkgruppe;
            }
        );
        foreach ($linkGroupLang as &$lang) {
            $lang = reindex($lang, function ($e) {
                return $e->cISOSprache;
            });
        }

        foreach ($boxes as $box) {
            foreach ($langs as $lang) {
                $this->execute(
                    "INSERT IGNORE INTO tboxsprache (
                          kBox, cISO, cTitel, cInhalt
                      ) VALUES (
                        " . $box->kBox . ",
                        '" . $lang . "',
                        '" . $linkGroupLang[(string)$box->kCustomID][(string)$lang]->cName ."',
                        ''
                    )"
                );
            }
        }
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->execute('ALTER TABLE tboxsprache DROP INDEX `kBox_cISO`');
    }
}
