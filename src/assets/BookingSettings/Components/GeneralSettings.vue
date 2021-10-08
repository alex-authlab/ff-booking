<template>
  <div class="ff_booking_general_settings">
    <div class="ff_booking_navigation">
      <ul>
        <li @click="current_page = 'general'" :class="{ff_active: current_page == 'general'}">General</li>
      </ul>
    </div>

    <el-form label-position="left"  label-width="220px">
      <div class="ff_booking_settings_section">
        <div v-if="current_page == 'general'" >
          <el-form-item>
            <template slot="label">
              Status
              <el-tooltip class="item" placement="bottom-start" effect="light">
                <div slot="content">
                  <h3>Status</h3>
                  <p>
                    Disable Booking
                  </p>
                </div>
                <i class="el-icon-info el-text-info"></i>
              </el-tooltip>
            </template>
            <el-checkbox @change="toggleBookingModule" true-label="1" false-label="0" v-model="general_settings.is_setup">Enable Booking Module
            </el-checkbox>
          </el-form-item>

        </div>

      </div>
    </el-form>

    <div class="action_right">
      <el-button @click="saveSettings()" type="primary" size="small">Save Settings</el-button>
    </div>

    <h3 style="color: red" v-if="general_settings.status == 'no'">Booking Module has been disabled currently.</h3>

  </div>
</template>

<script type="text/babel">

  export default {
    name: 'general_payment_settings',
    props: ['settings'],
    components: {
    },
    data() {
      return {
        general_settings: window.ff_booking_settings,
        current_page: 'general'
      }
    },
    methods: {
      saveSettings() {

      },
      toggleBookingModule() {
        jQuery.post(window.ff_booking_settings.ajaxUrl, {
          action: 'handle_booking_ajax_endpoint',
          status:this.general_settings.is_setup,
          route: 'toggle_booking'
        })
            .then(response => {
              this.$notify.success(response.data.message);
              if (response.data.reload) {
                location.reload();
              }
            });
      },
    }
  }
</script>

<style lang="scss">
  .item_full_width {
    width: 100%;
  }

</style>
