<template>
    <div>
        <Navbar v-if="user" :user="user" />

        <!-- Contenu du Dashboard -->
        <div class="dashboard-content">
            <div v-if="loading">Chargement...</div>
            <div v-else>
                <!-- Vue si l'utilisateur est un client -->
                <div v-if="userRole === 'client'">
                    <div class="header-container-centered">
                        <h2>Vos Annonces Publiées</h2>
                        <button class="btn-add-job" @click="createOffer">
                            <span>+</span>
                        </button>
                    </div>
                    <div v-if="jobPosts.length === 0" class="no-jobs">Vous n'avez publié aucune annonce.</div>
                    <div v-else>
                        <div v-for="job in jobPosts" :key="job.id" class="job-application">
                            <div class="job-application-content">
                                <h3>{{ job.title }}</h3>
                                <p><strong>Description: </strong>{{ job.description }}</p>
                                <p v-if="job.skills && job.skills.length > 0">
                                    <strong>Compétences requises: </strong>
                                    <span
                                        v-for="(skill, index) in job.skills"
                                        :key="index"
                                        class="skill-bubble"
                                    >
                                        {{ skill.name }}
                                    </span>
                                </p>
                                <p><strong>Posté le: </strong>{{ new Date(job.created_at).toLocaleDateString() }}</p>
                                <p><strong>Nombre de postulants: </strong>{{ job.applications?.length || 0 }}</p>
                                <button @click="viewApplications(job.id)" class="btn-view-applications">Voir les postulants</button>
                            </div>

                            <!-- Liste déroulante verticale pour les postulants -->
                            <div v-if="selectedJobId === job.id" class="carousel-container-vertical">
                                <div
                                    v-for="application in job.applications"
                                    :key="application.id"
                                    class="carousel-item"
                                >
                                    <p><strong>Nom : </strong>{{ application.provider.first_name }} {{ application.provider.last_name }}</p>
                                    <a :href="`/profile/${application.provider.id}`" target="_blank">Voir le profil</a>

                                    <!-- Sélection des statuts -->
                                    <div class="status-selector">
                                        <label>
                                            <input
                                                type="radio"
                                                name="status-{{ application.id }}"
                                                value="refused"
                                                :checked="application.status === 'refused'"
                                                @change="updateApplicationStatus(application.id, 'refused', job.id)"
                                            />
                                            <span class="status-refused">Refusé</span>
                                        </label>
                                        <label>
                                            <input
                                                type="radio"
                                                name="status-{{ application.id }}"
                                                value="on hold"
                                                :checked="application.status === 'on hold'"
                                                @change="updateApplicationStatus(application.id, 'on hold', job.id)"
                                            />
                                            <span class="status-on-hold">En attente</span>
                                        </label>
                                        <label>
                                            <input
                                                type="radio"
                                                name="status-{{ application.id }}"
                                                value="accepted"
                                                :checked="application.status === 'accepted'"
                                                @change="confirmAcceptApplication(application.id, job.id)"
                                            />
                                            <span class="status-accepted">Accepté</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Vue si l'utilisateur est un prestataire -->
                <div v-if="userRole === 'provider'">
                    <div class="header-container-centered">
                        <h2>Les annonces auxquelles vous avez postulé</h2>
                    </div>
                    <div v-if="applications.length === 0">Vous n'avez postulé à aucune annonce.</div>
                    <div v-else>
                        <div v-for="application in applications" :key="application.id" class="job-application">
                            <div class="job-application-content">
                                <h3 v-if="application.job_post && application.job_post.title">
                                    {{ application.job_post.title }}
                                </h3>
                                <p v-if="application.job_post && application.job_post.description">
                                    <strong>Description: </strong>{{ application.job_post.description }}
                                </p>
                                <p v-if="application.job_post && application.job_post.skills && application.job_post.skills.length > 0">
                                    <strong>Compétences requises: </strong>
                                    <span
                                        v-for="(skill, index) in application.job_post.skills"
                                        :key="index"
                                        class="skill-bubble"
                                    >
                                        {{ skill.name }}
                                    </span>
                                </p>
                                <p v-if="application.job_post && application.job_post.client">
                                    <strong>Posté par: </strong>
                                    <a :href="`/profile/${application.job_post.client.id}`">
                                        {{ application.job_post.client.first_name }} {{ application.job_post.client.last_name }}
                                    </a>
                                </p>
                                <p><strong>Statut: </strong>
                                    <span
                                        class="status-indicator"
                                        :class="{
                                            'on-hold': application.status === 'on hold',
                                            'accepted': application.status === 'accepted',
                                            'refused': application.status === 'refused'
                                        }"
                                    ></span>
                                    {{ application.status }}
                                </p>
                            </div>
                            <div class="job-application-actions">
                                <button class="btn-unapply" @click="confirmUnapply(application.job_post.id)">
                                    Se désinscrire
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Navbar from './Navbar.vue';
import axios from 'axios';

export default {
    components: {
        Navbar
    },
    data() {
        return {
            user: null,
            userRole: window.userRole,
            loading: true,
            jobPosts: [],
            applications: [],
            selectedJobId: null
        };
    },
    async created() {
        await this.fetchUser();
        if (this.userRole === 'client') {
            await this.fetchClientJobPosts();
        } else if (this.userRole === 'provider') {
            await this.fetchProviderApplications();
        }
        this.loading = false;
    },
    methods: {
        async fetchUser() {
            try {
                const response = await axios.get('/api/user');
                this.user = response.data;
            } catch (error) {
                console.error('Erreur lors de la récupération des données utilisateur:', error);
            }
        },
        async fetchClientJobPosts() {
            try {
                const response = await axios.get('/api/client/job-posts');
                const jobPosts = response.data;
                for (const job of jobPosts) {
                    const applicationsResponse = await axios.get(`/api/job-posts/${job.id}/applications`);
                    job.applications = applicationsResponse.data;
                }
                this.jobPosts = jobPosts;
            } catch (error) {
                console.error('Erreur lors de la récupération des annonces du client :', error);
            }
        },
        viewApplications(jobId) {
            this.selectedJobId = this.selectedJobId === jobId ? null : jobId;
        },
        async chooseProvider(providerId, jobId) {
            if (confirm('Êtes-vous sûr de vouloir sélectionner ce prestataire ?')) {
                try {
                    const response = await axios.post(`/api/job-posts/${jobId}/choose-provider`, {
                        providerId
                    });
                    alert('Prestataire sélectionné avec succès.');
                } catch (error) {
                    console.error('Erreur lors de la sélection du prestataire :', error);
                    alert('Une erreur s\'est produite lors de la sélection du prestataire.');
                }
            }
        },
        async fetchProviderApplications() {
            try {
                const response = await axios.get('/api/provider/dashboard-applications');
                console.log('Applications reçues pour le tableau de bord :', response.data); // Log pour vérifier les données reçues
                this.applications = response.data;
            } catch (error) {
                console.error('Erreur lors de la récupération des candidatures du prestataire :', error);
            }
        },
        createOffer() {
            window.location.href = '/create-offer';
        },
        async unapplyJob(jobId) {
            try {
                const response = await axios.delete(`/api/job-posts/${jobId}/unapply`);
                console.log('Désinscription réussie:', response.data);
                // Retirer l'application de la liste des applications
                this.applications = this.applications.filter(
                    (application) => application.job_post.id !== jobId
                );
            } catch (error) {
                console.error('Erreur lors de la désinscription:', error);
                alert('Une erreur s\'est produite lors de la désinscription. Veuillez réessayer.');
            }
        },
            confirmUnapply(jobId) {
                if (confirm('Êtes-vous sûr de vouloir vous désinscrire de cette annonce ?')) {
                    this.unapplyJob(jobId);
                }
            },
            async updateApplicationStatus(applicationId, status, jobId) {
            try {
                await axios.post(`/api/applications/${applicationId}/update-status`, { status });
                const job = this.jobPosts.find((job) => job.id === jobId);
                const application = job.applications.find((app) => app.id === applicationId);
                application.status = status;
            } catch (error) {
                console.error('Erreur lors de la mise à jour du statut:', error);
                alert('Une erreur s\'est produite lors de la mise à jour du statut.');
            }
        },
        confirmAcceptApplication(applicationId, jobId) {
            if (confirm('En acceptant ce prestataire, il recevra un email de confirmation pour ce poste. Voulez-vous continuer ?')) {
                this.updateApplicationStatus(applicationId, 'accepted', jobId);
            }
        }
    }
};
</script>

<style scoped>

.btn-view-applications {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 5px;
    margin-top: 10px;
}

.btn-view-applications:hover {
    background-color: #0056b3;
}

.application-item {
    margin: 10px 0;
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.btn-choose-provider {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 5px;
    margin-top: 5px;
}

.btn-choose-provider:hover {
    background-color: #218838;
}

.header-container-centered {
    display: flex;
    justify-content: center; /* Centrer horizontalement le conteneur */
    align-items: center; /* Aligner verticalement les éléments */
    gap: 10px; /* Espacement entre le titre et le bouton */
    margin-bottom: 20px;
    text-align: center; /* Centrer le texte */
}

.btn-add-job {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 35px;
    width: 35px;
    font-size: 20px;
    font-weight: bold;
    border: none;
    border-radius: 50%;
    background-color: #004080;
    color: white;
    cursor: pointer;
    transition: background-color 0.2s ease-in-out;
}

.btn-add-job:hover {
    background-color: #003366;
}

body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

.dashboard-content {
    padding: 20px;
}

ul {
    list-style-type: none;
    padding: 0;
}

li {
    border: 1px solid #ccc;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 5px;
}

h2 {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
    color: #004080;
}


.job-list, .application-list {
    margin-top: 20px;
}

.job-post, .job-application {
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
    background-color: #f9f9f9;
    margin-left: 12%;
    margin-right: 12%;
}

.navbar {
    margin: 0;
    padding: 0;
}

.skill-bubble {
    display: inline-block;
    padding: 5px 10px;
    margin: 2px;
    background-color: #e7f3ff;
    color: #004080;
    border-radius: 15px;
    font-size: 12px;
}
.job-application {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 15px;
    background-color: #f9f9f9;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}
.status-indicator {
    display: inline-block;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    margin-left: 5px;
    margin-right: 5px;
    vertical-align: middle;
}

.job-application-content {
    flex-grow: 1;
    padding-right: 20px;
}

.job-application-actions {
    flex-shrink: 0;
}

/* Couleurs pour chaque statut */
.on-hold {
    background-color: orange;
}

.accepted {
    background-color: green;
}

.refused {
    background-color: red;
}

.btn-unapply {
    background-color: #ff4d4d;
    color: white;
    border: none;
    padding: 10px 15px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    cursor: pointer;
    border-radius: 5px;
}

.btn-unapply:hover {
    background-color: #cc0000;
}

.carousel-container-vertical {
    max-height: 300px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.carousel-item {
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fff;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

/* Style for the status selector */
.status-selector {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 10px;
    padding: 10px;
    border-radius: 5px;
}

.status-selector label {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}

.status-selector input {
    display: none;
}

.status-selector .status-refused {
    background-color: red;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
}

.status-selector .status-on-hold {
    background-color: orange;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
}

.status-selector .status-accepted {
    background-color: green;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
}

/* Highlight for the selected status */
.status-selector input:checked + span {
    box-shadow: 0 0 5px 2px rgba(0, 0, 0, 0.3);
    font-weight: bold;
    scale: 1.1;
}
</style>
