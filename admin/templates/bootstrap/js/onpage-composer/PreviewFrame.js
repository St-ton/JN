/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class PreviewFrame
{
    init()
    {
        installGuiElements(this, [
            'previewPanel',
            'previewFrame',
            'previewForm',
            'previewPageDataInput',
        ]);
    }

    showPreview(pageFullUrl, draftData, onload)
    {
        this.previewPageDataInput
            .val(draftData);

        this.previewForm
            .attr('action', pageFullUrl)
            .submit();

        this.previewFrame
            .off('load')
            .on('load', () => {
                onload();
            });
    }

    onFrameLoad()
    {
        console.log('preview frame has loaded');
    }
}