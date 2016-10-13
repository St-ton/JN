<?php
/**
 * add language variables for downloads
 *
 * @author ms
 * @created Thu, 13 Oct 2016 10:22:00 +0200
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
class Migration_20161013102200 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute('INSERT INTO tsprachsektion (cName) VALUES ("productDownloads");');

        $this->setLocalization('ger', 'productDownloads', 'downloadSection', 'Downloads');
        $this->setLocalization('eng', 'productDownloads', 'downloadSection', 'downloads');

        $this->setLocalization('ger', 'productDownloads', 'downloadName', 'Name');
        $this->setLocalization('eng', 'productDownloads', 'downloadName', 'name');

        $this->setLocalization('ger', 'productDownloads', 'downloadDescription', 'Beschreibung');
        $this->setLocalization('eng', 'productDownloads', 'downloadDescription', 'description');

        $this->setLocalization('ger', 'productDownloads', 'downloadFileExt', 'Dateiformat');
        $this->setLocalization('eng', 'productDownloads', 'downloadFileExt', 'file extension');

        $this->setLocalization('ger', 'productDownloads', 'downloadPreview', 'Downloadvorschau');
        $this->setLocalization('eng', 'productDownloads', 'downloadPreview', 'download preview');
    }

    public function down()
    {
        $this->removeLocalization('downloadSection');
        $this->removeLocalization('downloadName');
        $this->removeLocalization('downloadDescription');
        $this->removeLocalization('downloadFileExt');
        $this->removeLocalization('downloadPreview');
        $this->execute('DELETE FROM tsprachsektion WHERE cName = "productDownloads";');
    }
}
