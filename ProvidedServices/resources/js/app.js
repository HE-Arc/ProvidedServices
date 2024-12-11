import './bootstrap';
import '../css/app.css';
import { createApp } from 'vue';
import ExampleComponent from './components/ExampleComponent.vue';
import LoginForm from './components/LoginForm.vue';
import Profile from './components/Profile.vue';
import axios from 'axios';
import JobPostForm from './components/JobPostForm.vue';
import Notification from './components/Notification.vue';
import DashboardComponent from './components/Dashboard.vue';
import Navbar from './components/Navbar.vue';

const app = createApp({
    mounted() {
        // Vérifie si l'utilisateur est sur la page de login
        if (window.location.pathname !== '/login') {
            // Vérification de l'authentification lors du montage de l'application
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
    }
});

app.component('example-component', ExampleComponent);
app.component('login-form', LoginForm);
app.component('profile', Profile);
app.component('jobpost-form', JobPostForm);
app.component('notification', Notification);
app.component('dashboard-component', DashboardComponent);
app.component('Navbar', Navbar);
app.mount('#app');
