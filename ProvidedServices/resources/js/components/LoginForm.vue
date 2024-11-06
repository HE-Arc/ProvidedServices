<template>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Notification pop-up -->
                <div v-if="notification.show" :class="['notification', notification.type]" class="fade-in-out">
                    {{ notification.message }}
                </div>

                <div v-if="isRegistering">
                    <h2 class="text-center">Create an Account</h2>
                    <form @submit.prevent="register">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input v-model="form.email" type="email" class="form-control" required>
                        </div>

                        <!-- Prénom -->
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input v-model="form.first_name" type="text" class="form-control" required>
                        </div>

                        <!-- Nom -->
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input v-model="form.last_name" type="text" class="form-control" required>
                        </div>

                        <!-- Genre -->
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select v-model="form.gender" class="form-control" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>

                        <!-- Rôle -->
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select v-model="form.role" class="form-control" required>
                                <option value="client">Client</option>
                                <option value="prestataire">Provider</option>
                            </select>
                        </div>

                        <!-- Mot de passe -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input v-model="form.password" type="password" class="form-control" required>
                        </div>

                        <!-- Confirmation mot de passe -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input v-model="form.password_confirmation" type="password" class="form-control" required>
                        </div>

                        <!-- Bouton d'inscription -->
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </div>

                        <!-- Bouton pour basculer vers la connexion -->
                        <p class="text-center">
                            Already have an account? <a href="#" @click.prevent="isRegistering = false">Login here</a>
                        </p>
                    </form>
                </div>

                <div v-else>
                    <h2 class="text-center">Login</h2>
                    <form @submit.prevent="login">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input v-model="form.email" type="email" class="form-control" required>
                        </div>

                        <!-- Mot de passe -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input v-model="form.password" type="password" class="form-control" required>
                        </div>

                        <!-- Bouton connexion -->
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </div>

                        <!-- Lien pour s'inscrire -->
                        <p class="text-center">
                            Don't have an account? <a href="#" @click.prevent="isRegistering = true">Create one here</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            isRegistering: false, // Change entre login et inscription
            form: {
                email: '',
                first_name: '',
                last_name: '',
                gender: '',
                role: 'client',
                password: '',
                password_confirmation: ''
            },
            notification: {
                show: false,
                message: '',
                type: '' // 'success' or 'error'
            }
        };
    },
    methods: {
        showNotification(message, type) {
            this.notification.message = message;
            this.notification.type = type;
            this.notification.show = true;

            setTimeout(() => {
                this.notification.show = false;
            }, 5000); // Hide after 5 seconds
        },
        login() {
            axios.post('/api/login', {
                email: this.form.email,
                password: this.form.password,
            })
            .then(response => {
                this.showNotification('Login successful', 'success');
                setTimeout(() => {
                    window.location.href = '/';
                }, 1000); // Redirect after 1 second
            })
            .catch(error => {
                if (error.response && error.response.data.message) {
                    // Afficher l'erreur spécifique renvoyée par le backend
                    this.showNotification(error.response.data.message, 'error');
                } else {
                    this.showNotification('An unknown error occurred', 'error');
                }
            });
        },
        register() {
            axios.post('/api/register', this.form)
            .then(response => {
                this.showNotification('Registration successful', 'success');
                this.isRegistering = false;
            })
            .catch(error => {
                if (error.response && error.response.data.errors) {
                    // Afficher les erreurs de validation spécifiques
                    const errors = error.response.data.errors;
                    let errorMessage = 'Please fix the following errors:\n';
                    for (const field in errors) {
                        errorMessage += `${field}: ${errors[field].join(', ')}\n`;
                    }
                    this.showNotification(errorMessage, 'error');
                } else {
                    this.showNotification('Error during registration', 'error');
                }
            });
        }
    }
};
</script>

<style scoped>
/* Notification Styles */
</style>
