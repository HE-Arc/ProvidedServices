<template>
  <div>
    <h1 v-if="user">Hello, {{ user.first_name }} {{ user.last_name }}</h1>
    <h1 v-else>Loading...</h1>

    <!-- Notification pop-up -->
    <Notification ref="notification"/>

    <!-- Bouton Logout -->
    <button v-if="user" @click="logout">Logout</button>

    <!-- Bouton View Profile -->
    <button v-if="user" @click="goToProfile(user.id)">View Profile</button>

    <!-- Bouton Create Offer, visible uniquement si l'utilisateur est un client -->
    <button v-if="user && user.role === 'client'" @click="createOffer">Create Offer</button>
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
      user: null,
      notification: {
        show: false,
        message: '',
        type: '' // 'success' or 'error'
      }
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
          this.$refs.notification.showNotification('Error fetching user data', 'error');
          console.error('Error fetching user data:', error);
        });
    },
    logout() {
      axios.post('/logout')
        .then(response => {
          // Réinitialise l'utilisateur et affiche une notification
          this.user = null;
          this.$refs.notification.showNotification(response.data.message, 'success');
          setTimeout(() => {
            window.location.href = '/login'; // Redirige vers la page de connexion
          }, 1000); // Redirige après 1 seconde
        })
        .catch(error => {
          this.$refs.notification.showNotification('Error during logout', 'error');
          console.error('Error during logout:', error);
        });
    },
    goToProfile(userId) {
      window.location.href = `/profile/${userId}`;
    },
    createOffer() {
      window.location.href = '/create-offer'; // Changez l'URL si nécessaire
    }
  }
};
</script>

<style scoped>

</style>
