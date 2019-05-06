{if $standalonePage}
    {include file='tpl_inc/header.tpl'}
    {$cTitel = 'Suchergebnisse für: '|cat:$query}
    {include file='tpl_inc/seite_header.tpl' cTitel=$cTitel}
    <ul>
{/if}

{if $adminMenuItems|count}
    <li>
        <h4>Seiten / Menüpunkte</h4>
    </li>
    <ul class="backend-search-section">
        {foreach $adminMenuItems as $item}
            <li class="backend-search-item" tabindex="-1">
                <a href="{$item->link}">
                    {$item->path}
                </a>
            </li>
        {/foreach}
    </ul>
{/if}
{if isset($settings)}
    {foreach $settings as $setting}
        <li>
            <h4>
                {$setting->cName}
                <small>
                    <a href="{$setting->cURL}">{$setting->cSektionsPfad}</a>
                </small>
            </h4>
        </li>
        <li>
            <ul class="backend-search-section">
                {foreach $setting->oEinstellung_arr as $s}
                    <li class="backend-search-item" tabindex="-1">
                        <a href="einstellungen.php?cSuche={$s->kEinstellungenConf}&einstellungen_suchen=1"
                           class="value">
                            <span>{$s->cName} ({__('settingNumberShort')}: {$s->kEinstellungenConf})</span>
                            <small>{$s->cBeschreibung}</small>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/foreach}
{elseif isset($shippings)}
    <li>
        <h4><a href="versandarten.php" class="value">{__('shippingTypesOverview')}</a></h4>
    </li>
    <li>
        <ul class="backend-search-section">
            {foreach $shippings as $shipping}
                <li class="backend-search-item" tabindex="-1">
                    <form method="post" action="versandarten.php">
                        {$jtl_token}
                        <input type="hidden" name="edit" value="{$shipping->kVersandart}">
                        <button type="submit" class="btn btn-link">{$shipping->cName}</button>
                    </form>
                </li>
            {/foreach}
        </ul>
    </li>
{elseif isset($paymentMethods)}
    <li>
        <h4><a href="zahlungsarten.php" class="value">{__('paymentTypesOverview')}</a></h4>
    </li>
    <li>
        <ul class="backend-search-section">
            {foreach $paymentMethods as $paymentMethod}
                <li class="backend-search-item" tabindex="-1">
                    <a href="zahlungsarten.php?kZahlungsart={$paymentMethod->kZahlungsart}&token={$smarty.session.jtl_token}" class="value">
                        <p>{$paymentMethod->cName}</p>
                    </a>
                </li>
            {/foreach}
        </ul>
    </li>
{/if}

{if $standalonePage}
    </ul>
    {include file='tpl_inc/footer.tpl'}
{/if}