<template>
    <div>
        <h3 class="text-sm uppercase tracking-wide text-80 bg-30 p-3">
            {{ filter.name }}
        </h3>

        <div class="p-2">
            <date-time-picker
                :enable-seconds="false"
                :enable-time="false"
                :first-day-of-week="firstDayOfWeek"
                :placeholder="placeholder"
                :value="value"
                alt-format="Y-m-d"
                autocomplete="off"
                class="w-full form-control form-input form-input-bordered"
                date-format="Y-m-d"
                dusk="date-filter"
                name="date-filter"
                @change="handleChange"
                @input.prevent=""
            />
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
        lens: String,
    },

    methods: {
        handleChange(value)
        {
            this.$store.commit(`${this.resourceName}/updateFilterState`, {
                filterClass: this.filterKey,
                value,
            });
            this.$emit('change');
        },
    },

    computed: {
        placeholder()
        {
            return this.filter.placeholder || this.__('Choose date');
        },

        value()
        {
            return this.filter.currentValue;
        },

        filter()
        {
            return this.$store.getters[`${this.resourceName}/getFilter`](
                this.filterKey,
            );
        },

        options()
        {
            return this.$store.getters[`${this.resourceName}/getOptionsForFilter`](
                this.filterKey,
            );
        },

        firstDayOfWeek()
        {
            return this.filter.firstDayOfWeek || 0;
        },
    },
};
</script>
