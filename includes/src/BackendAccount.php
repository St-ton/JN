<?php declare(strict_types = 1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */


namespace JTL;

use JTL\Language\LanguageHelper;
use JTL\News\Item;
use stdClass;
use StringHandler;

/**
 * Class BackendAccount
 */
class BackendAccount
{
    /**
     * @param array $tmpFile
     * @param string $attribName
     * @return mixed bool|string
     */
    private static function uploadImage(array $tmpFile, string $attribName)
    {
        $imgType = \array_search($tmpFile['type'][$attribName], [
            \IMAGETYPE_JPEG => \image_type_to_mime_type(\IMAGETYPE_JPEG),
            \IMAGETYPE_PNG  => \image_type_to_mime_type(\IMAGETYPE_PNG),
            \IMAGETYPE_BMP  => \image_type_to_mime_type(\IMAGETYPE_BMP),
            \IMAGETYPE_GIF  => \image_type_to_mime_type(\IMAGETYPE_GIF),
        ], true);

        if ($imgType !== false) {
            $imagePath = \PFAD_MEDIA_IMAGE . 'avatare/';
            $imageName = \pathinfo($tmpFile['name'][$attribName], \PATHINFO_FILENAME)
                . \image_type_to_extension($imgType);
            if (\is_dir(\PFAD_ROOT . $imagePath) || \mkdir(\PFAD_ROOT . $imagePath, 0755)) {
                if (\move_uploaded_file($tmpFile['tmp_name'][$attribName], \PFAD_ROOT . $imagePath . $imageName)) {
                    return '/' . $imagePath . $imageName;
                }
            }
        }

        return false;
    }

    /**
     * @param string $relURL
     * @return string
     */
    private static function getFullImageURL(string $relURL): string
    {
        return Shop::getImageBaseURL() . \ltrim($relURL, '/');
    }

    /**
     * @param $imagePath
     */
    private static function deleteImage(string $imagePath): void
    {
        if (\is_file(\PFAD_ROOT . $imagePath)) {
            \unlink(\PFAD_ROOT . $imagePath);
        }
    }

    /**
     * @param array $contentArr
     */
    public static function hydrateNews(array $contentArr): void
    {
        foreach ($contentArr as $newsItem) {
            /** @var Item $newsItem */
            $author = ContentAuthor::getInstance()->getAuthor('NEWS', $newsItem->getID(), true);

            if (!isset($author->kAdminlogin) || $author->kAdminlogin <= 0) {
                continue;
            }
            if ($author->extAttribs['useAvatar']->cAttribValue === 'G') {
                $params = ['email' => null, 's' => 80, 'd' => 'mm', 'r' => 'g'];
                $url    = 'https://www.gravatar.com/avatar/';
                $mail   = empty($author->extAttribs['useGravatarEmail']->cAttribValue)
                    ? $author->cMail
                    : $author->extAttribs['useGravatarEmail']->cAttribValue;
                $url   .= \md5(\mb_convert_case(\trim($mail), MB_CASE_LOWER));
                $url   .= '?' . \http_build_query($params, '', '&');

                $author->cAvatarImgSrc     = $url;
                $author->cAvatarImgSrcFull = $url;
            }
            if ($author->extAttribs['useAvatar']->cAttribValue === 'U') {
                $author->cAvatarImgSrc     = $author->extAttribs['useAvatarUpload']->cAttribValue;
                $author->cAvatarImgSrcFull = self::getFullImageURL(
                    $author->extAttribs['useAvatarUpload']->cAttribValue
                );
            }
            unset($author->extAttribs['useAvatarUpload'], $author->extAttribs['useGravatarEmail']);

            $author->cVitaShort = $author->extAttribs['useVita_' . $_SESSION['cISOSprache']]->cAttribValue;
            $author->cVitaLong  = $author->extAttribs['useVita_' . $_SESSION['cISOSprache']]->cAttribText;
            foreach (LanguageHelper::getAllLanguages() as $language) {
                unset($author->extAttribs['useVita_' . $language->cISO]);
            }

            $newsItem->setAuthor($author);
        }
    }

    /**
     * @param array $contentArr
     * @param string $realm
     * @param string $contentKey
     */
    public static function getFrontend($contentArr, $realm, $contentKey): void
    {
        if (!\is_array($contentArr)) {
            return;
        }
        foreach ($contentArr as $key => $content) {
            $author = ContentAuthor::getInstance()->getAuthor($realm, $content->$contentKey, true);

            if (!isset($author->kAdminlogin) || $author->kAdminlogin <= 0) {
                continue;
            }
            if ($author->extAttribs['useAvatar']->cAttribValue === 'U') {
                $author->cAvatarImgSrc     = $author->extAttribs['useAvatarUpload']->cAttribValue;
                $author->cAvatarImgSrcFull = self::getFullImageURL(
                    $author->extAttribs['useAvatarUpload']->cAttribValue
                );
            }
            unset($author->extAttribs['useAvatarUpload'], $author->extAttribs['useGravatarEmail']);

            $author->cVitaShort = $author->extAttribs['useVita_' . $_SESSION['cISOSprache']]->cAttribValue;
            $author->cVitaLong  = $author->extAttribs['useVita_' . $_SESSION['cISOSprache']]->cAttribText;
            foreach (LanguageHelper::getAllLanguages() as $language) {
                unset($author->extAttribs['useVita_' . $language->cISO]);
            }

            $contentArr[$key]->oAuthor = $author;
        }
    }

    /**
     * HOOK_BACKEND_ACCOUNT_EDIT - VALIDATE
     *
     * @param stdClass $account
     * @param array     $attribs
     * @param array     $messages
     * @return mixed bool|array - true if success otherwise errormap
     */
    public static function validateAccount(stdClass $account, array &$attribs, array &$messages)
    {
        $result = true;

        if (!$attribs['useAvatar']) {
            $attribs['useAvatar'] = 'N';
        }

        if ($attribs['useAvatar'] === 'U') {
            if (isset($_FILES['extAttribs']) && !empty($_FILES['extAttribs']['name']['useAvatarUpload'])) {
                $attribs['useAvatarUpload'] = self::uploadImage($_FILES['extAttribs'], 'useAvatarUpload');

                if ($attribs['useAvatarUpload'] === false) {
                    $messages['error'] .= 'Fehler beim Bilupload!';

                    $result = ['useAvatarUpload' => 1];
                }
            } elseif (empty($attribs['useAvatarUpload'])) {
                $messages['error'] .= 'Bitte geben Sie ein Bild an!';

                $result = ['useAvatarUpload' => 1];
            }
        } elseif (!empty($attribs['useAvatarUpload'])) {
            self::deleteImage($attribs['useAvatarUpload']);
            $attribs['useAvatarUpload'] = '';
        }

        foreach (LanguageHelper::getAllLanguages() as $language) {
            $useVita_ISO = 'useVita_' . $language->cISO;
            if (!empty($attribs[$useVita_ISO])) {
                $shortText = StringHandler::filterXSS($attribs[$useVita_ISO]);
                $longtText = $attribs[$useVita_ISO];

                if (\mb_strlen($shortText) > 255) {
                    $shortText = \mb_substr($shortText, 0, 250) . '...';
                }

                $attribs[$useVita_ISO] = [$shortText, $longtText];
            }
        }

        return $result;
    }
}
