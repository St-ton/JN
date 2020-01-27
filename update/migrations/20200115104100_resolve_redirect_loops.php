<?php
/**
 * Resolve redirect loops
 */

/**
 * Class Migration_20200115104100
 */
class Migration_20200115104100 extends Migration implements IMigration
{
    protected $author      = 'Danny Raufeisen';
    protected $description = 'Resolve redirect loops';

    public function up()
    {
        $urlParts = parse_url(Shop::getURL());
        $shopPath = rtrim(isset($urlParts['path']) ? $urlParts['path'] : '', '/') . '/';

        $entries = $this->fetchAll(
            "SELECT r2.kRedirect
            FROM tredirect AS r1
            JOIN tredirect AS r2
            ON r2.cFromUrl = r1.cToUrl
            OR r2.cFromUrl = CONCAT('{$shopPath}', r1.cToUrl)"
        );

        foreach ($entries as $entry) {
            $kRedirect = $entry->kRedirect;
            $chain     = [];

            while ($kRedirect) {
                $chain[] = $kRedirect;

                $kRedirect = $this->fetchOne(
                    "SELECT r2.kRedirect
                        FROM tredirect AS r1
                        JOIN tredirect AS r2
                        ON r1.kRedirect = {$kRedirect}
                        AND (r2.cFromUrl = r1.cToUrl
                            OR r2.cFromUrl = CONCAT('{$shopPath}', r1.cToUrl)
                        )"
                )->kRedirect;

                if (in_array($kRedirect, $chain)) {
                    $this->execute("DELETE FROM tredirect WHERE kRedirect = {$kRedirect}");
                    break;
                }
            }
        }
    }

    public function down()
    {
    }
}
