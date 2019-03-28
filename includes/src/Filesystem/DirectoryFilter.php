<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filesystem;

use RecursiveFilterIterator;

class DirectoryFilter extends RecursiveFilterIterator
{
    protected $exclude;

    public function __construct($iterator, array $exclude)
    {
        parent::__construct($iterator);
        $this->exclude = $exclude;
    }

    public function accept()
    {
        return !($this->isDir() && in_array($this->getFilename(), $this->exclude));
    }

    public function getChildren()
    {
        return new DirectoryFilter($this->getInnerIterator()->getChildren(), $this->exclude);
    }
}
