<?php declare(strict_types=1);

namespace JTL\Template\Admin\Installation\Items;

use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Plugin\InstallCode;
use SimpleXMLElement;
use stdClass;

/**
 * Class Consent
 * @package JTL\Template\Admin\Installation\Items
 */
class Consent extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): ?SimpleXMLElement
    {
        return $this->xml->ServicesRequiringConsent ?? null;
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $templateID          = $this->model->getTemplate();
        $allLanguages        = \collect(LanguageHelper::getAllLanguages(1));
        $defaultLanguage     = LanguageHelper::getDefaultLanguage();
        $addedItems          = [];
        $addedLanguages      = [];
        $defaultLocalization = null;
        $added               = false;
        foreach ([$this->xml, $this->parentXml] as $xml) {
            foreach ($xml->ServicesRequiringConsent ?? [] as $node) {
                foreach ($node as $vendor) {
                    $vendor = (array)$vendor;
                    if (!isset($vendor['ID'])) {
                        continue;
                    }
                    $consentID    = $this->addVendorForTemplate($templateID, $vendor);
                    $addedItems[] = $consentID;
                    if ($consentID <= 0) {
                        return InstallCode::SQL_CANNOT_SAVE_VENDOR;
                    }
                    $added                    = true;
                    $localization             = new stdClass();
                    $localization->consentID  = $consentID;
                    $localization->languageID = 0;
                    foreach ($vendor['Localization'] as $localized) {
                        $localized = (array)$localized;
                        $langCode  = \mb_convert_case($localized['@attributes']['iso'], \MB_CASE_LOWER);
                        $mapped    = LanguageHelper::getLangIDFromIso($langCode);
                        if ($mapped === null) {
                            $localization->languageID = 0;
                            continue;
                        }
                        $localization->name          = $localized['Name'];
                        $localization->purpose       = $localized['Purpose'];
                        $localization->description   = $localized['Description'];
                        $localization->privacyPolicy = $localized['PrivacyPolicy'];
                        $localization->languageID    = $mapped->kSprache;
                        $addedLanguages[]            = $localization->languageID;
                        $existingID                  = $this->db->getSingleInt(
                            'SELECT id 
                                FROM tconsentlocalization
                                WHERE consentID = :cid
                                    AND languageID = :lid',
                            'id',
                            ['cid' => $localization->consentID, 'lid' => $localization->languageID]
                        );
                        if ($existingID === -1) {
                            $this->db->insert('tconsentlocalization', $localization);
                        } else {
                            $this->db->update('tconsentlocalization', 'id', $existingID, $localization);
                        }
                        if ($defaultLocalization === null || $localization->languageID === $defaultLanguage->getId()) {
                            $defaultLocalization = clone $localization;
                        }
                    }
                }
                $missingLanguages = $allLanguages->filter(static function (LanguageModel $e) use ($addedLanguages) {
                    return !\in_array($e->getId(), $addedLanguages, true);
                });
                $this->addMissingTranslations($missingLanguages->toArray(), $defaultLocalization);
            }
        }
        $this->cleanUpOldVendors($templateID, $addedItems);
        if ($added === true) {
            $this->db->query('UPDATE tglobals SET consentVersion = consentVersion + 1, dLetzteAenderung = NOW()');
        }

        return InstallCode::OK;
    }

    /**
     * @param LanguageModel[] $missingLanguages
     * @param stdClass        $defaultLocalization
     */
    private function addMissingTranslations(array $missingLanguages, stdClass $defaultLocalization): void
    {
        foreach ($missingLanguages as $language) {
            $defaultLocalization->languageID = $language->getId();
            $this->db->insert('tconsentlocalization', $defaultLocalization);
        }
    }

    /**
     * @param string $templateID
     * @param array  $items
     * @return int
     */
    private function addVendorForTemplate(string $templateID, array $items): int
    {
        $item             = new stdClass();
        $item->itemID     = $items['ID'];
        $item->company    = $items['Company'];
        $item->pluginID   = 0;
        $item->active     = 1;
        $item->templateID = $templateID;
        $exists           = $this->db->getSingleObject(
            'SELECT * 
                FROM tconsent
                WHERE templateID = :tplID
                    AND itemID = :cID',
            ['tplID' => $templateID, 'cID' => $item->itemID]
        );
        if ($exists !== null) {
            $id = (int)$exists->id;
            $this->db->update('tconsent', 'id', $id, $item);

            return $id;
        }

        return $this->db->insert('tconsent', $item);
    }

    /**
     * @param string $templateID
     * @param int[]  $addedItems - list of ids that were inserted/updated
     * @return int
     */
    private function cleanUpOldVendors(string $templateID, array $addedItems): int
    {
        $this->db->queryPrepared(
            'UPDATE tconsent SET active = 0
                WHERE templateID != \'0\'
                    AND templateID != :tplID',
            ['tplID' => $templateID]
        );
        if (\count($addedItems) === 0) {
            return $this->db->getAffectedRows(
                'DELETE FROM tconsent
                    WHERE templateID = :tplID',
                ['tplID' => $templateID]
            );
        }

        return $this->db->getAffectedRows(
            'DELETE FROM tconsent
                WHERE templateID = :tplID
                    AND id NOT IN (' . \implode(',', \array_map('\intval', $addedItems)) . ')',
            ['tplID' => $templateID]
        );
    }
}
