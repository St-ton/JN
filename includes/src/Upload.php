<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_UPLOADS)) {
    /**
     * Class Upload
     */
    class Upload
    {
        /**
         * @param int        $kArtikel
         * @param bool|array $eigenschaftenArr
         * @return array
         */
        public static function gibArtikelUploads(int $kArtikel, $eigenschaftenArr = false): array
        {
            $uploadSchema = new UploadSchema();
            $uploads      = $uploadSchema::fetchAll($kArtikel, UPLOAD_TYP_WARENKORBPOS);
            foreach ($uploads as &$upload) {
                $upload->nEigenschaften_arr = $eigenschaftenArr;
                $upload->cUnique            = self::uniqueDateiname($upload);
                $upload->cDateiTyp_arr      = self::formatTypen($upload->cDateiTyp);
                $upload->cDateiListe        = implode(';', $upload->cDateiTyp_arr);
                $upload->bVorhanden         = is_file(PFAD_UPLOADS . $upload->cUnique);
                $uploadDatei                = $_SESSION['Uploader'][$upload->cUnique] ?? null;
                if ($uploadDatei !== null && is_object($uploadDatei)) {
                    $upload->cDateiname    = $uploadDatei->cName;
                    $upload->cDateigroesse = self::formatGroesse($uploadDatei->nBytes);
                }
            }

            return $uploads;
        }

        /**
         * Deletes all uploaded files for an article with ID (kArtikel)
         *
         * @param  int $kArtikel
         * @return int
         */
        public static function deleteArtikelUploads(int $kArtikel): int
        {
            $count   = 0;
            $uploads = self::gibArtikelUploads($kArtikel);

            foreach ($uploads as &$upload) {
                unset($_SESSION['Uploader'][$upload->cUnique]);
                if ($upload->bVorhanden && unlink(PFAD_UPLOADS . $upload->cUnique)) {
                    ++$count;
                }
            }

            return $count;
        }

        /**
         * @param Warenkorb $oWarenkorb
         * @return stdClass[]
         */
        public static function gibWarenkorbUploads(Warenkorb $oWarenkorb): array
        {
            $uploads = [];
            foreach ($oWarenkorb->PositionenArr as &$oPosition) {
                if ($oPosition->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL || empty($oPosition->Artikel->kArtikel)) {
                    continue;
                }
                $eigenschaftArr = [];
                if (!empty($oPosition->WarenkorbPosEigenschaftArr)) {
                    foreach ($oPosition->WarenkorbPosEigenschaftArr as $eigenschaft) {
                        $eigenschaftArr[$eigenschaft->kEigenschaft] = is_string($eigenschaft->cEigenschaftWertName)
                            ? $eigenschaft->cEigenschaftWertName
                            : reset($eigenschaft->cEigenschaftWertName);
                    }
                }
                $oUpload        = new stdClass();
                $oUpload->cName = $oPosition->Artikel->cName;
                if (!empty($oPosition->WarenkorbPosEigenschaftArr)) {
                    $oUpload->WarenkorbPosEigenschaftArr = $oPosition->WarenkorbPosEigenschaftArr;
                }
                $oUpload->oUpload_arr = self::gibArtikelUploads($oPosition->Artikel->kArtikel, $eigenschaftArr);
                if (count($oUpload->oUpload_arr) > 0) {
                    $uploads[] = $oUpload;
                }
            }

            return $uploads;
        }

        /**
         * @param int $kBestellung
         * @return array
         */
        public static function gibBestellungUploads(int $kBestellung): array
        {
            $oUploadDatei = new UploadDatei();

            return $oUploadDatei::fetchAll($kBestellung, UPLOAD_TYP_BESTELLUNG);
        }

        /**
         * @param Warenkorb $oWarenkorb
         * @return bool
         */
        public static function pruefeWarenkorbUploads(Warenkorb $oWarenkorb): bool
        {
            foreach (self::gibWarenkorbUploads($oWarenkorb) as &$oUploadSchema) {
                foreach ($oUploadSchema->oUpload_arr as &$oUpload) {
                    if ($oUpload->nPflicht && !$oUpload->bVorhanden) {
                        return false;
                    }
                }
            }

            return true;
        }

        /**
         * @param int $nErrorCode
         */
        public static function redirectWarenkorb(int $nErrorCode)
        {
            header('Location: ' .
                LinkHelper::getInstance()->getStaticRoute('warenkorb.php') .
                '?fillOut=' . $nErrorCode, true, 303);
        }

        /**
         * @param Warenkorb $oWarenkorb
         * @param int       $kBestellung
         */
        public static function speicherUploadDateien(Warenkorb $oWarenkorb, int $kBestellung)
        {
            foreach (self::gibWarenkorbUploads($oWarenkorb) as $oUploadSchema) {
                foreach ($oUploadSchema->oUpload_arr as $oUploadDatei) {
                    $oUploadInfo = $_SESSION['Uploader'][$oUploadDatei->cUnique] ?? null;
                    if ($oUploadInfo !== null && is_object($oUploadInfo)) {
                        self::setzeUploadQueue($kBestellung, $oUploadDatei->kCustomID);
                        self::setzeUploadDatei(
                            $kBestellung,
                            UPLOAD_TYP_BESTELLUNG,
                            $oUploadInfo->cName,
                            $oUploadDatei->cUnique,
                            $oUploadInfo->nBytes
                        );
                    }
                    unset($_SESSION['Uploader'][$oUploadDatei->cUnique]);
                }
            }
            session_regenerate_id();
            unset($_SESSION['Uploader']);
        }

        /**
         * @param int    $kCustomID
         * @param int    $nTyp
         * @param string $cName
         * @param string $cPfad
         * @param int    $nBytes
         */
        public static function setzeUploadDatei(int $kCustomID, int $nTyp, $cName, $cPfad, int $nBytes)
        {
            $oUploadDatei            = new stdClass();
            $oUploadDatei->kCustomID = $kCustomID;
            $oUploadDatei->nTyp      = $nTyp;
            $oUploadDatei->cName     = $cName;
            $oUploadDatei->cPfad     = $cPfad;
            $oUploadDatei->nBytes    = $nBytes;
            $oUploadDatei->dErstellt = 'now()';

            Shop::Container()->getDB()->insert('tuploaddatei', $oUploadDatei);
        }

        /**
         * @param int $kBestellung
         * @param int $kCustomID
         */
        public static function setzeUploadQueue(int $kBestellung, int $kCustomID)
        {
            $oUploadQueue              = new stdClass();
            $oUploadQueue->kBestellung = $kBestellung;
            $oUploadQueue->kArtikel    = $kCustomID;

            Shop::Container()->getDB()->insert('tuploadqueue', $oUploadQueue);
        }

        /**
         * @return int|mixed
         */
        public static function uploadMax()
        {
            return min(
                    Shop()->PHPSettingsHelper()->uploadMaxFileSize(),
                    Shop()->PHPSettingsHelper()->postMaxSize(),
                    Shop()->PHPSettingsHelper()->limit()
                );
        }

        /**
         * @param int $nFileSize
         * @return string
         */
        public static function formatGroesse($nFileSize): string
        {
            if (!is_numeric($nFileSize)) {
                return '---';
            }
            $nStep       = 0;
            $nDecr       = 1024;
            $cPrefix_arr = ['Byte', 'KB', 'MB', 'GB', 'TB', 'PB'];

            while (($nFileSize / $nDecr) > 0.9) {
                $nFileSize /= $nDecr;
                ++$nStep;
            }

            return round($nFileSize, 2) . ' ' . $cPrefix_arr[$nStep];
        }

        /**
         * @param object $oUpload
         * @return string
         */
        public static function uniqueDateiname($oUpload): string
        {
            $unique = $oUpload->kUploadSchema . $oUpload->kCustomID . $oUpload->nTyp . self::getSessionKey();
            if (!empty($oUpload->nEigenschaften_arr)) {
                $eigenschaften = '';
                foreach ($oUpload->nEigenschaften_arr as $k => $v) {
                    $eigenschaften .= $k . $v;
                }
                $unique .= $eigenschaften;
            }

            return md5($unique);
        }

        /**
         * @return string
         */
        private static function getSessionKey(): string
        {
            if (!isset($_SESSION['Uploader']['sessionKey'])) {
                $_SESSION['Uploader']['sessionKey'] = uniqid('sk', true);
            }

            return $_SESSION['Uploader']['sessionKey'];
        }

        /**
         * @param string $type
         * @return array
         */
        public static function formatTypen(string $type): array
        {
            $fileTypes = explode(',', $type);
            foreach ($fileTypes as &$cTyp) {
                $cTyp = '*' . $cTyp;
            }

            return $fileTypes;
        }

        /**
         * @param string $cName
         * @return bool
         */
        public static function vorschauTyp(string $cName): bool
        {
            $cPath_arr = pathinfo($cName);

            return is_array($cPath_arr)
                ? in_array(
                    $cPath_arr['extension'],
                    ['gif', 'png', 'jpg', 'jpeg', 'bmp', 'jpe'],
                    true
                )
                : false;
        }
    }
}
