<template>
  <div class="profile-container">
    <Notification ref="notification"/>
    <!-- Bouton de retour avec une flèche -->
    <button class="back-button" @click="goBack">
      <i class="fas fa-arrow-left"></i>
    </button>

    <div class="profile-header">
      <h1>Profile</h1>
    </div>

    <!-- Section photo de profil -->
    <div class="profile-picture-section">
      <div class="profile-picture">
        <img v-if="profilePictureUrl" :src="profilePictureUrl" alt="Profile Picture" />
        <img v-else src="/storage/images/default-avatar.png" alt="Default Avatar" />
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
            placeholder="Enter first name"
          />
          <input
            type="text"
            v-model="editableUser.last_name"
            class="edit-input"
            placeholder="Enter last name"
          />
          <button class="save-name-button" @click="toggleEditMode">
            <i class="fas fa-save"></i> Save
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

    <!-- Section "Personal" -->
    <div class="personal-section">
      <h3>Personal Information</h3>
      <div class="profile-field">
        <label>Email:</label>
        <span>{{ editableUser.email }}</span>
      </div>
      <div class="profile-field">
        <label>Gender:</label>
        <span>{{ editableUser.genre }}</span>
      </div>
      <div class="profile-field">
        <label>Role:</label>
        <span>{{ editableUser.role }}</span>
      </div>
    </div>

    <!-- Section CV -->
    <div class="cv-section">
      <h3>Curriculum Vitae</h3>
      <div class="cv-upload" v-if="isOwner">
        <button @click="triggerCvInput" class="cv-button" id="upload">
          <i class="fas fa-file-upload"></i> Upload CV
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
            <i class="fas fa-file-pdf"></i> View CV
          </button>
        </p>
        <p v-else>
          <i class="fas fa-times-circle"></i> No CV uploaded
        </p>
      </div>
      <button v-if="isOwner && cvUrl" @click="deleteCv" class="delete-cv-button">
        <i class="fas fa-trash-alt"></i> Delete CV
      </button>
    </div>

    <!-- Section Skills -->
    <div class="skills-section">
      <h3>Skills</h3>

      <!-- Liste des compétences ajoutées -->
      <div>
        <ul class="selected-skills">
          <li v-for="skill in userSkills" :key="skill.id" 
              @click="isOwner ? removeSkill(skill) : null" 
              :class="{'clickable': isOwner}">
            {{ skill.name }} <span v-if="isOwner" class="remove-btn">x</span>
          </li>
        </ul>
      </div>

      <!-- Bouton pour afficher ou masquer la liste des compétences -->
      <div v-if="isOwner">
        <button @click="toggleSkillsList" class="add-skill-button">
          <i :class="isSkillsListVisible ? 'fas fa-minus' : 'fas fa-plus'"></i> 
          {{ isSkillsListVisible ? 'Hide available skills' : 'Add more skills' }}
        </button>
      </div>

      <!-- Liste des compétences disponibles (visible lorsque le bouton est cliqué) -->
      <div v-if="isSkillsListVisible">
        <ul class="skills-list">
          <li v-for="skill in availableSkills" :key="skill.id" @click="addSkill(skill)">
            {{ skill.name }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>


<script>
import axios from 'axios';
import Notification from './Notification.vue';

export default {
  components: {
    Notification 
  },
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
      editableUser: { ...this.user }, // Créer une copie de l'utilisateur
      showCvModal: false,
      cvFile: null,
      cvUrl: null,
      profilePictureUrl: this.user.picture ? `/storage/${this.user.picture}` : null, // Ajout de la variable pour l'URL de la photo
      userSkills: [], // Compétences actuelles de l'utilisateur
      availableSkills: [], // Compétences disponibles pour ajout
      selectedSkill: null, // Compétence sélectionnée pour ajout
      isSkillsListVisible: false,
    };
  },
  computed: {
    isOwner() {
      return this.authUserId === this.user.id;
    },
    profileFields() {
      return {
        first_name: { 
          label: "First Name", 
          display: this.editableUser.first_name, 
          type: "text", 
          inputType: "text" 
        },
        last_name: { 
          label: "Last Name", 
          display: this.editableUser.last_name, 
          type: "text", 
          inputType: "text" 
        },
      };
    }
  },
  mounted() {
    this.fetchUserCv();
    this.fetchUserSkills();
    this.fetchAvailableSkills();
  },
  methods: {
    viewCv() {
      window.open(this.cvUrl, '_blank');
    },
    toggleSkillsList() {
      this.isSkillsListVisible = !this.isSkillsListVisible;
    },
    fetchUserSkills() {
      axios.get(`/api/profile/${this.user.id}/skills`)
        .then(response => {
          this.userSkills = Array.isArray(response.data) ? response.data : [];
        })
        .catch(error => {
          console.error('Error fetching user skills:', error);
          this.$refs.notification.showNotification('Error fetching user skills', 'error');
        });
    },
    fetchAvailableSkills() {
      axios.get('/api/skills')
        .then(response => {
          // Vérifier si this.userSkills est un tableau, sinon utiliser un tableau vide
          const userSkillIds = Array.isArray(this.userSkills) 
            ? this.userSkills.map(skill => skill.id) 
            : [];
          
          // Exclure les compétences déjà associées à l'utilisateur
          this.availableSkills = response.data.filter(skill => !userSkillIds.includes(skill.id));
        })
        .catch(error => {
          console.error('Error fetching available skills:', error);
          this.$refs.notification.showNotification('Error fetching available skills', 'error');
        });
    },
    addSkill(skill) {
      if (!skill) return; // Vérifier si une compétence est bien sélectionnée

      axios.post(`/api/profile/${this.user.id}/add-skill`, { skill_id: skill.id })
        .then(() => {
          // Ajouter la compétence dans userSkills
          this.userSkills.push(skill);

          // Retirer la compétence de la liste des compétences disponibles
          this.availableSkills = this.availableSkills.filter(s => s.id !== skill.id);

          // Réinitialiser selectedSkill si nécessaire
          this.selectedSkill = null;

          // Afficher une notification
          this.$refs.notification.showNotification('Skill added successfully!', 'success');
        })
        .catch(error => {
          console.error('Error adding skill:', error);
          this.$refs.notification.showNotification('Error adding skill', 'error');
        });
    },
    removeSkill(skill) {
      axios.delete(`/api/profile/${this.user.id}/remove-skill`, {
        data: { skill_id: skill.id }
      })
      .then(() => {
        this.userSkills = this.userSkills.filter(s => s.id !== skill.id);
        this.availableSkills.push(skill);
        this.$refs.notification.showNotification('Skill removed successfully!', 'success');
      })
      .catch(error => {
        console.error('Error removing skill:', error);
        this.$refs.notification.showNotification('Error removing skill', 'error');
      });
    },
    fetchProfilePicture() {
      if (this.user.picture) {
        this.profilePictureUrl = `/storage/${this.user.picture}`;
      }
    },
    handleProfilePictureUpload(event) {
      this.profilePictureFile = event.target.files[0];
      this.saveProfilePicture();
    },
    saveProfilePicture() {
      if (!this.profilePictureFile) return;

      const formData = new FormData();
      formData.append("profile_picture", this.profilePictureFile);

      axios.post(`/api/profile/${this.user.id}/upload-profile-picture`, formData, {
        headers: { "Content-Type": "multipart/form-data" }
      })
      .then(response => {
        this.profilePictureUrl = `${response.data.profilePictureUrl}`;
        this.$refs.notification.showNotification('Profile picture uploaded successfully!', 'success');
      })
      .catch(error => {
        this.$refs.notification.showNotification('Error uploading profile picture', 'error');
        console.error('Error uploading profile picture:', error);
      });
    },
    goBack() {
      window.history.back();
    },
    toggleEditMode() {
      if (this.isEditing) {
        // Envoi de la requête PUT uniquement avec le prénom et le nom
        axios.put(`/api/profile/${this.user.id}`, {
            first_name: this.editableUser.first_name,
            last_name: this.editableUser.last_name,
        })
        .then(response => {
            // Mettre à jour les données locales avec la réponse du backend
            this.editableUser.first_name = response.data.user.first_name;
            this.editableUser.last_name = response.data.user.last_name;
            this.isEditing = false;
            this.$refs.notification.showNotification('Profile updated successfully', 'success');
          })
        .catch(error => {
            this.$refs.notification.showNotification('Error saving profile', 'error');
            console.error('Error saving profile:', error.response ? error.response.data : error);
          });
      } else {
        // Activer le mode édition
        this.isEditing = true;
      }
    },
    triggerCvInput() {
      this.$refs.cvInput.click();
    },
    handleCvUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append("cv", file);

      axios.post(`/api/profile/${this.user.id}/upload-cv`, formData, {
          headers: { "Content-Type": "multipart/form-data" }
        })
        .then(response => {
          this.cvUrl = response.data.cvUrl;
          this.$refs.notification.showNotification('CV uploaded successfully!', 'success');
        })
        .catch(error => {
          this.$refs.notification.showNotification('Error uploading CV', 'error');
          console.error('Error uploading CV:', error);
        });
    },
    deleteCv() {
      axios.delete(`/api/profile/${this.user.id}/delete-cv`)
        .then(() => {
          this.cvUrl = null;
          this.$refs.notification.showNotification('CV deleted successfully!', 'success');
        })
        .catch(error => {
          this.$refs.notification.showNotification('Error deleting CV', 'error');
          console.error('Error deleting CV:', error);
        });
    },
    fetchUserCv() {
      axios.get(`/api/profile/${this.user.id}/cv`)
        .then(response => {
          this.cvUrl = response.data.cvUrl;
        })
        .catch(error => {
          console.error('Error fetching CV:', error);
          this.cvUrl = null;
        });
    },
    triggerFileInput() {
      this.$refs.fileInput.click();
    }
  },
  watch: {
    user(newUser) {
      this.editableUser = { ...newUser };
      this.fetchProfilePicture();
    }
  }
};
</script>

<style scoped>

</style>
