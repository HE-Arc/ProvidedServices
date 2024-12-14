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
                        <div v-for="job in jobPosts" :key="job.id" class="job-post">
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
                            <div class="btn-container">
                                <button
                                    @click="viewApplications(job.id)"
                                    :class="{
                                        'btn-view-applications': selectedJobId !== job.id,
                                        'btn-hide-applications': selectedJobId === job.id
                                    }"
                                >
                                    {{ selectedJobId === job.id ? 'Cacher les postulants' : 'Voir les postulants' }}
                                </button>
                            </div>

                            <!-- Liste déroulante verticale pour les postulants -->
                            <div v-if="selectedJobId === job.id" class="carousel-container-vertical">
                                <div
                                    v-for="application in job.applications"
                                    :key="application.id"
                                    class="carousel-item"
                                >
                                <strong>Nom: </strong>
                                <a :href="`/profile/${application.provider.id}`">
                                    {{ application.provider.first_name }} {{ application.provider.last_name }}
                                </a>

                                    <!-- Sélection des statuts -->
                                    <div class="status-selector">
                                        <!-- Icône pour refuser -->
                                        <i
                                            class="fas fa-times-circle status-icon refused"
                                            :class="{ active: application.status === 'refused' }"
                                            @click="updateApplicationStatus(application.id, 'refused', job.id)"
                                            title="Refuser"
                                        ></i>

                                        <!-- Icône pour accepter -->
                                        <i
                                            class="fas fa-check-circle status-icon accepted"
                                            :class="{ active: application.status === 'accepted' }"
                                            @click="confirmAcceptApplication(application.id, job.id)"
                                            title="Accepter"
                                        ></i>
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
                        <div v-for="application in applications" :key="application.id" class="job-post">
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
                                        'i-on-hold': application.status === 'on hold',
                                        'i-accepted': application.status === 'accepted',
                                        'i-refused': application.status === 'refused'
                                    }"
                                ></span>
                                {{ application.status }}
                            </p>
                            <div class="btn-container">
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

</style>
