<?php
/**
 * remove_tkuponneukunde
 *
 * @author mh
 * @created Thu, 29 Nov 2018 15:12:42 +0100
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
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20181129151242 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Remove tkuponneukunde, add tkuponflag';

    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tkuponneukunde`');

        $this->execute('CREATE TABLE `tkuponflag` (
                          `kKuponFlag` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                          `cEmailHash` VARCHAR(255) NOT NULL,
                          `cKuponTyp`  VARCHAR(255) NOT NULL,
                          `dErstellt`  DATETIME NOT NULL,
                          PRIMARY KEY (`kKuponFlag`),
                          KEY cEmailHash_cKuponTyp (`cEmailHash`, `cKuponTyp`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

        //add flags for already used new customer coupons
        $newCustomerCouponEmails = $this->fetchAll(
            "SELECT tkuponkunde.cMail,
                  MAX(tkuponkunde.dErstellt) as dErstellt
                FROM tkuponkunde
                LEFT JOIN tkupon
                  ON tkupon.kKupon = tkuponkunde.kKupon
                WHERE tkupon.cKuponTyp = 'neukundenkupon'
                GROUP BY tkuponkunde.cMail"
        );
        foreach ($newCustomerCouponEmails as $email) {
            Shop::Container()->getDB()->insert('tkuponflag', (object)[
                'cEmailHash' => Kupon::hash($email->cMail),
                'cKuponTyp'  => 'neukundenkupon',
                'dErstellt'  => $email->dErstellt
            ]);
        }

        $this->execute('ALTER TABLE `tkuponbestellung` CHANGE COLUMN `cKuponTyp` `cKuponTyp` VARCHAR(255) NOT NULL');

        //fix nVerwendungen -> remove kKunde, allow only unique entries for each cMail + kKupon
        $oldCustomerCoupons = $this->fetchAll('
          SELECT tkuponkunde.cMail,
                tkuponkunde.kKupon,
                COUNT(tkuponkunde.cMail) as nVerwendungen,
                MAX(tkuponkunde.dErstellt) as dErstellt
            FROM tkuponkunde
            LEFT JOIN tkupon
                ON tkupon.kKupon = tkuponkunde.kKupon
            GROUP BY tkuponkunde.cMail, tkuponkunde.kKupon'
        );

        $this->execute('TRUNCATE TABLE tkuponkunde');
        $this->execute('ALTER TABLE `tkuponkunde`
                          DROP KEY `kKupon`,
                          DROP KEY `kKunde`,
                          DROP COLUMN `kKunde`,
                          ADD UNIQUE KEY `kKupon_cMail` (`kKupon`, `cMail`)');

        foreach ($oldCustomerCoupons as $oldCoupon) {
            Shop::Container()->getDB()->insert('tkuponkunde', (object)[
                'cMail'         => Kupon::hash($oldCoupon->cMail),
                'kKupon'        => $oldCoupon->kKupon,
                'nVerwendungen' => $oldCoupon->nVerwendungen,
                'dErstellt'     => $oldCoupon->dErstellt
            ]);
        }

        $this->setLocalization('ger', 'global', 'couponErr6', 'Fehler: Maximale Verwendungen für den Kupon erreicht.');
        $this->setLocalization('eng', 'global', 'couponErr6', 'Error: Maximum usage reached for this coupon.');
    }

    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tkuponflag`');

        $this->execute("CREATE TABLE `tkuponneukunde` (
                          `kKuponNeukunde` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                          `kKupon`         INT(10) UNSIGNED NOT NULL,
                          `cEmail`         VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
                          `cDatenHash`     VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
                          `dErstellt`      DATETIME NOT NULL,
                          `cVerwendet`     VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                          PRIMARY KEY (`kKuponNeukunde`),
                          KEY `cEmail` (`cEmail`,`cDatenHash`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

        $this->execute("ALTER TABLE `tkuponbestellung`
                          CHANGE COLUMN `cKuponTyp` `cKuponTyp` ENUM('prozent', 'festpreis', 'versand', 'neukunden') COLLATE utf8_unicode_ci DEFAULT NULL");

        $this->execute('ALTER TABLE `tkuponkunde`
                          DROP KEY `kKupon_cMail`,
                          ADD COLUMN `kKunde` INT(10) UNSIGNED AFTER `kKupon`,
                          ADD KEY `kKupon` (`kKupon`),
                          ADD KEY `kKunde` (`kKunde`)');

        $this->setLocalization('ger', 'global', 'couponErr6', 'Fehler: Maximale Verwendungen erreicht.');
        $this->setLocalization('eng', 'global', 'couponErr6', 'Error: Maximum usage reached');
    }
}
