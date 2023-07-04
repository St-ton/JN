<?php

namespace JTL;

use JTL\DB\DbInterface;
use JTL\News\Author;

/**
 * Class ContentAuthor
 * @package JTL
 * @deprecated since 5.2.0
 */
class ContentAuthor
{
    /**
     * @param DbInterface|null $db
     * @return Author
     */
    public static function getInstance(?DbInterface $db = null): Author
    {
        \trigger_error(__CLASS__ . ' is deprecated. Use JTL\News\Author instead.', \E_USER_DEPRECATED);
        return Author::getInstance($db ?? Shop::Container()->getDB());
    }
}
