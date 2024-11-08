<template>
  <div class="container">
    <Notification ref="notification"/>
    <h2>Create Job Post</h2>
    <form @submit.prevent="submitJobPost">
      <div class="mb-3">
        <label for="title">Job Title</label>
        <input type="text" v-model="jobPost.title" id="title" required />
      </div>

      <div class="mb-3">
        <label for="skills">Required Skills</label>
        <ul class="skills-list">
          <li v-for="skill in availableSkills" :key="skill.id" @click="addSkill(skill)">
            {{ skill.name }}
          </li>
        </ul>
      </div>

      <div class="mb-3">
        <label>Selected Skills</label>
        <ul class="selected-skills">
          <li v-for="skill in jobPost.skills" :key="skill.id" @click="removeSkill(skill)">
            {{ skill.name }} <span class="remove-btn">x</span>
          </li>
        </ul>
      </div>

      <div class="mb-3">
        <label for="description">Description</label>
        <textarea v-model="jobPost.description" id="description" required></textarea>
      </div>

      <button type="submit">Create Job Post</button>
    </form>
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
      jobPost: {
        title: '',
        description: '',
        skills: [],
      },
      skillsList: [],
      availableSkills: [],
      notification: {
        show: false,
        message: '',
        type: '' // 'success' or 'error'
      }
    };
  },
  mounted() {
    this.fetchSkills();
  },
  methods: {
    fetchSkills() {
      axios.get('/api/skills')
        .then(response => {
          this.skillsList = response.data;
          this.availableSkills = [...this.skillsList]; // Clone pour gérer les compétences disponibles
        })
        .catch(error => {
          console.error('Error fetching skills:', error);
          this.$refs.notification.showNotification('Error fetching skills', 'error');
        });
    },
    addSkill(skill) {
      // Ajoute la compétence dans `jobPost.skills` et la retire de `availableSkills`
      this.jobPost.skills.push(skill);
      this.availableSkills = this.availableSkills.filter(s => s.id !== skill.id);
    },
    removeSkill(skill) {
      // Retire la compétence de `jobPost.skills` et la remet dans `availableSkills`
      this.jobPost.skills = this.jobPost.skills.filter(s => s.id !== skill.id);
      this.availableSkills.push(skill);
    },
    submitJobPost() {
      const jobPostData = {
        ...this.jobPost,
        skills: this.jobPost.skills.map(skill => skill.id), // Envoie seulement les IDs des compétences
      };

      axios.post('/api/job_posts', jobPostData)
        .then(response => {
          this.$refs.notification.showNotification('Job post created successfully!', 'success');
          this.resetForm();
        })
        .catch(error => {
          console.error('Error creating job post:', error);
          this.$refs.notification.showNotification('An error occurred while creating the job post.', 'error');
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
