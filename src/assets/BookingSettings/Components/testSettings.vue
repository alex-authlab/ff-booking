<template>
    <div v-loading="loading" class="ff_method_settings">
        <el-form v-if="settings" label-position="left" rel="test_settings" :model="settings" label-width="220px">
            <el-form-item label="Status">
                <el-checkbox true-label="yes" false-label="no" v-model="settings.is_active">
                    Enable Offline/Test Payment Method
                </el-checkbox>
            </el-form-item>
            <el-form-item label="Payment Mode">
                <el-radio-group v-model="settings.payment_mode">
                    <el-radio label="test">Sandbox Mode</el-radio>
                    <el-radio label="live">Live Mode</el-radio>
                </el-radio-group>
            </el-form-item>

            <div class="action_right">
                <el-button @click="saveSettings()" type="primary" size="small">Save Settings</el-button>
            </div>

            <div v-if="errors" class="ff-errors">
                <ul>
                    <li style="color: red;" v-for="(error,errorKey) in errors" :key="errorKey">{{error}}</li>
                </ul>
            </div>

        </el-form>
        <div v-else-if="!loading">
            <p>Sorry! No settings found. Maybe your payment module is disabled!</p>
        </div>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'testSettings',
        data() {
            return {
                loading: false,
                settings: false,
                errors: false
            }
        },
        methods: {
            getSettings() {
                this.loading = true;
                this.errors = false;
                jQuery.get(window.ajaxurl, {
                    action: 'handle_payment_ajax_endpoint',
                    route: 'get_payment_method_settings',
                    method: 'test'
                })
                    .then(response => {
                        this.settings = response.data.settings;
                    })
                    .fail(error => {

                    })
                    .always(() => {
                        this.loading = false;
                    })
            },
            saveSettings() {
                this.errors = false;
                this.saving = true;
                jQuery.post(window.ajaxurl, {
                    action: 'handle_payment_ajax_endpoint',
                    route: 'save_payment_method_settings',
                    method: 'test',
                    settings: this.settings
                })
                    .then((response) => {
                        this.$notify.success(response.data.message);
                    })
                    .fail((error) => {
                        this.$notify.error(error.responseJSON.data.message);
                        this.errors = error.responseJSON.data.errors;
                        console.log(error);
                    })
                    .always(() => {
                        this.saving = false;
                    });
            }
        },
        mounted() {
            this.getSettings();
        }
    }
</script>
