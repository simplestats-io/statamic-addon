<template>
    <div v-if="chips.length" class="simplestats-active-filters">
        <span
            v-for="chip in chips"
            :key="chip.type"
            class="simplestats-chip"
        >
            {{ chip.label }}: <strong>{{ chip.name }}</strong>
            <button
                type="button"
                class="simplestats-chip-clear"
                title="Clear filter"
                @click="$emit('clear', chip.type)"
            >
                &times;
            </button>
        </span>
    </div>
</template>

<script>
export default {
    props: {
        active: { type: Object, default: () => ({}) },
        grouped: { type: Object, default: () => ({}) },
        groups: { type: Array, default: () => [] },
    },

    emits: ['clear'],

    computed: {
        labelByType() {
            const map = {};
            for (const g of this.groups) {
                map[g.type] = g.label || g.heading || g.type;
            }
            return map;
        },

        chips() {
            const result = [];
            for (const [type, typeId] of Object.entries(this.active)) {
                if (typeId === null || typeId === '' || typeId === undefined) continue;
                result.push({
                    type,
                    typeId,
                    label: this.labelByType[type] ?? type,
                    name: this.lookupName(type, typeId),
                });
            }
            return result;
        },
    },

    methods: {
        lookupName(type, typeId) {
            const rows = this.grouped[type]?.data ?? [];
            for (const row of rows) {
                const rowId = row.type_id ?? -1;
                if (String(rowId) === String(typeId)) {
                    return row.name ?? `#${typeId}`;
                }
            }
            return `#${typeId}`;
        },
    },
};
</script>
