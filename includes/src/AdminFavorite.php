<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class AdminFavorite
 */
class AdminFavorite
{
    /**
     * @var int
     */
    public $kAdminfav;

    /**
     * @var int
     */
    public $kAdminlogin;

    /**
     * @var string
     */
    public $cTitel;

    /**
     * @var string
     */
    public $cUrl;

    /**
     * @var int
     */
    public $nSort;

    /**
     * Konstruktor
     *
     * @param int $kAdminfav
     */
    public function __construct(int $kAdminfav = 0)
    {
        if ($kAdminfav > 0) {
            $this->loadFromDB($kAdminfav);
        }
    }

    /**
     * @param int $kAdminfav
     * @return $this
     */
    public function loadFromDB(int $kAdminfav): self
    {
        $obj = Shop::Container()->getDB()->select('tadminfavs', 'kAdminfav', $kAdminfav);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        executeHook(HOOK_ATTRIBUT_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = ObjectHelper::copyMembers($this);
        unset($obj->kAdminfav);

        return Shop::Container()->getDB()->insert('tadminfavs', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = ObjectHelper::copyMembers($this);

        return Shop::Container()->getDB()->update('tadminfavs', 'kAdminfav', $obj->kAdminfav, $obj);
    }

    /**
     * @param int $kAdminlogin
     * @return array
     */
    public static function fetchAll(int $kAdminlogin): array
    {
        $favs = Shop::Container()->getDB()->selectAll(
            'tadminfavs',
            'kAdminlogin',
            $kAdminlogin,
            'kAdminfav, cTitel, cUrl',
            'nSort ASC'
        );

        $favs = is_array($favs) ? $favs : [];

        foreach ($favs as &$fav) {
            $fav->bExtern = true;
            $fav->cAbsUrl = $fav->cUrl;
            if (strpos($fav->cUrl, 'http') !== 0) {
                $fav->bExtern = false;
                $fav->cAbsUrl = Shop::getURL() . '/' . $fav->cUrl;
            }
        }

        return $favs;
    }

    /**
     * @param int    $id
     * @param string $title
     * @param string $url
     * @param int    $sort
     * @return bool
     */
    public static function add(int $id, $title, $url, int $sort = -1): bool
    {
        $urlHelper = new UrlHelper($url);
        $url       = str_replace(
            [Shop::getURL(), Shop::getURL(true)],
            '',
            $urlHelper->normalize()
        );

        $url = strip_tags($url);
        $url = ltrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if ($sort < 0) {
            $sort = count(static::fetchAll($id));
        }

        $item = (object)[
            'kAdminlogin' => $id,
            'cTitel'      => $title,
            'cUrl'        => $url,
            'nSort'       => $sort
        ];

        if ($id > 0 && strlen($item->cTitel) > 0 && strlen($item->cUrl) > 0) {
            Shop::Container()->getDB()->insertRow('tadminfavs', $item);

            return true;
        }

        return false;
    }

    /**
     * @param int $id
     * @param int $kAdminfav
     */
    public static function remove($id, int $kAdminfav = 0)
    {
        if ($kAdminfav > 0) {
            Shop::Container()->getDB()->delete('tadminfavs', ['kAdminfav', 'kAdminlogin'], [$kAdminfav, $id]);
        } else {
            Shop::Container()->getDB()->delete('tadminfavs', 'kAdminlogin', $id);
        }
    }
}
