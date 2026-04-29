<template>
    <div>
        <div class="simplestats-card-heading">Revenue</div>
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
            const cents = (rows, key) => rows.map((r) => (Number(r[key]) || 0) / 100);
            const series = [
                { name: 'Gross', type: 'area', data: cents(this.chronological, 'pd_gross') },
                { name: 'Net', type: 'area', data: cents(this.chronological, 'pd_net') },
            ];
            if (this.chronologicalPrev.length) {
                series.push({ name: 'Gross (prev)', type: 'line', data: cents(this.chronologicalPrev, 'pd_gross') });
                series.push({ name: 'Net (prev)', type: 'line', data: cents(this.chronologicalPrev, 'pd_net') });
            }
            return series;
        },

        chartOptions() {
            return {
                chart: { type: 'line', toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit', animations: { enabled: false } },
                colors: ['#f59e0b', '#ec4899', 'rgba(245, 158, 11, 0.7)', 'rgba(236, 72, 153, 0.7)'],
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
                yaxis: { labels: { style: { fontSize: '11px' }, formatter: (v) => v.toFixed(2) } },
                legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px', markers: { width: 10, height: 10, radius: 10 } },
                grid: { borderColor: 'rgba(0,0,0,0.06)' },
                tooltip: { y: { formatter: (v) => v.toFixed(2) } },
            };
        },
    },
};
</script>
