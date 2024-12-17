<template>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <Notification ref="notification" />

                <div v-if="isRegistering">
                    <h2 class="text-center">Créer un compte</h2>
                    <form @submit.prevent="register">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input v-model="form.email" type="email" class="form-control" id="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="first_name" class="form-label">Prénom</label>
                            <input v-model="form.first_name" type="text" class="form-control" id="first_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Nom</label>
                            <input v-model="form.last_name" type="text" class="form-control" id="last_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="gender" class="form-label">Genre</label>
                            <select v-model="form.gender" class="form-control" id="gender" required>
                                <option value="male">Homme</option>
                                <option value="female">Femme</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <select v-model="form.role" class="form-control" id="role" required>
                                <option value="client">Client</option>
                                <option value="provider">Prestataire</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input v-model="form.password" type="password" class="form-control" id="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmez le mot de passe</label>
                            <input v-model="form.password_confirmation" type="password" class="form-control" id="password_confirmation" required>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="auth-btn">S'inscrire</button>
                        </div>

                        <p class="text-center">
                            Vous avez déjà un compte ? <a href="#" @click.prevent="isRegistering = false">Connectez-vous ici</a>
                        </p>
                    </form>
                </div>

                <div v-else>
                    <h2 class="text-center">Connexion</h2>
                    <form @submit.prevent="login">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input v-model="form.email" type="email" class="form-control" id="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input v-model="form.password" type="password" class="form-control" id="password" required>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="auth-btn">Se connecter</button>
                        </div>

                        <p class="text-center">
                            Vous n'avez pas de compte ? <a href="#" @click.prevent="isRegistering = true">Créez-en un ici</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import Notification from './Notification.vue';

export default {
    components: {
        Notification 
    },
    data() {
        return {
            isRegistering: false,
            form: {
                email: '',
                first_name: '',
                last_name: '',
                gender: '',
                role: 'client',
                password: '',
                password_confirmation: ''
            }
        };
    },
    methods: {
        login() {
            axios.post('/api/login', {
                email: this.form.email,
                password: this.form.password
            })
            .then(() => {
                this.$refs.notification.showNotification('Connexion réussie.', 'success');
                setTimeout(() => {
                    window.location.href = '/';
                }, 1000);
            })
            .catch(error => {
                const message = error.response?.data?.message || 'Une erreur inconnue est survenue.';
                this.$refs.notification.showNotification(`Erreur de connexion : ${message}`, 'error');
            });
        },
        register() {
            if (this.form.password !== this.form.password_confirmation) {
                this.$refs.notification.showNotification('Les mots de passe ne correspondent pas.', 'error');
                return;
            }

            axios.post('/api/register', this.form)
            .then(() => {
                this.$refs.notification.showNotification('Inscription réussie. Vous allez être connecté.', 'success');
                this.login();
            })
            .catch(error => {
                if (error.response?.data?.errors) {
                    const errors = error.response.data.errors;
                    let errorMessage = 'Erreurs rencontrées :\n';
                    for (const field in errors) {
                        errorMessage += `${field} : ${errors[field].join(', ')}\n`;
                    }
                    this.$refs.notification.showNotification(errorMessage, 'error');
                } else {
                    const message = error.response?.data?.message || 'Une erreur inconnue est survenue.';
                    this.$refs.notification.showNotification(`Erreur lors de l'inscription : ${message}`, 'error');
                }
            });
        }
    }
};
</script>

<style scoped>
</style>
