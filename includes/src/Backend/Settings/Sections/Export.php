<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\DB\SqlObject;
use JTL\Helpers\Text;
use stdClass;

/**
 * Class Export
 * @package Backend\Settings
 */
class Export extends Base
{
    /**
     * @inheritdoc
     */
    public function load(?SqlObject $sql = null): void
    {
        parent::load();
        if ($sql === null) {
            $sql = new SqlObject();
            $sql->setWhere(' 1 = 1');
        }
        $data = $this->db->getObjects(
            'SELECT *
                FROM texportformateinstellungen
                WHERE ' . $sql->getWhere(),
            $sql->getParams()
        );
        foreach ($this->getItems() as $config) {
            foreach ($data as $efSetting) {
                if ($efSetting->cName === $config->getValueName()) {
                    $config->setSetValue($efSetting->cWert);
                    break;
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function update(array $data, bool $filter = true, array $tags = [\CACHING_GROUP_OPTION]): array
    {
        if ($filter === true) {
            $data = Text::filterXSS($data);
        }
        $updated  = [];
        $exportID = (int)($data['exportID'] ?? 0);
        if ($exportID === 0) {
            return [];
        }
        $sql = new SqlObject();
        $sql->setWhere('kExportformat = :eid');
        $sql->addParam(':eid', $exportID);
        if ($this->loaded === false) {
            $this->load($sql);
        }
        $this->db->delete('texportformateinstellungen', 'kExportformat', $exportID);
        foreach ($this->getItems() as $item) {
            $id                 = $item->getValueName();
            $ins                = new stdClass();
            $ins->cWert         = $data[$id];
            $ins->cName         = $id;
            $ins->kExportformat = $exportID;
            switch ($item->getInputType()) {
                case 'kommazahl':
                    $ins->cWert = (float)$ins->cWert;
                    break;
                case 'zahl':
                case 'number':
                    $ins->cWert = (int)$ins->cWert;
                    break;
                case 'text':
                    $ins->cWert = \mb_substr($ins->cWert, 0, 255);
                    break;
            }
            if (!$this->validate($item, $data[$id])) {
                $this->updateErrors++;
                continue;
            }
            $this->db->insert('texportformateinstellungen', $ins);
            $updated[] = ['id' => $id, 'value' => $data[$id]];
        }

        return $updated;
    }
}
