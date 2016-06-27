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
     * @param string $realm
     * @param int $contentID
     * @param mixed int|null $authorID
     * @return mixed int|boolean
     */
    public function setAuthor($realm, $contentID, $authorID = null)
    {
        if (!isset($authorID) || (int)$authorID === 0) {
            $account = $GLOBALS['oAccount']->account();

            if ($account !== false) {
                $authorID = $account->kAdminlogin;
            }
        }

        $authorID = (int)$authorID;

        if ($authorID > 0) {
            return Shop::DB()->query(
                "INSERT INTO tcontentauthor (cRealm, kAdminlogin, kContentId)
                VALUES('" . $realm . "', " . (int)$authorID . ", " . (int)$contentID . ")", 4
            );
        }

        return false;
    }

    /**
     * @param string $realm
     * @param int $contentID
     */
    public function clearAuthor($realm, $contentID)
    {
        Shop::DB()->query(
            "DELETE FROM tcontentauthor
                WHERE cRealm = '" . $realm . "'
                    AND kContentId = " . (int)$contentID, 4
        );
    }

    /**
     * @param string $realm
     * @param int $contentID
     * @return object
     */
    public function getAuthor($realm, $contentID)
    {
        $author  = Shop::DB()->select('tcontentauthor', 'cRealm', $realm, 'kContentId', $contentID);
        $attribs = Shop::DB()->query(
            "SELECT tadminloginattribut.kAttribut, tadminloginattribut.cName, tadminloginattribut.cAttribValue, tadminloginattribut.cAttribText
                FROM tadminloginattribut
                WHERE tadminloginattribut.kAdminlogin = " . (int)$author->kAdminlogin, 2
        );
        $author->extAttribs = array();
        foreach ($attribs as $attrib) {
            $author->extAttribs[$attrib->cName] = $attrib;
        }

        return $author;
    }

    /**
     * @param array|null $adminRights
     * @return array of objects
     */
    public function getPossibleAuthors(array $adminRights = null)
    {
        if (isset($adminRights) && is_array($adminRights)) {
            $filter = "AND (tadminlogin.kAdminlogingruppe = 1
                        OR EXISTS (
                            SELECT 1 
                            FROM tadminrechtegruppe
                            WHERE tadminrechtegruppe.kAdminlogingruppe = tadminlogin.kAdminlogingruppe
                                AND tadminrechtegruppe.cRecht IN ('" . implode("', '", $adminRights) . "')
                        ))";
        } else {
            $filter = '';
        }

        return Shop::DB()->query(
            "SELECT tadminlogin.kAdminlogin, tadminlogin.cLogin, tadminlogin.cName, tadminlogin.cMail 
                FROM tadminlogin
                WHERE tadminlogin.bAktiv = 1
                    AND COALESCE(tadminlogin.dGueltigBis, NOW()) >= NOW()
                    AND tadminlogin.kAdminlogin > 1
                    $filter", 2
        );
    }
}
