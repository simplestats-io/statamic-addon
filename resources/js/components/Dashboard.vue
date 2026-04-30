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

            <active-filters
                class="span-6"
                :active="drilldowns"
                :grouped="grouped"
                :groups="groupedConfig"
                @clear="clearDrilldown"
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
                :active-drilldown="drilldowns[g.type] ?? null"
                @toggle-filter="(typeId) => toggleDrilldown(g.type, typeId)"
            />
        </div>
    </div>
</template>

<script>
import StatsOverview from './StatsOverview.vue';
import VisitorsChart from './VisitorsChart.vue';
import RevenueChart from './RevenueChart.vue';
import GroupedTable from './GroupedTable.vue';
import ActiveFilters from './ActiveFilters.vue';

const inFlightFetches = new Map();

const DRILLDOWN_KEYS = [
    'track_referer', 'track_source', 'track_medium', 'track_campaign',
    'track_term', 'track_content', 'location_country', 'location_region',
    'location_city', 'device_type', 'device_platform', 'device_browser',
    'page_entry', 'custom_event_name',
];

function parseInitialFilters(source) {
    // Statamic CP strips the query string from the page URL via
    // Statamic::nonInertiaPageData(), so window.location.search is empty by
    // the time Vue mounts. The dashboard view passes request()->query()
    // server-side via the initial-filters prop instead.
    const get = (key) => {
        const value = source?.[key];
        return value === undefined ? null : value;
    };
    const drilldowns = {};
    for (const key of DRILLDOWN_KEYS) {
        const value = get(key);
        if (value === null || value === '') continue;
        drilldowns[key] = /^-?\d+$/.test(value) ? parseInt(value, 10) : value;
    }
    return {
        preset: get('preset') || 'last_7_days',
        comparison: get('comparison') || '0',
        drilldowns,
    };
}

export default {
    components: { StatsOverview, VisitorsChart, RevenueChart, GroupedTable, ActiveFilters },

    props: {
        endpoint: { type: String, required: true },
        endpointGroupedTemplate: { type: String, required: true },
        initialFilters: { type: Object, default: () => ({}) },
    },

    data() {
        const initial = parseInitialFilters(this.initialFilters);
        return {
            preset: initial.preset,
            comparison: initial.comparison,
            drilldowns: initial.drilldowns,
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
                { type: 'track_referer', heading: 'Top Referrers', label: 'Referer', gradient: '139, 92, 246' },
                { type: 'track_source', heading: 'Top Sources', label: 'Source', gradient: '99, 102, 241' },
                { type: 'location_country', heading: 'Top Countries', label: 'Country', gradient: '16, 185, 129' },
                { type: 'page_entry', heading: 'Entry Pages', label: 'Entry Page', gradient: '14, 165, 233' },
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
            for (const [key, value] of Object.entries(this.drilldowns)) {
                if (value === null || value === '' || value === undefined) continue;
                p[key] = value;
            }
            return p;
        },
    },

    watch: {
        preset() { this.fetch(); },
        comparison() { this.fetch(); },
        drilldowns: { handler() { this.fetch(); }, deep: true },
    },

    mounted() {
        this.fetch();
    },

    methods: {
        toggleDrilldown(type, typeId) {
            // -1 represents NULL/Direct/Unknown rows on the API side (TRACK_ORGANIC).
            const id = typeId ?? -1;
            const next = { ...this.drilldowns };
            if (String(next[type] ?? '') === String(id)) {
                delete next[type];
            } else {
                next[type] = id;
            }
            this.drilldowns = next;
        },

        clearDrilldown(type) {
            const next = { ...this.drilldowns };
            delete next[type];
            this.drilldowns = next;
        },

        updateUrl() {
            if (typeof window === 'undefined') return;
            const query = new URLSearchParams(this.params).toString();
            const newUrl = window.location.pathname + (query ? '?' + query : '');
            if (newUrl !== window.location.pathname + window.location.search) {
                window.history.replaceState({}, '', newUrl);
            }
        },

        async fetch() {
            this.updateUrl();

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
