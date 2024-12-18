<template>
    <div>
        <Navbar v-if="user" :user="user" />
        <Notification ref="notification" />

        <!-- Contenu du Dashboard -->
        <div class="dashboard-content">
            <div v-if="loading">Chargement...</div>
            <div v-else>
                <!-- Vue si l'utilisateur est un client -->
                <div v-if="userRole === 'client'">
                    <div class="header-container-centered">
                        <h2>Vos annonces publiées</h2>
                        <button class="btn-add-job" @click="createOffer">
                            <span>+</span>
                        </button>
                    </div>
                    <div v-if="jobPosts.length === 0" class="no-jobs">Vous n'avez publié aucune annonce.</div>
                    <div v-else>
                        <div v-for="job in jobPosts" :key="job.id" class="job-post">
                            <h3>{{ job.title }}</h3>
                            <p><strong>Description :</strong> {{ job.description }}</p>
                            <p v-if="job.skills && job.skills.length > 0">
                                <strong>Compétences requises :</strong>
                                <span
                                    v-for="(skill, index) in job.skills"
                                    :key="index"
                                    class="skill-bubble"
                                >
                                    {{ skill.name }}
                                </span>
                            </p>
                            <p><strong>Posté le :</strong> {{ new Date(job.created_at).toLocaleDateString() }}</p>
                            <p><strong>Nombre de postulants :</strong> {{ job.applications?.length || 0 }}</p>
                            <div class="btn-container">
                                <button v-if="job.applications && job.applications.length > 0"
                                    @click="viewApplications(job.id)"
                                    :class="{
                                        'btn-view-applications': selectedJobId !== job.id,
                                        'btn-hide-applications': selectedJobId === job.id
                                    }"
                                >
                                    {{ selectedJobId === job.id ? 'Cacher les postulants' : 'Voir les postulants' }}
                                </button>
                                <button
                                    class="btn-delete-job"
                                    @click="openDeleteJobModal(job.id)"
                                >
                                    Supprimer
                                </button>
                            </div>
                            <!-- Liste déroulante verticale pour les postulants -->
                            <div v-if="selectedJobId === job.id" class="carousel-container-vertical">
                                <div v-for="application in job.applications" :key="application.provider_id" class="carousel-item">
                                    <strong>Nom :</strong>
                                    <a :href="`/profile/${application.provider.id}`">
                                        {{ application.provider.first_name }} {{ application.provider.last_name }}
                                    </a>

                                    <!-- Sélection des statuts -->
                                    <div class="status-selector">
                                        <i class="fas fa-times-circle status-icon refused"
                                            :class="{ active: application.status === 'refused' }"
                                            @click="updateApplicationStatus(application.job_post_id, application.provider_id, 'refused')"
                                            title="Refuser"></i>

                                        <i class="fas fa-check-circle status-icon accepted"
                                            :class="{ active: application.status === 'accepted' }"
                                            @click="openModal(job.id, application.provider)"
                                            title="Accepter"></i>
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
                        <div v-for="application in applications" :key="application.job_post_id" class="job-post">
                            <h3 v-if="application.job_post && application.job_post.title">
                                {{ application.job_post.title }}
                            </h3>
                            <p v-if="application.job_post && application.job_post.description">
                                <strong>Description :</strong> {{ application.job_post.description }}
                            </p>
                            <p v-if="application.job_post && application.job_post.skills && application.job_post.skills.length > 0">
                                <strong>Compétences requises :</strong>
                                <span
                                    v-for="(skill, index) in application.job_post.skills"
                                    :key="index"
                                    class="skill-bubble"
                                >
                                    {{ skill.name }}
                                </span>
                            </p>
                            <p v-if="application.job_post && application.job_post.client">
                                <strong>Posté par :</strong>
                                <a :href="`/profile/${application.job_post.client.id}`">
                                    {{ application.job_post.client.first_name }} {{ application.job_post.client.last_name }}
                                </a>
                            </p>
                            <p><strong>Statut :</strong>
                                <span
                                    class="status-indicator"
                                    :class="{
                                        'i-on-hold': application.status === 'on hold',
                                        'i-accepted': application.status === 'accepted',
                                        'i-refused': application.status === 'refused'
                                    }"
                                ></span>
                                {{ translateStatus(application.status) }}
                            </p>
                            <div class="btn-container">
                                <button 
                                    v-if="application.status !== 'refused'" 
                                    class="btn-unapply" 
                                    @click="confirmUnapply(application.job_post.id)"
                                >
                                    Se désinscrire
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale de suppression d'une annonce -->
        <div v-if="showDeleteJobModal" class="modal-overlay">
            <div class="modal">
                <h2>Confirmer la suppression</h2>
                <p>
                    Êtes-vous sûr de vouloir supprimer cette annonce ? Cette action est irréversible.
                </p>
                <div class="modal-buttons">
                    <button class="btn-cancel" @click="closeDeleteJobModal">Annuler</button>
                    <button class="btn-confirm" @click="deleteJob">Confirmer</button>
                </div>
            </div>
        </div>

        <!-- Modale de désinscription -->
        <div v-if="showUnapplyModal" class="modal-overlay">
            <div class="modal">
                <h2>Confirmer la désinscription</h2>
                <p>
                    Êtes-vous sûr de vouloir vous désinscrire de cette annonce ?
                </p>
                <div class="modal-buttons">
                    <button class="btn-cancel" @click="closeUnapplyModal">Annuler</button>
                    <button class="btn-confirm" @click="confirmUnapplyJob">Confirmer</button>
                </div>
            </div>
        </div>

        <!-- Modale de confirmation -->
        <div v-if="showModal" class="modal-overlay">
            <div class="modal">
                <h2>Confirmer la sélection du prestataire</h2>
                <p>
                    Êtes-vous sûr de vouloir sélectionner <strong>{{ selectedProvider?.first_name }} {{ selectedProvider?.last_name }}</strong> pour l'annonce <strong>{{ selectedJob?.title }}</strong> ? Un mail lui sera envoyé.
                </p>
                <div class="modal-buttons">
                    <button class="btn-cancel" @click="closeModal">Annuler</button>
                    <button class="btn-confirm" @click="confirmProviderSelection()">Confirmer</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Navbar from './Navbar.vue';
import axios from 'axios';
import Notification from './Notification.vue';

export default {
    components: {
        Navbar,
        Notification
    },
    data() {
        return {
            user: null,
            userRole: window.userRole,
            loading: true,
            jobPosts: [],
            applications: [],
            selectedJobId: null,
            showModal: false,
            selectedProvider: null, 
            selectedJob: null,
            showUnapplyModal: false,
            unapplyJobId: null,
            showDeleteJobModal: false,
            jobIdToDelete: null,
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
        translateStatus(status) {
            const statusMap = {
                'on hold': 'En attente',
                'accepted': 'Accepté',
                'refused': 'Refusé',
            };
            return statusMap[status] || status; 
        },
        openDeleteJobModal(jobId) {
            this.jobIdToDelete = jobId;
            this.showDeleteJobModal = true;
        },
        closeDeleteJobModal() {
            this.jobIdToDelete = null;
            this.showDeleteJobModal = false;
        },
        async deleteJob() {
            try {
                await axios.delete(`/api/job-posts/${this.jobIdToDelete}`);
                this.jobPosts = this.jobPosts.filter(job => job.id !== this.jobIdToDelete);
                this.$refs.notification.showNotification('Annonce supprimée avec succès.', 'success');
            } catch (error) {
                this.$refs.notification.showNotification(`Erreur : ${error.response?.data?.message || 'Une erreur s\'est produite lors de la suppression de l\'annonce.'}`, 'error');
            } finally {
                this.closeDeleteJobModal();
            }
        },
        confirmUnapply(jobPostId) {
            this.unapplyJobId = jobPostId;
            this.showUnapplyModal = true;
        },
        closeUnapplyModal() {
            this.unapplyJobId = null;
            this.showUnapplyModal = false;
        },
        openModal(jobId, provider) {
            this.showModal = true;
            this.selectedProvider = provider;
            this.selectedJobIdForModal = jobId;
        },
        closeModal() {
            this.showModal = false;
            this.selectedProvider = null;
            this.selectedJobIdForModal = null;
        },
        async unapplyJob(jobPostId) {
            try {
                await axios.delete(`/api/job-posts/${jobPostId}/unapply`);
                this.applications = this.applications.filter(app => app.job_post_id !== jobPostId);
                this.$refs.notification.showNotification('Vous avez été désinscrit avec succès.', 'success');
            } catch (error) {
                this.$refs.notification.showNotification(
                    `Erreur lors de la désinscription : ${error.response?.data?.message || 'Une erreur est survenue.'}`,
                    'error'
                );
            }
        },
        async confirmUnapplyJob() {
            try {
                await this.unapplyJob(this.unapplyJobId);
            } finally {
                this.closeUnapplyModal();
            }
        },
        async fetchUser() {
            try {
                const response = await axios.get('/api/user');
                this.user = response.data;
            } catch (error) {
                this.$refs.notification.showNotification('Erreur lors de la récupération des données utilisateur.', 'error');
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
                this.$refs.notification.showNotification('Erreur lors de la récupération des annonces.', 'error');
            }
        },
        viewApplications(jobId) {
            this.selectedJobId = this.selectedJobId === jobId ? null : jobId;
        },
        async fetchProviderApplications() {
            try {
                const response = await axios.get('/api/provider/dashboard-applications');
                this.applications = response.data;
            } catch (error) {
                this.$refs.notification.showNotification('Erreur lors de la récupération des candidatures.', 'error');
            }
        },
        createOffer() {
            window.location.href = '/create-offer';
        },
        async confirmProviderSelection() {
            try {
                await axios.post(`/api/job-posts/${this.selectedJobIdForModal}/choose-provider`, {
                    providerId: this.selectedProvider.id,
                });
                this.closeModal();
                await this.fetchClientJobPosts();
                this.$refs.notification.showNotification('Le prestataire a été sélectionné avec succès.', 'success');
            } catch (error) {
                this.$refs.notification.showNotification(
                    `Erreur lors de la sélection du prestataire : ${error.response?.data?.message || 'Une erreur est survenue.'}`,
                    'error'
                );
            }
        },
        async updateApplicationStatus(jobPostId, providerId, status) {
            try {
                await axios.post(`/api/applications/${jobPostId}/${providerId}/update-status`, { status });
                await this.fetchClientJobPosts();
                this.$refs.notification.showNotification('Statut mis à jour avec succès.', 'success');
            } catch (error) {
                this.$refs.notification.showNotification(
                    `Erreur lors de la mise à jour du statut : ${error.response?.data?.message || 'Une erreur est survenue.'}`,
                    'error'
                );
            }
        },
    }
};
</script>

<style scoped>

</style>