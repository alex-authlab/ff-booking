<template>
  <div v-loading="loading" class="ff_booking_wrapper">
    <div class="ff_pre_settings_wrapper" v-if="!settings.is_setup">
      <p>Enable appointment Booking</p>
      <el-button @click="enableBookingModule()" type="success" size="large">Enable Fluent Forms Booking Module
      </el-button>
    </div>
    <div class="ff_booking_settings_wrapper " v-if="settings.is_setup">
      <el-menu :router="true" :default-active="selectedRoute" class="el-menu-demo" mode="horizontal">
        <el-menu-item v-for="menu in topMenus" :route="{ name: menu.name }" :index="menu.name" :key="menu.route">
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
        selectedRoute: this.$route.name,
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
            route: 'general',
            name: 'General'
          },
          {
            route: 'service',
            name: 'Service'
          },
          {
            route: 'provider',
            name: 'Provider'
          },
        ]
      },
      enableBookingModule() {
        jQuery.post(window.ff_booking_settings.ajaxUrl, {
          action: 'handle_booking_ajax_endpoint',
          route: 'enable_booking'
        })
        .then(response => {
          this.$notify.success(response.data.message);
          if (response.data.reload) {
            location.reload();
          }
        });
      },
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

