export default VueInstance => {
    VueInstance.prototype.$addClasses = conf => {
        conf.result = parseInt(conf.result, 10);
        if (conf.result === 0) {
            conf.className = 'badge-success';
            conf.icon = 'check';
        } else if (conf.result === 1) {
            if (conf.isOptional === true) {
                if (conf.isRecommended === true) {
                    conf.className = 'badge-warning';
                    conf.icon = 'exclamation-triangle';
                } else {
                    conf.className = 'badge-primary';
                    conf.icon = 'times';
                }
            } else {
                conf.result = 2;
                conf.className = 'badge-danger';
                conf.icon = 'exclamation-triangle';
            }
        } else {
            conf.className = 'badge-danger';
            conf.icon = 'times';
        }
        return conf;
    };
    VueInstance.prototype.$getTotalResultCode = (acc, val) => {
        if (val.result === 1 && acc < 2) {
            return 1;
        }
        return val.result > acc ? val.result : acc;
    };
    /* eslint-disable no-process-env */
    VueInstance.prototype.$getApiUrl = task => {
        const base = process.env.NODE_ENV === 'production'
            ? ''
            : 'http://felix.vm0.halle/install/';
        return `${base}install.php?task=${task}&t=${new Date().getTime()}`;
    };
};
