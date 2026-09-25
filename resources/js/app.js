import './bootstrap';

import { createApp } from 'vue';
import GasTracker from './components/GasTracker.vue';

const appElement = document.getElementById('app');

if (appElement) {
    createApp(GasTracker).mount(appElement);
}
