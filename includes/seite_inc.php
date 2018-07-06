<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibStartBoxen()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMSHelper::getHomeBoxes();
}

/**
 * @param array $conf
 * @return array|mixed
 * @deprecated since 5.0.0
 */
function gibNews($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMSHelper::getHomeNews($conf);
}

/**
 * @param array $search
 * @param array $conf
 * @return null|stdClass
 * @deprecated since 5.0.0
 */
function gibNextBoxPrio($search, $conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return null;
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibLivesucheTop($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMSHelper::getLiveSearchTop($conf);
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibLivesucheLast($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMSHelper::getLiveSearchLast($conf);
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibTagging($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMSHelper::getTagging($conf);
}

/**
 * @return mixed
 * @deprecated since 5.0.0
 */
function gibNewsletterHistory()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMSHelper::getNewsletterHistory();
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibGratisGeschenkArtikel($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return CMSHelper::getFreeGifts($conf);
}

/**
 * @param array $Einstellungen
 * @return null
 * @deprecated since 5.0.0
 */
function gibAuswahlAssistentFragen($Einstellungen)
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not do anything useful.', E_USER_DEPRECATED);
    return null;
}

/**
 * @return KategorieListe
 * @deprecated since 5.0.0
 */
function gibSitemapKategorien()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $oKategorieliste           = new KategorieListe();
    $oKategorieliste->elemente = KategorieHelper::getInstance()->combinedGetAll();

    return $oKategorieliste;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibSitemapGlobaleMerkmale()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sm = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), Shop::getConfig([CONF_SITEMAP]));

    return $sm->getGlobalAttributes();
}

/**
 * @param object $oMerkmal
 * @deprecated since 5.0.0
 */
function verarbeiteMerkmalBild(&$oMerkmal)
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not do anything useful.', E_USER_DEPRECATED);
}

/**
 * @param object $oMerkmalWert
 * @deprecated since 5.0.0
 */
function verarbeiteMerkmalWertBild(&$oMerkmalWert)
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not do anything useful.', E_USER_DEPRECATED);
}

/**
 * @param array $conf
 * @return array
 * @deprecated since 5.0.0
 */
function gibBoxNews($conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not return anything useful.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibSitemapNews()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sm = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), Shop::getConfig([CONF_NEWS]));

    return $sm->getNews();
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function gibNewsKategorie()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $sm = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), Shop::getConfig([CONF_SITEMAP]));

    return $sm->getNewsCategories();
}

/**
 * @param array $conf
 * @param JTLSmarty $smarty
 * @deprecated since 5.0.0
 */
function gibSeiteSitemap($conf, $smarty)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::setPageType(PAGE_SITEMAP);
    $sm = new \JTL\Sitemap(Shop::Container()->getDB(), Shop::Container()->getCache(), $conf);
    $sm->assignData($smarty);
}

/**
 * @param int $nLinkart
 * @deprecated since 5.0.0
 */
function pruefeSpezialseite(int $nLinkart)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $specialPages = Shop::Container()->getLinkService()->getLinkGroupByName('specialpages');
    if ($nLinkart > 0 && $specialPages !== null) {
        $res = $specialPages->getLinks()->first(function (\Link\LinkInterface $l) use ($nLinkart) {
            return $l->getLinkType() === $nLinkart;
        });
        /** @var \Link\LinkInterface $res */
        if ($res !== null && $res->getFileName() !== '') {
            header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute($res->getFileName()));
            exit();
        }
    }
}
