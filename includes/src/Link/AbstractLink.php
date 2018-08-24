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
        'cLocalizedName'     => 'NamesCompat',
        'cLocalizedTitle'    => 'Title',
        'kLink'              => 'ID',
        'kSprache'           => 'LanguageID',
        'cName'              => 'Name',
        'kPlugin'            => 'PluginID',
        'kVaterLink'         => 'Parent',
        'kLinkgruppe'        => 'LinkGroupID',
        'cKundengruppen'     => 'CustomerGroupsCompat',
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
        'cDruckButton'       => 'PrintButtonCompat',
        'nLinkart'           => 'LinkType',
        'level'              => 'Level',
    ];

    /**
     * @param string|array $ssk
     * @return array
     */
    protected static function parseSSKAdvanced($ssk): array
    {
        return \is_string($ssk) && \strtolower($ssk) !== 'null'
            ? \array_map('\intval', \array_map('\trim', \array_filter(\explode(';', $ssk))))
            : [];
    }

    /**
     * @return $this
     */
    public function getLangCompat(): LinkInterface
    {
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomerGroupsCompat()
    {
        $groups = $this->getCustomerGroups();

        return \is_array($groups) && \count($groups) > 0
            ? \implode(';', $groups) . ';'
            : null;
    }

    /**
     * @param string|array $value
     */
    public function setCustomerGroupsCompat($value)
    {
        $this->setCustomerGroups(!\is_array($value) ? self::parseSSKAdvanced($value) : $value);
    }

    /**
     * @return string
     */
    public function getPrintButtonCompat(): string
    {
        return $this->hasPrintButton() === true ? 'Y' : 'N';
    }

    /**
     * @param string|bool $value
     */
    public function setPrintButtonCompat($value)
    {
        $this->setPrintButton($value === 'Y' || $value === true);
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

    /**
     * @return array
     */
    public function getNamesCompat(): array
    {
        $byCode = [];
        $languages = \Sprache::getAllLanguages(1);
        foreach ($this->getNames() as $langID => $name) {
            $byCode[$languages[$langID]->cISO] = $name;
        }

        return $byCode;
    }
}
