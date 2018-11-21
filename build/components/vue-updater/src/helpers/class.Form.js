import Errors from './class.Errors';
import axios from 'axios';

export default class Form {
    /**
     * Create a new Form instance.
     *
     * @param data
     */
    constructor(data) {
        this.originalData = data;

        for (let field in data) this[field] = data[field];

        this.errors = new Errors();
    }

    /**
     * Add a new field to the form.
     *
     * @param field
     */
    add(field) {
        this.originalData[field] = field;
        this[field] = field;
    }

    /**
     * Fetch all relevant data for the form.
     *
     * @returns {*}
     */
    data() {
        let data = {};

        for (let property in this.originalData) {
            data[property] = this[property] ;
        }

        return data;
    }

    /**
     * Reset the form fields.
     */
    reset() {
        for (let field in this.originalData) {
            if (this.hasOwnProperty(field)) this[field] = this.originalData[field];
        }

        this.errors.clear();
    }

    /**
     * Creates the axios get call.
     *
     * @param url
     * @returns {*}
     */
    axiosGet(url) {
        return this.submit('get', url);
    }

    /**
     * Creates the axios post call.
     *
     * @param url
     * @returns {*}
     */
    axiosPost(url) {
        return this.submit('post', url);
    }

    /**
     * Creates the axios delete call.
     *
     * @param url
     * @returns {*}
     */
    axiosDelete(url) {
        return this.submit('delete', url);
    }

    /**
     * Submit the form.
     *
     * @param requestType
     * @param url
     */
    submit(requestType, url) {
        return new Promise((resolve, reject) => {
            axios[requestType](url, this.data())
                .then(response => {
                    resolve(response.data);
                })
                .catch(error => {
                    this.onFail(error.response.data);
                    reject(error.response.data);
                });
        });
    }

    /**
     * Handle a successful form submission.
     *
     * @param data
     */
    onSuccess(data) {
        this.reset();
    }

    /**
     * Handle a failed form submission.
     *
     * @param errors
     */
    onFail(errors) {
        this.errors.record(errors);
    }
}