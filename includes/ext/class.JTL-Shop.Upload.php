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
        public static function gibArtikelUploads($kArtikel, $eigenschaftenArr = false)
        {
            $kArtikel     = (int)$kArtikel;
            $uploadSchema = new UploadSchema();
            $uploads      = $uploadSchema::fetchAll($kArtikel, UPLOAD_TYP_WARENKORBPOS);
            if (!is_array($uploads) || count($uploads) === 0) {
                return [];
            }
            foreach ($uploads as &$upload) {
                $upload->nEigenschaften_arr = $eigenschaftenArr;
                $upload->cUnique            = self::uniqueDateiname($upload);
                $upload->cDateiTyp_arr      = self::formatTypen($upload->cDateiTyp);
                $upload->cDateiListe        = implode(';', $upload->cDateiTyp_arr);
                $upload->bVorhanden         = is_file(PFAD_UPLOADS . $upload->cUnique);
                $uploadDatei                = isset($_SESSION['Uploader'][$upload->cUnique])
                    ? $_SESSION['Uploader'][$upload->cUnique]
                    : null;
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
        public static function deleteArtikelUploads($kArtikel)
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
         * @return array
         */
        public static function gibWarenkorbUploads($oWarenkorb)
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
         * @return mixed
         */
        public static function gibBestellungUploads($kBestellung)
        {
            $oUploadDatei = new UploadDatei();

            return $oUploadDatei::fetchAll($kBestellung, UPLOAD_TYP_BESTELLUNG);
        }

        /**
         * @param Warenkorb $oWarenkorb
         * @return bool
         */
        public static function pruefeWarenkorbUploads($oWarenkorb)
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
        public static function redirectWarenkorb($nErrorCode)
        {
            header('Location: ' .
                LinkHelper::getInstance()->getStaticRoute('warenkorb.php') .
                '?fillOut=' . $nErrorCode, true, 303);
        }

        /**
         * @param Warenkorb $oWarenkorb
         * @param int       $kBestellung
         */
        public static function speicherUploadDateien($oWarenkorb, $kBestellung)
        {
            $kBestellung = (int)$kBestellung;
            foreach (self::gibWarenkorbUploads($oWarenkorb) as &$oUploadSchema) {
                foreach ($oUploadSchema->oUpload_arr as &$oUploadDatei) {
                    $oUploadInfo = isset($_SESSION['Uploader'][$oUploadDatei->cUnique])
                        ? $_SESSION['Uploader'][$oUploadDatei->cUnique]
                        : null;
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
        public static function setzeUploadDatei($kCustomID, $nTyp, $cName, $cPfad, $nBytes)
        {
            $oUploadDatei            = new stdClass();
            $oUploadDatei->kCustomID = $kCustomID;
            $oUploadDatei->nTyp      = $nTyp;
            $oUploadDatei->cName     = $cName;
            $oUploadDatei->cPfad     = $cPfad;
            $oUploadDatei->nBytes    = $nBytes;
            $oUploadDatei->dErstellt = 'now()';

            Shop::DB()->insert('tuploaddatei', $oUploadDatei);
        }

        /**
         * @param int $kBestellung
         * @param int $kCustomID
         */
        public static function setzeUploadQueue($kBestellung, $kCustomID)
        {
            $oUploadQueue              = new stdClass();
            $oUploadQueue->kBestellung = $kBestellung;
            $oUploadQueue->kArtikel    = $kCustomID;

            Shop::DB()->insert('tuploadqueue', $oUploadQueue);
        }

        /**
         * @return int|mixed
         */
        public static function uploadMax()
        {
            $nMaxUpload   = (int)ini_get('upload_max_filesize');
            $nMaxPost     = (int)ini_get('post_max_size');
            $nMemoryLimit = Shop()->PHPSettingsHelper()->limit();
            $nUploadMax   = min($nMaxUpload, $nMaxPost, $nMemoryLimit);
            $nUploadMax   *= (1024 * 1024);

            return $nUploadMax;
        }

        /**
         * @param int $nFileSize
         * @return string
         */
        public static function formatGroesse($nFileSize)
        {
            if (is_numeric($nFileSize)) {
                $nStep       = 0;
                $nDecr       = 1024;
                $cPrefix_arr = ['Byte', 'KB', 'MB', 'GB', 'TB', 'PB'];

                while (($nFileSize / $nDecr) > 0.9) {
                    $nFileSize /= $nDecr;
                    ++$nStep;
                }

                return round($nFileSize, 2) . ' ' . $cPrefix_arr[$nStep];
            }

            return '---';
        }

        /**
         * @param object $oUpload
         * @return string
         */
        public static function uniqueDateiname($oUpload)
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
        private static function getSessionKey()
        {
            if (!isset($_SESSION['Uploader']['sessionKey'])) {
                $_SESSION['Uploader']['sessionKey'] = uniqid('sk', true);
            }

            return $_SESSION['Uploader']['sessionKey'];
        }

        /**
         * @param string $cDateiTyp
         * @return array
         */
        public static function formatTypen($cDateiTyp)
        {
            $cDateiTyp_arr = explode(',', $cDateiTyp);
            foreach ($cDateiTyp_arr as &$cTyp) {
                $cTyp = '*' . $cTyp;
            }

            return $cDateiTyp_arr;
        }

        /**
         * @param string $cName
         * @return bool
         */
        public static function vorschauTyp($cName)
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
