{*
-------------------------------------------------------------------------------
JTL-Shop 3
File: filecheck.tpl, smarty template inc file

page for JTL-Shop 3 
Admin

Author: daniel.boehmer@jtl-software.de, JTL-Software
http://www.jtl-software.de

Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file='tpl_inc/header.tpl'}

{config_load file="$lang.conf" section="dbcheck"}

{include file="tpl_inc/seite_header.tpl" cTitel=#dbcheck# cBeschreibung=#dbcheckDesc# cDokuURL=#dbcheckURL#}

<div id="content">

    <div id="pageCheck">

        {if isset($cDBFileStruct_arr) && $cDBFileStruct_arr|@count > 0}
            <div id="contentCheck">

                <p class="box_info"><strong>Anzahl Tabellen:</strong> {$cDBFileStruct_arr|@count}<br /><strong>Anzahl modifizierter Tabellen:</strong> {$cDBError_arr|@count}</p>

                {if $cDBError_arr|@count > 0}
                    <p>
                        <input name="viewAll" type="button" class="button viewAll hide" value="Alle anzeigen" />
                        <input name="viewModified" type="button" class="button viewModified" value="Modifizierte anzeigen" />
                    </p>
                    <br />
                {/if}
                <ul class="req">
                    {foreach name=datei from=$cDBFileStruct_arr key=cTable item=oDatei}
                        <li class="mod{$smarty.foreach.datei.iteration%2} {if !$cTable|array_key_exists:$cDBError_arr}unmodified{else}modified{/if}">{$cTable}

                            {if $cTable|array_key_exists:$cDBError_arr}
                                <span class="red">{$cDBError_arr[$cTable]}</span>
                            {else}
                                <span class="green">Ok</span>
                            {/if}

                        </li>
                    {/foreach}
                </ul>
            </div>
        {else}
            {if isset($cFehler) && $cFehler|count_characters > 0}
                <p class="box_error">{$cFehler}</p>
            {/if}
        {/if}   

    </div>

</div>


<script>
    {literal}
        $(document).ready(function () {

            $(".viewAll").click(function () {
                $(".viewAll").hide();
                $(".viewModified").show();
                $(".unmodified").show();
                $(".modified").show();
                colorLines();
            });

            $(".viewModified").click(function () {
                $(".viewAll").show();
                $(".viewModified").hide();
                $(".unmodified").hide();
                $(".modified").show();
                colorLines();
            });

            function colorLines() {
                var mod = 1;
                $(".req li:not(:hidden)").each(function () {
                    if (mod == 1) {
                        $(this).removeClass("mod0");
                        $(this).removeClass("mod1");
                        $(this).addClass("mod1");
                        mod = 0;
                    } else {
                        $(this).removeClass("mod1");
                        $(this).removeClass("mod0");
                        $(this).addClass("mod0");
                        mod = 1;
                    }
                });
            }
        });

    {/literal}
</script>
{include file='tpl_inc/footer.tpl'}