{include file='tpl_inc/header.tpl'}

{*
{config_load file="$lang.conf" section="status"}
{include file='tpl_inc/seite_header.tpl' cTitel=#status# cDokuURL=#statusURL#}
*}

<script>
{literal}
$(function() {
    
});
{/literal}
</script>

<div id="content" class="container-fluid" style="padding-top: 10px;">
    <div class="row">

        <div class="col-md-4">
        
            {$cacheOptions = $objectCache->getOptions()}
        
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fa fa-thumbs-down" aria-hidden="true"></i> Caching</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <div class="text-center">
                                {if $objectCache->getResultCode() === 1}
                                    <i class="fa fa-check-circle text-success" style="font-size:4em"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">{$cacheOptions.method}</h3>
                                    <span style="color:#c7c7c7">System-Cache</span>
                                {else}
                                    <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                                {/if}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                Cache 2
                            </div>
                        </div>
                    </div>
                    
                    
                    
                </div>
            </div>
        
        </div>
        <div class="col-md-8">
        
            <div class="panel panel-default">
                <div class="panel-body">
                    Panel content
                </div>
            </div>
        
        </div>
    </div>
</div>