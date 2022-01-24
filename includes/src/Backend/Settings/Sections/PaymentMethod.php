<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

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
    public function update(array $data, bool $filter = true, array $tags = [\CACHING_GROUP_OPTION]): array
    {
        $unfiltered = $data;
        if ($filter === true) {
            $data = Text::filterXSS($data);
        }
        $updated = [];
        if ($this->loaded === false) {
            $this->load();
        }
        foreach ($this->getItems() as $sectionData) {
            $id = $sectionData->getValueName();
            if (!isset($data[$id])) {
                continue;
            }
            $update                        = new stdClass();
            $update->cWert                 = $data[$id];
            $update->cName                 = $id;
            $update->kEinstellungenSektion = \CONF_ZAHLUNGSARTEN;
            $update->cModulId              = $data['cModulId'];

            switch ($sectionData->getInputType()) {
                case 'kommazahl':
                    $update->cWert = (float)\str_replace(',', '.', $update->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $update->cWert = (int)$update->cWert;
                    break;
                case 'text':
                    $update->cWert = \mb_substr($update->cWert, 0, 255);
                    break;
                case 'pass':
                    $update->cWert = $unfiltered[$id];
                    break;
                default:
                    break;
            }
            $this->db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [\CONF_ZAHLUNGSARTEN, $id]
            );
            $this->db->insert('teinstellungen', $update);
            $updated[] = ['id' => $id, 'value' => $data[$id]];
        }

        return $updated;
    }
}
