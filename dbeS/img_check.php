<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

ob_start();
require_once dirname(__FILE__) . '/syncinclude.php';

$return = 3;
if (auth()) {
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Image Check: Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Image Check: Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
        }

        $newTmpDir = PFAD_SYNC_TMP . uniqid("check_") . '/';
        mkdir($newTmpDir, 0777, true);

        if ($extracedList = $archive->extract(PCLZIP_OPT_PATH, $newTmpDir)) {
            $return = 0;
            foreach ($list as $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('Image Check: bearbeite: ' . $newTmpDir . $zip['filename'] . ' size: ' . filesize($newTmpDir . $zip['filename']),
                        JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
                }
                if ($zip['filename'] === 'bildercheck.xml') {
                	$xml = simplexml_load_file($newTmpDir . $zip['filename']);
                    bildercheck_xml($xml);
                }
                removeTemporaryFiles($newTmpDir . $zip['filename']);
            }
            removeTemporaryFiles($newTmpDir);
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Image Check Error: ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'img_check_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Image Check Error: ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'img_check_xml');
    }
}
echo $return;

/*
$xmlString = <<<XML
<?xml version='1.0'?>
<bildcheck cloudURL="http://www.google.de">
    <item kBild="1" cHash="1024_768_d774fdbe7ff617eff8e28a7f1181b853_367f69f988bfe6f062249c145146eda126e8e4cb248709782974b4499f9aabf395797f98cde3461695eeadc2a40169f4fa73a59bc8432834ad6b49353c2611ba.jpg" />
    <item kBild="18" cHash="--1024_768_881dd83994a7d74ffd37b7211aa54306_0e8a9a6a33e1549b814b4c06fbb63d7959d7fd0d460981562d8c31cee5f10346c2689c64e8fc4e266389a783d8a1dd4d91278104be41b960e2bb4a304e50a5ff.jpg" />
    <item kBild="36" cHash="1024_1024_f33f1c16bfbf36b435f86866d128f6a4_e0781a3b0ef4afeb6cd50b119a0ad72ee4263b7edc4d8cb98553e18298f6539ad180b1ade442efa00f7c0909fa8e27ca35a9ba2640284d97529a1e99d31584de.jpg" />
    <item kBild="48" cHash="994_377_0af357f377013b10a6414238d4c2208d_ce18c4713e7915b566232e9f5618eef3066c61b81473a28eaadc2af8ac498def4521abfc42490c17b70dad8fe0105bc1195262bea11a7d9c5b0da65a7a7c9be1.jpg" />
    <item kBild="68" cHash="2304_1728_e01d005023e887391c09c504641dab53_feda8d5e50b76329eca6fcb0d385109adf258c6e691017edb65368aa84f52c6b7411bdf24c4e9a1539b1eb8080a108ed4ffadb4680bfe2e2858af12afe1509e8.jpg" />
    <item kBild="110" cHash="400_600_3fe24cc241cdaaebfcedeb43efed4939_d80bbf48d31125b74e861ad0e5767045c43bc0b00b23fb03bb506187be260ee9158c711f18acb78dc6146872cbe271b057ddd36978e10aeb29d9edd1c6d96a75.jpg" />
    <item kBild="127" cHash="800_800_31f064ca7d0194eff3a80bc6ffea0ff7_74246cce02d48e0e89a4beeb93d45456d8bac3862fef21b4749b2847f3540947192eb20993528197362fbe1b877090356543fdfcb87760ebd44c3db8c7d8bdea.jpg" />
</bildcheck>
XML;

$xml = simplexml_load_string($xmlString);
bildercheck_xml($xml);
*/

/**
 * @param SimpleXMLElement $xml
 */
function bildercheck_xml(SimpleXMLElement $xml)
{
    $found  = array();
    $sqls   = array();
    $object = get_object($xml);
    foreach ($object->items as $item) {
        $hash   = Shop::DB()->escape($item->hash);
        $sqls[] = "(kBild={$item->id} && cPfad='{$hash}')";
    }
    $sqlOr  = implode(' || ', $sqls);
    $sql    = "SELECT kBild AS id, cPfad AS hash FROM tbild WHERE {$sqlOr}";
    $images = Shop::DB()->query($sql, 2);
    if ($images !== false) {
        foreach ($images as $image) {
            $storage = PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $image->hash;
            if (!file_exists($storage)) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog("Dropping orphan {$image->id} -> {$image->hash}: no such file", JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
                }
                Shop::DB()->delete('tbild', 'kBild', $image->id);
                Shop::DB()->delete('tartikelpict', 'kBild', $image->id);
            }
            $found[] = $image->id;
        }
    }
    if ($object->cloud) {
        foreach ($object->items as $item) {
            if (in_array($item->id, $found)) {
                continue;
            }
            if (cloud_download($item->hash)) {
                $oBild = (object)[
                    'kBild' => $item->id,
                    'cPfad' => $item->hash
                ];
                DBUpdateInsert('tbild', array($oBild), 'kBild');
                $found[] = $item->id;
            }
        }
    }

    if (!empty($found) && Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        $checkids = array_map(function ($item) {
            return $item->id;
        }, $object->items);

        $checklist = implode(';', $checkids);
        Jtllog::writeLog('Checking: ' . $checklist, JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
    }

    $missing = array_filter($object->items, function ($item) use ($found) {
        return !in_array($item->id, $found);
    });

    $ids = array_map(function ($item) {
        return $item->id;
    }, $missing);

    $idlist = implode(';', $ids);
    push_response("0;\n<bildcheck><notfound>{$idlist}</notfound></bildcheck>");
}

/**
 * @param string $content
 */
function push_response($content)
{
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Image check response: ' . htmlentities($content), JTLLOG_LEVEL_DEBUG, false, 'img_check_xml');
    }

    ob_clean();
    echo $content;
    exit;
}

/**
 * @param SimpleXMLElement $xml
 * @return object
 */
function get_object(SimpleXMLElement $xml)
{
    $cloudURL = (string)$xml->attributes()->cloudURL;
    $check    = (object)array(
        'url'   => $cloudURL,
        'cloud' => strlen($cloudURL) > 0,
        'items' => array()
    );
    foreach ($xml->children() as $child) {
        $check->items[] = (object)array(
            'id'   => (int)$child->attributes()->kBild,
            'hash' => (string)$child->attributes()->cHash
        );
    }

    return $check;
}

/**
 * @param string $hash
 * @return bool
 */
function cloud_download($hash)
{
    $service   = ImageCloud::getInstance();
    $url       = $service->get($hash);
    $imageData = download($url);

    if ($imageData !== null) {
        $tmpFile = tempnam(sys_get_temp_dir(), 'jtl');
        $filename = PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $hash;

        file_put_contents($tmpFile, $imageData, FILE_BINARY);

        return rename($tmpFile, $filename);
    }
    
    return false;
}

/**
 * @param string $url
 * @return mixed|null
 */
function download($url)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'JTL-Shop/' . JTL_VERSION);

    $data = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $code === 200 ? $data : null;
}
