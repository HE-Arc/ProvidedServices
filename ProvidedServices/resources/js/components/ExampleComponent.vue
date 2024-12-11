<template>
  <div>
    <Navbar :user="user" />

    <!-- Notification pop-up -->
    <Notification ref="notification" />

    <!-- Liste des offres d'emploi -->
    <div class="job-posts-header">
      <h2>Job Posts</h2>
      <button v-if="user && user.role === 'client'" class="btn-add-job" @click="createOffer">
        <span>+</span>
      </button>
    </div>

    <!-- Liste des offres d'emploi -->
    <div v-if="jobPosts.length > 0">
      <div v-for="job in jobPosts" :key="job.id" class="job-post">
        <h3>{{ job.title }}</h3>
        <p><strong>Description: </strong>{{ job.description }}</p>
        <p>
          <strong>Posted by: </strong>
          <a :href="`/profile/${job.client.id}`">
            {{ job.client.first_name }} {{ job.client.last_name }}
          </a>
        </p>
        <p>
          <strong>Skills Required: </strong>
          <span v-for="(skill, index) in job.skills" :key="index" class="skill-bubble">{{ skill.name }}</span>
        </p>
        <div class="btn-container">
          <button
            v-if="user && user.role === 'provider'"
            @click="toggleApplication(job.id)"
            :class="isApplied(job.id) ? 'btn-unapply' : 'btn-apply'"
          >
            {{ isApplied(job.id) ? 'Unapply' : 'Apply' }}
          </button>
        </div>
      </div>
    </div>
    <div v-else>
      <h3>No job posts available at the moment.</h3>
    </div>
  </div>
</template>


<script>
import axios from 'axios';
import Notification from './Notification.vue';
import Navbar from './Navbar.vue';

export default {
  components: {
    Notification
  },
  data() {
    return {
      user: null,
      jobPosts: [],
      appliedJobs: [],
      dropdownOpen: false,
    };
  },
  mounted() {
    this.fetchUser();
    this.fetchJobPosts();
    document.addEventListener('click', this.handleOutsideClick);
  },
  beforeDestroy() {
  // Nettoyer l'écouteur pour éviter les fuites de mémoire
  document.removeEventListener('click', this.handleOutsideClick);
  },
  methods: {
    fetchUser() {
      axios.get('/api/user')
        .then(response => {
          this.user = response.data;
          if (this.user.role === 'provider') {
            this.fetchAppliedJobs();
          }
        })
        .catch(error => {
          this.$refs.notification.showNotification('Error fetching user data', 'error');
          console.error('Error fetching user data:', error);
        });
    },
    fetchJobPosts() {
      axios.get('/api/job-posts')
        .then(response => {
          this.jobPosts = response.data;
        })
        .catch(error => {
          this.$refs.notification.showNotification('Error fetching job posts', 'error');
          console.error('Error fetching job posts:', error);
        });
    },
    handleOutsideClick(event) {
      const dropdown = this.$el.querySelector('.dropdown');
      const profilePicture = this.$el.querySelector('.profile-picture');

      // Si le clic n'est pas dans le menu déroulant ou sur la photo de profil, fermer la liste
      if (dropdown && !dropdown.contains(event.target) && !profilePicture.contains(event.target)) {
        this.dropdownOpen = false;
      }
    },
    fetchAppliedJobs() {
      // Récupère les jobs déjà postulés par l'utilisateur
      axios.get('/api/applied-jobs')
        .then(response => {
          this.appliedJobs = response.data; // Par exemple : [1, 2, 3]
        })
        .catch(error => {
          this.$refs.notification.showNotification('Error fetching applied jobs', 'error');
          console.error('Error fetching applied jobs:', error);
        });
    },
    logout() {
      axios.post('/logout')
        .then(response => {
          this.user = null;
          this.$refs.notification.showNotification(response.data.message, 'success');
          setTimeout(() => {
            window.location.href = '/login';
          }, 1000);
        })
        .catch(error => {
          this.$refs.notification.showNotification('Error during logout', 'error');
          console.error('Error during logout:', error);
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
      axios.post(`/api/job-posts/${jobId}/apply`)
        .then(response => {
          this.appliedJobs.push(jobId); // Ajouter l'ID du job à la liste des jobs postulés
          this.$refs.notification.showNotification('Successfully applied for the job!', 'success');
        })
        .catch(error => {
          this.$refs.notification.showNotification('Error applying for the job', 'error');
          console.error('Error applying for job:', error);
        });
    },
    unapplyForJob(jobId) {
      axios.delete(`/api/job-posts/${jobId}/unapply`)
        .then(response => {
          this.appliedJobs = this.appliedJobs.filter(id => id !== jobId); // Retirer l'ID du job de la liste des jobs postulés
          this.$refs.notification.showNotification('Successfully removed your application!', 'success');
        })
        .catch(error => {
          this.$refs.notification.showNotification('Error removing application', 'error');
          console.error('Error removing application:', error);
        });
    },
    isApplied(jobId) {
      return this.appliedJobs.includes(jobId); // Vérifie si le job est dans la liste des jobs postulés
    }
  }
};
</script>

<style scoped>
body {
  margin: 0;
  padding: 0;
}


</style>
