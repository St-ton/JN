{strip}
<div class="grid">
{foreach $settings as $setting}
    <div class="grid-item">  
        <h2>{$setting->cName} <small>{$setting->cSektionsPfad}</small></h2>
        <ul>
        {foreach $setting->oEinstellung_arr as $s}
            <li>
                <a href="einstellungen.php?cSuche={$s->kEinstellungenConf}&einstellungen_suchen=1" class="value">
                    <p>{$s->cName}</p>
                    <small>{$s->cBeschreibung}</small>
                </a>
            </dt>
        {/foreach}
        </ul>
    </div>
{/foreach}
</div>
{/strip}