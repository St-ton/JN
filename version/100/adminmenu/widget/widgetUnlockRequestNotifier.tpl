<div class="widget-custom-data">
    <h5>Bewertungen</h5>
    <ul>
        {foreach from=$oBewertung_arr item=b}
            <li>{$b->cTitel}</li>
        {/foreach}
    </ul>
</div>