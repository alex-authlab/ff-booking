import Vue from 'vue';
import App from './components/App.vue';

new Vue({
    el: '#analytics-app',
    components: { App },
    data: {
        formId: window.fluentformpro_analytics.form_id,
        fluentformpro_analytics: window.fluentformpro_analytics
    },
    template: '<App :formId="formId" :fluentformpro_analytics="fluentformpro_analytics"/>',
});