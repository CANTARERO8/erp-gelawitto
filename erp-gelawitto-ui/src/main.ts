import './css/app.css';
import { createApp } from 'vue';
import App from './App.vue';
import { spaRouter } from './router';

createApp(App)
    .use(spaRouter)
    .mount('#app');
