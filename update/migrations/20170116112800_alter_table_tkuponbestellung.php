<?php
/**
 * Alter table tkuponbestellung
 *
 * @author Mirko Schmidt
 * @created Thue, 16 Jan 2017 11:28:00 +0100
 */

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20170116112800 extends Migration implements IMigration
{
    protected $author = 'msc';
    protected $description = 'Alter table tkuponbestellung';

    public function up()
    {
        $this->execute("ALTER TABLE `tkuponbestellung` ADD `kKunde` INT, ADD `cBestellNr` VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci, ADD `fGesamtsummeBrutto` DOUBLE NOT NULL , ADD `fKuponwertBrutto` DOUBLE, ADD `cKuponTyp` ENUM('prozent','festpreis','versand','neukunden') CHARACTER SET latin1 COLLATE latin1_swedish_ci, ADD `dErstellt` DATETIME, ADD INDEX (`cKuponTyp`, `dErstellt`)");
        $this->execute("UPDATE `tkuponbestellung` AS `kbg`
                        INNER JOIN (SELECT `bsk`.`kBestellung`, `bsk`.`kKunde`, `bsk`.`cBestellNr`, ROUND(`bsk`.`fGesamtsumme`, 2) AS `fGesamtsummeBrutto`,
                                    IF(
                                        (ROUND(`wkp`.`fPreisEinzelNetto`*(1+`wkp`.`fMwSt`/100), 2)*(-1)) > 0,
                                        (ROUND(`wkp`.`fPreisEinzelNetto`*(1+`wkp`.`fMwSt`/100), 2)*(-1)),
                                        (SELECT `va`.`fPreis`
                                            FROM `twarenkorbpos` AS `wpv`
                                            LEFT JOIN `tversandartsprache` AS `vs` ON `vs`.`cName` = `wpv`.`cName`
                                            LEFT JOIN `tversandart` AS `va` ON `va`.`kVersandart` = `vs`.`kVersandart` OR `va`.cName = `wpv`.`cName`
                                            WHERE `wpv`.`kWarenkorb` IN
                                                (SELECT `kWarenkorb` 
                                                    FROM `twarenkorbpos` 
                                                    WHERE `nPosTyp` = 3
                                                    AND `fPreisEinzelNetto` = 0)
                                                AND `wpv`.`nPosTyp` = 2
                                                AND `wkp`.`kWarenkorb` = `wpv`.`kWarenkorb`)
                                        ) AS `fKuponwertBrutto`,
                                    IF(`kp`.`cKuponTyp` = 'neukundenkupon', 'neukunden', IF(IFNULL(`kp`.`cWertTyp`,'festpreis') != '', IFNULL(`kp`.`cWertTyp`,'festpreis'), 'versand')) AS `cKuponTyp`,
                                    `bsk`.`dErstellt`
                                    FROM `tbestellung` AS `bsk`
                                    LEFT JOIN `twarenkorbpos` AS `wkp` ON `bsk`.`kWarenkorb` = `wkp`.`kWarenkorb`
                                    LEFT JOIN `tkuponbestellung` AS `kpb` ON `kpb`.`kBestellung` = `bsk`.`kBestellung`
                                    LEFT JOIN `tkupon` AS `kp` ON `kpb`.`kKupon` = `kp`.`kKupon`
                                    WHERE  `wkp`.`nPosTyp` = 3 OR `wkp`.`nPosTyp` = 7) AS `mergetable` ON `mergetable`.`kBestellung` = `kbg`.`kBestellung`
                        SET
                            `kbg`.`kKunde` = `mergetable`.`kKunde`,
                            `kbg`.`cBestellNr` = `mergetable`.`cBestellNr`,
                            `kbg`.`fGesamtsummeBrutto` = `mergetable`.`fGesamtsummeBrutto`,
                            `kbg`.`fKuponwertBrutto` = `mergetable`.`fKuponwertBrutto`,
                            `kbg`.`cKuponTyp` = `mergetable`.`cKuponTyp`,
                            `kbg`.`dErstellt` = `mergetable`.`dErstellt`");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tkuponbestellung` DROP `kKunde`, DROP `cBestellNr`, DROP `fGesamtsummeBrutto`, DROP `fKuponwertBrutto`, DROP `cKuponTyp`, DROP `dErstellt`");
    }
}
