<?php
/**
 * Remove old options
 */

/**
 * Class Migration_20180320085100
 */
class Migration_20180320085100 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Remove old options';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->removeConfig('object_caching_activated');
        $this->removeConfig('object_caching_method');
        $this->removeConfig('object_caching_memcached_host');
        $this->removeConfig('object_caching_memcached_port');
        $this->removeConfig('object_caching_debug_mode');
        $this->removeConfig('global_gesamtsummenanzeige');
        $this->removeConfig('news_navigation_anzeige');
        $this->removeConfig('trustedshops_siegelbox_anzeigen');
        $this->removeConfig('page_cache_debugging');
        $this->removeConfig('caching_page_cache');
        $this->removeConfig('advanced_page_cache');
        $this->execute("DELETE FROM teinstellungen WHERE cName = '' AND cWert = ''");
        $this->execute("DELETE FROM teinstellungen WHERE kEinstellungenSektion = 8 AND cName LIKE 'box_%_anzeigen'");
        $this->execute("INSERT INTO teinstellungenconf (kEinstellungenSektion, cName, cBeschreibung, cWertName, cInputTyp, cModulId, nSort, nStandardAnzeigen) 
          (SELECT teinstellungen.kEinstellungenSektion, teinstellungen.cName, '' AS cBeschreibung, teinstellungen.cName AS cWertName, 'text' AS cInputTyp, teinstellungen.cModulId, 0 AS nSort, 1 AS nStandardAnzeigen 
                FROM teinstellungen
                LEFT JOIN teinstellungenconf
                    ON teinstellungenconf.cWertName = teinstellungen.cName
                    AND teinstellungenconf.kEinstellungenSektion = teinstellungen.kEinstellungenSektion
				WHERE teinstellungenconf.cWertName IS NULL)");
    }

    /**
     * @return bool|void
     */
    public function down()
    {
    }
}
