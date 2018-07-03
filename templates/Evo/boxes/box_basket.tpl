<section class="panel panel-default box box-basket" id="sidebox{$oBox->getID()}">
    <div class="panel-heading">
        <div class="panel-title">{lang key='yourBasket'}<span id="basket_loader"></span></div>
    </div>
    <div class="box-body panel-body text-center">
        <a href="{get_static_route id='warenkorb.php'}" class="basket {if $WarenkorbArtikelanzahl > 0}pushed{/if}" id="basket_drag_area">
            <span id="basket_text">{$Warenkorbtext}</span><br>
            <span class="basket_link">{lang key='gotoBasket'}</span>
        </a>
    </div>
</section>