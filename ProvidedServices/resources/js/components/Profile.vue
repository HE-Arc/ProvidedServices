<template>
  <div class="profile-container">
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

    <button @click="toggleEditMode">{{ isEditing ? 'Save' : 'Edit' }}</button>
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

        <div v-if="cvUrl" class="cv-display">
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
        axios.put(`/api/profile/${this.user.id}/update`, {
            first_name: this.editableUser.first_name,
            last_name: this.editableUser.last_name,
            email: this.editableUser.email,
            role: this.editableUser.role,
            genre: this.editableUser.genre,
        })
        .then(response => {
            // Mettre à jour directement `editableUser` avec les nouvelles données
            this.editableUser = { ...response.data.user };  // Mise à jour avec les données du serveur
            this.isEditing = false;
        })
        .catch(error => {
            console.error('Error saving profile:', error.response ? error.response.data : error);
            alert("An error occurred while saving your profile.");
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
      axios.get(`/api/profile/${this.user.id}/cv`)  // GET pour récupérer le CV
        .then(response => {
          this.cvUrl = response.data.cvUrl;
        })
        .catch(error => {
          console.error('Error fetching CV:', error);
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
        alert("CV uploaded successfully!");
        this.closeCvModal();
      })
      .catch(error => {
        console.error('Error uploading CV:', error);
        alert("An error occurred while uploading the CV.");
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
