<template>
    <div>
      <h1 v-if="user">Hello, {{ user.first_name }} {{ user.last_name }}</h1>
      <h1 v-else>Loading...</h1>
  
      <!-- Bouton Logout -->
      <button v-if="user" @click="logout">Logout</button>
  
      <!-- Bouton View Profile -->
      <button v-if="user" @click="goToProfile(user.id)">View Profile</button>
  
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        user: null
      };
    },
    mounted() {
      this.fetchUser();
    },
    methods: {
      fetchUser() {
        axios.get('/api/user')
          .then(response => {
            this.user = response.data;
          })
          .catch(error => {
            console.error('Error fetching user data:', error);
          });
      },
      logout() {
        axios.post('/logout')
          .then(response => {
            // Optionnel : Redirige ou affiche un message après la déconnexion
            this.user = null; // Réinitialise l'utilisateur
            alert(response.data.message); // Message de succès
            window.location.href = '/login'; // Redirige vers la page de connexion
          })
          .catch(error => {
            console.error('Error during logout:', error);
          });
      },
      goToProfile(userId) {
        // Redirige vers la page de profil de l'utilisateur
        window.location.href = `/profile/${userId}`;
      },
    }
  };
  </script>
  
  <style scoped>
  /* Style optionnel */
  </style>
  