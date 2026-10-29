import './css/app.css';
import { createApp } from 'vue';
import App from './App.vue';
import { spaRouter } from './router';

const savedTheme = localStorage.getItem('gelawitto-theme');
if (savedTheme === 'dark') {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

createApp(App)
    .use(spaRouter)
    .mount('#app');
