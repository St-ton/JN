<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Blueprints
 * @package JTL\Plugin\Admin\Installation\Items
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
            ? \PFAD_ROOT . \PLUGIN_DIR .
            $this->plugin->cVerzeichnis . '/' .
            \PFAD_PLUGIN_BLUEPRINTS
            : \PFAD_ROOT . \PFAD_PLUGIN .
            $this->plugin->cVerzeichnis . '/' . \PFAD_PLUGIN_VERSION .
            $this->plugin->nVersion . '/' . \PFAD_PLUGIN_BLUEPRINTS;
        foreach ($this->getNode() as $i => $blueprint) {
            $i = (string)$i;
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
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
