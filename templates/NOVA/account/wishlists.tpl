{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1>{block name='account-wishlist-title'}{lang key='yourWishlist' section='login'}{/block}</h1>
{if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
    {block name='account-wishlist'}
        {block name='account-wishlist-body'}
            {if !empty($oWunschliste_arr[0]->kWunschliste)}
                {row}
                    {col md=3}
                        <strong>{lang key='wishlistName' section='login'}</strong>
                    {/col}
                    {col md=3}
                        <strong>{lang key='wishlistStandard' section='login'}</strong>
                    {/col}
                {/row}
                {foreach $oWunschliste_arr as $Wunschliste}
                    <hr>
                    {row}
                        {col md=3}{link href="{get_static_route id='jtl.php'}?wl={$Wunschliste->kWunschliste}"}{$Wunschliste->cName}{/link}{/col}
                        {col md=3}{if $Wunschliste->nStandard == 1}{lang key='active' section='global'}{/if} {if $Wunschliste->nStandard == 0}{lang key='inactive' section='global'}{/if}{/col}
                        {col md=6 class="text-right"}
                            {form method="post" action="{get_static_route id='jtl.php'}?wllist=1"}
                                {input type="hidden" name="wl" value=$Wunschliste->kWunschliste}
                                {buttongroup}
                                    {if $Wunschliste->nStandard != 1}
                                        {button size="sm"
                                            type="submit"
                                            name="wls"
                                            value=$Wunschliste->kWunschliste
                                            title="{lang key='wishlistStandard' section='login'}"
                                            data=["toggle" => "tooltip", "placement" => "bottom"]}
                                            <i class="fa fa-ok"></i> {lang key='wishlistStandard' section='login'}
                                        {/button}
                                    {/if}
                                    {if $Wunschliste->nOeffentlich == 1}
                                        {button size="sm"
                                            type="submit"
                                            name="wlAction"
                                            value="setPrivate"
                                            title="{lang key='wishlistPrivat' section='login'}"
                                            data=["toggle" => "tooltip", "placement" => "bottom"]}
                                            <i class="fa fa-eye-slash"></i> <span class="d-none d-md-inline-block">{lang key='wishlistSetPrivate' section='login'}</span>
                                        {/button}
                                    {/if}
                                    {if $Wunschliste->nOeffentlich == 0}
                                        {button size="sm"
                                            type="submit"
                                            name="wlAction"
                                            value="setPublic"
                                            title="{lang key='wishlistNotPrivat' section='login'}"
                                            data=["toggle" => "tooltip", "placement" => "bottom"]}
                                            <i class="fa fa-eye"></i> <span class="d-none d-md-inline-block">{lang key='wishlistNotPrivat' section='login'}</span>
                                        {/button}
                                    {/if}
                                    {button size="sm"
                                        type="submit"
                                        variant="danger"
                                        name="wllo"
                                        value=$Wunschliste->kWunschliste
                                        title="{lang key='wishlisteDelete' section='login'}"
                                        data=["toggle" => "tooltip", "placement" => "bottom"]}
                                        <i class="fa fa-trash-alt"></i>
                                    {/button}
                                {/buttongroup}
                            {/form}
                        {/col}
                    {/row}
                {/foreach}
            {/if}
            <hr>
            {form method="post" action="{get_static_route id='jtl.php'}?wllist=1" class="form form-inline"}
                {input name="wlh" type="hidden" value="1"}
                {inputgroup}
                    {input name="cWunschlisteName" type="text" placeholder="{lang key='wishlistAddNew' section='login'}" aria=["label"=>"{lang key='wishlistAddNew' section='login'}"]}
                    {inputgroupaddon append=true}
                        {button type="submit" name="submit" value="1"}{lang key='wishlistSaveNew' section='login'}{/button}
                    {/inputgroupaddon}
                {/inputgroup}
            {/form}
        {/block}
    {/block}
{/if}
