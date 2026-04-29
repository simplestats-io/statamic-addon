import VueApexCharts from 'vue3-apexcharts';
import Dashboard from './components/Dashboard.vue';

Statamic.configuring(() => {
    Statamic.$app.use(VueApexCharts);
});

Statamic.booting(() => {
    Statamic.$components.register('simplestats-dashboard', Dashboard);
});
