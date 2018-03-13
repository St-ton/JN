<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

class Blueprint implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $kBlueprint = 0;

    /**
     * @var string
     */
    protected $cName = 'New Blueprint';

    /**
     * @var string
     */
    protected $cJson = '{}';

    /**
     * @var PortletInstance
     */
    protected $instance = null;

    /**
     * Blueprint constructor.
     * @param int $id
     * @throws \Exception
     */
    public function __construct($id = 0)
    {
        if ($id > 0) {
            $blueprintDB = \Shop::DB()->select('topcblueprint', 'kBlueprint', $id);

            $this
                ->setId($blueprintDB->kBlueprint)
                ->setName($blueprintDB->cName)
                ->setJson($blueprintDB->cJson);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->kBlueprint;
    }

    /**
     * @param int $kBlueprint
     * @return $this;
     */
    public function setId($kBlueprint)
    {
        $this->kBlueprint = $kBlueprint;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @param string $cName
     * @return $this;
     */
    public function setName($cName)
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @return string
     */
    public function getJson()
    {
        if ($this->getInstance() !== null) {
            $this->cJson = json_encode($this->getInstance());
        }

        return $this->cJson;
    }

    /**
     * @param string $cJson
     * @return $this;
     * @throws \Exception
     */
    public function setJson($cJson)
    {
        $this->cJson  = $cJson;
        $instanceData = json_decode($cJson, true);
        $instance     = new PortletInstance($instanceData);
        $this->setInstance($instance);

        return $this;
    }

    /**
     * @param array $instanceData
     * @return $this
     * @throws \Exception
     */
    public function setData($instanceData)
    {
        $instance    = new PortletInstance($instanceData);
        $this->cJson = json_encode($instance);
        $this->setInstance($instance);

        return $this;
    }

    /**
     * @return PortletInstance
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param PortletInstance $instance
     * @return $this;
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * @return bool
     */
    public function existsInDB()
    {
        return is_object(\Shop::DB()->select('topcblueprint', 'kBlueprint', $this->getId()));
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        if ($this->cName === '') {
            throw new \Exception('The OPC blueprint data to be saved is incomplete or invalid.');
        }

        $blueprintDB = (object)$this->jsonSerialize();

        if ($this->existsInDB()) {
            if (\Shop::DB()->update('topcblueprint', 'kBlueprint', $this->getId(), $blueprintDB) === -1) {
                throw new \Exception('The OPC blueprint could not be updated in the DB.');
            }
        } else {
            $key = \Shop::DB()->insert('topcblueprint', $blueprintDB);

            if ($key === 0) {
                throw new \Exception('The OPC blueprint could not be inserted into the DB.');
            }

            $this->setId($key);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        \Shop::DB()->delete('topcblueprint', 'kBlueprint', $this->getId());

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'kBlueprint' => $this->getId(),
            'cName'      => $this->getName(),
            'cJson'      => $this->getJson(),
        ];
    }
}
