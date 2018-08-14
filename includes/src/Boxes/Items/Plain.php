<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;


/**
 * Class CompareList
 * @package Boxes
 */
final class Plain extends AbstractBox
{
    /**
     * Plain constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->supportsRevisions = true;
    }

    /**
     * @inheritdoc
     */
    public function map(array $boxData)
    {
        parent::map($boxData);
        $this->setShow(!empty($this->getContent(\Shop::getLanguageID())));
    }
}
