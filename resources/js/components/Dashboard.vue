<template>
    <div class="simplestats-dashboard">
        <header class="simplestats-header">
            <h1 class="simplestats-title">SimpleStats</h1>
            <div class="simplestats-filters">
                <select v-model="preset" :disabled="loading">
                    <option v-for="opt in presetOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
                <select v-model="comparison" :disabled="loading">
                    <option v-for="opt in comparisonOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
                <span v-if="loading" class="simplestats-spinner" aria-label="Loading"></span>
            </div>
        </header>

        <div v-if="error" class="simplestats-error">
            <strong>Could not load stats:</strong> {{ error }}
            <button type="button" @click="fetch">Retry</button>
        </div>

        <div class="simplestats-grid">
            <stats-overview
                :data="statsData"
                :previous="statsPrevious"
                :loading="loading"
            />

            <visitors-chart
                class="simplestats-card span-3"
                :data="statsData"
                :previous="statsPrevious"
                :loading="loading"
            />

            <revenue-chart
                class="simplestats-card span-3"
                :data="statsData"
                :previous="statsPrevious"
                :loading="loading"
            />

            <grouped-table
                v-for="g in groupedConfig"
                :key="g.type"
                class="simplestats-card span-6"
                :heading="g.heading"
                :type="g.type"
                :gradient="g.gradient"
                :response="grouped[g.type]"
                :loading="loading"
                :range-type="rangeType"
                :endpoint-template="endpointGroupedTemplate"
                :params="params"
            />
        </div>
    </div>
</template>

<script>
import StatsOverview from './StatsOverview.vue';
import VisitorsChart from './VisitorsChart.vue';
import RevenueChart from './RevenueChart.vue';
import GroupedTable from './GroupedTable.vue';

const inFlightFetches = new Map();

export default {
    components: { StatsOverview, VisitorsChart, RevenueChart, GroupedTable },

    props: {
        endpoint: { type: String, required: true },
        endpointGroupedTemplate: { type: String, required: true },
    },

    data() {
        return {
            preset: 'last_7_days',
            comparison: '0',
            stats: null,
            grouped: {},
            loading: false,
            error: null,
            requestId: 0,
            presetOptions: [
                { value: 'today', label: 'Today' },
                { value: 'yesterday', label: 'Yesterday' },
                { value: 'last_7_days', label: 'Last 7 Days' },
                { value: 'last_30_days', label: 'Last 30 Days' },
                { value: 'last_12_weeks', label: 'Last 12 Weeks' },
                { value: 'last_6_months', label: 'Last 6 Months' },
                { value: 'this_month', label: 'This Month' },
                { value: 'last_month', label: 'Last Month' },
                { value: 'this_year', label: 'This Year' },
                { value: 'last_year', label: 'Last Year' },
                { value: 'all_time', label: 'All Time' },
            ],
            comparisonOptions: [
                { value: '0', label: 'No comparison' },
                { value: 'period', label: 'Previous period' },
                { value: 'cycle', label: 'Previous cycle' },
                { value: 'year', label: 'Year over year' },
            ],
            groupedConfig: [
                { type: 'track_referer', heading: 'Top Referrers', gradient: '139, 92, 246' },
                { type: 'track_source', heading: 'Top Sources', gradient: '99, 102, 241' },
                { type: 'location_country', heading: 'Top Countries', gradient: '16, 185, 129' },
                { type: 'page_entry', heading: 'Entry Pages', gradient: '14, 165, 233' },
            ],
        };
    },

    computed: {
        statsData() { return this.stats?.data || []; },
        statsPrevious() { return this.stats?.data_previous || []; },
        rangeType() { return this.stats?.meta?.range_type || 'days'; },
        params() {
            const p = { preset: this.preset };
            if (this.comparison !== '0') p.comparison = this.comparison;
            return p;
        },
    },

    watch: {
        preset() { this.fetch(); },
        comparison() { this.fetch(); },
    },

    mounted() {
        this.fetch();
    },

    methods: {
        async fetch() {
            const requestId = ++this.requestId;
            this.loading = true;
            this.error = null;

            const key = this.endpoint + '?' + new URLSearchParams(this.params).toString();
            let promise = inFlightFetches.get(key);
            if (!promise) {
                promise = this.$axios.get(this.endpoint, { params: this.params })
                    .then(r => r.data)
                    .finally(() => inFlightFetches.delete(key));
                inFlightFetches.set(key, promise);
            }

            try {
                const data = await promise;
                if (requestId !== this.requestId) return;
                this.stats = data?.stats || null;
                this.grouped = data?.grouped || {};
            } catch (e) {
                if (requestId !== this.requestId) return;
                this.error = e?.response?.statusText || e?.message || 'Request failed';
            } finally {
                if (requestId === this.requestId) this.loading = false;
            }
        },
    },
};
</script>
