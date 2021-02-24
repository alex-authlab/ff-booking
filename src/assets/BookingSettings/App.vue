<template>
    <div v-loading="loading" class="ff_booking_wrapper">
        <div class="ff_pre_settings_wrapper" v-if="!settings.is_setup">
            <h2>Fluent Forms Booking Module</h2>
            <p>Enable your users to pay online as part of the Forms submission process. With Fluent Forms Powerful payment integration, you can easily accept and process payments in your Fluent Forms. Just activate this module and setup your payment methods.</p>
            <el-button @click="enableBookingModule()" type="success" size="large">Enable Fluent Forms Booking Module</el-button>
        </div>

        <div class="ff_settings_wrapper"  v-else >
            <div class="ff_settings_sidebar">
                <ul class="ff_settings_list">




                        <router-link v-for="menuItem in topMenus" :key="menuItem.route" tag="li" exact  :to="{ name: menuItem.route }">
                           <a>  {{ menuItem.title }} </a>
                        </router-link>


                </ul>
            </div>
            <div class="ff_settings_container">
                <router-view></router-view>
            </div>
        </div>
    </div>
</template>
<script type="text/babel">

    export default {
        name: 'booking-settings',
        props: ['settings'],
        components: {

        },
        data() {
            return {
                topMenus: [],
                loading: false,
                selectedMethod: this.settings.active_nav
            }
        },
        methods: {
            setTopmenu() {
                this.topMenus = [
                    {
                        route: 'service',
                        title: 'Service'
                    } ,
                    {
                        route: 'bookings',
                        title: 'Booking'
                    }
                ]
            },
            enableBookingModule() {
                jQuery.post(window.ajaxurl, {
                    action: 'handle_booking_ajax_endpoint',
                    route: 'enable_booking'
                })
                .then(response => {
                    this.$notify.success(response.data.message);
                    if(response.data.reload) {
                        location.reload();
                    }
                });
            },
            disableBookingModule(){
                jQuery.post(window.ajaxurl, {
                        action: 'handle_booking_ajax_endpoint',
                        route: 'disable_booking'
                    })
                    .then(response => {
                        this.$notify.success(response.data.message);
                        if(response.data.reload) {
                            location.reload();
                        }
                    });
            }
        },
        mounted() {
            this.setTopmenu();
            console.log(this.$route.name);
            if(!this.$route.name){
                this.$router.push(this.settings.active_nav);
            }
        }
    }
</script>

<style lang="scss">
    .ff_pre_settings_wrapper {
        text-align: center;
        padding: 20px 50px 50px;
        max-width: 800px;
        margin: 50px auto;
        background: #f1f1f1;
        border-radius: 20px;
        h2 {
            line-height: 36px;
            font-size: 26px;
        }
    }
    .ff_booking_wrapper {
        margin: 0px ;
        .el-tabs--border-card>.el-tabs__content {
            padding: 45px;
        }
    }
</style>
