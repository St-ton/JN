<?php
/**
 * Add CMS Live-editor tables
 *
 * @author Marco Stickel
 */

class Migration_20171102121900 extends Migration implements IMigration
{
    protected $author      = 'Marco Stickel';
    protected $description = 'Add tables for the CMS Live-Editor';

    public function up()
    {
        $this->execute("CREATE TABLE tcmsportlet (
            kPortlet INT AUTO_INCREMENT PRIMARY KEY,
            kPlugin INT NOT NULL,
            cTitle VARCHAR(255) NOT NULL,
            cClass VARCHAR(255) NOT NULL,
            cGroup VARCHAR(255) NOT NULL,
            bActive TINYINT NOT NULL DEFAULT 1
        )");

        $this->execute("CREATE TABLE tcmstemplate (
            kTemplate INT AUTO_INCREMENT PRIMARY KEY,
            cName VARCHAR(255) NOT NULL,
            cJson LONGTEXT
        )");

        $this->execute("CREATE TABLE tcmspage (
            kPage INT AUTO_INCREMENT PRIMARY KEY,
            cIdHash CHAR(32) NOT NULL,
            cPageURL VARCHAR(255) NOT NULL,
            cJson LONGTEXT NOT NULL,
            dLastModified DATETIME,
            cLockedBy VARCHAR(255),
            dLockedAt DATETIME
        )");

        $this->execute("INSERT INTO tadminmenu (kAdminmenueGruppe, cModulId, cLinkname, cURL, cRecht, nSort)
            VALUES ('4', 'core_jtl', 'CMS Live Editor', 'cms-live-editor-backend.php', 'CONTENT_PAGE_VIEW', '115');");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Heading', 'Heading', 'content')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Image', 'Image', 'content')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Row', 'Row', 'layout')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Button', 'Button', 'content')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Bilder-Slider', 'ImageSlider', 'content')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Artikel-Slider', 'ProductSlider', 'content')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Product-Stream', 'ProductStream', 'content')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Banner', 'Banner', 'content')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Text', 'Text', 'content')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Video', 'Video', 'content')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Panel', 'Panel', 'layout')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Accordion', 'Accordion', 'layout')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Divider', 'Divider', 'layout')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Tabs', 'Tabs', 'layout')");
    }

    public function down()
    {
        $this->execute("DROP TABLE tcmsportlet");
        $this->execute("DROP TABLE tcmstemplate");
        $this->execute("DROP TABLE tcmspage");
    }
}
