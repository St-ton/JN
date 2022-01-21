<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\DB\SqlObject;

/**
 * Class Cache
 * @package Backend\Settings
 */
class Cache extends Base
{
    /**
     * @inheritdoc
     */
    public function generateConfigData(SqlObject $sql = null): array
    {
        if ($sql === null) {
            $sql = new SqlObject();
            $sql->setWhere('ec.kEinstellungenSektion = :sid
                AND ec.nModul = 0
                AND ec.nStandardanzeigen IN (0, 1, 2)');
            $sql->addParam('sid', $this->id);
        }
        return parent::generateConfigData($sql);
    }

    /**
     * @inheritdoc
     */
    public function getConfigData(): array
    {
        $data = $this->generateConfigData();
        foreach ($data as $i => $item) {
            if ($item->getValueName() === 'caching_types_disabled') {
                unset($data[$i]);
                break;
            }
        }

        return $data;
    }
}
