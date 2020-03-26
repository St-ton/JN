{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-mediafile'}
    {if !empty($Artikel->oMedienDatei_arr)}
        {assign var=mp3List value=false}
        {assign var=titles value=false}
        <div class="card-columns">
        {foreach $Artikel->oMedienDatei_arr as $oMedienDatei}
            {if ($mediaType->name == $oMedienDatei->cMedienTyp && $oMedienDatei->cAttributTab|count_characters == 0)
            || ($oMedienDatei->cAttributTab|count_characters > 0 && $mediaType->name == $oMedienDatei->cAttributTab)}
                {if $oMedienDatei->nErreichbar == 0}
                    {block name='productdetails-mediafilealert'}
                        {col}
                            {alert variant="danger"}
                                {lang key='noMediaFile' section='errorMessages'}
                            {/alert}
                        {/col}
                    {/block}
                {else}
                    {assign var=cName value=$oMedienDatei->cName}
                    {assign var=titles value=$titles|cat:$cName}
                    {if !$oMedienDatei@last}
                        {assign var=titles value=$titles|cat:'|'}
                    {/if}

                    {* Images *}
                    {if $oMedienDatei->nMedienTyp == 1}
                        {block name='productdetails-mediafile-images'}
                            {$cMediaAltAttr = ""}
                            {if isset($oMedienDatei->oMedienDateiAttribut_arr) && $oMedienDatei->oMedienDateiAttribut_arr|@count > 0}
                                {foreach $oMedienDatei->oMedienDateiAttribut_arr as $oAttribut}
                                    {if $oAttribut->cName === 'img_alt'}
                                        {assign var=cMediaAltAttr value=$oAttribut->cWert}
                                    {/if}
                                {/foreach}
                            {/if}
                            {card img-src="{if !empty($oMedienDatei->cPfad)}{$smarty.const.PFAD_MEDIAFILES}{$oMedienDatei->cPfad}{elseif !empty($oMedienDatei->cURL)}{$oMedienDatei->cURL}{/if}" title="{$oMedienDatei->cName}" img-top=true img-alt="{$cMediaAltAttr}"}
                                <p>{$oMedienDatei->cBeschreibung}</p>
                            {/card}
                        {/block}
                        {* Audio *}
                    {elseif $oMedienDatei->nMedienTyp == 2}
                        {if $oMedienDatei->cName|strlen > 1}
                            {block name='productdetails-mediafile-audio'}
                                {card title=$oMedienDatei->cName class="mb-3"}
                                    {row}
                                        {col cols=12}
                                            {$oMedienDatei->cBeschreibung}
                                        {/col}
                                        {col cols=12}
                                            {if $oMedienDatei->cPfad|strlen > 1 || $oMedienDatei->cURL|strlen > 1}
                                                {assign var=audiosrc value=$oMedienDatei->cURL}
                                                {if $oMedienDatei->cPfad|strlen > 1}
                                                    {assign var=audiosrc value=$smarty.const.PFAD_MEDIAFILES|cat:$oMedienDatei->cPfad}
                                                {/if}
                                                {if $audiosrc|strlen > 1}
                                                    <audio controls controlsList="nodownload">
                                                        <source src="{$audiosrc}" type="audio/mpeg">
                                                        Your browser does not support the audio element.
                                                    </audio>
                                                {/if}
                                            {/if}
                                        {/col}
                                    {/row}
                                {/card}
                            {/block}
                            {* Audio *}
                        {/if}

                        {* Video *}
                    {elseif $oMedienDatei->nMedienTyp == 3}
                        {block name='productdetails-mediafile-video'}
                        <!-- flash videos are not supported any more. Use html5 videos instead. -->
                        {/block}
                        {* Sonstiges *}
                    {elseif $oMedienDatei->nMedienTyp == 4}
                        {block name='productdetails-mediafile-misc'}
                            {card title=$oMedienDatei->cName class="mb-3"}
                                {row}
                                    {col md=6}
                                        {$oMedienDatei->cBeschreibung}
                                    {/col}
                                    {col md=6}
                                        {if isset($oMedienDatei->oEmbed) && $oMedienDatei->oEmbed->code}
                                            {$oMedienDatei->oEmbed->code}
                                        {/if}
                                        {if !empty($oMedienDatei->cPfad)}
                                            <p>
                                                {link href="{$smarty.const.PFAD_MEDIAFILES}{$oMedienDatei->cPfad}" target="_blank"}{$oMedienDatei->cName}{/link}
                                            </p>
                                        {elseif !empty($oMedienDatei->cURL)}
                                            <p>
                                                {link href=$oMedienDatei->cURL target="_blank"}<i class="fa fa-external-link"></i> {$oMedienDatei->cName}{/link}
                                            </p>
                                        {/if}
                                    {/col}
                                {/row}
                            {/card}
                        {/block}
                        {* PDF *}
                    {elseif $oMedienDatei->nMedienTyp == 5}
                        {block name='productdetails-mediafile-pdf'}
                            {card title=$oMedienDatei->cName class="mb-3"}
                                {row}
                                    {col md=6}
                                        {$oMedienDatei->cBeschreibung}
                                    {/col}
                                    {col md=6}
                                        {if !empty($oMedienDatei->cPfad)}
                                            {link href="{$smarty.const.PFAD_MEDIAFILES}{$oMedienDatei->cPfad}" target="_blank"}
                                                {image alt="PDF" src="{$smarty.const.PFAD_BILDER}intern/file-pdf.png"}
                                            {/link}
                                            <br />
                                            {link href="{$smarty.const.PFAD_MEDIAFILES}{$oMedienDatei->cPfad}" target="_blank"}
                                                {$oMedienDatei->cName}
                                            {/link}
                                        {elseif !empty($oMedienDatei->cURL)}
                                            {link href=$oMedienDatei->cURL target="_blank"}{image alt="PDF" src="{$smarty.const.PFAD_BILDER}intern/file-pdf.png"}{/link}
                                            <br />
                                            {link href=$oMedienDatei->cURL target="_blank"}{$oMedienDatei->cName}{/link}
                                        {/if}
                                    {/col}
                                {/row}
                            {/card}
                        {/block}
                    {/if}
                {/if}
            {/if}
        {/foreach}
        </div>
    {/if}
{/block}