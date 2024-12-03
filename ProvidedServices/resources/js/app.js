import './bootstrap';
import '../css/app.css';
import { createApp } from 'vue';
import ExampleComponent from './components/ExampleComponent.vue';
import LoginForm from './components/LoginForm.vue';
import Profile from './components/Profile.vue';
import JobPostForm from './components/JobPostForm.vue';
import Notification from './components/Notification.vue';
import DashboardComponent from './components/Dashboard.vue';
import axios from 'axios';

// Créer l'application Vue
const app = createApp({});

// Enregistrer les composants globalement
app.component('example-component', ExampleComponent);
app.component('login-form', LoginForm);
app.component('profile', Profile);
app.component('jobpost-form', JobPostForm);
app.component('notification', Notification);
app.component('dashboard-component', DashboardComponent);

// Vérification de l'authentification avant de monter l'application
app.mount('#app');

// Vérification de l'authentification lors du montage de l'application
if (window.location.pathname !== '/login') {
    axios.get('/api/auth-check')
        .then(response => {
            if (!response.data.authenticated) {
                // Si l'utilisateur n'est pas authentifié, rediriger vers la page de login
                window.location.href = '/login';
            }
        })
        .catch(error => {
            console.error('Error checking authentication:', error);
            window.location.href = '/login';  // En cas d'erreur, rediriger aussi
        });
}
