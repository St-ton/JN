{include file='tpl_inc/seite_header.tpl' cTitel=#coupons# cBeschreibung=#couponsDesc# cDokuURL=#couponsURL#}

<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if $tab == 'standard'} active{/if}">
            <a data-toggle="tab" role="tab" href="#standard" aria-expanded="true">{#standardCoupon#}s</a>
        </li>
        <li class="tab{if $tab == 'versandkupon'} active{/if}">
            <a data-toggle="tab" role="tab" href="#versandkupon" aria-expanded="true">{#shippingCoupon#}s</a>
        </li>
        <li class="tab{if $tab == 'neukundenkupon'} active{/if}">
            <a data-toggle="tab" role="tab" href="#neukundenkupon" aria-expanded="true">{#newCustomerCoupon#}s</a>
        </li>
    </ul>
    <div class="tab-content">
        {include file='tpl_inc/kupons_uebersicht_tab.tpl' cKuponTyp='standard' cKuponTypName=#standardCoupon# oKupon_arr=$oKuponStandard_arr}
        {include file='tpl_inc/kupons_uebersicht_tab.tpl' cKuponTyp='versandkupon' cKuponTypName=#shippingCoupon# oKupon_arr=$oKuponVersandkupon_arr}
        {include file='tpl_inc/kupons_uebersicht_tab.tpl' cKuponTyp='neukundenkupon' cKuponTypName=#newCustomerCoupon# oKupon_arr=$oKuponNeukundenkupon_arr}
    </div>
</div>