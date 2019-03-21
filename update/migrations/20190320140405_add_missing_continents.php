<?php
/**
 *
 *
 * @author Michael Hillmann
 * @created Wed, 20 Mar 2019 14:04:05 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

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
class Migration_20190320140405 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add missing continents';

    public function up()
    {
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'AG'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'AI'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'AW'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'BB'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'BS'");
        $this->execute("UPDATE tland SET cKontinent = 'Antarktis' WHERE cISO = 'BV'");
        $this->execute("UPDATE tland SET cKontinent = 'Asien' WHERE cISO = 'CC'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'CU'");
        $this->execute("UPDATE tland SET cKontinent = 'Asien' WHERE cISO = 'CX'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'DM'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'DO'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'GD'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'GP'");
        $this->execute("UPDATE tland SET cKontinent = 'Antarktis' WHERE cISO = 'HM'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'HT'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'KN'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'KY'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'LC'");
        $this->execute("UPDATE tland SET cKontinent = 'Asien' WHERE cISO = 'MO'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'MQ'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'MS'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'PR'");
        $this->execute("UPDATE tland SET cKontinent = 'Afrika' WHERE cISO = 'RE'");
        $this->execute("UPDATE tland SET cKontinent = 'Afrika' WHERE cISO = 'SH'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'TC'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'TT'");
        $this->execute("UPDATE tland SET cKontinent = 'Asien' WHERE cISO = 'UZ'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'VC'");
        $this->execute("UPDATE tland SET cKontinent = 'Afrika' WHERE cISO = 'YT'");
        $this->execute("UPDATE tland SET cKontinent = 'Asien' WHERE cISO = 'KZ'");
        $this->execute("UPDATE tland SET cKontinent = 'Nordamerika' WHERE cISO = 'JM'");
        $this->execute("UPDATE tland SET cKontinent = 'Afrika' WHERE cISO = 'EG'");
    }

    public function down()
    {

    }
}
