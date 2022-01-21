<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\DB\SqlObject;

/**
 * Class Comparelist
 * @package Backend\Settings
 */
class Comparelist extends Base
{
    /**
     * @inheritdoc
     */
    public function generateConfigData(SqlObject $sql = null): array
    {
        if ($sql === null) {
            $sql = new SqlObject();
            $sql->setWhere('ec.kEinstellungenSektion = :sid
                OR ec.kEinstellungenConf IN (469, 470)');
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
