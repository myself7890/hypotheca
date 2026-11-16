import { createApp } from 'vue';
import App from './App.vue';
import './styles/main.css';

const el = document.getElementById('app');
const page = JSON.parse(el.dataset.page);

createApp(App, { page }).mount(el);
