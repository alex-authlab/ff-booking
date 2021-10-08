<template>
  <div class="ff_booking_info">
    <el-form :data="booking_info" label-position="top">

      <el-form-item label="Booking info">
        <el-input type="text" v-model="booking_info.title" placeholder="Service Title" />
        <p>Name of the Service {{booking_id}}</p>
      </el-form-item>



      <el-row :gutter="30">
        <el-col :span="8">
          <el-form-item label="Show End Time Slot">
            <el-radio-group v-model="booking_info.show_end_time">
              <el-radio label="show">Show</el-radio>
              <el-radio label="hide">Hide</el-radio>
            </el-radio-group>
            <p>Show Booking End Time Slot</p>
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="Show Booked Slot">
            <el-radio-group v-model="booking_info.show_booked_time">
              <el-radio label="show">Show</el-radio>
              <el-radio label="hide">Hide</el-radio>
            </el-radio-group>
            <p>Show Booked Time Slot</p>
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="Status">

            <el-radio-group v-model="booking_info.status">
              <el-radio label="active">Active</el-radio>
              <el-radio label="inactive">Inactive</el-radio>
            </el-radio-group>
            <p>Status</p>
          </el-form-item>
        </el-col>

      </el-row>



    </el-form>
    <div v-if="errors">
      <ul style="color: red;">
        <li v-for="error in errors">{{Object.values(error).join(', ')}}</li>
      </ul>
    </div>
  </div>
</template>
<script>
  export default {
    name: 'BookingInfo',
    data(){
      return {
        loading: false,
        booking_info:{},
        errors:'',
        booking_id: this.$route.params.bookingId,
      }
    },
  }
</script>
