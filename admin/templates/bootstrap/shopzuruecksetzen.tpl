{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="shopzuruecksetzen"}
{include file='tpl_inc/seite_header.tpl' cTitel=#shopReset# cBeschreibung=#shopResetDesc# cDokuURL=#shopResetURL#}
{literal}
    <script>
        $(document).ready(function(){
            $('input[type="checkbox"]').change(function(){
                var itemsChecked = '';
                $('input[type="checkbox"]:checked').next().each(function(i){
                    itemsChecked += $(this).prev().val();
                });
                if (itemsChecked === 'artikel' || itemsChecked === '') {
                    $('#warningZuruecksetzen, #messageDataGetsLost').addClass('hide');
                    $('button[data-target=".zuruecksetzen-modal"]').prop('disabled', itemsChecked === '');
                    $('#backupDone').closest('div.checkbox').addClass('hide');
                } else {
                    $('#warningZuruecksetzen, #messageDataGetsLost').removeClass('hide');
                    $('#backupDone').closest('div.checkbox').removeClass('hide');
                    $('button[data-target=".zuruecksetzen-modal"]').prop('disabled', !$("#backupDone").is(':checked'));
                }
            });
            $('#backupDone').change(function(){
                if (this.checked) {
                    $('button[data-target=".zuruecksetzen-modal"]').prop('disabled', false);
                } else {
                    $('button[data-target=".zuruecksetzen-modal"]').prop('disabled', true);
                }
            });
            $('#submitZuruecksetzen').click(function(){
                $('#formZuruecksetzen').submit();
            });
            $('button[data-target=".zuruecksetzen-modal"]').click(function(){
                var itemsToDelete = '';
                $('input[type="checkbox"]:checked').next().each(function(i){
                    itemsToDelete += '<li class="list-group-item list-group-item-warning">' + $(this).text() + '</li>';
                });
                $('.zuruecksetzen-modal .modal-body').html('<ul class="list-group">' + itemsToDelete + '</ul>');
            });
        });
    </script>
{/literal}
<div id="warningZuruecksetzen" class="alert alert-warning hide" >
    <h3>!!! ACHTUNG !!!</h3>
    <p>Es wurden Daten zur L&ouml;schung ausgew&auml;hlt die NICHT durch einen Abgleich mit der JTL-Wawi wiederhergestellt werden k&ouml;nnen.
        Es wird daher dringend empfohlen ein Backup der Shop-Datenbank zu erstellen!</p>
</div>
<div id="content" class="container-fluid settings">
    <form id="formZuruecksetzen" name="login" method="post" action="shopzuruecksetzen.php">
        {$jtl_token}
        <input type="hidden" name="zuruecksetzen" value="1" />

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Shopinhalte</h3>
            </div>
            <div class="panel-body">
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="artikel" tabindex="3" id="Artikel" />
                    <label for="Artikel">Artikel, Kategorien, Merkmale l&ouml;schen (Komplett&uuml;bertragung aus JTL-Wawi f&uuml;llt diese Daten wieder auf)</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="news" tabindex="4" id="News" />
                    <label for="News">News l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="bestseller" tabindex="5" id="Bestseller" />
                    <label for="Bestseller">Bestseller l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="besucherstatistiken" tabindex="6" id="Besucherstatistiken" />
                    <label for="Besucherstatistiken">Besucherstatistiken l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="preisverlaeufe" tabindex="8" id="Preisverlaufe" />
                    <label for="Preisverlaufe">Preisverl&auml;ufe l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="umfragen" tabindex="9" id="Umfragen" />
                    <label for="Umfragen">Umfragen l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="verfuegbarkeitsbenachrichtigungen" tabindex="10" id="Verfugbarkeitsbenachrichtigungen" />
                    <label for="Verfugbarkeitsbenachrichtigungen">Verf&uuml;gbarkeitsbenachrichtigungen l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="revisions" tabindex="11" id="Revisions" />
                    <label for="Revisions">Revisionen l&ouml;schen</label>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Benutzergenerierte Inhalte</h3>
            </div>
            <div class="panel-body">
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="suchanfragen" tabindex="11" id="Suchanfragen" />
                    <label for="Suchanfragen">Suchanfragen l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="tags" tabindex="12" id="Tags" />
                    <label for="Tags">Tags l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="bewertungen" tabindex="13" id="Bewertungen" />
                    <label for="Bewertungen">Bewertungen l&ouml;schen</label>
                </div>
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Shopkunden, Bestellungen und Coupons</h3>
            </div>
            <div class="panel-body">
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="shopkunden" tabindex="14" id="Shopkunden" />
                    <label for="Shopkunden">Shopkunden l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="kwerbenk" tabindex="14" id="KwerbenK" />
                    <label for="KwerbenK">Daten zu „Kunden werben Kunden“ löschen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="bestellungen" tabindex="15" id="Bestellungen" />
                    <label for="Bestellungen">Bestellungen l&ouml;schen</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="kupons" tabindex="15" id="Kupons" />
                    <label for="Kupons">Coupons l&ouml;schen</label>
                </div>
            </div>
        </div>
        <div class="save_wrapper">
            <div class="checkbox hide">
                <label><input id="backupDone" type="checkbox" value="" />Ja, ich habe ein Backup meiner Shop-Datenbank erstellt.</label>
            </div>
            <button disabled="true" type="button" value="{#shopReset#}" data-toggle="modal" data-target=".zuruecksetzen-modal" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> {#shopReset#}</button>
        </div>
    </form>
</div>
<div class="modal zuruecksetzen-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Es werden folgende Bereiche von JTL-Shop zur&uuml;ckgesetzt<span id="messageDataGetsLost" class="hide">, das heißt, dass alle bisher gespeicherten Daten verloren gehen:</span></h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <p>M&ouml;chten Sie fortfahren?</p>
                <button type="button" id="submitZuruecksetzen" class="btn btn-danger">Shopdaten zur&uuml;cksetzen</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Abbrechen</button>
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}