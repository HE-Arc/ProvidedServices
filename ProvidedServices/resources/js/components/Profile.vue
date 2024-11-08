<template>
  <div class="profile-container">
    <Notification ref="notification"/>
    <h1>User Profile</h1>
    <div class="profile-info">
      <div class="profile-field" v-for="(value, key) in profileFields" :key="key">
        <label>{{ value.label }}:</label>
        <span v-if="!isEditing">{{ value.display }}</span>
        <template v-else>
          <input v-if="value.type === 'text'" v-model="editableUser[key]" :type="value.inputType" />
          <select v-else v-model="editableUser[key]">
            <option v-for="(label, optionValue) in value.options" :value="optionValue">{{ label }}</option>
          </select>
        </template>
      </div>
    </div>

    <!-- Afficher le bouton Edit uniquement si l'utilisateur est le propriétaire du profil -->
    <button v-if="authUserId === user.id" @click="toggleEditMode">{{ isEditing ? 'Save' : 'Edit' }}</button>

    <!-- Si l'utilisateur est le propriétaire, il peut uploader un CV -->
    <button v-if="authUserId === user.id" @click="openCvModal">Upload CV</button>

    <!-- Si l'utilisateur n'est pas le propriétaire, il peut seulement voir le CV -->
    <button v-else @click="openCvModal">View CV</button>

    <button @click="goBack">Back to Home</button>

    <!-- Modal pour l'ajout du CV -->
    <div v-if="showCvModal" class="modal-overlay" @click.self="closeCvModal">
      <div class="modal-content">
        <h2>{{ authUserId === user.id ? 'Upload CV' : 'View CV' }}</h2>
        <div class="cv-upload" v-if="authUserId === user.id">
          <input type="file" @change="handleCvUpload" accept="application/pdf" />
          <button v-if="cvFile" @click="saveCv">Save CV</button>
        </div>

        <div v-if="cvUrl">
          <p>Uploaded CV:</p>
          <a :href="cvUrl" target="_blank">View CV</a>
        </div>

        <button @click="closeCvModal">Close</button>
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
    };
  },
  computed: {
    profileFields() {
      return {
        first_name: { label: "First Name", display: this.editableUser.first_name, type: "text", inputType: "text" },
        last_name: { label: "Last Name", display: this.editableUser.last_name, type: "text", inputType: "text" },
        email: { label: "Email", display: this.editableUser.email, type: "text", inputType: "email" },
        genre: { label: "Gender", display: this.editableUser.genre === 'male' ? 'Male' : 'Female', type: "select", options: { male: "Male", female: "Female" } },
        role: { label: "Role", display: this.editableUser.role === 'client' ? 'Client' : 'Provider', type: "select", options: { client: "Client", provider: "Provider" } },
      };
    }
  },
  mounted() {
    this.fetchUserCv();
  },
  methods: {
    goBack() {
      window.history.back();
    },
    toggleEditMode() {
      if (this.isEditing) {
          axios.put(`/api/profile/${this.user.id}`, {
              first_name: this.editableUser.first_name,
              last_name: this.editableUser.last_name,
              email: this.editableUser.email,
              role: this.editableUser.role,
              genre: this.editableUser.genre,
          })
          .then(response => {
              this.editableUser = { ...response.data.user }; // Mise à jour avec les données du serveur
              this.isEditing = false;
              this.$refs.notification.showNotification('Profile updated successfully', 'success');
          })
          .catch(error => {
              this.$refs.notification.showNotification('Error saving profile', 'error');
              console.error('Error saving profile:', error.response ? error.response.data : error);
          });
      } else {
          this.isEditing = true;
      }
  },
    openCvModal() {
      this.showCvModal = true;
    },
    closeCvModal() {
      this.showCvModal = false;
      this.cvFile = null;
    },
    handleCvUpload(event) {
      this.cvFile = event.target.files[0];
    },
    fetchUserCv() {
      axios.get(`/api/profile/${this.user.id}/cv`)
        .then(response => {
          // Vérifier si le CV existe ou pas
          this.cvUrl = response.data.cvUrl;
        })
        .catch(error => {
          console.error('Error fetching CV:', error);
          // Vous pouvez aussi assigner null à `cvUrl` ici si besoin
          this.cvUrl = null;
        });
    },
    saveCv() {
      if (!this.cvFile) return;

      const formData = new FormData();
      formData.append("cv", this.cvFile);

      axios.post(`/api/profile/${this.user.id}/upload-cv`, formData, {
          headers: { "Content-Type": "multipart/form-data" }
      })
      .then(response => {
          this.cvUrl = response.data.cvUrl;
          this.$refs.notification.showNotification('CV uploaded successfully!', 'success');
          this.closeCvModal();
      })
      .catch(error => {
          this.$refs.notification.showNotification('Error uploading CV', 'error');
          console.error('Error uploading CV:', error);
      });
    }
  },
  watch: {
    user(newUser) {
      this.editableUser = { ...newUser };  // Réinitialiser editableUser lorsqu'une nouvelle prop `user` est reçue
    }
  }
};
</script>

<style scoped>
/* Styles ici */
</style>
