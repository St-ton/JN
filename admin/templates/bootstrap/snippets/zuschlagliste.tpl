
<div class="col-md-5 text-right">Zuschlagliste:</div>
<div class="col-md-7 text-left">{$zuschlagliste->getTitle()}</div>
<div class="col-md-5 text-right">Zuschlag:</div>
<div class="col-md-7 text-left">{$zuschlagliste->getSurcharge()}</div>
<div class="col-md-5 text-right">PLZ:</div>
{*{$zuschlagliste|var_dump}*}
<div class="col-md-7 text-left">
    {foreach $zuschlagliste->getZIPCodes() as $zipCode}{$zipCode}{if !$zipCode@last} ,{/if}{/foreach}
    {foreach $zuschlagliste->getZIPAreas() as $zipArea}{$zipArea->getArea()}{if !$zipArea@last} ,{/if}{/foreach}
</div>