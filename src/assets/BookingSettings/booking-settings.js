import Vue from 'vue';
import locale from 'element-ui/lib/locale';
import lang from 'element-ui/lib/locale/lang/en';


import Router from 'vue-router';

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'

Vue.use(Router);
Vue.use(ElementUI);

import {
    Tabs,
    TabPane,
    ColorPicker,
    Button,
    ButtonGroup,
    Input,
    Checkbox,
    Select,
    Option,
    Collapse,
    CollapseItem,
    Popover,
    Slider,
    Form,
    FormItem,
    Radio,
    RadioGroup,
    Switch,
    Tooltip,
    Loading,
    Message,
    Notification
} from 'element-ui';

Vue.use(Loading.directive);
Vue.prototype.$loading = Loading.service;
Vue.prototype.$message = Message;
Vue.prototype.$notify = Notification;


Vue.use(Button);
Vue.use(ButtonGroup);
Vue.use(Input);
Vue.use(Form);
Vue.use(Tooltip);
Vue.use(FormItem);
Vue.use(Radio);
Vue.use(RadioGroup);
Vue.use(Tabs);
Vue.use(Switch);
Vue.use(TabPane);
Vue.use(Checkbox);
Vue.use(Select);
Vue.use(Option);
Vue.use(Popover);
Vue.use(Slider);

import dayjs from 'dayjs';

var weekday = require('dayjs/plugin/weekday');
dayjs.extend(weekday);

import App from './App.vue';
import {routes} from './routes';

const router = new Router({
                    routes: routes,
                    linkActiveClass: 'active'
                });
locale.use(lang);


Object.defineProperties(Vue.prototype, {
    $date: {
        get() {
            return dayjs
        }
    }
});



new Vue({
    el: "#ff-booking-settings",
    router: router,
    components: {
        'ff-booking-settings': App
    },
    data: {
        settings: window.ff_booking_settings
    }
});


