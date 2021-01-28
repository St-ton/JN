<?php

/**
 * @author mh
 * @created Wed, 08 June 2020 09:49:00 +0200
 */

/**
 * Class Migration_20200708094900
 */
class Migration_20200708094900 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove maintenance hint';

    /**
     * @return bool|void
     */
    public function up()
    {
        $this->removeConfig('wartungsmodus_hinweis');
    }

    /**
     * @return bool|void
     * @throws Exception
     */
    public function down()
    {
        $this->setConfig(
            'wartungsmodus_hinweis',
            'Dieser Shop befindet sich im Wartungsmodus.',
            \CONF_GLOBAL,
            'Wartungsmodus Hinweis',
            'text',
            1020,
            (object)[
                'cBeschreibung' => 'Dieser Hinweis wird Besuchern angezeigt, wenn der Shop im Wartungsmodus ist. ' .
                    'Achtung: Im Evo-Template steuern Sie diesen Text über die Sprachvariable maintenanceModeActive.',
            ]
        );
    }
}
