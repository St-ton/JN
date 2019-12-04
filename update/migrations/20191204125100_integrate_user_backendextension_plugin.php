<?php

/**
 * Integrate user backendextension plugin in shop core
 *
 * @author mh
 * @created Wed, 04 Dec 2019 12:51:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191204125100
 */
class Migration_20191204125100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Integrate user backendextension plugin in shop core';

    public function up()
    {
        $this->execute("
          UPDATE `tadminloginattribut`
            SET cAttribValue = 'N'
            WHERE cName = 'useAvatar'
              AND cAttribValue = 'G'
        ");
        $this->execute("DELETE FROM `tadminloginattribut` WHERE cName = 'useGPlus' OR cName = 'useGravatarEmail'");
    }

    public function down()
    {

    }
}
