<template>
    <div>
        <div class="simplestats-card-heading">{{ heading }}</div>
        <div v-if="(loading || sortLoading) && !rows.length" class="simplestats-empty">
            <span class="simplestats-spinner"></span>
        </div>
        <div v-else-if="!rows.length" class="simplestats-empty">No data</div>
        <table v-else class="simplestats-table" :class="{ 'is-loading': loading || sortLoading }">
            <thead>
                <tr>
                    <th
                        class="sortable"
                        :class="{ active: sortColumn === 'name' }"
                        @click="setSort('name')"
                    >
                        Name<span class="sort-arrow">{{ arrowFor('name') }}</span>
                    </th>
                    <th
                        v-if="hasField('visitors')"
                        class="num sortable"
                        :class="{ active: sortColumn === 'visitors' }"
                        @click="setSort('visitors')"
                    >
                        Visitors<span class="sort-arrow">{{ arrowFor('visitors') }}</span>
                    </th>
                    <th
                        v-if="hasField('reg')"
                        class="num sortable"
                        :class="{ active: sortColumn === 'reg' }"
                        @click="setSort('reg')"
                    >
                        Regs<span class="sort-arrow">{{ arrowFor('reg') }}</span>
                    </th>
                    <th
                        v-if="hasField('cr')"
                        class="num sortable"
                        :class="{ active: sortColumn === 'cr' }"
                        @click="setSort('cr')"
                    >
                        CR<span class="sort-arrow">{{ arrowFor('cr') }}</span>
                    </th>
                    <th
                        v-if="hasField('dau')"
                        class="num sortable"
                        :class="{ active: sortColumn === 'dau' }"
                        @click="setSort('dau')"
                    >
                        {{ activeUsersLabel }}<span class="sort-arrow">{{ arrowFor('dau') }}</span>
                    </th>
                    <th
                        v-if="hasField('net')"
                        class="num sortable"
                        :class="{ active: sortColumn === 'net' }"
                        @click="setSort('net')"
                    >
                        Revenue<span class="sort-arrow">{{ arrowFor('net') }}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in displayedRows" :key="row.type_id ?? row.name" :style="rowStyle(row)">
                    <td :title="row.name">{{ truncate(row.name) }}</td>
                    <td v-if="hasField('visitors')" class="num">
                        <div>{{ formatInt(row.visitors) }}</div>
                        <div v-if="trendBadge(row, 'visitors')" class="simplestats-trend-badge" :style="trendBadge(row, 'visitors').style">{{ trendBadge(row, 'visitors').text }}</div>
                    </td>
                    <td v-if="hasField('reg')" class="num">
                        <div>{{ formatInt(row.reg) }}</div>
                        <div v-if="trendBadge(row, 'reg')" class="simplestats-trend-badge" :style="trendBadge(row, 'reg').style">{{ trendBadge(row, 'reg').text }}</div>
                    </td>
                    <td v-if="hasField('cr')" class="num">
                        <div>{{ row.cr }}%</div>
                        <div v-if="trendBadge(row, 'cr')" class="simplestats-trend-badge" :style="trendBadge(row, 'cr').style">{{ trendBadge(row, 'cr').text }}</div>
                    </td>
                    <td v-if="hasField('dau')" class="num">
                        <div>{{ formatInt(row.dau) }}</div>
                        <div v-if="trendBadge(row, 'dau')" class="simplestats-trend-badge" :style="trendBadge(row, 'dau').style">{{ trendBadge(row, 'dau').text }}</div>
                    </td>
                    <td v-if="hasField('net')" class="num">
                        <div>{{ formatMoney(row.net) }}</div>
                        <div v-if="trendBadge(row, 'net')" class="simplestats-trend-badge" :style="trendBadge(row, 'net').style">{{ trendBadge(row, 'net').text }}</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
const DEFAULT_SORT = 'visitors';
const DEFAULT_DIR = 'desc';

export default {
    props: {
        heading: { type: String, required: true },
        type: { type: String, required: true },
        response: { type: Object, default: null },
        gradient: { type: String, default: '99, 102, 241' },
        rangeType: { type: String, default: 'days' },
        loading: { type: Boolean, default: false },
        endpointTemplate: { type: String, required: true },
        params: { type: Object, default: () => ({}) },
    },

    data() {
        return {
            sortColumn: DEFAULT_SORT,
            sortDir: DEFAULT_DIR,
            sortedResponse: null,
            sortLoading: false,
            sortRequestId: 0,
        };
    },

    computed: {
        activeResponse() {
            return this.sortedResponse ?? this.response;
        },
        rows() { return this.activeResponse?.data ?? []; },
        displayedRows() {
            if (this.sortDir === 'asc') {
                return [...this.rows].reverse();
            }
            return this.rows;
        },
        previousByTypeId() {
            const map = {};
            for (const prev of (this.activeResponse?.data_previous ?? [])) {
                if (prev.type_id !== undefined) map[prev.type_id] = prev;
            }
            return map;
        },
        maxVisitors() {
            if (!this.rows.length) return 0;
            return Math.max(...this.rows.map((r) => Number(r.visitors) || 0));
        },
        endpoint() {
            return this.endpointTemplate.replace('__TYPE__', encodeURIComponent(this.type));
        },
        activeUsersLabel() {
            return { weeks: 'WAU', months: 'MAU' }[this.rangeType] || 'DAU';
        },
    },

    watch: {
        response() {
            this.sortColumn = DEFAULT_SORT;
            this.sortDir = DEFAULT_DIR;
            this.sortedResponse = null;
        },
    },

    methods: {
        hasField(field) {
            if (field === 'name') return true;
            return this.rows.length > 0 && Object.prototype.hasOwnProperty.call(this.rows[0], field);
        },
        arrowFor(field) {
            if (this.sortColumn !== field) return '';
            return this.sortDir === 'asc' ? ' ↑' : ' ↓';
        },
        setSort(column) {
            if (this.sortColumn === column) {
                this.sortDir = this.sortDir === 'desc' ? 'asc' : 'desc';
                return;
            }
            this.sortColumn = column;
            this.sortDir = 'desc';
            if (column === DEFAULT_SORT) {
                this.sortedResponse = null;
                return;
            }
            this.fetchSorted();
        },
        async fetchSorted() {
            const requestId = ++this.sortRequestId;
            this.sortLoading = true;
            try {
                const { data } = await this.$axios.get(this.endpoint, {
                    params: { ...this.params, sort: this.sortColumn },
                });
                if (requestId !== this.sortRequestId) return;
                this.sortedResponse = data;
            } catch (e) {
                if (requestId !== this.sortRequestId) return;
                // Keep showing previous data; surface error in console for debugging.
                console.warn('SimpleStats grouped sort failed', e);
            } finally {
                if (requestId === this.sortRequestId) this.sortLoading = false;
            }
        },
        formatInt(v) { return new Intl.NumberFormat().format(Math.round(Number(v) || 0)); },
        formatMoney(v) {
            return new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format((Number(v) || 0) / 100);
        },
        truncate(str, max = 30) {
            if (!str) return '';
            return str.length > max ? str.slice(0, max - 1) + '…' : str;
        },
        rowStyle(row) {
            if (!this.maxVisitors) return {};
            const pct = Math.floor(((Number(row.visitors) || 0) / this.maxVisitors) * 100);
            return { background: `linear-gradient(to right, rgba(${this.gradient}, 0.18) ${pct}%, transparent ${pct}%)` };
        },
        trendBadge(row, field) {
            const previous = this.previousByTypeId[row.type_id]?.[field];
            const state = row[field];
            if (previous === undefined || state === undefined) return null;
            const cur = Number(state);
            const prev = Number(previous);
            if (prev === 0 && cur === 0) return null;
            const change = prev === 0 ? 100 : ((cur - prev) / prev) * 100;
            const rounded = Math.abs(change) >= 10 ? Math.round(change) : Math.round(change * 10) / 10;
            const sign = rounded > 0 ? '+' : '';
            const color = rounded > 0 ? '#16a34a' : (rounded < 0 ? '#dc2626' : '#6b7280');
            return {
                text: `${sign}${rounded}%`,
                style: { color },
            };
        },
    },
};
</script>
