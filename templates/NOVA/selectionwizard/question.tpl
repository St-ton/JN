{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='selectionwizard-question'}
    {listgroupitem class="selection-wizard-question {if $nQuestion > $AWA->getCurQuestion()}disabled{/if}"}
        {block name='selectionwizard-question-heading'}
            <div class="h5 selection-wizard-question-heading">
                {$oFrage->cFrage}
                {if $nQuestion < $AWA->getCurQuestion()}
                    {link href="#" data=["value"=>$nQuestion] class="question-edit"}<i class="fa fa-edit"></i>{/link}
                {/if}
            </div>
        {/block}
        {if $nQuestion < $AWA->getCurQuestion()}
            {block name='selectionwizard-question-answer-smaller'}
                {row class="text-center"}
                    {col cols=4 sm=4 md=3 xl=2 class="mb-3"}
                        <span class="selection-wizard-answer">
                            {$characteristicValue = $AWA->getSelectedValue($nQuestion)}
                            {$img = $characteristicValue->getImage(\JTL\Media\Image::SIZE_XS)}
                            {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['B', 'BT']:true && $img !== null}
                                {image webp=true lazy=true
                                    src=$img
                                    srcset="{$characteristicValue->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_merkmalwert_mini_breite}w,
                                        {$characteristicValue->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_merkmalwert_klein_breite}w,
                                        {$characteristicValue->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_merkmalwert_normal_breite}w"
                                    alt=$characteristicValue->getValue()|escape:'html'
                                    sizes="auto"
                                    class="w-100"
                                }
                            {/if}
                            {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['T', 'BT', 'S']:true}
                                {$characteristicValue->getValue()}
                            {/if}
                        </span>
                    {/col}
                {/row}
            {/block}
        {elseif $nQuestion === $AWA->getCurQuestion()}
            {if $AWA->getConf('auswahlassistent_anzeigeformat') === 'S'}
                {block name='selectionwizard-question-answer-equals-s'}
                    <label for="kMerkmalWert-{$nQuestion}" class="sr-only">{lang key='pleaseChoose' section='global'}</label>
                    {select id="kMerkmalWert-{$nQuestion}" class='custom-select'}
                        <option value="-1">{lang key='pleaseChoose' section='global'}</option>
                        {foreach $oFrage->oWert_arr as $characteristicValue}
                            {if $characteristicValue->getCount() > 0}
                                <option value="{$characteristicValue->getID()}">
                                    {$characteristicValue->getValue()}
                                    {if $AWA->getConf('auswahlassistent_anzahl_anzeigen') === 'Y'}
                                        ({$characteristicValue->nAnzahl})
                                    {/if}
                                </option>
                            {/if}
                        {/foreach}
                    {/select}
                {/block}
            {else}
                {block name='selectionwizard-question-answer-equals-other'}
                    {row class="text-center"}
                        {foreach $oFrage->oWert_arr as $characteristicValue}
                            {col cols=4 sm=4 md=3 xl=2 class="mb-3"}
                                {if $characteristicValue->getCount() > 0}
                                    {link class="selection-wizard-answer no-deco" href="#" data=["value"=>$characteristicValue->getID()]}
                                        {$img = $characteristicValue->getImage(\JTL\Media\Image::SIZE_XS)}
                                        {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['B', 'BT']:true && $img !== null}
                                            {image webp=true lazy=true
                                                src=$img
                                                srcset="{$characteristicValue->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_merkmalwert_mini_breite}w,
                                                    {$characteristicValue->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_merkmalwert_klein_breite}w,
                                                    {$characteristicValue->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_merkmalwert_normal_breite}w"
                                                alt=$characteristicValue->getValue()|escape:'html'
                                                sizes="auto"
                                                class="w-100"
                                            }
                                        {/if}
                                        {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['T', 'BT']:true}
                                            <span class="text-clamp-2">
                                                {$characteristicValue->getValue()}
                                                {if $AWA->getConf('auswahlassistent_anzahl_anzeigen') === 'Y'}
                                                    <span class="badge badge-outline-secondary text-cl">{$characteristicValue->getCount()}</span>
                                                {/if}
                                            </span>
                                        {/if}
                                    {/link}
                                {/if}
                            {/col}
                        {/foreach}
                    {/row}
                {/block}
            {/if}
        {elseif $nQuestion > $AWA->getCurQuestion()}
            {block name='selectionwizard-question-anwser-bigger'}
                {if $AWA->getConf('auswahlassistent_anzeigeformat') === 'S'}
                    <label for="kMerkmalWert-{$nQuestion}" class="sr-only">{lang key='pleaseChoose' section='global'}</label>
                    {select id="kMerkmalWert-{$nQuestion}" class='custom-select' disabled="disabled"}
                        <option value="-1">{lang key='pleaseChoose' section='global'}</option>
                    {/select}
                {else}
                    {foreach $oFrage->oWert_arr as $characteristicValue}
                        {if $characteristicValue->getCount() > 0}
                            {$img = $characteristicValue->getImage(\JTL\Media\Image::SIZE_XS)}
                            <span class="selection-wizard-answer">
                                {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['B', 'BT']:true && $img !== null}
                                    {image webp=true lazy=true
                                        src=$img
                                        srcset="{$characteristicValue->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_merkmalwert_mini_breite}w,
                                            {$characteristicValue->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_merkmalwert_klein_breite}w,
                                            {$characteristicValue->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_merkmalwert_normal_breite}w"
                                        alt=$characteristicValue->getValue()|escape:'html'
                                        sizes="40px"
                                    }
                                {/if}
                                {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['T', 'BT']:true}
                                    {$characteristicValue->getValue()}
                                {/if}
                            </span>
                        {/if}
                    {/foreach}
                {/if}
            {/block}
        {/if}
    {/listgroupitem}
{/block}
