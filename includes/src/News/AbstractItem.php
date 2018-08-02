<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;

/**
 * Class AbstractItem
 * @package News
 */
abstract class AbstractItem implements ItemInterFace
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'kNews'                => 'ID',
        'kSprache'             => 'LanguageID',
        'cKundengruppe'        => 'CustomerGroupsCompat',
        'cBetreff'             => 'Title',
        'cText'                => 'Content',
        'cVorschauText'        => 'Preview',
        'cPreviewImage'        => 'PreviewImage',
        'cMetaTitle'           => 'MetaTitle',
        'cMetaDescription'     => 'MetaDescription',
        'cMetaKeywords'        => 'MetaKeyword',
        'cISO'                 => 'LanguageCode',
        'nAktiv'               => 'IsActive',
        'cSeo'                 => 'SEO',
        'cURL'                 => 'URL',
        'cURLFull'             => 'URL',
        'dErstellt'            => 'DateCreatedCompat',
        'dErstellt_de'         => 'DateCreatedLocalizedCompat',
        'Datum'                => 'DateCompat',
        'dGueltigVon'          => 'DateValidFromCompat',
        'dGueltigVon_de'       => 'DateValidFromLocalizedCompat',
        'nNewsKommentarAnzahl' => 'CommentCount',
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
    public function getDateCreatedCompat(): string
    {
        return $this->getDateCreated()->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function getDateValidFromCompat(): string
    {
        return $this->getDateValidFrom()->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function getDateValidFromLocalizedCompat(): string
    {
        return $this->getDateValidFrom()->format('Y-m-d H:i');
    }

    /**
     * @return string
     */
    public function getDateCreatedLocalizedCompat(): string
    {
        return $this->getDateCreated()->format('d.m.Y H:i');
    }

    /**
     * @return string
     */
    public function getDateCompat(): string
    {
        return $this->getDateCreated()->format('d.m.Y H:i');
    }
}
