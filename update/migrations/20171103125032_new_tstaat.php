<?php
/**
 * new_tstaat
 *
 * @author Clemens Rudolph
 * @created Fri, 03 Nov 2017 12:50:32 +0100
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
class Migration_20171103125032 extends Migration implements IMigration
{
    protected $author = 'cr';
    protected $description = 're-create the table tstaat, transfer to valid ISO 3166-2';

    public function up()
    {
		$szSql = <<<SQL

DROP TABLE IF EXISTS `tstaat`;

CREATE TABLE IF NOT EXISTS `tstaat` (
      `kStaat` int(10) unsigned NOT NULL AUTO_INCREMENT
    , `cLandIso` varchar(2) NOT NULL COMMENT 'should be value of ISO 3166-1'
    , `cName` varchar(255) NOT NULL COMMENT 'full name of the state or province'
    , `cCode` varchar(10) NOT NULL COMMENT 'should be value of ISO 3166-2'
    , PRIMARY KEY (`kStaat`)
    , KEY `cLandIso` (`cLandIso`)
    , KEY `cName` (`cName`)
    , KEY `cCode` (`cCode`)
);

/*US military post offices*/
INSERT INTO `tstaat` (`cLandIso`, `cName`, `cCode`) VALUES ('US','Armed Forces Africa','AF');
INSERT INTO `tstaat` (`cLandIso`, `cName`, `cCode`) VALUES ('US','Armed Forces Americas','AA');
INSERT INTO `tstaat` (`cLandIso`, `cName`, `cCode`) VALUES ('US','Armed Forces Canada','AC');
INSERT INTO `tstaat` (`cLandIso`, `cName`, `cCode`) VALUES ('US','Armed Forces Europe','AE');
INSERT INTO `tstaat` (`cLandIso`, `cName`, `cCode`) VALUES ('US','Armed Forces Middle East','AM');
INSERT INTO `tstaat` (`cLandIso`, `cName`, `cCode`) VALUES ('US','Armed Forces Pacific','AP');

/*United States Of America*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-AL', 'Alabama');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-AK', 'Alaska');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-AZ', 'Arizona');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-AR', 'Arkansas');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-CA', 'California');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-CO', 'Colorado');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-CT', 'Connecticut');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-DE', 'Delaware');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-FL', 'Florida');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-GA', 'Georgia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-HI', 'Hawaii');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-ID', 'Idaho');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-IL', 'Illinois');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-IN', 'Indiana');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-IA', 'Iowa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-KS', 'Kansas');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-KY', 'Kentucky');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-LA', 'Louisiana');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-ME', 'Maine');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-MD', 'Maryland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-MA', 'Massachusetts');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-MI', 'Michigan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-MN', 'Minnesota');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-MS', 'Mississippi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-MO', 'Missouri');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-MT', 'Montana');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-NE', 'Nebraska');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-NV', 'Nevada');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-NH', 'New Hampshire');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-NJ', 'New Jersey');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-NM', 'New Mexico');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-NY', 'New York');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-NC', 'North Carolina');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-ND', 'North Dakota');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-OH', 'Ohio');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-OK', 'Oklahoma');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-OR', 'Oregon');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-PA', 'Pennsylvania');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-RI', 'Rhode Island');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-SC', 'South Carolina');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-SD', 'South Dakota');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-TN', 'Tennessee');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-TX', 'Texas');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-UT', 'Utah');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-VT', 'Vermont');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-VA', 'Virginia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-WA', 'Washington');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-WV', 'West Virginia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-WI', 'Wisconsin');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-WY', 'Wyoming');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-DC', 'District of Columbia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-AS', 'American Samoa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-GU', 'Guam');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-MP', 'Northern Mariana Islands');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-PR', 'Puerto Rico');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-UM', 'United States Minor Outlying Islands');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('US', 'US-VI', 'Virgin Islands, U.S.');

/*Canada*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-AB', 'Alberta');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-BC', 'British Columbia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-MB', 'Manitoba');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-NB', 'New Brunswick');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-NL', 'Newfoundland and Labrador');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-NS', 'Nova Scotia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-ON', 'Ontario');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-PE', 'Prince Edward Island');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-QC', 'Quebec');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-SK', 'Saskatchewan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-NT', 'Northwest Territories');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-NU', 'Nunavut');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CA', 'CA-YT', 'Yukon');

/*Germany*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-BW', 'Baden-Württemberg');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-BY', 'Bayern');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-BE', 'Berlin');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-BB', 'Brandenburg');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-HB', 'Bremen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-HH', 'Hamburg');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-HE', 'Hessen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-MV', 'Mecklenburg-Vorpommern');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-NI', 'Niedersachsen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-NW', 'Nordrhein-Westfalen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-RP', 'Rheinland-Pfalz');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-SL', 'Saarland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-SN', 'Sachsen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-ST', 'Sachsen-Anhalt');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-SH', 'Schleswig-Holstein');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DE', 'DE-TH', 'Thüringen');

/*Austria*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AT', 'AT-1', 'Burgenland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AT', 'AT-2', 'Kärnten');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AT', 'AT-3', 'Niederösterreich');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AT', 'AT-4', 'Oberösterreich');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AT', 'AT-5', 'Salzburg');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AT', 'AT-6', 'Steiermark');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AT', 'AT-7', 'Tirol');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AT', 'AT-8', 'Vorarlberg');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AT', 'AT-9', 'Wien');

/*Switzerland*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-AG', 'Aargau');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-AR', 'Appenzell Ausserrhoden');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-AI', 'Appenzell Innerrhoden');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-BL', 'Basel-Landschaft');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-BS', 'Basel-Stadt');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-BE', 'Bern,');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-FR', 'Freiburg');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-GE', 'Genève');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-GL', 'Glarus');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-GR', 'Graubünden');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-JU', 'Jura');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-LU', 'Luzern');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-NE', 'Neuchâtel');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-NW', 'Nidwalden');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-OW', 'Obwalden');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-SG', 'Sankt Gallen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-SH', 'Schaffhausen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-SZ', 'Schwyz');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-SO', 'Solothurn');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-TG', 'Thurgau');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-TI', 'Ticino');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-UR', 'Uri');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-VS', 'Wallis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-VD', 'Vaud');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-ZG', 'Zug');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CH', 'CH-ZH', 'Zürich');

/*Spain*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-C', 'A Coruña');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-VI', 'Araba');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-AB', 'Albacete');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-A', 'Alicante');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-AL', 'Almería');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-O', 'Asturias');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-AV', 'Ávila');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-BA', 'Badajoz');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-PM', 'Baleares');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-B', 'Barcelona');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-BU', 'Burgos');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-CC', 'Cáceres');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-CA', 'Cádiz');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-S', 'Cantabria');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-CS', 'Castellón');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-CR', 'Ciudad Real');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-CO', 'Córdoba');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-CU', 'Cuenca');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-GI', 'Gerona');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-GR', 'Granada');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-GU', 'Guadalajara');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-SS', 'Gipuzkoa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-H', 'Huelva');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-HU', 'Huesca');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-J', 'Jaén');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-LO', 'La Rioja');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-GC', 'Las Palmas');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-LE', 'León');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-L', 'Lérida');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-LU', 'Lugo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-M', 'Madrid');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-MA', 'Málaga');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-MU', 'Murcia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-NA', 'Navarra');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-OR', 'Orense');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-P', 'Palencia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-PO', 'Pontevedra');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-SA',  'Salamanca');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-TF', 'Santa Cruz de Tenerife');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-SG', 'Segovia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-SE', 'Sevilla');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-SO', 'Soria');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-T', 'Tarragona');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-TE', 'Teruel');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-TO', 'Toledo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-V', 'Valencia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-VA', 'Valladolid');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-BI', 'Bizkaia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-ZA', 'Zamora');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('ES', 'ES-Z', 'Zaragoza');

/*Fance*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-ARA', 'Auvergne-Rhône-Alpes');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-BFC', 'Bourgogne-Franche-Comté');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-BRE', 'Bretagne');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-CVL', 'Centre-Val de Loire');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-COR', 'Corse');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-GES', 'Grand Est');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-HDF', 'Hauts-de-France');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-IDF', 'Île-de-France');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-NOR', 'Normandie');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-NAQ', 'Nouvelle-Aquitaine');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-OCC', 'Occitanie');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-PDL', 'Pays de la Loire');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-PAC', 'Provence-Alpes-Côte d\'Azur');
/*France Overseas departments*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-GP', 'Guadeloupe');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-GF', 'Guyane');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-MQ', 'Martinique');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-YT', 'Mayotte');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FR', 'FR-RE', 'La Réunion');

/*Romania*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-AB','Alba');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-AR','Arad');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-AG','Arges');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-BC','Bacau');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-BH','Bihor');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-BN','Bistrita-Nasaud');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-BT','Botosani');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-BV','Brasov');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-BR','Braila');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-BZ','Buzau');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-CS','Caras-Severin');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-CL','Calarasi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-CJ','Cluj');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-CT','Constanta');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-CV','Covasna');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-DB','Dâmbovita');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-DJ','Dolj');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-GL','Galati');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-GR','Giurgiu');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-GJ','Gorj');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-HR','Harghita');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-HD','Hunedoara');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-IL','Ialomita');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-IS','Iasi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-IF','Ilfov');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-MM','Maramures');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-MH','Mehedinti');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-MS','Mures');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-NT','Neamt');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-OT','Olt');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-PH','Prahova');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-SM','Satu Mare');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-SJ','Salaj');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-SB','Sibiu');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-SV','Suceava');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-TR','Teleorman');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-TM','Timis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-TL','Tulcea');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-VS','Vaslui');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-VL','Vâlcea');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-VN','Vrancea');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('RO', 'RO-B','Bucuresti');

/*Finland*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-01', 'Åland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-02', 'South Karelia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-03', 'Southern Ostrobothnia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-04', 'Southern Savonia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-05', 'Kainuu');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-06', 'Tavastia Proper');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-07', 'Central Ostrobothnia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-08', 'Central Finland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-09', 'Kymenlaakso');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-10', 'Lapland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-11', 'Pirkanmaa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-12', 'Ostrobothnia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-13', 'North Karelia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-14', 'Northern Ostrobothnia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-15', 'Northern Savonia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-16', 'Päijänne Tavastia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-17', 'Satakunta');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-18', 'Uusimaa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('FI', 'FI-19', 'Southwest Finland');

/*Estonia*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-37', 'Harju');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-39', 'Hiiu');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-44', 'Ida-Viru');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-49', 'Jõgeva');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-51', 'Järva');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-57', 'Lääne');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-59', 'Lääne-Viru');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-65', 'Põlva');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-67', 'Pärnu');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-70', 'Rapla');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-74', 'Saare');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-78', 'Tartu');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-82', 'Valga');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-84', 'Viljandi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('EE', 'EE-86', 'Võru');

/*Latvia (Lettland)*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-DGV', 'Daugavpils');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-JEL', 'Jelgava');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-JKB', 'Jekabpils');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-JUR', 'Jurmala');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-LPX', 'Liepaja');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-REZ', 'Rezekne');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-RIX', 'Riga');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-VMR', 'Valmiera');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-VEN', 'Ventspils');
/*municipality codes*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-001', 'Aglonas novads (Aglona)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-002', 'Aizkraukles novads (Aizkraukle)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-003', 'Aizputes novads (Aizpute)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-004', 'Aknistes novads (Akniste)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-005', 'Alojas novads (Aloja)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-006', 'Alsungas novads (Alsunga)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-007', 'Aluksnes novads (Aluksne)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-008', 'Amatas novads (Amata)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-009', 'Apes novads (Ape)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-010', 'Auces novads (Auce)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-011', 'Adazu novads (Adazi)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-012', 'Babites novads (Babite)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-013', 'Baldones novads (Baldone)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-014', 'Baltinavas novads (Baltinava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-015', 'Balvu novads (Balvi)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-016', 'Bauskas novads (Bauska)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-017', 'Beverinas novads (Beverina)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-018', 'Brocenu novads (Broceni)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-019', 'Burtnieku novads (Burtnieki)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-020', 'Carnikavas novads (Carnikava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-021', 'Cesvaines novads (Cesvaine)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-022', 'Cesu novads (Cesis)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-023', 'Ciblas novads (Cibla)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-024', 'Dagdas novads (Dagda)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-025', 'Daugavpils novads (Daugavpils)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-026', 'Dobeles novads (Dobele)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-027', 'Dundagas novads (Dundaga)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-028', 'Durbes novads (Durbe)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-029', 'Engures novads (Engure)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-030', 'Erglu novads (Ergli)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-031', 'Garkalnes novads (Garkalne)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-032', 'Grobinas novads (Grobina)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-033', 'Gulbenes novads (Gulbene)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-034', 'Iecavas novads (Iecava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-035', 'Ikskiles novads (Ikskile)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-036', 'Ilukstes novads (Ilukste)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-037', 'Incukalna novads (Incukalns)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-038', 'Jaunjelgavas novads (Jaunjelgava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-039', 'Jaunpiebalgas novads (Jaunpiebalga)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-040', 'Jaunpils novads (Jaunpils)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-041', 'Jelgavas novads (Jelgava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-042', 'Jekabpils novads (Jekabpils)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-043', 'Kandavas novads (Kandava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-044', 'Karsavas novads (Karsava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-045', 'Kocenu novads (Koceni)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-046', 'Kokneses novads (Koknese)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-047', 'Kraslavas novads (Kraslava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-048', 'Krimuldas novads (Krimulda)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-049', 'Krustpils novads (Krustpils)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-050', 'Kuldigas novads (Kuldiga)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-051', 'Keguma novads (Kegums)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-052', 'Kekavas novads (Kekava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-053', 'Lielvardes novads (Lielvarde)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-054', 'Limbazu novads (Limbazi)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-055', 'Ligatnes novads (Ligatne)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-056', 'Livanu novads (Livani)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-057', 'Lubanas novads (Lubana)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-058', 'Ludzas novads (Ludza)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-059', 'Madonas novads (Madona)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-060', 'Mazsalacas novads (Mazsalaca)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-061', 'Malpils novads (Malpils)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-062', 'Marupes novads (Marupe)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-063', 'Mersraga novads (Mersrags)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-064', 'Nauksenu novads (Naukseni)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-065', 'Neretas novads (Nereta)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-066', 'Nicas novads (Nica)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-067', 'Ogres novads (Ogre)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-068', 'Olaines novads (Olaine)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-069', 'Ozolnieku novads (Ozolnieki)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-070', 'Pargaujas novads (Pargauja)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-071', 'Pavilostas novads (Pavilosta)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-072', 'Plavinu novads (Plavinas)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-073', 'Preilu novads (Preili)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-074', 'Priekules novads (Priekule)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-075', 'Priekulu novads (Priekuli)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-076', 'Raunas novads (Rauna)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-077', 'Rezeknes novads (Rezekne)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-078', 'Riebinu novads (Riebini)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-079', 'Rojas novads (Roja)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-080', 'Ropazu novads (Ropazi)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-081', 'Rucavas novads (Rucava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-082', 'Rugaju novads (Rugaji)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-083', 'Rundales novads (Rundale)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-084', 'Rujienas novads (Rujiena)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-085', 'Salas novads (Sala)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-086', 'Salacgrivas novads (Salacgriva)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-087', 'Salaspils novads (Salaspils)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-088', 'Saldus novads (Saldus)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-089', 'Saulkrastu novads (Saulkrasti)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-090', 'Sejas novads (Seja)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-091', 'Siguldas novads (Sigulda)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-092', 'Skriveru novads (Skriveri)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-093', 'Skrundas novads (Skrunda)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-094', 'Smiltenes novads (Smiltene)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-095', 'Stopinu novads (Stopini)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-096', 'Strencu novads (Strenci)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-097', 'Talsu novads (Talsi)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-098', 'Tervetes novads (Tervete)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-099', 'Tukuma novads (Tukums)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-100', 'Vainodes novads (Vainode)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-101', 'Valkas novads (Valka)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-102', 'Varaklanu novads (Varaklani)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-103', 'Varkavas novads (Varkava)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-104', 'Vecpiebalgas novads (Vecpiebalga)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-105', 'Vecumnieku novads (Vecumnieki)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-106', 'Ventspils novads (Ventspils)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-107', 'Viesites novads (Viesite)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-108', 'Vilakas novads (Vilaka)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-109', 'Vilanu novads (Vilani)');*/
/*INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LV', 'LV-110', 'Zilupes novads (Zilupe)');*/

/*Lithuania (Littauen)*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LT', 'LT-AL', 'Alytaus Apskritis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LT', 'LT-KU', 'Kauno Apskritis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LT', 'LT-KL', 'Klaipedos Apskritis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LT', 'LT-MR', 'Marijampoles Apskritis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LT', 'LT-PN', 'Panevezio Apskritis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LT', 'LT-SA', 'Siauliu Apskritis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LT', 'LT-TA', 'Taurages Apskritis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LT', 'LT-TE', 'Telsiu Apskritis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LT', 'LT-UT', 'Utenos Apskritis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('LT', 'LT-VL', 'Vilniaus Apskritis');

/*Brazil*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-DF', 'Distrito Federal');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-AC', 'Acre');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-AL', 'Alagoas');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-AP', 'Amapá');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-AM', 'Amazonas');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-BA', 'Bahia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-CE', 'Ceará');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-ES', 'Espírito Santo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-GO', 'Goiás');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-MA', 'Maranhão');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-MT', 'Mato Grosso');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-MS', 'Mato Grosso do Sul');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-MG', 'Minas Gerais');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-PA', 'Pará');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-PB', 'Paraíba');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-PR', 'Paraná');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-PE', 'Pernambuco');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-PI', 'Piauí');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-RJ', 'Rio de Janeiro');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-RN', 'Rio Grande do Norte');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-RS', 'Rio Grande do Sul');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-RO', 'Rondônia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-RR', 'Roraima');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-SC', 'Santa Catarina');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-SP', 'São Paulo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-SE', 'Sergipe');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BR', 'BR-TO', 'Tocantins');

/*India*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-AP', 'Andhra Pradesh');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-AR', 'Arunachal Pradesh');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-AS', 'Assam');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-BR', 'Bihar');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-CT', 'Chhattisgarh');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-GA', 'Goa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-GJ', 'Gujarat');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-HR', 'Haryana');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-HP', 'Himachal Pradesh');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-JK', 'Jammu and Kashmir');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-JH', 'Jharkhand');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-KA', 'Karnataka');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-KL', 'Kerala');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-MP', 'Madhya Pradesh');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-MH', 'Maharashtra');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-MN', 'Manipur');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-ML', 'Meghalaya');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-MZ', 'Mizoram');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-NL', 'Nagaland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-OR', 'Odisha');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-PB', 'Punjab');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-RJ', 'Rajasthan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-SK', 'Sikkim');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-TN', 'Tamil Nadu');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-TG', 'Telangana');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-TR', 'Tripura');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-UT', 'Uttarakhand');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-UP', 'Uttar Pradesh');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-WB', 'West Bengal');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-AN', 'Andaman and Nicobar Islands');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-CH', 'Chandigarh');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-DN', 'Dadra and Nagar Haveli');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-DD', 'Daman and Diu');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-DL', 'Delhi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-LD', 'Lakshadweep');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IN', 'IN-PY', 'Puducherry');

/*Argentina*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-C', 'Ciudad Autónoma de Buenos Aires');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-B', 'Buenos Aires');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-K', 'Catamarca');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-H', 'Chaco');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-U', 'Chubut');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-X', 'Córdoba');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-W', 'Corrientes');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-E', 'Entre Ríos');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-P', 'Formosa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-Y', 'Jujuy');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-L', 'La Pampa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-F', 'La Rioja');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-M', 'Mendoza');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-N', 'Misiones');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-Q', 'Neuquén');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-R', 'Río Negro');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-A', 'Salta');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-J', 'San Juan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-D', 'San Luis');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-Z', 'Santa Cruz');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-S', 'Santa Fe');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-G', 'Santiago del Estero');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-V', 'Tierra del Fuego');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('AR', 'AR-T', 'Tucumán');

/*Italy*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-AG', 'Agrigento');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-AL', 'Alessandria');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-AN', 'Ancona');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-AO', 'Aosta');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-AR', 'Arezzo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-AP', 'Ascoli Piceno');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-AT', 'Asti');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-AV', 'Avellino');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-BA', 'Bari');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-BT', 'Barletta-Andria-Trani');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-BL', 'Belluno');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-BN', 'Benevento');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-BG', 'Bergamo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-BI', 'Biella');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-BO', 'Bologna');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-BZ', 'Bolzano');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-BS', 'Brescia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-BR', 'Brindisi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CA', 'Cagliari');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CL', 'Caltanissetta');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CB', 'Campobasso');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CI', 'Carbonia-Iglesias');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CE', 'Caserta');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CT', 'Catania');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CZ', 'Catanzaro');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CH', 'Chieti');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CO', 'Como');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CS', 'Cosenza');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CR', 'Cremona');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-KR', 'Crotone');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-CN', 'Cuneo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-EN', 'Enna');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-FM', 'Fermo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-FE', 'Ferrara');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-FI', 'Firenze');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-FG', 'Foggia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-FC', 'Forlì-Cesena');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-FR', 'Frosinone');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-GE', 'Genova');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-GO', 'Gorizia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-GR', 'Grosseto');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-IM', 'Imperia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-IS', 'Isernia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-SP', 'La Spezia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-AQ', 'L\'Aquila');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-LT', 'Latina');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-LE', 'Lecce');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-LC', 'Lecco');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-LI', 'Livorno');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-LO', 'Lodi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-LU', 'Lucca');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-MC', 'Macerata');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-MN', 'Mantova');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-MS', 'Massa and Carrara');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-MT', 'Matera');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-VS', 'Medio Campidano');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-ME', 'Messina');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-MI', 'Milano');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-MO', 'Modena');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-MB', 'Monza and Brianza');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-NA', 'Napoli');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-NO', 'Novara');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-NU', 'Nuoro');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-OG', 'Ogliastra');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-OT', 'Olbia-Tempio');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-OR', 'Oristano');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PD', 'Padova');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PA', 'Palermo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PR', 'Parma');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PV', 'Pavia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PG', 'Perugia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PU', 'Pesaro and Urbino');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PE', 'Pescara');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PC', 'Piacenza');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PI', 'Pisa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PT', 'Pistoia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PN', 'Pordenone');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PZ', 'Potenza');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-PO', 'Prato');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-RG', 'Ragusa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-RA', 'Ravenna');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-RC', 'Reggio Calabria');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-RE', 'Reggio Emilia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-RI', 'Rieti');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-RN', 'Rimini');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-RM', 'Roma');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-RO', 'Rovigo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-SA', 'Salerno');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-SS', 'Sassari');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-SV', 'Savona');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-SI', 'Siena');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-SR', 'Siracusa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-SO', 'Sondrio');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-TA', 'Taranto');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-TE', 'Teramo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-TR', 'Terni');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-TO', 'Torino');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-TP', 'Trapani');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-TN', 'Trento');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-TV', 'Treviso');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-TS', 'Trieste');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-UD', 'Udine');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-VA', 'Varese');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-VE', 'Venezia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-VB', 'Verbano-Cusio-Ossola');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-VC', 'Vercelli');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-VR', 'Verona');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-VV', 'Vibo Valentia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-VI', 'Vicenza');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('IT', 'IT-VT', 'Viterbo');

/*Japan*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-23', 'Aichi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-05', 'Akita');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-02', 'Aomori');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-38', 'Ehime');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-21', 'Gifu');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-10', 'Gunma');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-34', 'Hiroshima');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-01', 'Hokkaido');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-18', 'Fukui');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-40', 'Fukuoka');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-07', 'Fukushima');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-28', 'Hyogo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-08', 'Ibaraki');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-17', 'Ishikawa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-03', 'Iwate');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-37', 'Kagawa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-46', 'Kagoshima');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-14', 'Kanagawa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-39', 'Kochi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-43', 'Kumamoto');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-26', 'Kyoto');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-24', 'Mie');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-04', 'Miyagi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-45', 'Miyazaki');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-20', 'Nagano');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-42', 'Nagasaki');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-29', 'Nara');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-15', 'Niigata');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-44', 'Oita');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-33', 'Okayama');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-47', 'Okinawa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-27', 'Osaka');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-41', 'Saga');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-11', 'Saitama');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-25', 'Shiga');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-32', 'Shimane');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-22', 'Shizuoka');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-12', 'Chiba');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-09', 'Tochigi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-36', 'Tokushima');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-13', 'Tokyo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-31', 'Tottori');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-16', 'Toyama');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-30', 'Wakayama');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-06', 'Yamagata');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-35', 'Yamaguchi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('JP', 'JP-19', 'Yamanashi');

/*Mexico*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-CMX', 'Mexican Federal District');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-AGU', 'Aguascalientes');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-BCN', 'Baja California');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-BCS', 'Baja California Sur');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-CAM', 'Campeche');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-COA', 'Coahuila');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-COL', 'Colima');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-CHP', 'Chiapas');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-CHH', 'Chihuahua');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-DUR', 'Durango');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-GUA', 'Guanajuato');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-GRO', 'Guerrero');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-HID', 'Hidalgo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-JAL', 'Jalisco');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-MEX', 'México');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-MIC', 'Michoacán');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-MOR', 'Morelos');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-NAY', 'Nayarit');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-NLE', 'Nuevo León');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-OAX', 'Oaxaca');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-PUE', 'Puebla');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-QUE', 'Querétaro');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-ROO', 'Quintana Roo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-SLP', 'San Luis Potosí');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-SIN', 'Sinaloa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-SON', 'Sonora');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-TAB', 'Tabasco');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-TAM', 'Tamaulipas');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-TLA', 'Tlaxcala');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-VER', 'Veracruz');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-YUC', 'Yucatán');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('MX', 'MX-ZAC', 'Zacatecas');

/*Thailand*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-10', 'Krung Thep Maha Nakhon [Bangkok]');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-S', 'Phatthaya');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-37', 'Amnat Charoen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-15', 'Ang Thong');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-38', 'Bueng Kan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-31', 'Buri Ram');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-24', 'Chachoengsao');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-18', 'Chai Nat');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-36', 'Chaiyaphum');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-22', 'Chanthaburi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-50', 'Chiang Mai');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-57', 'Chiang Rai');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-20', 'Chon Buri');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-86', 'Chumphon');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-46', 'Kalasin');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-62', 'Kamphaeng Phet');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-71', 'Kanchanaburi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-40', 'Khon Kaen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-81', 'Krabi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-52', 'Lampang');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-51', 'Lamphun');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-42', 'Loei');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-16', 'Lop Buri');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-58', 'Mae Hong Son');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-44', 'Maha Sarakham');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-49', 'Mukdahan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-26', 'Nakhon Nayok');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-73', 'Nakhon Pathom');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-48', 'Nakhon Phanom');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-30', 'Nakhon Ratchasima');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-60', 'Nakhon Sawan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-80', 'Nakhon Si Thammarat');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-55', 'Nan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-96', 'Narathiwat');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-39', 'Nong Bua Lam Phu');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-43', 'Nong Khai');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-12', 'Nonthaburi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-13', 'Pathum Thani');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-94', 'Pattani');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-82', 'Phangnga');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-93', 'Phatthalung');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-56', 'Phayao');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-67', 'Phetchabun');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-76', 'Phetchaburi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-66', 'Phichit');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-65', 'Phitsanulok');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-54', 'Phrae');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-14', 'Phra Nakhon Si Ayutthaya');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-83', 'Phuket');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-25', 'Prachin Buri');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-77', 'Prachuap Khiri Khan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-85', 'Ranong');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-70', 'Ratchaburi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-21', 'Rayong');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-45', 'Roi Et');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-27', 'Sa Kaeo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-47', 'Sakon Nakhon');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-11', 'Samut Prakan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-74', 'Samut Sakhon');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-75', 'Samut Songkhram');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-19', 'Saraburi');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-91', 'Satun');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-17', 'Sing Buri');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-33', 'Si Sa Ket');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-90', 'Songkhla');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-64', 'Sukhothai');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-72', 'Suphan Buri');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-84', 'Surat Thani');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-32', 'Surin');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-63', 'Tak');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-92', 'Trang');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-23', 'Trat');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-34', 'Ubon Ratchathani');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-41', 'Udon Thani');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-61', 'Uthai Thani');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-53', 'Uttaradit');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-95', 'Yala');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('TH', 'TH-35', 'Yasothon');

/*Netherlands*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-DR', 'Drenthe');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-FL', 'Flevoland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-FR', 'Fryslân');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-GE', 'Gelderland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-GR', 'Groningen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-LI', 'Limburg');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-NB', 'Noord-Brabant');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-NH', 'Noord-Holland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-OV', 'Overijssel');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-UT', 'Utrecht');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-ZE', 'Zeeland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('NL', 'NL-ZH', 'Zuid-Holland');

/*Berlgium*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BE', 'BE-VAN', 'Antwerpen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BE', 'BE-WBR', 'Brabant wallon');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BE', 'BE-WHT', 'Hainaut');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BE', 'BE-WLG', 'Liège');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BE', 'BE-VLI', 'Limburg');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BE', 'BE-WLX', 'Luxembourg');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BE', 'BE-WNA', 'Namur');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BE', 'BE-VOV', 'Oost-Vlaanderen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BE', 'BE-VBR', 'Vlaams-Brabant');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BE', 'BE-VWV', 'West-Vlaanderen');

/*Bulgaria*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-01', 'Blagoevgrad');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-02', 'Burgas');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-08', 'Dobrich');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-07', 'Gabrovo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-26', 'Haskovo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-09', 'Kardzhali');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-10', 'Kyustendil');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-11', 'Lovech');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-12', 'Montana');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-13', 'Pazardzhik');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-14', 'Pernik');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-15', 'Pleven');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-16', 'Plovdiv');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-17', 'Razgrad');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-18', 'Ruse');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-27', 'Shumen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-19', 'Silistra');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-20', 'Sliven');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-21', 'Smolyan');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-23', 'Sofia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-22', 'Sofia (stolitsa)');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-24', 'Stara Zagora');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-25', 'Targovishte');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-03', 'Varna');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-04', 'Veliko Tarnovo');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-05', 'Vidin');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-06', 'Vratsa');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('BG', 'BG-28', 'Yambol');

/*Cypros*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CY', 'CY-04', 'Famagusta');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CY', 'CY-06', 'Kyrenia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CY', 'CY-03', 'Larnaca');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CY', 'CY-01', 'Nicosia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CY', 'CY-02', 'Limassol');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CY', 'CY-05', 'Paphos');

/*Czechia*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-31', 'South Bohemia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-64', 'South Moravia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-41', 'Karlovy Vary');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-52', 'Hradec Králové');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-51', 'Liberec');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-80', 'Moravia-Silesia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-71', 'Olomouc');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-53', 'Pardubice');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-32', 'Plzen');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-10', 'Prague');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-20', 'Central Bohemia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-42', 'Ústí nad Labem');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-63', 'Vysocina');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('CZ', 'CZ-72', 'Zlín');

/*Denmark*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DK', 'DK-84', 'Capital Region of Denmark');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DK', 'DK-82', 'Central Denmark Region');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DK', 'DK-81', 'North Denmark Region');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DK', 'DK-85', 'Region Zealand');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('DK', 'DK-83', 'Region of Southern Denmark');

/*United Kingdom*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GB', 'GB-ENG', 'England');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GB', 'GB-NIR', 'Northern Ireland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GB', 'GB-SCT', 'Scotland');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GB', 'GB-WLS', 'Wales');

/*Greece*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-A', 'Eastern Macedonia and Thrace');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-I', 'Attica');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-G', 'Western Greece');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-C', 'Western Macedonia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-F', 'Ionian Islands');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-D', 'Epirus');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-B', 'Central Macedonia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-M', 'Crete');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-L', 'Southern Aegean');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-J', 'Peloponnese');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-H', 'Central Greece');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-E', 'Thessaly');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('GR', 'GR-K', 'Northern Aegean');

/*Croatia*/
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-21', 'Zagreb City');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-07', 'Bjelovar-Bilogora');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-12', 'Brod-Posavina');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-19', 'Dubrovnik-Neretva');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-18', 'Istria');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-04', 'Karlovac');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-06', 'Koprivnica-Krizevci');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-02', 'Krapina-Zagorje');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-09', 'Lika-Senj');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-20', 'Medimurje');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-14', 'Osijek-Baranja');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-11', 'Pozega-Slavonia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-08', 'Primorje-Gorski Kotar');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-03', 'Sisak-Moslavina');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-17', 'Split-Dalmatia');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-15', 'Sibenik-Knin');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-05', 'Varazdin');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-10', 'Virovitica-Podravina');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-16', 'Vukovar-Srijem');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-13', 'Zadar');
INSERT INTO `tstaat`(`cLandIso`, `cCode`, `cName`) VALUES('HR', 'HR-01', 'Zagreb County');
SQL;

		$this->execute($szSql);
    }

    public function down()
    {
		$szSql = <<<SQL
DROP TABLE IF EXISTS `tstaat`;

CREATE TABLE IF NOT EXISTS `tstaat` (
      `kStaat` int(10) unsigned NOT NULL AUTO_INCREMENT
    , `cLandIso` varchar(2) NOT NULL
    , `cName` varchar(255) NOT NULL
    , `cCode` varchar(10) NOT NULL
    , PRIMARY KEY (`kStaat`)
    , KEY `cLandIso` (`cLandIso`)
    , KEY `cName` (`cName`)
    , KEY `cCode` (`cCode`)
);

INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (1,'US','Alabama','AL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (2,'US','Alaska','AK');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (3,'US','American Samoa','AS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (4,'US','Arizona','AZ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (5,'US','Arkansas','AR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (6,'US','Armed Forces Africa','AF');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (7,'US','Armed Forces Americas','AA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (8,'US','Armed Forces Canada','AC');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (9,'US','Armed Forces Europe','AE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (10,'US','Armed Forces Middle East','AM');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (11,'US','Armed Forces Pacific','AP');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (12,'US','California','CA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (13,'US','Colorado','CO');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (14,'US','Connecticut','CT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (15,'US','Delaware','DE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (16,'US','District of Columbia','DC');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (17,'US','Federated States Of Micronesia','FM');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (18,'US','Florida','FL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (19,'US','Georgia','GA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (20,'US','Guam','GU');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (21,'US','Hawaii','HI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (22,'US','Idaho','ID');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (23,'US','Illinois','IL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (24,'US','Indiana','IN');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (25,'US','Iowa','IA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (26,'US','Kansas','KS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (27,'US','Kentucky','KY');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (28,'US','Louisiana','LA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (29,'US','Louisiana','LA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (30,'US','Maine','ME');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (31,'US','Marshall Islands','MH');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (32,'US','Maryland','MD');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (33,'US','Massachusetts','MA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (34,'US','Michigan','MI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (35,'US','Minnesota','MN');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (36,'US','Mississippi','MS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (37,'US','Missouri','MO');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (38,'US','Montana','MT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (39,'US','Nebraska','NE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (40,'US','Nevada','NV');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (41,'US','New Hampshire','NH');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (42,'US','New Jersey','NJ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (43,'US','New Mexico','NM');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (44,'US','New York','NY');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (45,'US','North Carolina','NC');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (46,'US','North Dakota','ND');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (47,'US','Northern Mariana Islands','MP');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (48,'US','Ohio','OH');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (49,'US','Oklahoma','OK');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (50,'US','Oregon','OR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (51,'US','Palau','PW');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (52,'US','Pennsylvania','PA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (53,'US','Puerto Rico','PR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (54,'US','Rhode Island','RI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (55,'US','South Carolina','SC');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (56,'US','South Dakota','SD');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (57,'US','Tennessee','TN');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (58,'US','Texas','TX');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (59,'US','Utah','UT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (60,'US','Vermont','VT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (61,'US','Virgin Islands','VI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (62,'US','Virginia','VA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (63,'US','Washington','WA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (64,'US','West Virginia','WV');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (65,'US','Wisconsin','WI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (66,'US','Wyoming','WY');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (67,'CA','Alberta','AB');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (68,'CA','British Columbia','BC');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (69,'CA','Manitoba','MB');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (70,'CA','Newfoundland and Labrador','NL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (71,'CA','New Brunswick','NB');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (72,'CA','Nova Scotia','NS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (73,'CA','Northwest Territories','NT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (74,'CA','Nunavut','NU');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (75,'CA','Ontario','ON');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (76,'CA','Prince Edward Island','PE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (77,'CA','Quebec','QC');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (78,'CA','Saskatchewan','SK');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (79,'CA','Yukon Territory','YT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (80,'DE','Niedersachsen','NDS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (81,'DE','Baden-Württemberg','BAW');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (82,'DE','Bayern','BAY');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (83,'DE','Berlin','BER');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (84,'DE','Brandenburg','BRG');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (85,'DE','Bremen','BRE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (86,'DE','Hamburg','HAM');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (87,'DE','Hessen','HES');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (88,'DE','Mecklenburg-Vorpommern','MEC');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (89,'DE','Nordrhein-Westfalen','NRW');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (90,'DE','Rheinland-Pfalz','RHE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (91,'DE','Saarland','SAR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (92,'DE','Sachsen','SAS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (93,'DE','Sachsen-Anhalt','SAC');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (94,'DE','Schleswig-Holstein','SCN');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (95,'DE','Thüringen','THE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (96,'AT','Wien','WI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (97,'AT','Niederösterreich','NO');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (98,'AT','Oberösterreich','OO');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (99,'AT','Salzburg','SB');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (100,'AT','Kärnten','KN');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (101,'AT','Steiermark','ST');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (102,'AT','Tirol','TI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (103,'AT','Burgenland','BL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (104,'AT','Voralberg','VB');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (105,'CH','Aargau','AG');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (106,'CH','Appenzell Innerrhoden','AI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (107,'CH','Appenzell Ausserrhoden','AR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (108,'CH','Bern','BE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (109,'CH','Basel-Landschaft','BL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (110,'CH','Basel-Stadt','BS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (111,'CH','Freiburg','FR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (112,'CH','Genf','GE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (113,'CH','Glarus','GL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (114,'CH','Graubünden','GR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (115,'CH','Jura','JU');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (116,'CH','Luzern','LU');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (117,'CH','Neuenburg','NE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (118,'CH','Nidwalden','NW');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (119,'CH','Obwalden','OW');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (120,'CH','St. Gallen','SG');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (121,'CH','Schaffhausen','SH');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (122,'CH','Solothurn','SO');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (123,'CH','Schwyz','SZ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (124,'CH','Thurgau','TG');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (125,'CH','Tessin','TI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (126,'CH','Uri','UR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (127,'CH','Waadt','VD');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (128,'CH','Wallis','VS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (129,'CH','Zug','ZG');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (130,'CH','Zürich','ZH');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (131,'ES','A Coruña','A Coru?a');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (132,'ES','Alava','Alava');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (133,'ES','Albacete','Albacete');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (134,'ES','Alicante','Alicante');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (135,'ES','Almeria','Almeria');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (136,'ES','Asturias','Asturias');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (137,'ES','Avila','Avila');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (138,'ES','Badajoz','Badajoz');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (139,'ES','Baleares','Baleares');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (140,'ES','Barcelona','Barcelona');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (141,'ES','Burgos','Burgos');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (142,'ES','Caceres','Caceres');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (143,'ES','Cadiz','Cadiz');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (144,'ES','Cantabria','Cantabria');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (145,'ES','Castellon','Castellon');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (146,'ES','Ceuta','Ceuta');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (147,'ES','Ciudad Real','Ciudad Rea');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (148,'ES','Cordoba','Cordoba');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (149,'ES','Cuenca','Cuenca');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (150,'ES','Girona','Girona');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (151,'ES','Granada','Granada');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (152,'ES','Guadalajara','Guadalajar');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (153,'ES','Guipuzcoa','Guipuzcoa');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (154,'ES','Huelva','Huelva');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (155,'ES','Huesca','Huesca');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (156,'ES','Jaen','Jaen');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (157,'ES','La Rioja','La Rioja');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (158,'ES','Las Palmas','Las Palmas');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (159,'ES','Leon','Leon');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (160,'ES','Lleida','Lleida');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (161,'ES','Lugo','Lugo');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (162,'ES','Madrid','Madrid');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (163,'ES','Malaga','Malaga');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (164,'ES','Melilla','Melilla');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (165,'ES','Murcia','Murcia');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (166,'ES','Navarra','Navarra');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (167,'ES','Ourense','Ourense');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (168,'ES','Palencia','Palencia');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (169,'ES','Pontevedra','Pontevedra');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (170,'ES','Salamanca','Salamanca');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (171,'ES','Santa Cruz de Tenerife','Santa Cruz');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (172,'ES','Segovia','Segovia');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (173,'ES','Sevilla','Sevilla');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (174,'ES','Soria','Soria');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (175,'ES','Tarragona','Tarragona');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (176,'ES','Teruel','Teruel');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (177,'ES','Toledo','Toledo');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (178,'ES','Valencia','Valencia');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (179,'ES','Valladolid','Valladolid');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (180,'ES','Vizcaya','Vizcaya');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (181,'ES','Zamora','Zamora');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (182,'ES','Zaragoza','Zaragoza');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (183,'FR','Ain','1');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (184,'FR','Aisne','2');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (185,'FR','Allier','3');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (186,'FR','Alpes-de-Haute-Provence','4');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (187,'FR','Hautes-Alpes','5');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (188,'FR','Alpes-Maritimes','6');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (189,'FR','Ardèche','7');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (190,'FR','Ardennes','8');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (191,'FR','Ariège','9');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (192,'FR','Aube','10');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (193,'FR','Aude','11');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (194,'FR','Aveyron','12');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (195,'FR','Bouches-du-Rhône','13');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (196,'FR','Calvados','14');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (197,'FR','Cantal','15');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (198,'FR','Charente','16');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (199,'FR','Charente-Maritime','17');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (200,'FR','Cher','18');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (201,'FR','Corrèze','19');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (202,'FR','Corse-du-Sud','2A');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (203,'FR','Haute-Corse','2B');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (204,'FR','Côte-d\'Or','21');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (205,'FR','Côtes-d\'Armor','22');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (206,'FR','Creuse','23');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (207,'FR','Dordogne','24');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (208,'FR','Doubs','25');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (209,'FR','Drôme','26');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (210,'FR','Eure','27');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (211,'FR','Eure-et-Loir','28');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (212,'FR','Finistère','29');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (213,'FR','Gard','30');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (214,'FR','Haute-Garonne','31');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (215,'FR','Gers','32');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (216,'FR','Gironde','33');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (217,'FR','Hérault','34');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (218,'FR','Ille-et-Vilaine','35');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (219,'FR','Indre','36');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (220,'FR','Indre-et-Loire','37');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (221,'FR','Isère','38');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (222,'FR','Jura','39');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (223,'FR','Landes','40');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (224,'FR','Loir-et-Cher','41');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (225,'FR','Loire','42');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (226,'FR','Haute-Loire','43');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (227,'FR','Loire-Atlantique','44');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (228,'FR','Loiret','45');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (229,'FR','Lot','46');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (230,'FR','Lot-et-Garonne','47');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (231,'FR','Lozère','48');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (232,'FR','Maine-et-Loire','49');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (233,'FR','Manche','50');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (234,'FR','Marne','51');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (235,'FR','Haute-Marne','52');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (236,'FR','Mayenne','53');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (237,'FR','Meurthe-et-Moselle','54');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (238,'FR','Meuse','55');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (239,'FR','Morbihan','56');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (240,'FR','Moselle','57');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (241,'FR','Nièvre','58');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (242,'FR','Nord','59');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (243,'FR','Oise','60');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (244,'FR','Orne','61');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (245,'FR','Pas-de-Calais','62');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (246,'FR','Puy-de-Dôme','63');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (247,'FR','Pyrénées-Atlantiques','64');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (248,'FR','Hautes-Pyrénées','65');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (249,'FR','Pyrénées-Orientales','66');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (250,'FR','Bas-Rhin','67');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (251,'FR','Haut-Rhin','68');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (252,'FR','Rhône','69');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (253,'FR','Haute-Saône','70');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (254,'FR','Saône-et-Loire','71');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (255,'FR','Sarthe','72');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (256,'FR','Savoie','73');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (257,'FR','Haute-Savoie','74');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (258,'FR','Paris','75');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (259,'FR','Seine-Maritime','76');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (260,'FR','Seine-et-Marne','77');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (261,'FR','Yvelines','78');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (262,'FR','Deux-Sèvres','79');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (263,'FR','Somme','80');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (264,'FR','Tarn','81');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (265,'FR','Tarn-et-Garonne','82');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (266,'FR','Var','83');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (267,'FR','Vaucluse','84');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (268,'FR','Vendée','85');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (269,'FR','Vienne','86');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (270,'FR','Haute-Vienne','87');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (271,'FR','Vosges','88');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (272,'FR','Yonne','89');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (273,'FR','Territoire-de-Belfort','90');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (274,'FR','Essonne','91');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (275,'FR','Hauts-de-Seine','92');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (276,'FR','Seine-Saint-Denis','93');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (277,'FR','Val-de-Marne','94');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (278,'FR','Val-d\'Oise','95');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (279,'RO','Alba','AB');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (280,'RO','Arad','AR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (281,'RO','Arges','AG');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (282,'RO','Bacau','BC');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (283,'RO','Bihor','BH');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (284,'RO','Bistrita-Nasaud','BN');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (285,'RO','Botosani','BT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (286,'RO','Brasov','BV');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (287,'RO','Braila','BR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (288,'RO','Bucuresti','B');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (289,'RO','Buzau','BZ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (290,'RO','Caras-Severin','CS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (291,'RO','Calarasi','CL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (292,'RO','Cluj','CJ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (293,'RO','Constanta','CT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (294,'RO','Covasna','CV');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (295,'RO','Dâmbovita','DB');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (296,'RO','Dolj','DJ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (297,'RO','Galati','GL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (298,'RO','Giurgiu','GR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (299,'RO','Gorj','GJ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (300,'RO','Harghita','HR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (301,'RO','Hunedoara','HD');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (302,'RO','Ialomita','IL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (303,'RO','Iasi','IS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (304,'RO','Ilfov','IF');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (305,'RO','Maramures','MM');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (306,'RO','Mehedinti','MH');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (307,'RO','Mures','MS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (308,'RO','Neamt','NT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (309,'RO','Olt','OT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (310,'RO','Prahova','PH');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (311,'RO','Satu-Mare','SM');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (312,'RO','Salaj','SJ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (313,'RO','Sibiu','SB');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (314,'RO','Suceava','SV');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (315,'RO','Teleorman','TR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (316,'RO','Timis','TM');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (317,'RO','Tulcea','TL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (318,'RO','Vaslui','VS');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (319,'RO','Vâlcea','VL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (320,'RO','Vrancea','VN');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (321,'FI','Lappi','Lappi');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (322,'FI','Pohjois-Pohjanmaa','Pohjois-Po');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (323,'FI','Kainuu','Kainuu');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (324,'FI','Pohjois-Karjala','Pohjois-Ka');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (325,'FI','Pohjois-Savo','Pohjois-Sa');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (326,'FI','Etelä-Savo','Etelä-Savo');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (327,'FI','Etelä-Pohjanmaa','Etelä-Pohj');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (328,'FI','Pohjanmaa','Pohjanmaa');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (329,'FI','Pirkanmaa','Pirkanmaa');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (330,'FI','Satakunta','Satakunta');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (331,'FI','Keski-Pohjanmaa','Keski-Pohj');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (332,'FI','Keski-Suomi','Keski-Suom');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (333,'FI','Varsinais-Suomi','Varsinais-');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (334,'FI','Etelä-Karjala','Etelä-Karj');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (335,'FI','Päijät-Häme','Päijät-Häm');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (336,'FI','Kanta-Häme','Kanta-Häme');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (337,'FI','Uusimaa','Uusimaa');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (338,'FI','Itä-Uusimaa','Itä-Uusima');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (339,'FI','Kymenlaakso','Kymenlaaks');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (340,'FI','Ahvenanmaa','Ahvenanmaa');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (341,'EE','Harjumaa','EE-37');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (342,'EE','Hiiumaa','EE-39');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (343,'EE','Ida-Virumaa','EE-44');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (344,'EE','Jõgevamaa','EE-49');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (345,'EE','Järvamaa','EE-51');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (346,'EE','Läänemaa','EE-57');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (347,'EE','Lääne-Virumaa','EE-59');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (348,'EE','Põlvamaa','EE-65');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (349,'EE','Pärnumaa','EE-67');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (350,'EE','Raplamaa','EE-70');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (351,'EE','Saaremaa','EE-74');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (352,'EE','Tartumaa','EE-78');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (353,'EE','Valgamaa','EE-82');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (354,'EE','Viljandimaa','EE-84');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (355,'EE','Võrumaa','EE-86');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (356,'LV','Daugavpils','LV-DGV');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (357,'LV','Jelgava','LV-JEL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (358,'LV','Jekabpils','Jekabpils');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (359,'LV','Jurmala','LV-JUR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (360,'LV','Liepaja','LV-LPX');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (361,'LV','Liepajas novads','LV-LE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (362,'LV','Rezekne','LV-REZ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (363,'LV','Riga','LV-RIX');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (364,'LV','Rigas novads','LV-RI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (365,'LV','Valmiera','Valmiera');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (366,'LV','Ventspils','LV-VEN');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (367,'LV','Aglonas novads','Aglonas no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (368,'LV','Aizkraukles novads','LV-AI');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (369,'LV','Aizputes novads','Aizputes n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (370,'LV','Aknistes novads','Aknistes n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (371,'LV','Alojas novads','Alojas nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (372,'LV','Alsungas novads','Alsungas n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (373,'LV','Aluksnes novads','LV-AL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (374,'LV','Amatas novads','Amatas nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (375,'LV','Apes novads','Apes novad');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (376,'LV','Auces novads','Auces nova');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (377,'LV','Babites novads','Babites no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (378,'LV','Baldones novads','Baldones n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (379,'LV','Baltinavas novads','Baltinavas');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (380,'LV','Balvu novads','LV-BL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (381,'LV','Bauskas novads','LV-BU');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (382,'LV','Beverinas novads','Beverinas ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (383,'LV','Brocenu novads','Brocenu no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (384,'LV','Burtnieku novads','Burtnieku ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (385,'LV','Carnikavas novads','Carnikavas');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (386,'LV','Cesvaines novads','Cesvaines ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (387,'LV','Ciblas novads','Ciblas nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (388,'LV','Cesu novads','LV-CE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (389,'LV','Dagdas novads','Dagdas nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (390,'LV','Daugavpils novads','LV-DA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (391,'LV','Dobeles novads','LV-DO');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (392,'LV','Dundagas novads','Dundagas n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (393,'LV','Durbes novads','Durbes nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (394,'LV','Engures novads','Engures no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (395,'LV','Garkalnes novads','Garkalnes ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (396,'LV','Grobinas novads','Grobinas n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (397,'LV','Gulbenes novads','LV-GU');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (398,'LV','Iecavas novads','Iecavas no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (399,'LV','Ikskiles novads','Ikskiles n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (400,'LV','Ilukstes novads','Ilukstes n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (401,'LV','Incukalna novads','Incukalna ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (402,'LV','Jaunjelgavas novads','Jaunjelgav');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (403,'LV','Jaunpiebalgas novads','Jaunpiebal');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (404,'LV','Jaunpils novads','Jaunpils n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (405,'LV','Jelgavas novads','LV-JL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (406,'LV','Jekabpils novads','LV-JK');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (407,'LV','Kandavas novads','Kandavas n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (408,'LV','Kokneses novads','Kokneses n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (409,'LV','Krimuldas novads','Krimuldas ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (410,'LV','Krustpils novads','Krustpils ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (411,'LV','Kraslavas novads','LV-KR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (412,'LV','Kuldigas novads','LV-KU');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (413,'LV','Karsavas novads','Karsavas n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (414,'LV','Lielvardes novads','Lielvardes');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (415,'LV','Limbazu novads','LV-LM');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (416,'LV','Lubanas novads','Lubanas no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (417,'LV','Ludzas novads','LV-LU');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (418,'LV','Ligatnes novads','Ligatnes n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (419,'LV','Livanu novads','Livanu nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (420,'LV','Madonas novads','LV-MA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (421,'LV','Mazsalacas novads','Mazsalacas');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (422,'LV','Malpils novads','Malpils no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (423,'LV','Marupes novads','Marupes no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (424,'LV','Nauksenu novads','Nauksenu n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (425,'LV','Neretas novads','Neretas no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (426,'LV','Nicas novads','Nicas nova');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (427,'LV','Ogres novads','LV-OG');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (428,'LV','Olaines novads','Olaines no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (429,'LV','Ozolnieku novads','Ozolnieku ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (430,'LV','Preilu novads','LV-PR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (431,'LV','Priekules novads','Priekules ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (432,'LV','Priekulu novads','Priekulu n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (433,'LV','Pargaujas novads','Pargaujas ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (434,'LV','Pavilostas novads','Pavilostas');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (435,'LV','Plavinu novads','Plavinu no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (436,'LV','Raunas novads','Raunas nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (437,'LV','Riebinu novads','Riebinu no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (438,'LV','Rojas novads','Rojas nova');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (439,'LV','Ropazu novads','Ropazu nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (440,'LV','Rucavas novads','Rucavas no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (441,'LV','Rugaju novads','Rugaju nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (442,'LV','Rundales novads','Rundales n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (443,'LV','Rezeknes novads','LV-RE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (444,'LV','Rujienas novads','Rujienas n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (445,'LV','Salacgrivas novads','Salacgriva');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (446,'LV','Salas novads','Salas nova');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (447,'LV','Salaspils novads','Salaspils ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (448,'LV','Saldus novads','LV-SA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (449,'LV','Saulkrastu novads','Saulkrastu');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (450,'LV','Siguldas novads','Siguldas n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (451,'LV','Skrundas novads','Skrundas n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (452,'LV','Skriveru novads','Skriveru n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (453,'LV','Smiltenes novads','Smiltenes ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (454,'LV','Stopinu novads','Stopinu no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (455,'LV','Strencu novads','Strencu no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (456,'LV','Sejas novads','Sejas nova');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (457,'LV','Talsu novads','LV-TA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (458,'LV','Tukuma novads','LV-TU');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (459,'LV','Tervetes novads','Tervetes n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (460,'LV','Vainodes novads','Vainodes n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (461,'LV','Valkas novads','LV-VK');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (462,'LV','Valmieras novads','LV-VM');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (463,'LV','Varaklanu novads','Varaklanu ');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (464,'LV','Vecpiebalgas novads','Vecpiebalg');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (465,'LV','Vecumnieku novads','Vecumnieku');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (466,'LV','Ventspils novads','LV-VE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (467,'LV','Viesites novads','Viesites n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (468,'LV','Vilakas novads','Vilakas no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (469,'LV','Vilanu novads','Vilanu nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (470,'LV','Varkavas novads','Varkavas n');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (471,'LV','Zilupes novads','Zilupes no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (472,'LV','Adazu novads','Adazu nova');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (473,'LV','Erglu novads','Erglu nova');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (474,'LV','Keguma novads','Keguma nov');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (475,'LV','Kekavas novads','Kekavas no');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (476,'LT','Alytaus Apskritis','LT-AL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (477,'LT','Kauno Apskritis','LT-KU');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (478,'LT','Klaipedos Apskritis','LT-KL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (479,'LT','Marijampoles Apskritis','LT-MR');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (480,'LT','Panevezio Apskritis','LT-PN');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (481,'LT','siauliu Apskritis','LT-SA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (482,'LT','Taurages Apskritis','LT-TA');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (483,'LT','Telsiu Apskritis','LT-TE');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (484,'LT','Utenos Apskritis','LT-UT');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (485,'LT','Vilniaus Apskritis','LT-VL');
INSERT INTO `tstaat` (`kStaat`, `cLandIso`, `cName`, `cCode`) VALUES (486,'FR','Französisch-Guayana','973');
SQL;

		$this->execute($szSql);
    }
}

