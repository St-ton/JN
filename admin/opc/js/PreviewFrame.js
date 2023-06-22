export class PreviewFrame
{
    init()
    {
        installGuiElements(this, [
            'previewPanel',
            'previewFrame',
        ]);
    }

    showPreview(pageFullUrl, draftData)
    {
        window.previewPageDataInput.value = draftData;
        window.previewForm.action = pageFullUrl;
        window.previewForm.submit();

        this.previewFrame
            .contents().find('body').html('');

        this.previewPanel.show();
    }
}
