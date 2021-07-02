<template>
    <div>
        <h3 class="text-sm uppercase tracking-wide text-80 bg-30 p-3">
            {{ filter.name }}
        </h3>

        <div class="p-2">
            <select
                :dusk="filter.name + '-filter-select'"
                :value="value"
                class="block w-full form-control-sm form-select"
                @change="handleChange"
            >
                <option selected value="">&mdash;</option>

                <option v-for="option in filter.options" :value="option.value">
                    {{ option.name }}
                </option>
            </select>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        resourceName: {
            type: String,
            required: true,
        },
        filterKey: {
            type: String,
            required: true,
        },
    },

    methods: {
        handleChange(event)
        {
            this.$store.commit(`${this.resourceName}/updateFilterState`, {
                filterClass: this.filterKey,
                value: event.target.value,
            });

            this.$emit('change');
        },
    },

    computed: {
        filter()
        {
            return this.$store.getters[`${this.resourceName}/getFilter`](
                this.filterKey,
            );
        },

        value()
        {
            return this.filter.currentValue;
        },
    },
};
</script>
