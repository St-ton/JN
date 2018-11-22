<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class Blueprints
 * @package Plugin\Admin\Installation\Items
 */
class Blueprints extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['Blueprints'][0]['Blueprint'])
        && \is_array($this->baseNode['Install'][0]['Blueprints'][0]['Blueprint'])
            ? $this->baseNode['Install'][0]['Blueprints'][0]['Blueprint']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $base = $this->plugin->bExtension === 1
            ? \PFAD_ROOT . \PFAD_EXTENSIONS .
            $this->plugin->cVerzeichnis . '/' .
            \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_BLUEPRINTS
            : \PFAD_ROOT . \PFAD_PLUGIN .
            $this->plugin->cVerzeichnis . '/' . \PFAD_PLUGIN_VERSION .
            $this->plugin->nVersion . '/' .
            \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_BLUEPRINTS;
        foreach ($this->getNode() as $u => $blueprint) {
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            $blueprintJson = \file_get_contents($base . $blueprint['JSONFile']);
            $blueprintData = \json_decode($blueprintJson, true);
            $instanceJson  = \json_encode($blueprintData['instance']);
            $blueprintObj  = (object)[
                'kPlugin' => $this->plugin->kPlugin,
                'cName'   => $blueprint['Name'],
                'cJson'   => $instanceJson,
            ];
            if (!$this->db->insert('topcblueprint', $blueprintObj)) {
                return InstallCode::SQL_CANNOT_SAVE_BLUEPRINT;
            }
        }

        return InstallCode::OK;
    }
}
