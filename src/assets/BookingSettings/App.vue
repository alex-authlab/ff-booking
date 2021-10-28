<template>
    <div v-loading="loading" class="ff_booking_wrapper">

        <div class="ff_booking_settings_wrapper ">
            <el-menu :router="true" :default-active="selectedRoute" class="el-menu-demo" mode="horizontal">
                <el-menu-item v-for="menu in topMenus" :route="{ name: menu.name }" :index="menu.name"
                              :key="menu.route">
                    {{ menu.name }}
                </el-menu-item>
            </el-menu>

            <div class="ff_booking_router">
                <router-view></router-view>
            </div>

        </div>
    </div>
</template>
<script type="text/babel">


    export default {
        name: 'booking-settings',
        props: ['settings'],
        components: {},
        data() {
            return {
                loading: false,
                topMenus: [],
            }
        },
        methods: {
            setTopmenu() {
                this.topMenus = [
                    {
                        route: 'bookings',
                        name: 'Bookings'
                    },
                    {
                        route: 'service',
                        name: 'Service'
                    },
                    {
                        route: 'provider',
                        name: 'Provider'
                    },
                    {
                        route: 'settings',
                        name: 'Settings'
                    },
                ]
            },
        },
        computed: {
            selectedRoute() {
                return this.$route.name;
            }
        },
        mounted() {
            this.setTopmenu();
            if (this.$route.path == '/') {
                this.$router.push({name: window.ff_booking_settings.active_nav});
            }

            jQuery('li.ff_item_booking_settings').addClass('active');
        }
    }
</script>

