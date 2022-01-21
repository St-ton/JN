<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\Backend\Settings\Item;
use JTL\DB\SqlObject;
use JTL\Helpers\Text;
use stdClass;

/**
 * Class PaymentMethod
 * @package Backend\Settings
 */
class PaymentMethod extends Base
{
    /**
     * @inheritdoc
     */
    public function generateConfigData(SqlObject $sql = null): array
    {
        if ($sql === null) {
            $sql = new SqlObject();
            $sql->setWhere(' 1 = 1');
        }

        $data             = $this->db->getObjects(
            'SELECT *
                FROM teinstellungenconf
                WHERE ' . $sql->getWhere() . '
                 ORDER BY nSort',
            $sql->getParams()
        );
        $this->configData = [];
        foreach ($data as $item) {
            $config = new Item();
            $config->parseFromDB($item);
            $this->configData[] = $config;
        }

        return $this->configData;
    }

    /**
     * @inheritdoc
     */
    public function update(array $data, bool $filter = true): array
    {
        $unfiltered = $data;
        if ($filter === true) {
            $data = Text::filterXSS($data);
        }
        $updated = [];
        foreach ($this->getConfigData() as $sectionData) {
            $id = $sectionData->getValueName();
            if (!isset($data[$id])) {
                continue;
            }
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = $data[$id];
            $aktWert->cName                 = $id;
            $aktWert->kEinstellungenSektion = \CONF_ZAHLUNGSARTEN;
            $aktWert->cModulId              = $data['cModulId'];

            switch ($sectionData->getInputType()) {
                case 'kommazahl':
                    $aktWert->cWert = (float)\str_replace(',', '.', $aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = \mb_substr($aktWert->cWert, 0, 255);
                    break;
                case 'pass':
                    $aktWert->cWert = $unfiltered[$id];
                    break;
                default:
                    break;
            }
            $this->db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [\CONF_ZAHLUNGSARTEN, $id]
            );
            $this->db->insert('teinstellungen', $aktWert);
            $updated[] = ['id' => $id, 'value' => $data[$id]];
        }

        return $updated;
    }
}
