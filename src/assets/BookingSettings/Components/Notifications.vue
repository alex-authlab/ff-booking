<template>
    <div>
        <notification name="Instant Email" email_key="instant_email" :emailData="getEmail('instant_email')"
                      v-bind:time="false" @update-email="updateEmail"></notification>
        <notification name="Confirmation Email" email_key="confirm_email" :emailData="getEmail('confirm_email')"
                      v-bind:time="false" @update-email="updateEmail"></notification>
        <notification name="Reminder Email" email_key="reminder_email" :emailData="getEmail('reminder_email')"
                      time='before' @update-email="updateEmail"></notification>
        <notification name="Query Email" email_key="query_email" :emailData="getEmail('query_email')" time='after'
                      @update-email="updateEmail"></notification>
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
                notificationsData: {}
            }
        },
        methods: {
            updateEmail(email) {

                this.notifications[email.key] = email.value;
                this.$emit('update-notifications', this.notifications);
            },
            getEmail(key) {
                if (this.notifications[key]) {
                    return this.notifications[key];
                }
                return {};
            }
        },
        watch:{

        }

    }
</script>
