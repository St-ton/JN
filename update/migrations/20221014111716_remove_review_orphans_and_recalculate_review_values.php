<?php declare(strict_types=1);

/**
 * Remove review orphans and recalculate review values
 *
 * @author sl
 * @created Fri, 14 Oct 2022 11:17:16 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221014111716
 */
class Migration_20221014111716 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'Remove review orphans and recalculate review values';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute(
            'DELETE tartikelext
                FROM tartikelext
                    LEFT JOIN  tbewertung ON tartikelext.kArtikel = tbewertung.kArtikel
                                            AND tbewertung.nAktiv = 1
                WHERE tbewertung.kArtikel IS NULL'
        );

        $this->execute(
            'INSERT INTO tartikelext (kArtikel, fDurchschnittsBewertung)
                SELECT * FROM (
                    SELECT kArtikel, ROUND(SUM(nSterne) / COUNT(kBewertung), 1) AS avgStars
                    FROM tbewertung WHERE nAktiv = 1
                    GROUP BY kArtikel
                ) AS new
                ON DUPLICATE KEY UPDATE fDurchschnittsBewertung = new.avgStars'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        //not necessary
    }
}
