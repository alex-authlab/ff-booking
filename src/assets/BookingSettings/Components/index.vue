<template>
    <div class="ff_payment_general_settings">
        <el-form rel="currency_settings" label-position="left" :model="settings" label-width="220px">
            <div class="wpf_settings_section">
                <el-form-item>
                    <template slot="label">
                        Status
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3>Status</h3>
                                <p>
                                    if you disable this then all the payment releated functions will be disabled. If you want to process/accept payment using fluent forms. You should enable this.
                                </p>
                            </div>
                            <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                    </template>
                    <el-checkbox true-label="yes" false-label="no" v-model="settings.status">Enable Payment Module</el-checkbox>
                </el-form-item>
                <el-form-item>
                    <template slot="label">
                        Business Name
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3>Business Name</h3>
                                <p>
                                    Please provide your business name. It will be used to paypal's business name when redirect to checkout.
                                </p>
                            </div>
                            <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input v-model="settings.business_name" placeholder="Business Name" />
                </el-form-item>

                <el-form-item>
                    <template slot="label">
                        Business Address
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3>Business Address</h3>
                                <p>
                                    Please provide your full business address including street, city, zip, state and country.
                                </p>
                            </div>
                            <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input v-model="settings.business_address" placeholder="Full Business Address" />
                </el-form-item>

                <el-form-item label="Business Logo">
                    <photo-uploader enable_clear="yes" design_mode="horizontal" v-model="settings.business_logo" />
                </el-form-item>

                <div class="sub_section_header">
                    <h3>Currency Settings</h3>
                </div>
                <div class="sub_section_body">
                    <el-form-item>
                        <template slot="label">
                            Default Currency
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Default Currency</h3>
                                    <p>
                                        Provide your default currency. You can also change your currency to each form in form's payment settings
                                    </p>
                                </div>
                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </template>
                        <el-select size="small" filterable v-model="settings.currency" placeholder="Select Currency">
                            <el-option
                                    v-for="(currencyName, currenyKey) in currencies"
                                    :key="currenyKey"
                                    :label="currencyName"
                                    :value="currenyKey">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="Currency Sign Position">
                        <el-radio-group v-model="settings.currency_sign_position">
                            <el-radio v-for="(sign, sign_key) in currency_sign_positions" :key="sign_key"
                                      :label="sign_key">{{sign}}
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="Currency Separators">
                        <el-select class="item_full_width" v-model="settings.currency_separator">
                            <el-option value="dot_comma" label="Comma as Thousand and Dot as Decimal (EG: 12,000.00)" />
                            <el-option value="comma_dot" label="Dot as Thousand and Comma as Decimal ( EG: 12.000,00 )" />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="">
                        <el-checkbox true-label="0" false-label="2" v-model="settings.decimal_points">Hide decimal points for rounded numbers</el-checkbox>
                    </el-form-item>
                </div>
            </div>
        </el-form>

        <div class="action_right">
            <el-button @click="saveSettings()" type="primary" size="small">Save Settings</el-button>
        </div>

        <h3 style="color: red" v-if="settings.status == 'no'">Payment Module has been disabled currently. No Payments will be processed and associate functions will be disabled</h3>

    </div>
</template>

<script type="text/babel">
    import PhotoUploader from "./PhotoUploader";
    export default {
        name: 'index',
        props: ['settings'],
        components: {
            PhotoUploader
        },
        data() {
            return {
                currencies: window.ff_payment_settings.currencies,
                currency_sign_positions: {
                    left: 'Left ($100)',
                    right: 'Right (100$)',
                    left_space: 'Left Space ($ 100)',
                    right_space: 'Right Space 100 $'
                }
            }
        },
        methods: {
            saveSettings() {
                jQuery.post(window.ajaxurl, {
                        action: 'handle_payment_ajax_endpoint',
                        route: 'update_global_settings',
                        settings: this.settings
                    })
                    .then(response => {
                        this.$notify.success(response.data.message);
                    });
            }
        }
    }
</script>

<style lang="scss">
    .item_full_width {
        width: 100%;
    }

</style>
