{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-searchspecials'}
     {image class="overlay-img d-none d-md-block"
          srcset="{$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_XS)},
                          {$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_SM)} 2x,
                          {$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_MD)} 3x,
                          {$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_LG)} 4x"
          src=$src
          alt=$Artikel->oSuchspecialBild->getName()|default:$alt}
{/block}
