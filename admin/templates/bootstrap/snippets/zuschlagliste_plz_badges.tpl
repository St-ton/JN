<div id="zip-badge-{$zuschlagliste->getID()}">
    {foreach $zuschlagliste->getZIPCodes() as $zipCode}
        <button class="badge zip-badge" data-surcharge-id="{$zuschlagliste->getID()}" data-zip="{$zipCode}">
            {$zipCode} &times;
        </button>
    {/foreach}
    {foreach $zuschlagliste->getZIPAreas() as $zipArea}
        <button class="badge zip-badge" data-surcharge-id="{$zuschlagliste->getID()}" data-zip="{$zipArea->getZIPFrom()}-{$zipArea->getZIPTo()}">
            {$zipArea->getArea()} &times;
        </button>
    {/foreach}
</div>