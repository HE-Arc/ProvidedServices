<template>
    <div class="header">
      <div class="nav">
        <!-- Boutons de navigation vers Accueil et Dashboard -->
        <ul class="nav-list">
          <li @click="goHome">Accueil</li>
          <li @click="goDashboard">Dashboard</li>
        </ul>
      </div>

      <div v-if="user" class="profile-menu">
        <div class="profile-picture" id="smaller-profile-div" @click="toggleDropdown">
          <img v-if="user.picture" :src="'/storage/' + user.picture" alt="Profile Picture" />
          <img v-else src="/storage/images/default-avatar.png" alt="Default Avatar" />
        </div>
        <div v-if="dropdownOpen" class="dropdown">
          <ul>
            <li @click="goToProfile(user.id)">Profile</li>
            <li @click="logout">Logout</li>
          </ul>
        </div>
      </div>
    </div>
  </template>

  <script>
  import axios from 'axios';

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
    methods: {
      toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
      },
      goHome() {
        // Rediriger vers la page d'accueil
        window.location.href = '/';
      },
      goDashboard() {
        // Rediriger vers la page du tableau de bord
        window.location.href = '/dashboard';
      },
      goToProfile(userId) {
        // Rediriger vers la page de profil de l'utilisateur
        window.location.href = `/profile/${userId}`;
      },
      logout() {
        axios.post('/logout')
          .then(response => {
            this.$emit('user-logout'); // Émettre un événement après la déconnexion
            window.location.href = '/login';
          })
          .catch(error => {
            console.error('Error during logout:', error);
          });
      }
    }
  };
  </script>

  <style scoped>
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0px;
    margin: 0px;
    background-color: #004080;
    border-bottom: 1px solid #004080;
  }

  .nav {
    display: flex;
  }

  .nav-list {
    list-style-type: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 20px;
  }

  .nav-list li {
    cursor: pointer;
    padding: 15px;
    font-weight: bold;
    color: #FFFFFF;
  }

  .nav-list li:hover {
    text-decoration: underline;
  }

  .profile-menu {
    position: relative;
  }

  </style>
