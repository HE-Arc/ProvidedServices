<template>
  <Navbar v-if="user" :user="user" />
  <Notification ref="notification"/>
    
  <div class="container">
    <button class="back-button" @click="goBack" id="job-post-back">
        <i class="fas fa-arrow-left"></i> Retour
    </button>
    <h2>Créer une annonce</h2>
    <form @submit.prevent="submitJobPost">
      <div class="mb-3">
        <label for="title">Titre de l'annonce</label>
        <input type="text" v-model="jobPost.title" id="title" required placeholder="Entrez le titre de l'annonce"/>
      </div>

      <div class="mb-3">
        <label for="skills">Compétences requises</label>
        <ul class="skills-list">
          <li v-for="skill in availableSkills" :key="skill.id" @click="addSkill(skill)">
            {{ skill.name }}
          </li>
        </ul>
      </div>

      <div class="mb-3">
        <label>Compétences sélectionnées</label>
        <ul class="selected-skills">
          <li v-for="skill in jobPost.skills" :key="skill.id" @click="removeSkill(skill)">
            {{ skill.name }} <span class="remove-btn">x</span>
          </li>
        </ul>
      </div>

      <div class="mb-3">
        <label for="description">Description</label>
        <textarea v-model="jobPost.description" id="description" required placeholder="Entrez la description de l'annonce"></textarea>
      </div>

      <button type="submit" class="btn-create-job-post">Créer l'annonce</button>
    </form>
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
      jobPost: {
        title: '',
        description: '',
        skills: [],
      },
      user: null,
      skillsList: [],
      availableSkills: [],
    };
  },
  async created() {
    await this.fetchUser();
  },
  mounted() {
    this.fetchSkills();
  },
  methods: {
    async fetchUser() {
        try {
            const response = await axios.get('/api/user');
            this.user = response.data;
        } catch (error) {
            this.$refs.notification.showNotification('Erreur lors de la récupération des données utilisateur.', 'error');
        }
    },
    goBack() {
      window.history.back();
    },
    fetchSkills() {
      axios.get('/api/skills')
        .then(response => {
          this.skillsList = response.data;
          this.availableSkills = [...this.skillsList];
        })
        .catch(error => {
          const errorMessage = error.response?.data?.message || 'Erreur lors de la récupération des compétences';
          this.$refs.notification.showNotification(errorMessage, 'error');
        });
    },
    addSkill(skill) {
      this.jobPost.skills.push(skill);
      this.availableSkills = this.availableSkills.filter(s => s.id !== skill.id);
    },
    removeSkill(skill) {
      this.jobPost.skills = this.jobPost.skills.filter(s => s.id !== skill.id);
      this.availableSkills.push(skill);
    },
    submitJobPost() {
      const jobPostData = {
        ...this.jobPost,
        skills: this.jobPost.skills.map(skill => skill.id),
      };

      axios.post('/api/job_posts', jobPostData)
        .then(() => {
          this.$refs.notification.showNotification('Annonce créée avec succès !', 'success');
          this.resetForm();
        })
        .catch(error => {
          const errorMessage = error.response?.data?.message || 'Erreur lors de la création de l\'annonce';
          this.$refs.notification.showNotification(errorMessage, 'error');
        });
    },
    resetForm() {
      this.jobPost = {
        title: '',
        description: '',
        skills: [],
      };
      this.availableSkills = [...this.skillsList];
    }
  }
};
</script>


<style scoped>

</style>
