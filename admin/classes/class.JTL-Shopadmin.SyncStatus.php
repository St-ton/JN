<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SyncStatus
 */
class SyncStatus
{
    use SingletonTrait;

    /**
     * Initialize Syncstatus
     */
    protected function init()
    {
    }

    /**
     * @param string $syncfile
     * @return false|stdClass
     */
    protected function getSync($syncfile)
    {
        $result = Shop::DB()->select('tsyncstatus', 'syncfile', $syncfile);

        return $result === 0 ? false : $result;
    }

    /**
     * @param string $syncfile
     * @return stdClass
     */
    public function run($syncfile)
    {
        $sync = $this->getSync($syncfile);

        if (!isset($sync) || $sync === false) {
            $sync = (object)[
                'syncfile'   => $syncfile,
                'started'    => date('Y-m-d H:i:s'),
                'counter'    => 1,
                'lastupdate' => date('Y-m-d H:i:s'),
                'finished'   => 0,
            ];

            $sync->id = Shop::DB()->insert('tsyncstatus', $sync);
        } else {
            $sync->counter++;
            $sync->lastupdate = date('Y-m-d H:i:s');

            Shop::DB()->update('tsyncstatus', 'id', $sync->id, $sync);
        }

        return $sync;
    }

    /**
     * @param string|null $syncfile
     * @return void
     */
    public function finish($syncfile = null)
    {
        $keys    = ['finished'];
        $keyVals = [0];

        if (!empty($syncfile)) {
            $keys[]    = 'syncfile';
            $keyVals[] = $syncfile;
        }
        Shop::DB()->update('tsyncstatus', $keys, $keyVals, (object)['finished' => 1]);
        $keyVals[0]++;
        $syncs = Shop::DB()->selectArray('tsyncstatus', $keys, $keyVals, '*', 'lastupdate ASC');

        if (is_array($syncs)) {
            foreach ($syncs as $sync) {
                $fileName   = PFAD_ROOT . PFAD_DBES . $sync->syncfile . '.inc.php';
                $finishProc = $sync->syncfile . '_Finish';

                if (is_file($fileName)) {
                    require_once $fileName;

                    if (function_exists($finishProc)) {
                        $finishProc();
                    }
                }
            }
        }

        Shop::DB()->delete('tsyncstatus', $keys, $keyVals);
    }
}
