<template>
  <div>
    <Navbar v-if="user" :user="user" />

    <!-- Notification pop-up -->
    <Notification ref="notification" />

    <!-- Liste des offres d'emploi -->
    <div class="job-posts-header">
      <h2>Offres d'emploi</h2>
      <button v-if="user && user.role === 'client'" class="btn-add-job" @click="createOffer">
        <span>+</span>
      </button>
    </div>

    <div v-if="jobPosts.length > 0">
      <div v-for="job in jobPosts" :key="job.id" class="job-post">
        <h3>{{ job.title }}</h3>
        <p><strong>Description : </strong>{{ job.description }}</p>
        <p>
          <strong>Posté par : </strong>
          <a :href="`/profile/${job.client.id}`">
            {{ job.client.first_name }} {{ job.client.last_name }}
          </a>
        </p>
        <p>
          <strong>Compétences requises : </strong>
          <span v-for="(skill, index) in job.skills" :key="index" class="skill-bubble">{{ skill.name }}</span>
        </p>
        <div class="btn-container">
          <button
            v-if="user && user.role === 'provider'"
            @click="toggleApplication(job.id)"
            :class="isApplied(job.id) ? 'btn-unapply' : 'btn-apply'"
          >
            {{ isApplied(job.id) ? 'Annuler ma candidature' : 'Postuler' }}
          </button>
        </div>
      </div>
    </div>
    <div v-else>
      <h3>Aucune offre d'emploi disponible pour le moment.</h3>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Notification from './Notification.vue';
import Navbar from './Navbar.vue';

export default {
  components: {
    Notification,
    Navbar
  },
  data() {
    return {
      user: null,
      jobPosts: [],
      appliedJobs: [],
      dropdownOpen: false,
      user: null,
    };
  },
  async created() {
    await this.fetchUser();
  },
  mounted() {
    this.fetchJobPosts();
    document.addEventListener('click', this.handleOutsideClick);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.handleOutsideClick);
  },
  methods: {
    async fetchUser() {
      axios
        .get('/api/user')
        .then((response) => {
          this.user = response.data;
          if (this.user.role === 'provider') {
            this.fetchAppliedJobs();
          }
        })
        .catch((error) => {
          this.$refs.notification.showNotification(
            `Erreur lors de la récupération des données utilisateur : ${error.response?.data?.message || error.message}`,
            'error'
          );
        });
    },
    fetchJobPosts() {
      axios
        .get('/api/job-posts')
        .then((response) => {
          this.jobPosts = response.data;
        })
        .catch((error) => {
          this.$refs.notification.showNotification(
            `Erreur lors de la récupération des offres d'emploi : ${error.response?.data?.message || error.message}`,
            'error'
          );
        });
    },
    handleOutsideClick(event) {
      const dropdown = this.$el.querySelector('.dropdown');
      const profilePicture = this.$el.querySelector('.profile-picture');

      if (dropdown && !dropdown.contains(event.target) && !profilePicture.contains(event.target)) {
        this.dropdownOpen = false;
      }
    },
    fetchAppliedJobs() {
      axios
        .get('api/applied-jobs')
        .then((response) => {
          this.appliedJobs = response.data;
        })
        .catch((error) => {
          this.$refs.notification.showNotification(
            `Erreur lors de la récupération des candidatures : ${error.response?.data?.message || error.message}`,
            'error'
          );
        });
    },
    logout() {
      axios
        .post('/logout')
        .then((response) => {
          this.user = null;
          this.$refs.notification.showNotification(response.data.message || 'Déconnexion réussie.', 'success');
          setTimeout(() => {
            window.location.href = '/login';
          }, 1000);
        })
        .catch((error) => {
          this.$refs.notification.showNotification(
            `Erreur lors de la déconnexion : ${error.response?.data?.message || error.message}`,
            'error'
          );
        });
    },
    toggleDropdown() {
      this.dropdownOpen = !this.dropdownOpen;
    },
    goToProfile(userId) {
      window.location.href = `/profile/${userId}`;
    },
    createOffer() {
      window.location.href = '/create-offer';
    },
    toggleApplication(jobId) {
      if (this.isApplied(jobId)) {
        this.unapplyForJob(jobId);
      } else {
        this.applyForJob(jobId);
      }
    },
    applyForJob(jobId) {
      axios
        .post(`/api/job-posts/${jobId}/apply`)
        .then(() => {
          this.appliedJobs.push(jobId);
          this.$refs.notification.showNotification('Vous avez postulé avec succès.', 'success');
        })
        .catch((error) => {
          this.$refs.notification.showNotification(
            `Erreur lors de la candidature : ${error.response?.data?.message || error.message}`,
            'error'
          );
        });
    },
    unapplyForJob(jobId) {
      axios
        .delete(`/api/job-posts/${jobId}/unapply`)
        .then(() => {
          this.appliedJobs = this.appliedJobs.filter((id) => id !== jobId);
          this.$refs.notification.showNotification('Vous avez retiré votre candidature avec succès.', 'success');
        })
        .catch((error) => {
          this.$refs.notification.showNotification(
            `Erreur lors de la suppression de votre candidature : ${error.response?.data?.message || error.message}`,
            'error'
          );
        });
    },
    isApplied(jobId) {
      return this.appliedJobs.includes(jobId);
    }
  }
};
</script>

<style scoped>

</style>
