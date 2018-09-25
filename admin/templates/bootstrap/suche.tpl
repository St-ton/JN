{strip}
    {if isset($settings)}
        {foreach $settings as $setting}
            <li>
                <h4>{$setting->cName} <small>{$setting->cSektionsPfad}</small></h4>
            </li>
            <li>
                <ul class="backend-search-section">
                    {foreach $setting->oEinstellung_arr as $s}
                        <li class="backend-search-item">
                            <a href="einstellungen.php?cSuche={$s->kEinstellungenConf}&einstellungen_suchen=1"
                               class="value">
                                <span>{$s->cName} (Einstellungsnr.: {$s->kEinstellungenConf})</span>
                                <small>{$s->cBeschreibung}</small>
                            </a>
                        </li>
                    {/foreach}
                </ul>
            </li>
        {/foreach}
    {elseif isset($shippings)}
        <li>
            <h4><a href="versandarten.php" class="value">Versandartenübersicht</a></h4>
        </li>
        <li>
            <ul class="backend-search-section">
                {foreach $shippings as $shipping}
                    <li class="backend-search-item">
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
            <h4><a href="zahlungsarten.php" class="value">Zahlungsartenübersicht</a></h4>
        </li>
        <li>
            <ul class="backend-search-section">
                {foreach $paymentMethods as $paymentMethod}
                    <li class="backend-search-item">
                        <a href="zahlungsarten.php?kZahlungsart={$paymentMethod->kZahlungsart}&token={$smarty.session.jtl_token}" class="value">
                            <p>{$paymentMethod->cName}</p>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/if}
{/strip}