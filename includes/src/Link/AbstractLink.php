<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

/**
 * Class AbstractLink
 * @package Link
 */
abstract class AbstractLink implements LinkInterface
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'cNoFollow'          => 'NoFollowCompat',
        'cURL'               => 'URL',
        'cURLFull'           => 'URL',
        'cURLFullSSL'        => 'URL',
        'cLocalizedName'     => 'Name',
        'cLocalizedTitle'    => 'Title',
        'kLink'              => 'ID',
        'kVaterLink'         => 'Parent',
        'kLinkgruppe'        => 'LinkGroupID',
        'cKundengruppen'     => 'CustomerGroups',
        'cSichtbarNachLogin' => 'VisibleLoggedInOnlyCompat',
        'nSort'              => 'Sort',
        'bSSL'               => 'SSL',
        'bIsFluid'           => 'IsFluid',
        'cIdentifier'        => 'Identifier',
        'bIsActive'          => 'IsActive',
        'aktiv'              => 'IsActive',
        'cISO'               => 'LanguageCode',
        'cLocalizedSeo'      => 'URL',
        'cSeo'               => 'URL',
        'nHTTPRedirectCode'  => 'RedirectCode',
        'nPluginStatus'      => 'PluginEnabled',
        'Sprache'            => 'LangCompat',
        'cContent'           => 'Content',
        'cTitle'             => 'Title',
        'cMetaTitle'         => 'MetaTitle',
        'cMetaKeywords'      => 'MetaKeyword',
        'cMetaDescription'   => 'MetaDescription',
        'nLinkart'   => 'LinkType',
    ];

    /**
     * @return $this
     */
    public function getLangCompat(): LinkInterface
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getNoFollowCompat(): string
    {
        return $this->getNoFollow() === true ? 'Y' : 'N';
    }

    /**
     * @param string|bool $value
     */
    public function setNoFollowCompat($value)
    {
        $this->setNoFollow($value === 'Y' || $value === true);
    }

    /**
     * @return string
     */
    public function getVisibleLoggedInOnlyCompat(): string
    {
        return $this->getVisibleLoggedInOnly() === true ? 'Y' : 'N';
    }

    /**
     * @param string|bool $value
     */
    public function setVisibleLoggedInOnlyCompat($value)
    {
        $this->setVisibleLoggedInOnly($value === 'Y' || $value === true);
    }
}
