import {mapGetters} from 'vuex';
import store from './store';
import Continue from '../components/Continue';
import Jumbotron from '../components/Jumbotron';

export default {
    data() {
        return {
            steps: [
                'Hallo',
                'Vorige Installation prüfen',
                'Dateirechte',
                'Systemcheck',
                'Datenbankdaten',
                'Adminnutzer',
                'Schema',
                'Abschluss',
                'Wawi-Abgleich',
                'Globale Einstellungen',
                'Formulare',
                'Weiterführende Links'
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
            store.commit('setProgress', step / (this.steps.length - 1) * 100);
        }
    },
    computed: mapGetters({
        step:            'getStep',
        installProgress: 'getProgress'
    })
};
