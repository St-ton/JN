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
            VALUES (0, 'Heading', 'Heading', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Image', 'Image', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Row', 'Row', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Button', 'Button', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Bilder-Slider', 'ImageSlider', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Artikel-Slider', 'ProductSlider', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Product-Stream', 'ProductStream', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Banner', 'Banner', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Text', 'Text', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Video', 'Video', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Panel', 'Panel', 'Elements')");
    }

    public function down()
    {
        $this->execute("DROP TABLE tcmsportlet");
        $this->execute("DROP TABLE tcmstemplate");
        $this->execute("DROP TABLE tcmspage");
    }
}
