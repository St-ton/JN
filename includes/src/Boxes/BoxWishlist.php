<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxWishlist
 * @package Boxes
 */
final class BoxWishlist extends AbstractBox
{
    /**
     * @var int
     */
    private $wishListID = 0;

    /**
     * BoxWishlist constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('nBilderAnzeigen', 'ShowImages');
        parent::addMapping('CWunschlistePos_arr', 'Items');
        if (empty(\Session::WishList()->kWunschliste)) {
            $this->setShow(false);
        } else {
            $this->setWishListID(\Session::WishList()->kWunschliste);
            $wishlistItems    = \Session::WishList()->CWunschlistePos_arr;
            $validPostVars    = ['a', 'k', 's', 'h', 'l', 'm', 't', 'hf', 'kf', 'show', 'suche'];
            $additionalParams = '';
            $postMembers      = \array_keys($_REQUEST);
            foreach ($postMembers as $postMember) {
                if ((int)$_REQUEST[$postMember] > 0 && \in_array($postMember, $validPostVars, true)) {
                    $additionalParams .= '&' . $postMember . '=' . $_REQUEST[$postMember];
                }
            }
            $additionalParams = \StringHandler::filterXSS($additionalParams);
            foreach ($wishlistItems as $wishlistItem) {
                $cRequestURI  = $_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_NAME'];
                $nPosAnd      = \strrpos($cRequestURI, '&');
                $nPosQuest    = \strrpos($cRequestURI, '?');
                $nPosWD       = \strpos($cRequestURI, 'wlplo=');
                $cDeleteParam = '?wlplo='; // z.b. index.php
                if ($nPosWD) {
                    $cRequestURI = \substr($cRequestURI, 0, $nPosWD);
                }
                if ($nPosAnd === \strlen($cRequestURI) - 1) {
                    // z.b. index.php?a=4&
                    $cDeleteParam = 'wlplo=';
                } elseif ($nPosAnd) {
                    // z.b. index.php?a=4&b=2
                    $cDeleteParam = '&wlplo=';
                } elseif ($nPosQuest) {
                    // z.b. index.php?a=4
                    $cDeleteParam = '&wlplo=';
                } elseif ($nPosQuest === \strlen($cRequestURI) - 1) {
                    // z.b. index.php?
                    $cDeleteParam = 'wlplo=';
                }
                $wishlistItem->cURL = $cRequestURI .
                    $cDeleteParam .
                    $wishlistItem->kWunschlistePos .
                    $additionalParams;
                if (\Session::CustomerGroup()->isMerchant()) {
                    $fPreis = isset($wishlistItem->Artikel->Preise->fVKNetto)
                        ? (int)$wishlistItem->fAnzahl * $wishlistItem->Artikel->Preise->fVKNetto
                        : 0;
                } else {
                    $fPreis = isset($wishlistItem->Artikel->Preise->fVKNetto)
                        ? (int)$wishlistItem->fAnzahl * ($wishlistItem->Artikel->Preise->fVKNetto *
                            (100 + $_SESSION['Steuersatz'][$wishlistItem->Artikel->kSteuerklasse]) / 100)
                        : 0;
                }
                $wishlistItem->cPreis = \Preise::getLocalizedPriceString($fPreis, \Session::Currency());
            }
            $this->setShow(true);
            $this->setItemCount((int)$this->config['boxen']['boxen_wunschzettel_anzahl']);
            $this->setItems(\array_reverse($wishlistItems));

            \executeHook(\HOOK_BOXEN_INC_WUNSCHZETTEL, ['box' => $this]);
        }
    }

    /**
     * @return int
     */
    public function getWishListID(): int
    {
        return $this->wishListID;
    }

    /**
     * @param int $id
     */
    public function setWishListID(int $id)
    {
        $this->wishListID = $id;
    }

    /**
     * @return string
     */
    public function getShowImages(): string
    {
        return $this->config['boxen']['boxen_wunschzettel_bilder'] === 'Y';
    }

    /**
     * @param string $value
     */
    public function setShowImages($value)
    {

    }
}
