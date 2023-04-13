import moment from "../../../includes/node_modules/moment/dist/moment.js";
import {sleep} from "./utils.js";
import {showError} from "./gui.js";

const localDateFormat = 'DD.MM.YYYY - HH:mm';
const internalDateFormat = 'YYYY-MM-DD HH:mm:ss';
const lockIntervalSeconds = 60;

export class Page
{
    constructor(io, {shopUrl, pageKey, pageUrl, messages})
    {
        this.io            = io;
        this.shopUrl       = shopUrl;
        this.key           = pageKey;
        this.url           = pageUrl;
        this.messages      = messages;
        this.keepLockAlive = true;
        this.fullUrl       = this.shopUrl + this.url;
    }

    async init()
    {
        await this.lock();
        await this.loadMetaData();
    }

    async lock(delayed = false)
    {
        if (delayed) {
            await sleep(1000 * lockIntervalSeconds);
        }

        if (this.keepLockAlive === false) {
            return;
        }

        let state = await this.io.lockDraft(this.key);

        if (state !== 0) {
            if (state === 1) {
                showError(this.messages.opcPageLocked);
            } else if (state === 2) {
                showError(this.messages.dbUpdateNeeded.replace(/%s/, this.shopUrl));
            }

            throw state;
        }

        this.lock(true);
    }

    async unlock()
    {
        this.keepLockAlive = false;
        await this.io.unlockDraft(this.key);
    }

    async getRevisionList()
    {
        return await this.io.getRevisionList(this.key);
    }

    async loadMetaData()
    {
        let pageData = await this.io.getDraft(this.key);

        this.id           = pageData.id;
        this.name         = pageData.name;
        this.publishFrom  = pageData.publishFrom ? this.decodeDate(pageData.publishFrom) : null;
        this.publishTo    = pageData.publishTo ? this.decodeDate(pageData.publishTo) : null;
        this.url          = pageData.url;
        this.lastModified = pageData.lastModified;
        this.fullUrl      = this.shopUrl + this.url;
    }

    async getPreview()
    {
        return await this.io.getDraftPreview(this.key);
    }

    async getRevisionPreview(revId)
    {
        if(revId === -1) {
            return await this.getPreviewFromWebStorage();
        } else if(revId === 0) {
            return await this.io.getDraftPreview(this.key);
        } else {
            return await this.io.getRevisionPreview(revId);
        }
    }

    async getPreviewFromData(data)
    {
        opc.emit('page.getPreviewFromData', data);
        return await this.io.createPagePreview({areas: data.areas});
    }

    async getPreviewFromJSON(json)
    {
        let data = JSON.parse(json);
        return await this.getPreviewFromData(data);
    }

    async getPreviewFromImport()
    {
        let file = await new Promise(res => {
            this.jq('<input type="file" accept=".json">')
                .on('change', e => res(e.target.files[0]))
                .click();
        });

        let json = await new Promise(res => {
            let importReader = new FileReader();
            importReader.onload = () => res(importReader.result);
            importReader.readAsText(file);
        });

        return await this.getPreviewFromJSON(json);
    }

    async getPreviewFromWebStorage()
    {
        let pageJson = window.localStorage.getItem(this.getStorageId());

        if (pageJson !== null) {
            return await this.getPreviewFromJSON(pageJson);
        }

        throw 'could not find locally stored draft data';
    }

    async publicate()
    {
        await this.io.publicateDraft({
            key: this.key,
            publishFrom: this.publishFrom ? this.encodeDate(this.publishFrom) : null,
            publishTo: this.publishTo ? this.encodeDate(this.publishTo) : null,
            name: this.name,
        });
    }

    async save()
    {
        await this.io.saveDraft(this.toJSON());
        this.lastModified = moment().format(internalDateFormat);
    }

    encodeDate(localDate)
    {
        return moment(localDate, localDateFormat).format(internalDateFormat);
    }

    decodeDate(internalDate)
    {
        return moment(internalDate, internalDateFormat).format(localDateFormat);
    }

    getStorageId()
    {
        return 'opcpage.' + this.key;
    }
}