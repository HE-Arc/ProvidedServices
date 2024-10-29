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
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Rôle -->
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select v-model="form.role" class="form-control" required>
                                <option value="client">Client</option>
                                <option value="prestataire">Prestataire</option>
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
                this.showNotification('Invalid credentials', 'error');
            });
        },
        register() {
            axios.post('/api/register', this.form)
            .then(response => {
                this.showNotification('Registration successful', 'success');
                this.isRegistering = false;
            })
            .catch(error => {
                this.showNotification('Error during registration', 'error');
            });
        }
    }
};
</script>

<style scoped>
/* Notification Styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 5px;
    font-size: 1rem;
    z-index: 1000;
    opacity: 0;
    animation: fadeInOut 5s forwards;
}

.notification.success {
    background-color: #28a745;
    color: white;
}

.notification.error {
    background-color: #dc3545;
    color: white;
}

@keyframes fadeInOut {
    0% { opacity: 0; transform: translateY(-20px); }
    10% { opacity: 1; transform: translateY(0); }
    90% { opacity: 1; }
    100% { opacity: 0; transform: translateY(-20px); }
}

/* Form Container */
.container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    background-color: #f8f9fa;
}

/* Title */
h2 {
    margin-bottom: 20px;
    font-size: 1.8rem;
    color: #343a40;
    font-weight: bold;
}

/* Form group */
.mb-3 {
    margin-bottom: 15px;
    display: flex;
    flex-direction: column;
}

/* Labels */
.form-label {
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 1rem;
}

/* Input Fields */
.form-control {
    padding: 10px;
    font-size: 1rem;
    width: 100%;
    box-sizing: border-box;
}

/* Buttons */
.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    padding: 10px;
    font-size: 1rem;
    border-radius: 4px;
    width: 100%;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #004085;
}

/* Links */
a {
    color: #007bff;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Text Alignment */
.text-center {
    text-align: center;
}
</style>
