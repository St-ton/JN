{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="windows-1252">
        <meta name="robots" content="noindex,nofollow">
        <meta name="author" content="JTL-Software, www.jtl-software.de">
        <link rel="stylesheet" href="{$URL_SHOP}{$PFAD_ADMIN_TEMPLATE}css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="{$URL_SHOP}{$PFAD_ADMIN_TEMPLATE}css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="{$URL_SHOP}{$PFAD_ADMIN_TEMPLATE}css/custom.css" type="text/css">
        <link rel="stylesheet" href="{$URL_SHOP}install/template/css/style.css" type="text/css">
        <script src="{$URL_SHOP}{$PFAD_ADMIN_TEMPLATE}js/jquery-1.11.3.min.js" type="text/javascript"></script>
        <title>JTL-Shop4 Installation</title>
    </head>
    <body>
        {if $step === 'schritt2'}<div id="confetti" class="no-print"></div>{/if}
        <div id="content2" class="container">
            <div class="row header no-print">
                <div class="col-xs-12 col-md-12 col-lg-6">
                    <h1><img src="template/images/JTL-Shop.png" id="install-header-image" alt="JTL-Shop" /></h1>
                </div>
                <div class="col-xs-12 col-md-12 col-lg-6">
                    <h1>
                        <span class="steps-wrapper">
                            <span id="step1" class="step{if $step === 'schritt0'} active{/if}">1</span>
                            <span id="step2" class="step{if $step === 'schritt1'} active{/if}">2</span>
                            <span id="step3" class="step{if $step === 'schritt2'} active{/if}">3</span>
                        </span>
                    </h1>
                </div>
                <div class="col-xs-12">
                    <h3 class="step-header">
                    {if $step === 'schritt0'}
                        System-Check
                    {elseif $step === 'schritt1'}
                        Benutzerdaten
                    {elseif $step === 'schritt2'}
                        Installation abgeschlossen
                    {/if}
                    </h3>
                </div>
            </div>