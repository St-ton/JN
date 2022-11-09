<?php declare(strict_types=1);
/**
 * Create permanent checkbox for download module
 *
 * @author sl
 * @created Tue, 08 Nov 2022 12:17:31 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221108121731
 */
class Migration_20221108121731 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'Create permanent checkbox for download module';

    /**
     * @inheritDoc
     */
    public function up()
    {
       $this->execute('ALTER TABLE `tcheckbox` ADD COLUMN IF NOT EXISTS nInternal TINYINT(1)');
       $result = $this->exec('SELECT count(cName) as countNames FROM tcheckbox WHERE cName = "RightOfRevocationOfDownloadArticles"',8);
       if((int)$result['countNames'] === 0){
           $kCheckBox = $this->exec("INSERT INTO `tcheckbox` ( cName, cKundengruppe, cAnzeigeOrt, nAktiv, nPflicht, nLogging, nSort, dErstellt, `nInternal`)
                    VALUES('RightOfRevocationOfDownloadArticles', ';;', ';2;', '1', '1', '1', '1', NOW(), '1')",7);

           $cText = 'Für digitale Produkte: Ich stimme ausdrücklich zu, dass vor Ablauf der Widerrufsfrist mit dem Vertrag begonnen wird. Mir ist bekannt, dass mit Beginn der Ausführung mein Widerrufsrecht erlischt.';
           $cBeschreibung = '';
           $this->getDB()->queryPrepared("INSERT INTO tcheckboxsprache (kCheckBox, kSprache, cText, cBeschreibung)
                    VALUES (:kCheckBox, (SELECT kSprache FROM tsprache WHERE  cIso = 'ger'), :cText,:cBeschreibung)", ['kCheckBox'=>$kCheckBox,'cText'=>$cText, 'cBeschreibung'=>$cBeschreibung]);
           $this->getDB()->queryPrepared("INSERT INTO tcheckboxsprache (kCheckBox, kSprache, cText, cBeschreibung)
                    VALUES (:kCheckBox, (SELECT kSprache FROM tsprache WHERE  cIso = 'eng'), :cText, :cBeschreibung)", ['kCheckBox'=>$kCheckBox,'cText'=>$cText, 'cBeschreibung'=>$cBeschreibung]);
       }

    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $result = $this->exec('SELECT kCheckBox FROM tcheckbox WHERE cName = "RightOfRevocationOfDownloadArticles"',8);
        if((int)$result['kCheckBox'] !== 0){
            $this->execute('DELETE FROM `tcheckbox` WHERE cName = "RightOfRevocationOfDownloadArticles"');
            $this->getDB()->queryPrepared('DELETE FROM `tcheckboxsprache` WHERE kCheckBox = :kCheckBox',['kCheckBox'=>(int)$result['kCheckBox']]);
        }
        $this->execute('ALTER TABLE `tcheckbox` DROP COLUMN `nInternal`');
    }
}
