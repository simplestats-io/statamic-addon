<template>
    <template v-for="card in cards" :key="card.label">
        <div class="simplestats-card" :class="{ 'is-loading': loading && !data.length }">
            <div class="simplestats-stat-label">{{ card.label }}</div>
            <div class="simplestats-stat-value">
                <span v-if="loading && !data.length" class="simplestats-skeleton-line"></span>
                <template v-else>{{ card.value }}</template>
            </div>
            <div v-if="card.meta" class="simplestats-stat-meta">
                <span v-if="card.trendClass" class="simplestats-trend" :class="card.trendClass">{{ card.meta }}</span>
                <span v-else>{{ card.meta }}</span>
            </div>
        </div>
    </template>
</template>

<script>
export default {
    props: {
        data: { type: Array, default: () => [] },
        previous: { type: Array, default: () => [] },
        loading: { type: Boolean, default: false },
    },

    computed: {
        chronological() { return [...this.data].reverse(); },
        chronologicalPrev() { return [...this.previous].reverse(); },

        sums() {
            const reduce = (rows, key) => rows.reduce((s, r) => s + (Number(r[key]) || 0), 0);
            return {
                visitors: reduce(this.chronological, 'pd_visitor'),
                regs: reduce(this.chronological, 'pd_reg'),
                net: reduce(this.chronological, 'pd_net'),
                prevVisitors: reduce(this.chronologicalPrev, 'pd_visitor'),
                prevRegs: reduce(this.chronologicalPrev, 'pd_reg'),
                prevNet: reduce(this.chronologicalPrev, 'pd_net'),
            };
        },

        last() {
            return this.chronological[this.chronological.length - 1] || {};
        },

        cards() {
            const s = this.sums;
            const cr = s.visitors > 0 ? Math.round((s.regs / s.visitors) * 10000) / 100 : 0;
            const arpu = (this.last.lt_arpu || 0) / 100;
            const arpv = (this.last.lt_arpv || 0) / 100;
            const fmtCount = (n) => new Intl.NumberFormat().format(n);
            const fmtMoney = (n) => new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);

            return [
                this.statCard('Visitors', this.data.length ? fmtCount(s.visitors) : '-', s.visitors, s.prevVisitors, 'Unique visitors'),
                this.statCard('Registrations', this.data.length ? fmtCount(s.regs) : '-', s.regs, s.prevRegs, 'New user signups'),
                { label: 'CR', value: this.data.length ? `${cr}%` : '-', meta: 'Visitor to registration', trendClass: null },
                this.statCard('Net Revenue', this.data.length ? fmtMoney(s.net / 100) : '-', s.net, s.prevNet, 'Revenue after fees'),
                { label: 'ARPU', value: this.data.length ? fmtMoney(arpu) : '-', meta: 'Avg Revenue Per User', trendClass: null },
                { label: 'ARPV', value: this.data.length ? fmtMoney(arpv) : '-', meta: 'Avg Revenue Per Visitor', trendClass: null },
            ];
        },
    },

    methods: {
        statCard(label, value, current, previous, fallbackMeta) {
            if (!this.previous.length) return { label, value, meta: fallbackMeta, trendClass: null };
            const change = previous === 0
                ? (current > 0 ? 100 : 0)
                : Math.round(((current - previous) / previous) * 1000) / 10;
            const sign = change > 0 ? '+' : '';
            const cls = change > 0 ? 'up' : (change < 0 ? 'down' : 'flat');
            return { label, value, meta: `${sign}${change}%`, trendClass: cls };
        },
    },
};
</script>
