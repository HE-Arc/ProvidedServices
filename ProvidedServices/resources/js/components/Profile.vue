<template>
  <div class="profile-container">
    <h1>User Profile</h1>
    <div class="profile-info">
      <div class="profile-field">
        <label>First Name:</label>
        <span>{{ user.first_name }}</span>
      </div>
      <div class="profile-field">
        <label>Last Name:</label>
        <span>{{ user.last_name }}</span>
      </div>
      <div class="profile-field">
        <label>Email:</label>
        <span>{{ user.email }}</span>
      </div>
      <div class="profile-field">
        <label>Gender:</label>
        <span>{{ user.genre === 'male' ? 'Male' : 'Female' }}</span>
      </div>
      <div class="profile-field">
        <label>Role:</label>
        <span>{{ user.role === 'client' ? 'Client' : 'Provider' }}</span>
      </div>
    </div>

    <button @click="openCvModal">CV</button>
    <button @click="goBack">Back to Home</button>

    <!-- Modal pour l'ajout du CV -->
    <div v-if="showCvModal" class="modal-overlay" @click.self="closeCvModal">
      <div class="modal-content">
        <h2>Upload CV</h2>
        <div class="cv-upload">
          <input type="file" @change="handleCvUpload" accept="application/pdf" />
          <button v-if="cvFile" @click="saveCv">Save CV</button>
        </div>
        <div v-if="cvFile" class="cv-display">
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

export default {
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      showCvModal: false,
      cvFile: null,
      cvUrl: null, // Lien pour afficher le CV
      errorMessage: '' // Pour afficher les erreurs
    };
  },
  methods: {
    goBack() {
      window.history.back();
    },
    openCvModal() {
      this.showCvModal = true;
    },
    closeCvModal() {
      this.showCvModal = false;
      this.cvFile = null; // Réinitialise le fichier sélectionné
    },
    handleCvUpload(event) {
      this.cvFile = event.target.files[0];
    },
    saveCv() {
      if (!this.cvFile) return;

      const formData = new FormData();
      formData.append("cv", this.cvFile);
      formData.append("userId", this.user.id);

      axios.post(`/api/profile/${this.user.id}/upload-cv`, formData, {
        headers: {
          "Content-Type": "multipart/form-data"
        }
      })
      .then(response => {
        this.cvUrl = response.data.cvUrl; // Enregistre l'URL du CV pour l'afficher
        alert("CV uploaded successfully!");
      })
      .catch(error => {
        console.error('Error uploading CV:', error);
        alert("An error occurred while uploading the CV.");
      });
    }
  }
};
</script>

<style scoped>
.profile-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  background-color: #f9f9f9;
}

h1, h2 {
  color: #343a40;
  text-align: center;
}

.profile-info {
  margin-top: 20px;
}

.profile-field {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #e0e0e0;
}

.profile-field:last-child {
  border-bottom: none;
}

label {
  font-weight: bold;
}

span {
  color: #555;
}

button {
  display: block;
  width: 100%;
  padding: 10px;
  margin-top: 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

button:hover {
  background-color: #0056b3;
}

/* Styles pour la modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 400px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  text-align: center;
}

.cv-upload {
  margin-bottom: 20px;
}

.cv-display {
  margin-top: 10px;
  color: #007bff;
}

.modal-content button {
  width: auto;
  background-color: #007bff;
}

.modal-content button:hover {
  background-color: #0056b3;
}
</style>
