<template>
    <div>
        <div class="simplestats-card-heading">Visitors &amp; Registrations</div>
        <div v-if="loading && !series.length" class="simplestats-empty">
            <span class="simplestats-spinner"></span>
        </div>
        <apexchart
            v-else-if="series.length"
            type="line"
            height="280"
            :options="chartOptions"
            :series="series"
        />
        <div v-else class="simplestats-empty">No data</div>
    </div>
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
        labels() { return this.chronological.map((r) => r.date); },

        series() {
            if (!this.chronological.length) return [];
            const series = [
                { name: 'Visitors', type: 'area', data: this.chronological.map((r) => Number(r.pd_visitor) || 0) },
                { name: 'Registrations', type: 'area', data: this.chronological.map((r) => Number(r.pd_reg) || 0) },
            ];
            if (this.chronologicalPrev.length) {
                series.push({ name: 'Visitors (prev)', type: 'line', data: this.chronologicalPrev.map((r) => Number(r.pd_visitor) || 0) });
                series.push({ name: 'Registrations (prev)', type: 'line', data: this.chronologicalPrev.map((r) => Number(r.pd_reg) || 0) });
            }
            return series;
        },

        chartOptions() {
            return {
                chart: { type: 'line', toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit', animations: { enabled: false } },
                colors: ['#3b82f6', '#10b981', 'rgba(59, 130, 246, 0.7)', 'rgba(16, 185, 129, 0.7)'],
                stroke: { width: [2.75, 2.75, 1.5, 1.5], dashArray: [0, 0, 5, 5], curve: 'smooth' },
                markers: { size: 0, hover: { size: 5 } },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        shadeIntensity: 0,
                        type: 'vertical',
                        opacityFrom: 0.85,
                        opacityTo: 0.05,
                        stops: [0, 100],
                        inverseColors: false,
                    },
                },
                dataLabels: { enabled: false },
                xaxis: { categories: this.labels, labels: { style: { fontSize: '11px' } } },
                yaxis: { labels: { style: { fontSize: '11px' }, formatter: (v) => Math.round(v) } },
                legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px', markers: { width: 10, height: 10, radius: 10 } },
                grid: { borderColor: 'rgba(0,0,0,0.06)' },
                tooltip: { x: { show: true } },
            };
        },
    },
};
</script>
