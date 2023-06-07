<?php declare(strict_types=1);

/**
 * Create permanent checkbox for download module
 *
 * @author sl
 * @created Mon, 03 Apr 2023 08:46:44 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230403084644
 */
class Migration_20230403084644 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'Create permanent checkbox for download module';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $returnLastInsertedId = 7;
        $returnSingleAssocArray = 8;
        $customerGroupIDs = [];
        $customerGroups   = $this->fetchAll("SELECT kKundengruppe AS ID FROM `tkundengruppe`");
        foreach ($customerGroups as $customerGroup) {
            $customerGroupIDs[] = $customerGroup->ID;
        }
        $customerGroupIDsToInsert = \implode(';', $customerGroupIDs);
        $this->execute('ALTER TABLE `tcheckbox` ADD COLUMN nInternal TINYINT(1) Default 0');
        $result = $this->exec(
            "SELECT count(cName) as countNames FROM `tcheckbox` WHERE cName = 'RightOfWithdrawalOfDownloadItems'",
            $returnSingleAssocArray
        );
        if ((int)$result['countNames'] === 0) {
            $kCheckBox = $this->getDB()->queryPrepared(
                "INSERT INTO `tcheckbox` 
                        (cName, cKundengruppe, cAnzeigeOrt, nAktiv, nPflicht, nLogging, nSort, dErstellt, `nInternal`)
                      VALUES
                          (
                               'RightOfWithdrawalOfDownloadItems',
                               :customerGroupIDsToInsert,
                               ';2;',
                               '1',
                               '1',
                               '1',
                               '1',
                               NOW(),
                               '1'
                           )",
                ['customerGroupIDsToInsert' => $customerGroupIDsToInsert],
                $returnLastInsertedId
            );

            $cText         = 'Hinweis: Widerrufsrecht erlischt mit Vertragsbeginn.
Ich stimme ausdrücklich zu, dass der Vertrag für digitale Produkte vor Ablauf der Widerrufsfrist beginnt.' .
                ' Mir ist bekannt, dass mit Vertragsbeginn mein Widerrufsrecht erlischt.';
            $cBeschreibung = '';
            $this->getDB()->queryPrepared(
                "INSERT INTO `tcheckboxsprache` (kCheckBox, kSprache, cText, cBeschreibung)
                    VALUES (
                                :kCheckBox,
                                (SELECT kSprache FROM `tsprache` WHERE  cIso = 'ger'),
                                :cText,:cBeschreibung
                            )",
                ['kCheckBox' => $kCheckBox, 'cText' => $cText, 'cBeschreibung' => $cBeschreibung]
            );

            $cTextEng = 'Please note: Right of withdrawal ends with start of contract.
I hereby acknowledge that the contract for digital products is valid before the end of the withdrawal period.' .
                ' I am aware that my right of withdrawal ends with the start of the contract.';
            $this->getDB()->queryPrepared(
                "INSERT INTO `tcheckboxsprache` (kCheckBox, kSprache, cText, cBeschreibung)
                    VALUES (
                            :kCheckBox,
                            (SELECT kSprache FROM `tsprache` WHERE  cIso = 'eng'),
                            :cText, :cBeschreibung)",
                ['kCheckBox' => $kCheckBox, 'cText' => $cTextEng, 'cBeschreibung' => $cBeschreibung]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $returnSingleAssocArray = 8;
        $result = $this->exec(
            "SELECT kCheckBox FROM `tcheckbox` WHERE cName = 'RightOfWithdrawalOfDownloadItems'",
            $returnSingleAssocArray
        );
        if ((int)$result['kCheckBox'] !== 0) {
            $this->execute("DELETE FROM `tcheckbox` WHERE cName = 'RightOfWithdrawalOfDownloadItems'");
            $this->getDB()->queryPrepared(
                'DELETE FROM `tcheckboxsprache` WHERE kCheckBox = :kCheckBox',
                ['kCheckBox' => (int)$result['kCheckBox']]);
        }
        $this->execute('ALTER TABLE `tcheckbox` DROP COLUMN `nInternal`');
    }
}
