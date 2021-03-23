<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210323112600
 */
class Migration_20210323112600 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Add line ending config for exports';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'exportformate_line_ending',
            'N',
            CONF_EXPORTFORMATE,
            'Line ending',
            'selectbox',
            170,
            (object)[
                'cBeschreibung' => 'Line ending',
                'inputOptions'  => [
                    'LF'   => 'LF',
                    'CRLF' => 'CRLF',
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('exportformate_line_ending');
    }
}
