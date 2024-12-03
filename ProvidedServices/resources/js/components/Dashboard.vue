<template>
    <div>
      <h1>Tableau de Bord</h1>
      <div v-if="loading">Chargement...</div>
      <div v-else>
        <!-- Vue si l'utilisateur est un client -->
        <div v-if="userRole === 'client'">
          <h2>Vos Annonces Publiées</h2>
          <div v-if="jobPosts.length === 0">Vous n'avez publié aucune annonce.</div>
          <ul v-else>
            <li v-for="job in jobPosts" :key="job.id">
              <h3>{{ job.title }}</h3>
              <p>{{ job.description }}</p>
              <p>Compétences requises : {{ job.skills.map(skill => skill.name).join(', ') }}</p>
            </li>
          </ul>
          <button @click="createNewJob">Créer une Nouvelle Annonce</button>
        </div>

        <!-- Vue si l'utilisateur est un prestataire -->
        <div v-if="userRole === 'provider'">
          <h2>Les Annonces sur lesquelles vous avez postulé</h2>
          <div v-if="applications.length === 0">Vous n'avez postulé à aucune annonce.</div>
          <ul v-else>
            <li v-for="application in applications" :key="application.id">
              <h3>{{ application.jobPost.title }}</h3>
              <p>{{ application.jobPost.description }}</p>
              <p>Compétences requises : {{ application.jobPost.skills.map(skill => skill.name).join(', ') }}</p>
              <p>Statut : {{ application.status }}</p>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </template>

  <script>
export default {
    data() {
        return {
            userRole: window.userRole,
            userId: window.userId,
            loading: true,
            jobPosts: [],
            applications: []
        };
    },
    async created() {
        if (this.userRole === 'client') {
            await this.fetchClientJobPosts();
        } else if (this.userRole === 'provider') {
            await this.fetchProviderApplications();
        }
        this.loading = false;
    },
    methods: {
        async fetchClientJobPosts() {
            try {
                const response = await axios.get('/client/job-posts', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`
                    }
                });
                this.jobPosts = response.data;
            } catch (error) {
                console.error("Erreur lors de la récupération des annonces du client :", error);
            }
        },
        async fetchProviderApplications() {
            try {
                const response = await axios.get('/provider/applications', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`
                    }
                });
                this.applications = response.data;
            } catch (error) {
                console.error("Erreur lors de la récupération des candidatures du prestataire :", error);
            }
        }
    }
};

  </script>

  <style scoped>
  ul {
    list-style-type: none;
    padding: 0;
  }

  li {
    border: 1px solid #ccc;
    padding: 15px;
    margin-bottom: 10px;
  }
  </style>

