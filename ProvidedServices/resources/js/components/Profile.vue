<template>
    <Navbar v-if="user" :user="user" />
    <div class="profile-container">
      <Notification ref="notification" />

      <!-- Bouton de retour -->
      <button class="back-button" @click="goBack">
          <i class="fas fa-arrow-left"></i> Retour
      </button>

      <div class="profile-header">
          <h1>Profil</h1>
      </div>

      <!-- Section photo de profil -->
      <div class="profile-picture-section">
          <div class="profile-picture">
              <img v-if="profilePictureUrl" :src="profilePictureUrl" alt="Photo de profil" />
              <img v-else src="/storage/images/default-avatar.png" alt="Avatar par défaut" />
              <div v-if="isOwner" class="overlay" @click="triggerFileInput">
                  <i class="fas fa-camera"></i>
              </div>
          </div>
          <input
              ref="fileInput"
              type="file"
              v-if="isOwner"
              @change="handleProfilePictureUpload"
              accept="image/*"
              style="display: none;"
          />
          <div class="user-name">
              <template v-if="isOwner && isEditing">
                  <input
                      type="text"
                      v-model="editableUser.first_name"
                      class="edit-input"
                      placeholder="Prénom"
                  />
                  <input
                      type="text"
                      v-model="editableUser.last_name"
                      class="edit-input"
                      placeholder="Nom"
                  />
                  <button class="save-name-button" @click="toggleEditMode">
                      <i class="fas fa-save"></i> Sauvegarder
                  </button>
              </template>
              <template v-else>
                  <h2>{{ editableUser.first_name }} {{ editableUser.last_name }}</h2>
                  <button v-if="isOwner" class="edit-name-button" @click="toggleEditMode">
                      <i class="fas fa-pencil-alt"></i>
                  </button>
              </template>
          </div>
      </div>

      <!-- Section "Informations personnelles" -->
      <div class="personal-section">
          <h3>Informations personnelles</h3>
          <div class="profile-field">
              <label>Email :</label>
              <span>{{ editableUser.email }}</span>
          </div>
          <div class="profile-field">
              <label>Genre :</label>
              <span>{{ editableUser.genre === 'male' ? 'Homme' : 'Femme' }}</span>
          </div>
          <div class="profile-field">
              <label>Rôle :</label>
              <span>{{ editableUser.role === 'client' ? 'Client' : 'Prestataire' }}</span>
          </div>
      </div>

      <!-- Section CV -->
      <div class="cv-section">
          <h3>Curriculum Vitae</h3>
          <div class="cv-upload" v-if="isOwner">
              <button @click="triggerCvInput" class="cv-button">
                  <i class="fas fa-file-upload"></i> Télécharger un CV
              </button>
              <input
                  ref="cvInput"
                  type="file"
                  @change="handleCvUpload"
                  accept="application/pdf"
                  style="display: none;"
              />
          </div>
          <div class="cv-display">
              <p v-if="cvUrl">
                  <button class="view-cv-button" @click="viewCv">
                      <i class="fas fa-file-pdf"></i> Voir le CV
                  </button>
              </p>
              <p v-else>
                  <i class="fas fa-times-circle"></i> Aucun CV téléchargé
              </p>
          </div>
          <button v-if="isOwner && cvUrl" @click="deleteCv" class="delete-cv-button">
              <i class="fas fa-trash-alt"></i> Supprimer le CV
          </button>
      </div>

      <!-- Section Compétences -->
      <div class="skills-section">
          <h3>Compétences</h3>
          <ul class="selected-skills">
              <li v-for="skill in userSkills" :key="skill.id" 
                  @click="isOwner ? removeSkill(skill) : null" 
                  :class="{ 'clickable': isOwner }">
                  {{ skill.name }} <span v-if="isOwner" class="remove-btn">x</span>
              </li>
          </ul>

          <!-- Ajouter des compétences -->
          <div v-if="isOwner">
              <button @click="toggleSkillsList" class="add-skill-button">
                  <i :class="isSkillsListVisible ? 'fas fa-minus' : 'fas fa-plus'"></i>
                  {{ isSkillsListVisible ? 'Cacher les compétences disponibles' : 'Ajouter des compétences' }}
              </button>
              <div v-if="isSkillsListVisible">
                  <ul class="skills-list">
                      <li v-for="skill in availableSkills" :key="skill.id" @click="addSkill(skill)">
                          {{ skill.name }}
                      </li>
                  </ul>
              </div>
          </div>
      </div>
  </div>
</template>

<script>
import axios from 'axios';
import Notification from './Notification.vue';

export default {
  components: { Notification },
  props: {
      user: {
          type: Object,
          required: true
      },
      authUserId: {
          type: Number,
          required: true
      }
  },
  data() {
      return {
          isEditing: false,
          editableUser: { ...this.user },
          cvUrl: null,
          profilePictureUrl: this.user.picture ? `/storage/${this.user.picture}` : null,
          userSkills: [],
          availableSkills: [],
          isSkillsListVisible: false,
      };
  },
  computed: {
      isOwner() {
          return this.authUserId === this.user.id;
      }
  },
  mounted() {
      this.fetchUserCv();
      this.fetchUserSkills();
      this.fetchAvailableSkills();
  },
  methods: {
      goBack() {
          window.history.back();
      },
      viewCv() {
          window.open(this.cvUrl, '_blank');
      },
      toggleSkillsList() {
          this.isSkillsListVisible = !this.isSkillsListVisible;
      },
      fetchUserCv() {
          axios.get(`/api/profile/${this.user.id}/cv`)
              .then(response => {
                  this.cvUrl = response.data.cvUrl;
              })
              .catch(error => {
                  this.cvUrl = null;
                  this.$refs.notification.showNotification('Erreur lors du chargement du CV.', 'error');
              });
      },
      handleProfilePictureUpload(event) {
          const file = event.target.files[0];
          if (!file) return;

          const formData = new FormData();
          formData.append('profile_picture', file);

          axios.post(`/api/profile/${this.user.id}/upload-profile-picture`, formData, {
              headers: { 'Content-Type': 'multipart/form-data' }
          })
          .then(response => {
              this.profilePictureUrl = response.data.profilePictureUrl;
              this.$refs.notification.showNotification('Photo de profil mise à jour avec succès.', 'success');
          })
          .catch(error => {
              this.$refs.notification.showNotification('Erreur lors du téléchargement de la photo de profil.', 'error');
          });
      },
      handleCvUpload(event) {
          const file = event.target.files[0];
          if (!file) return;

          const formData = new FormData();
          formData.append('cv', file);

          axios.post(`/api/profile/${this.user.id}/upload-cv`, formData, {
              headers: { 'Content-Type': 'multipart/form-data' }
          })
          .then(response => {
              this.cvUrl = response.data.cvUrl;
              this.$refs.notification.showNotification('CV téléchargé avec succès.', 'success');
          })
          .catch(error => {
              this.$refs.notification.showNotification('Erreur lors du téléchargement du CV.', 'error');
          });
      },
      deleteCv() {
          axios.delete(`/api/profile/${this.user.id}/delete-cv`)
              .then(() => {
                  this.cvUrl = null;
                  this.$refs.notification.showNotification('CV supprimé avec succès.', 'success');
              })
              .catch(error => {
                  this.$refs.notification.showNotification('Erreur lors de la suppression du CV.', 'error');
              });
      },
      fetchUserSkills() {
          axios.get(`/api/profile/${this.user.id}/skills`)
              .then(response => {
                  this.userSkills = response.data;
              })
              .catch(error => {
                  this.$refs.notification.showNotification('Erreur lors du chargement des compétences.', 'error');
              });
      },
      fetchAvailableSkills() {
          axios.get('/api/skills')
              .then(response => {
                  const userSkillIds = this.userSkills.map(skill => skill.id);
                  this.availableSkills = response.data.filter(skill => !userSkillIds.includes(skill.id));
              })
              .catch(error => {
                  this.$refs.notification.showNotification('Erreur lors du chargement des compétences disponibles.', 'error');
              });
      },
      addSkill(skill) {
          axios.post(`/api/profile/${this.user.id}/add-skill`, { skill_id: skill.id })
              .then(() => {
                  this.userSkills.push(skill);
                  this.availableSkills = this.availableSkills.filter(s => s.id !== skill.id);
                  this.$refs.notification.showNotification('Compétence ajoutée avec succès.', 'success');
              })
              .catch(error => {
                  this.$refs.notification.showNotification('Erreur lors de l\'ajout de la compétence.', 'error');
              });
      },
      removeSkill(skill) {
          axios.delete(`/api/profile/${this.user.id}/remove-skill`, { data: { skill_id: skill.id } })
              .then(() => {
                  this.userSkills = this.userSkills.filter(s => s.id !== skill.id);
                  this.availableSkills.push(skill);
                  this.$refs.notification.showNotification('Compétence supprimée avec succès.', 'success');
              })
              .catch(error => {
                  this.$refs.notification.showNotification('Erreur lors de la suppression de la compétence.', 'error');
              });
      },
      toggleEditMode() {
          if (this.isEditing) {
              axios.put(`/api/profile/${this.user.id}`, {
                  first_name: this.editableUser.first_name,
                  last_name: this.editableUser.last_name
              })
              .then(() => {
                  this.isEditing = false;
                  this.$refs.notification.showNotification('Profil mis à jour avec succès.', 'success');
              })
              .catch(error => {
                  this.$refs.notification.showNotification('Erreur lors de la mise à jour du profil.', 'error');
              });
          } else {
              this.isEditing = true;
          }
      },
      triggerFileInput() {
          this.$refs.fileInput.click();
      },
      triggerCvInput() {
          this.$refs.cvInput.click();
      }
  }
};
</script>

<style scoped>
/* Ajoutez ici vos styles */
</style>
