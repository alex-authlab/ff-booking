<template>
    <div class="ff_payment_general_settings">
        <el-form label-position="left" :model="settings" label-width="220px">
            <div class="">

                 <el-row class="setting_header">
                    <el-col :md="18">
                        <h2>
                            Bookings List
                        </h2>
                    </el-col>
                    <el-col :md="6" class="action-buttons clearfix mb15">

                    </el-col>
                </el-row>

                <el-form  label-width="150px" label-position="left">

                <!-- {{$date(weekStart, "yyyy-MM-dd").add(7, 'day')}} -->


                <el-form-item label="Select Range">
                     <el-form-item>
                       <el-date-picker
                           v-model="date_range"
                           type="daterange"
                           size="small"
                           format="yyyy-MM-dd"
                           value-format="yyyy-MM-dd"
                           range-separator="-"
                           @change="get()"
                           start-placeholder="Start date"
                           end-placeholder="End date">
                       </el-date-picker>
                    </el-form-item>

                </el-form-item>

                </el-form>


                <el-table
                        :data="tableData"
                        border
                        style="width: 100%">
                    <el-table-column
                            prop="date"
                            label="Date"
                            width="180">
                    </el-table-column>
                    <el-table-column
                            prop="title"
                            label="Form"
                            width="180">
                      <template slot-scope="props">
                        <a v-if="props.row.submission_url" :href="props.row.submission_url">#{{props.row.title}}</a>
                        <span v-else>n/a</span>
                      </template>
                    </el-table-column>
                    <el-table-column
                            prop="email"
                            label="Email">
                    </el-table-column>
                </el-table>
              <el-pagination
                  background
                  @current-change="get"
                  :hide-on-single-page="true"
                  small
                  :page-size="per_page"
                  :current-page.sync="page_number"
                  layout="prev, pager, next"
                  :total="total">
              </el-pagination>



            </div>
        </el-form>

        <div class="action_right">
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
              logs: [],
              loading: false,
              page_number: 1,
              per_page: 20,
              total: 0,
              weekDays:[
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'ThursDay',
                    'Friday',

                ],
                tableData: [ {
                    date: '2016-05-01',
                    name: 'Tom',
                    address: 'No. 189, Grove St, Los Angeles'
                }],
                settings:{},
                date_range: [],
                weekStart: this.$date().weekday(-6).format('YYYY-MM-D') ,// last monday date
                weekEnd: this.$date().weekday(0).format('YYYY-MM-D')  // next monday date
            }

        },

        methods: {
          get(){
            this.outerLoading = true;
            jQuery.get(window.ajaxurl, {
              action: 'handle_booking_ajax_endpoint',
              route: 'get_bookings',
              page_number: this.page_number,
              per_page: this.per_page,
              date_range:this.date_range
            })
                .then(response => {
                  console.log(response)
                  this.tableData = response.data.data;
                });
          },
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
            setDefaultDate(data){
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() + 3600 * 1000 * 24 * 30);
              this.date_range = [ this.$date(start).format('YYYY-MM-DD'), this.$date(end).format('YYYY-MM-DD')];

            },

        },
        computed:{
        },

         mounted() {
           this.setDefaultDate()
           this.get();
         },

    }
</script>

<style lang="scss">
    .item_full_width {
        width: 100%;
    }

</style>
