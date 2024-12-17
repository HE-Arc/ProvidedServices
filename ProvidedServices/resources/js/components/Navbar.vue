<template>
  <div class="header">
    <Notification ref="notification" />
      <div class="nav">
          <!-- Navigation Accueil et Tableau de Bord -->
          <ul class="nav-list">
              <li @click="goHome">Accueil</li>
              <li @click="goDashboard">Tableau de Bord</li>
          </ul>
      </div>

      <div v-if="user" class="profile-menu">
          <div class="profile-picture" id="smaller-profile-div" @click="toggleDropdown">
              <img v-if="user.picture" :src="'/storage/' + user.picture" alt="Photo de profil" />
              <img v-else src="/storage/images/default-avatar.png" alt="Avatar par défaut" />
          </div>
          <div v-if="dropdownOpen" class="dropdown">
              <ul>
                  <li @click="goToProfile(user.id)">Profil</li>
                  <li @click="logout">Déconnexion</li>
              </ul>
          </div>
      </div>
  </div>
</template>

<script>
import axios from 'axios';
import Notification from './Notification.vue';

export default {
  props: {
      user: {
          type: Object,
          required: true
      }
  },
  data() {
      return {
          dropdownOpen: false
      };
  },
  components: {
      Notification
  },
  methods: {
      toggleDropdown() {
          this.dropdownOpen = !this.dropdownOpen;
      },
      goHome() {
          window.location.href = '/';
      },
      goDashboard() {
          window.location.href = '/dashboard';
      },
      goToProfile(userId) {
          window.location.href = `/profile/${userId}`;
      },
      logout() {
          axios.post('/logout')
              .then(response => {
                  this.$refs.notification.showNotification('Déconnexion réussie.', 'success');
                  setTimeout(() => {
                      window.location.href = '/login';
                  }, 1000);
              })
              .catch(error => {
                  const errorMessage = error.response?.data?.message || 'Une erreur inattendue est survenue lors de la déconnexion.';
                  this.$refs.notification.showNotification(errorMessage, 'error');
              });
      }
  }
};
</script>

<style scoped>
</style>
