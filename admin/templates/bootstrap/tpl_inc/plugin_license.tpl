{literal}
<style>
    div.markdown {
        padding: 0px 10px;
    }
    div.markdown ul li {
        list-style: outside none disc;
    }
    div.markdown ol li {
        list-style: outside none decimal;
    }
    div.markdown p {
        text-align: justify;
    }
    div.markdown blockquote {
        font-size: inherit;
    }
    div.markdown-wrapper {
        padding: 5px 40px 30px;
    }
    pre {
        overflow-wrap: break-word;
        white-space: pre-line;
        word-break: unset;
    }
</style>
{/literal}
<div class="panel panel-default">
    <div class="markdown-wrapper">
        {if $fMarkDown === true}
            <div class="markdown">
                {$szLicenseContent}
            </div>
        {else}
            <br>
            <pre>{$szLicenseContent}</pre>
        {/if}
    </div>
</div>
