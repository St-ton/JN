import { mapGetters } from 'vuex';
import store from './store';
import Continue from '../components/Continue';
import Jumbotron from '../components/Jumbotron';

export default {
    data() {
        return {
            isteps: [
                'step1',
                'step2',
                'step3',
                'step4',
                'step5',
                'step6',
                'step7',
                'step8'
            ]
        };
    },
    components: {
        Continue,
        Jumbotron
    },
    methods: {
        setStep(step) {
            store.commit('setStep', step);
            store.commit('setProgress', step / (this.isteps.length - 1) * 100);
        }
    },
    computed: mapGetters({
        istep:           'getStep',
        installProgress: 'getProgress'
    })
};
