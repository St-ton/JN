<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191014113600
 */
class Migration_20191018152300 extends Migration implements IMigration
{
    protected $author      = 'Danny Raufeisen';
    protected $description = 'Convert page IDs in topcpage to new json format';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $pages = $this->fetchAll('SELECT * FROM topcpage');

        foreach ($pages as $page) {
            $idObj       = new stdClass();
            $fields      = explode(';', $page->cPageId);
            $numfields   = count($fields);
            $first       = explode(':', $fields[0]);
            $idObj->type = $first[0];
            $idObj->id   = $first[1];

            if ($idObj->type !== 'search' && $idObj->type !== 'other') {
                $idObj->id = (int)$idObj->id;
            }

            for ($i = 1; $i < $numfields; $i++) {
                $field = explode(':', $fields[$i]);
                $key   = $field[0];
                $value = $field[1];

                if ($key === 'lang' || $key === 'manufacturerFilter') {
                    $value = (int)$value;
                }

                $idObj->{$key} = $value;
            }

            $jsonId = json_encode($idObj);
            $page->cPageId = $jsonId;

            $this->execute("UPDATE topcpage SET cPageId = '{$page->cPageId}' WHERE kPage = {$page->kPage}");
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $pages = $this->fetchAll('SELECT * FROM topcpage');

        foreach ($pages as $page) {
            $json      = \json_decode($page->cPageId, true);
            $type      = $json['type'];
            $id        = $json['id'];
            $oldPageId = $type . ':' . $id;

            foreach ($json as $key => $val) {
                if ($key !== 'type' && $key !== 'id') {
                    $oldPageId .= ';' . $key . ':' . $val;
                }
            }

            $page->cPageId = $oldPageId;

            $this->execute("UPDATE topcpage SET cPageId = '{$page->cPageId}' WHERE kPage = {$page->kPage}");
        }
    }
}
