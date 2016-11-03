<?php
/**
 * add row in temailvorlagesprache for english version of Status Email
 *
 * @author dr
 * @created Thu, 03 Nov 2016 11:00:00 +0200
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
class Migration_20161103110000 extends Migration implements IMigration
{
    protected $author = 'dr';

    public function up()
    {
        $cContentHtml = Shop::DB()->escape(file_get_contents(PFAD_ROOT . PFAD_EMAILVORLAGEN . 'eng/email_bericht_html.tpl'));
        $cContentText = Shop::DB()->escape(file_get_contents(PFAD_ROOT . PFAD_EMAILVORLAGEN . 'eng/email_bericht_plain.tpl'));

        $this->execute("
            INSERT INTO temailvorlagesprache
                VALUES (
                    (SELECT kEmailvorlage FROM temailvorlage WHERE cModulId = 'core_jtl_statusemail'),
                    (SELECT kSprache FROM tsprache WHERE cISO = 'eng'),
                    'Status email', '" . $cContentHtml . "', '" . $cContentText . "', '', ''
                )
        ");
        $this->execute("
            INSERT INTO temailvorlagespracheoriginal
                VALUES (
                    (SELECT kEmailvorlage FROM temailvorlage WHERE cModulId = 'core_jtl_statusemail'),
                    (SELECT kSprache FROM tsprache WHERE cISO = 'eng'),
                    'Status email', '" . $cContentHtml . "', '" . $cContentText . "', '', ''
                )
        ");
    }

    public function down()
    {
        $this->execute("
            DELETE FROM temailvorlagesprache
                WHERE kEmailvorlage = (SELECT kEmailvorlage FROM temailvorlage WHERE cModulId = 'core_jtl_statusemail')
                    AND kSprache = (SELECT kSprache FROM tsprache WHERE cISO = 'eng')
        ");
        $this->execute("
            DELETE FROM temailvorlagespracheoriginal
                WHERE kEmailvorlage = (SELECT kEmailvorlage FROM temailvorlage WHERE cModulId = 'core_jtl_statusemail')
                    AND kSprache = (SELECT kSprache FROM tsprache WHERE cISO = 'eng')
        ");
    }
}
