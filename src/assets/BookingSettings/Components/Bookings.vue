<template>
    <div class="ff_payment_general_settings">
        <el-form rel="currency_settings" label-position="left" :model="settings" label-width="220px">
            <div class="ff_settings_section">

                 <el-row class="setting_header">
                    <el-col :md="18">
                        <h2>

                            Bookings List
                        </h2>
                    </el-col>
                    <el-col :md="6" class="action-buttons clearfix mb15">
<!--                        <el-button size="medium" class="pull-right" type="primary" >-->
<!--                            New +-->
<!--                        </el-button>-->
                    </el-col>
                </el-row>

                <el-form  label-width="150px" label-position="left">

                <!-- {{$date(weekStart, "yyyy-MM-dd").add(7, 'day')}} -->


                <el-form-item label="Select Week">
                     <el-form-item>
                          <el-date-picker
                            @change="weekChange"
                              v-model="weekStart"
                              type="week"
                              format="Week WW"
                              value-format="yyyy-MM-dd"
                              placeholder="Pick a week">
                            </el-date-picker>
                    </el-form-item>

                </el-form-item>

                </el-form>

                <el-calendar v-model="value" :range="[weekStart, weekEnd]">

                </el-calendar>

            </div>
        </el-form>

        <div class="action_right">
            <el-button @click="saveSettings()" type="primary" size="small">Save Settings</el-button>
        </div>


    </div>
</template>

<script type="text/babel">

    export default {
        name: 'Bookings',
        props: [],
        components: {
        },
        data() {
            return {
                settings:{},
                value: new Date(),
                weekStart: this.$date().weekday(-6).format('YYYY-MM-D') ,// last monday date
                weekEnd: this.$date().weekday(0).format('YYYY-MM-D')  // next monday date
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
            },
            weekChange(data){
                this.weekStart = data
                this.weekEnd = this.$date(this.weekStart, "YYYY-MM-D").add(6, 'day').format("YYYY-MM-DD");

            },

        },

         mounted() {


         },



    }
</script>

<style lang="scss">
    .item_full_width {
        width: 100%;
    }

</style>
