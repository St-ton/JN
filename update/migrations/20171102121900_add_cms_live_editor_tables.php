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

        $this->execute("CREATE TABLE tcmspage (
            kPage INT AUTO_INCREMENT PRIMARY KEY,
            cKey VARCHAR(255) NOT NULL,
            kKey INT NOT NULL,
            kSprache INT NOT NULL,
            cJson LONGTEXT
        )");

        $this->execute("CREATE TABLE tcmspagecontent (
            kPageContent INT AUTO_INCREMENT PRIMARY KEY,
            kPage INT NOT NULL,
            cAreaId VARCHAR(255) NOT NULL,
            cHtml LONGTEXT
        )");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Heading', 'Heading', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Image', 'Image', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Row', 'Row', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'Button', 'Button', 'Elements')");

        $this->execute("INSERT INTO tcmsportlet (kPlugin, cTitle, cClass, cGroup)
            VALUES (0, 'ImageSlider', 'ImageSlider', 'Elements')");
    }

    public function down()
    {
        $this->execute("DROP TABLE tcmsportlet");
        $this->execute("DROP TABLE tcmspage");
        $this->execute("DROP TABLE tcmspagecontent");
    }
}
