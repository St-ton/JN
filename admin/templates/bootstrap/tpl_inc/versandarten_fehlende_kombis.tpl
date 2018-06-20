{config_load file="$lang.conf" section="versandarten"}

{if !empty($missingShippingClassCombis) && $missingShippingClassCombis === -1}
    <p>
        {#coverageShippingClassCombination#}
        {#noShipClassCombiValidation#|replace:'%s':$smarty.const.SHIP_CLASS_MAX_VALIDATION_COUNT}
    </p>
{/if}
{if !empty($missingShippingClassCombis) && $missingShippingClassCombis !== -1}
    <p>{#coverageShippingClassCombination#}</p>
    <button class="btn btn-warning" type="button" data-toggle="collapse" data-target="#collapseShippingClasses" aria-expanded="false" aria-controls="collapseShippingClasses">
        {#showMissingCombinations#}
    </button>
    <div class="collapse" id="collapseShippingClasses">
        <div class="row">
            {foreach $missingShippingClassCombis as $mscc}
                <div class="col-xs-12 col-sm-6">{$mscc}</div>
            {/foreach}
        </div>
    </div>
{/if}
