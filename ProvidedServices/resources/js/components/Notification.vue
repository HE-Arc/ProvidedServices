<template>
<div v-if="notification.show" :class="['notification', notification.type]" class="fade-in-out">
    {{ notification.message }}
</div>
</template>

<script>
export default {
data() {
    return {
    notification: {
        show: false,
        message: '',
        type: '' // 'success' or 'error'
    }
    };
},
methods: {
    showNotification(message, type) {
    this.notification.message = message;
    this.notification.type = type;
    this.notification.show = true;

    setTimeout(() => {
        this.notification.show = false;
    }, 5000); // Hide after 5 seconds
    }
}
};
</script>

<style scoped>

</style>
