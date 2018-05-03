<?php
/**
 * Add OnPage Composer tables
 *
 * @author Marco Stickel
 */

class Migration_20180226121900 extends Migration implements IMigration
{
    protected $author      = 'Marco Stickel';
    protected $description = 'Add OnPage Composer tables';

    public function up()
    {
        $this->execute("CREATE TABLE topcportlet (
            kPortlet INT AUTO_INCREMENT PRIMARY KEY,
            kPlugin INT NOT NULL,
            cTitle VARCHAR(255) NOT NULL,
            cClass VARCHAR(255) NOT NULL,
            cGroup VARCHAR(255) NOT NULL,
            bActive TINYINT NOT NULL DEFAULT 1
        )");

        $this->execute("CREATE TABLE topcblueprint (
            kBlueprint INT AUTO_INCREMENT PRIMARY KEY,
            kPlugin INT NOT NULL,
            cName VARCHAR(255) NOT NULL,
            cJson LONGTEXT,
            bActive TINYINT NOT NULL DEFAULT 1
        )");

        $this->execute("CREATE TABLE topcpage (
            kPage INT AUTO_INCREMENT PRIMARY KEY,
            cPageId CHAR(32) NOT NULL,
            cPageUrl VARCHAR(255) NOT NULL,
            cAreasJson LONGTEXT NOT NULL,
            dLastModified DATETIME NOT NULL,
            cLockedBy VARCHAR(255) NOT NULL,
            dLockedAt DATETIME NOT NULL,
            bReplace BOOL NOT NULL
        )");

        $this->execute("INSERT INTO tadminmenu (kAdminmenueGruppe, cModulId, cLinkname, cURL, cRecht, nSort)
            VALUES ('4', 'core_jtl', 'OnPage Composer', 'opc-controlcenter.php', 'CONTENT_PAGE_VIEW', '115');");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Heading', 'Heading', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Image', 'Image', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Text', 'Text', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Product Stream', 'ProductStream', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Row', 'Row', 'layout')");

        /*
        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Heading', 'Heading', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Image', 'Image', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Button', 'Button', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Bilder-Slider', 'ImageSlider', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Artikel-Slider', 'ProductSlider', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Product-Stream', 'ProductStream', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Banner', 'Banner', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Text', 'Text', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Video', 'Video', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Panel', 'Panel', 'layout')");

        */
        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Accordion', 'Accordion', 'layout')");
/*
        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Divider', 'Divider', 'layout')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Tab', 'Tab', 'layout')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'List', 'List', 'layout')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Gallery', 'Gallery', 'content')");

        $this->execute("INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Container', 'Container', 'layout')");
        */
    }

    public function down()
    {
        $this->execute("DROP TABLE topcportlet");
        $this->execute("DROP TABLE topcblueprint");
        $this->execute("DROP TABLE topcpage");

        $this->execute("DELETE FROM tadminmenu WHERE cLinkname='OnPage Composer';");
    }
}
