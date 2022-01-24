<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\Backend\Settings\Item;
use JTL\DB\SqlObject;
use JTL\Helpers\Text;
use stdClass;

/**
 * Class PluginPaymentMethod
 * @package Backend\Settings
 */
class PluginPaymentMethod extends Base
{
    /**
     * @inheritdoc
     */
    public function load(?SqlObject $sql = null): void
    {
        if ($sql === null) {
            $sql = new SqlObject();
            $sql->setWhere(' 1 = 1');
        }

        $data = $this->db->getObjects(
            'SELECT *, kPluginEinstellungenConf AS kEinstellungenConf,
                kPluginEinstellungenConf AS kEinstellungenSektion
                FROM tplugineinstellungenconf
                WHERE ' . $sql->getWhere() . '
                 ORDER BY nSort',
            $sql->getParams()
        );

        $configItems = [];
        foreach ($data as $item) {
            if ($item->cConf === 'N' && ($item->cInputTyp === '' || $item->cInputTyp === null)) {
                $config = new Subsection();
            } else {
                $config = new Item();
            }
            $config->parseFromDB($item);
            if (\in_array($config->getInputType(), ['selectbox', 'listbox'], true)) {
                $setValues = $this->db->selectAll(
                    'tplugineinstellungenconfwerte',
                    'kPluginEinstellungenConf',
                    $config->getID(),
                    '*',
                    'nSort'
                );
                foreach ($setValues as $confKey) {
                    $confKey->cName = \__($confKey->cName);
                }
                $config->setValues($setValues);
            }
            $setValue = $this->db->select(
                'tplugineinstellungen',
                'kPlugin',
                $config->getPluginID(),
                'cName',
                $config->getValueName()
            );
            $config->setName(\__($config->getName()));
            $config->setSetValue(isset($setValue->cWert)
                ? Text::htmlentities($setValue->cWert)
                : null);

            $configItems[] = $config;
            $this->items[] = $config;
        }
        $this->subsections = [];
        $currentSubsection = null;
        foreach ($configItems as $item) {
            if (\get_class($item) === Subsection::class) {
                if ($currentSubsection !== null) {
                    $this->subsections[] = $currentSubsection;
                }
                $currentSubsection = $item;
            } else {
                if ($currentSubsection === null) {
                    $currentSubsection = new Subsection();
                }
                $currentSubsection->addItem($item);
            }
        }
        $this->subsections[] = $currentSubsection;
        $this->subsections   = \array_filter($this->subsections);
    }

    /**
     * @inheritdoc
     */
    public function update(array $data, bool $filter = true, array $tags = [\CACHING_GROUP_OPTION]): array
    {
        $unfiltered = $data;
        if ($filter === true) {
            $data = Text::filterXSS($data);
        }
        $kPlugin = $data['kPlugin'];
        $updated = [];
        if ($this->loaded === false) {
            $this->load();
        }
        foreach ($this->getItems() as $sectionData) {
            $id = $sectionData->getValueName();
            if (!isset($data[$id])) {
                continue;
            }
            $aktWert          = new stdClass();
            $aktWert->kPlugin = $kPlugin;
            $aktWert->cName   = $id;
            $aktWert->cWert   = $data[$id];

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
                'tplugineinstellungen',
                ['kPlugin', 'cName'],
                [$kPlugin, $id]
            );
            $this->db->insert('tplugineinstellungen', $aktWert);
            $updated[] = ['id' => $id, 'value' => $data[$id]];
        }

        return $updated;
    }
}
