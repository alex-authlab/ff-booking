<template>
    <div>

        <notification :key="key" v-for="(notification, key) in notificationsData" :name="emailName(key)|ucFirst"
                      :email_key="key" :emailData="getEmail(key)" v-bind:time='false'
                      @update-email="updateEmail">
        </notification>

    </div>
</template>
<script>
    import notification from "./inc/notification";

    export default {
        name: 'Notifications',
        props: ['notifications'],
        components: {
            notification
        },
        data() {
            return {
                notificationsData: {
                    'instant_email': '',
                    'confirm_email': '',
                    'reminder_email': '',
                    'query_email': ''

                }
            }
        },
        methods: {
            updateEmail(email) {

                this.notificationsData[email.key] = email.value;
                this.$emit('update-notifications', this.notificationsData);
            },
            getEmail(key) {
                if (this.notificationsData[key]) {
                    return this.notificationsData[key];
                }
                return {};
            },
            emailName(key) {
                let name = key.replace("_", " ");
                return name;
            }
        },
        mounted() {
            if (this.notifications && Object.keys(this.notifications).length) {
                this.notificationsData = this.notifications
            }
        },
        computed: {}

    }
</script>
