<?php
/**
 * remove_product_tags
 *
 * @author mh
 * @created Tue, 07 May 2019 09:54:30 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190507095430
 */
class Migration_20190507095430 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove product tags';

    public function up()
    {
        $this->removeConfig('Tagfilter');
        $this->removeConfig('allgemein_tagfilter_benutzen');
        $this->removeConfig('tagfilter_max_anzeige');
        $this->removeConfig('tag_filter_type');
        $this->removeConfig('tagging_freischaltung');
        $this->removeConfig('tagging_anzeigen');
        $this->removeConfig('tagging_max_count');
        $this->removeConfig('tagging_max_ip_count');
        $this->removeConfig('boxen_tagging_anzeigen');
        $this->removeConfig('boxen_tagging_count');
        $this->removeConfig('sonstiges_tagging_all_count');
        $this->removeConfig('sitemap_tags_anzeigen');

        //remove LINKTYP_TAGGING
        $this->execute('DELETE FROM `tspezialseite` WHERE `nLinkart` = 14');
        $this->execute("DELETE `tlink`, `tlinkgroupassociations`, `tseo`
                          FROM `tlink`
                          JOIN `tlinkgroupassociations`
                            ON tlink.kLink = tlinkgroupassociations.linkID 
                          JOIN `tseo`
                            ON tlink.kLink = tseo.kKey AND tseo.cKey = 'kLink'
                          WHERE tlink.nLinkart = 14"
        );
        //remove PAGE_TAGGING
        $this->execute('DELETE FROM `tboxensichtbar` WHERE `kSeite` = 22');
        $this->execute('DELETE FROM `tboxenanzeige` WHERE `nSeite` = 22');
        $this->execute('DELETE FROM `textensionpoint` WHERE `nSeite` = 22');
        //remove BOX_TAGWOLKE, BOX_FILTER_TAG
        $this->execute('DELETE FROM `tboxvorlage` WHERE `kBoxvorlage` = 24 OR `kBoxvorlage` = 32');
        $this->execute('DELETE `tboxen`, `tboxensichtbar`
                          FROM `tboxen`
                          JOIN `tboxensichtbar`
                            ON tboxen.kBox = tboxensichtbar.kBox
                          WHERE tboxen.kBoxvorlage = 24 OR tboxen.kBoxvorlage = 32'
        );

        $this->execute("DELETE FROM `tseo` WHERE `cKey` = 'kTag'");
    }

    public function down()
    {
        $this->execute(
            "INSERT INTO `teinstellungenconf` (`kEinstellungenConf`,`kEinstellungenSektion`,`cName`,`cBeschreibung`,`cWertName`,`cInputTyp`,`cModulId`,`nSort`,`nStandardAnzeigen`,`nModul`,`cConf`)
            VALUES (529," . \CONF_NAVIGATIONSFILTER . ",'Tagfilter','',NULL,NULL,NULL,170,1,0,'N')"
        );
        $this->setConfig(
            'allgemein_tagfilter_benutzen',
            'Y',
            \CONF_NAVIGATIONSFILTER,
            'Tagfilter benutzen',
            'selectbox',
            172,
            (object)[
                'cBeschreibung' => 'Soll die Tagfilterung beim Filtern benutzt werden?',
                'inputOptions'  => [
                    'content' => 'Ja, im Contentbereich',
                    'box'     => 'Ja, in Navigationsbox',
                    'Y'       => 'Ja, im Contentbereich und der Navigationsbox',
                    'N'       => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'tag_filter_type',
            'Y',
            \CONF_NAVIGATIONSFILTER,
            'Typ des Tagfilters',
            'selectbox',
            176,
            (object)[
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A'       => 'Verundung',
                    'O'       => 'Veroderung'
                ]
            ]
        );
        $this->setConfig(
            'tagfilter_max_anzeige',
            'Y',
            \CONF_NAVIGATIONSFILTER,
            'Maximale Anzahl an Tags in der Filterung',
            'number',
            175,
            (object)['cBeschreibung' => 'Wieviele Tags sollen maximal in der Filteranzeige zu sehen sein?']
        );
        $this->setConfig(
            'tagging_freischaltung',
            'Y',
            \CONF_ARTIKELDETAILS,
            'Produkttags Eingabe anzeigen',
            'selectbox',
            1010,
            (object)[
                'cBeschreibung' => 'Produkttags von Besuchern können unter den Produkten angezeigt werden.',
                'inputOptions'  => [
                    'Y'       => 'Ja, nur für eingeloggte Kunden',
                    'O'       => 'Ja, für alle Besucher',
                    'N'       => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'tagging_anzeigen',
            'Y',
            \CONF_ARTIKELDETAILS,
            'Produkttags beim Artikel anzeigen',
            'selectbox',
            1020,
            (object)[
                'cBeschreibung' => 'Hier wird die Anzeige der Produkttags beim Artikel aktiviert bzw. deaktiviert.',
                'inputOptions'  => [
                    'Y'       => 'Ja',
                    'N'       => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'tagging_max_count',
            'Y',
            \CONF_ARTIKELDETAILS,
            'Anzahl angezeigter Produkttags',
            'number',
            1030,
            (object)['cBeschreibung' => 'Soviele Begriffe werden bei den Produkttags angezeigt.']
        );
        $this->setConfig(
            'tagging_max_ip_count',
            'Y',
            \CONF_ARTIKELDETAILS,
            'Maximale Einträge pro Besucher und Tag',
            'number',
            1040,
            (object)['cBeschreibung' => 'Damit verhindern Sie, dass einzelne IPs das Tagging manipulieren.']
        );
        $this->setConfig(
            'boxen_tagging_anzeigen',
            'Y',
            \CONF_BOXEN,
            'Box anzeigen',
            'selectbox',
            1005,
            (object)[
                'cBeschreibung' => 'Soll die Tagwolke in einer Box angezeigt werden?',
                'inputOptions'  => [
                    'Y'       => 'Ja',
                    'N'       => 'Nein'
                ],
                'nModul'        => 1
            ]
        );
        $this->setConfig(
            'boxen_tagging_count',
            'Y',
            \CONF_BOXEN,
            'Anzahl angezeigte Tagbegriffe',
            'number',
            1010,
            (object)[
                'cBeschreibung' => 'Soviele Begriffe werden in der Tagwolke angezeigt.',
                'nModul'        => 1
            ]
        );
        $this->setConfig(
            'sonstiges_tagging_all_count',
            'Y',
            \CONF_SONSTIGES,
            'Anzahl angezeigte Tagbegriffe in der Übersicht',
            'number',
            110,
            (object)[
                'cBeschreibung' => 'Soviele Begriffe werden in der Komplettübersicht der Tagwolke angezeigt',
                'nModul'        => 1
            ]
        );
        $this->setConfig(
            'sitemap_tags_anzeigen',
            'Y',
            \CONF_SITEMAP,
            'Tags anzeigen',
            'selectbox',
            100,
            (object)[
                'cBeschreibung' => 'Sollen Ihre Tags in der Sitemap erscheinen?',
                'inputOptions'  => [
                    'Y'       => 'Ja',
                    'N'       => 'Nein'
                ]
            ]
        );
    }
}
