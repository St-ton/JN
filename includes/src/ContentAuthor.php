<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ContentAuthor
 */
class ContentAuthor
{
    use SingletonTrait;

    /**
     * @param string   $realm
     * @param int      $contentID
     * @param int|null $authorID
     * @return int|boolean
     */
    public function setAuthor(string $realm, int $contentID, int $authorID = null)
    {
        if ($authorID === null || $authorID === 0) {
            $account = $GLOBALS['oAccount']->account();
            if ($account !== false) {
                $authorID = $account->kAdminlogin;
            }
        }
        if ($authorID > 0) {
            return Shop::Container()->getDB()->query(
                "INSERT INTO tcontentauthor (cRealm, kAdminlogin, kContentId)
                    VALUES('" . $realm . "', " . $authorID . ", " . $contentID . ")
                    ON DUPLICATE KEY UPDATE
                        kAdminlogin = " . $authorID,
                \DB\ReturnType::DEFAULT
            );
        }

        return false;
    }

    /**
     * @param string $realm
     * @param int    $contentID
     */
    public function clearAuthor($realm, $contentID)
    {
        Shop::Container()->getDB()->delete('tcontentauthor', ['cRealm', 'kContentId'], [$realm, (int)$contentID]);
    }

    /**
     * @param string  $realm
     * @param int     $contentID
     * @param boolean $activeOnly
     * @return object
     */
    public function getAuthor(string $realm, int $contentID, bool $activeOnly = false)
    {
        $filter = $activeOnly
            ? 'AND tadminlogin.bAktiv = 1
                    AND COALESCE(tadminlogin.dGueltigBis, NOW()) >= NOW()'
            : '';
        $author = Shop::Container()->getDB()->queryPrepared(
            'SELECT tcontentauthor.kContentAuthor, tcontentauthor.cRealm, 
                tcontentauthor.kAdminlogin, tcontentauthor.kContentId,
                tadminlogin.cName, tadminlogin.cMail
                FROM tcontentauthor
                INNER JOIN tadminlogin 
                    ON tadminlogin.kAdminlogin = tcontentauthor.kAdminlogin
                WHERE tcontentauthor.cRealm = :realm
                    AND tcontentauthor.kContentId = :contentid ' . $filter,
            ['realm' => $realm, 'contentid' => $contentID],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($author->kAdminlogin) && (int)$author->kAdminlogin > 0) {
            $attribs            = Shop::Container()->getDB()->query(
                'SELECT tadminloginattribut.kAttribut, tadminloginattribut.cName, 
                    tadminloginattribut.cAttribValue, tadminloginattribut.cAttribText
                    FROM tadminloginattribut
                    WHERE tadminloginattribut.kAdminlogin = ' . (int)$author->kAdminlogin,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $author->extAttribs = [];
            foreach ($attribs as $attrib) {
                $author->extAttribs[$attrib->cName] = $attrib;
            }
        }

        return $author;
    }

    /**
     * @param array|null $adminRights
     * @return array
     */
    public function getPossibleAuthors(array $adminRights = null): array
    {
        $filter = '';
        if ($adminRights !== null && is_array($adminRights)) {
            $filter = "AND (tadminlogin.kAdminlogingruppe = 1
                        OR EXISTS (
                            SELECT 1 
                            FROM tadminrechtegruppe
                            WHERE tadminrechtegruppe.kAdminlogingruppe = tadminlogin.kAdminlogingruppe
                                AND tadminrechtegruppe.cRecht IN ('" . implode("', '", $adminRights) . "')
                        ))";
        }

        return Shop::Container()->getDB()->query(
            "SELECT tadminlogin.kAdminlogin, tadminlogin.cLogin, tadminlogin.cName, tadminlogin.cMail 
                FROM tadminlogin
                WHERE tadminlogin.bAktiv = 1
                    AND COALESCE(tadminlogin.dGueltigBis, NOW()) >= NOW()
                    AND tadminlogin.kAdminlogin > 1
                    $filter",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }
}
