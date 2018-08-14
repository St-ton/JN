<div class="text-center" {$instance->getAttributeString()} {$instance->getDataAttributeString()} >
    {*<img src="{$instance->getProperty('src')}" class="{$instance->getAttribute('class')}"*}
         {*style="width: 98%;filter: grayscale(50%) opacity(60%)">*}
    {*<p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -56px;">Banner</p>*}
    <img {$instance->getImageAttributeString(null, null, null, 1, $portlet->getPlaceholderImgUrl())}
        class="{$instance->getAttribute('class')}"
        style="width: 98%;filter: grayscale(50%) opacity(60%)">
    <p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -56px;">Banner</p>
</div>