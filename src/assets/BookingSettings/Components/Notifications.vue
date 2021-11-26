<template>
    <div>
        <notification :key="key" v-for="(email ,key) in emails" :name="emailName(key)|ucFirst"
                      :email_key="key" :emailData="email" v-bind:time='false'
                      @update-email="updateEmail">
        </notification>

    </div>
</template>
<script>
    import notification from "./inc/notification";

    export default {
        name: 'Notifications',
        props: ['notifications' , 'targetUser'],
        components: {
            notification
        },
        methods: {
            updateEmail(emailKey,emailData) {
                this.notifications[this.targetUser][emailKey] = emailData;
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
            },
        },
        computed:{
            emails() {
                let notification = Object.assign([], this.notifications)
                return  notification[this.targetUser]
            },

        },
        mounted() {
        }

    }
</script>
