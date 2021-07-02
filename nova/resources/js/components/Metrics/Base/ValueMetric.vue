<template>
    <loading-card :loading="loading" class="px-6 py-4">
        <div class="flex mb-4">
            <h3 class="mr-3 text-base text-80 font-bold">{{ title }}</h3>

            <div v-if="helpText" class="absolute pin-r pin-b p-2 z-20">
                <tooltip placement="top-start" trigger="click">
                    <icon
                        class="cursor-pointer text-60 -mb-1"
                        height="16"
                        type="help"
                        viewBox="0 0 17 17"
                        width="16"
                    />

                    <tooltip-content
                        slot="content"
                        :max-width="helpWidth"
                        v-html="helpText"
                    />
                </tooltip>
            </div>

            <select
                v-if="ranges.length > 0"
                class="
          select-box-sm
          ml-auto
          min-w-24
          h-6
          text-xs
          appearance-none
          bg-40
          pl-2
          pr-6
          active:outline-none active:shadow-outline
          focus:outline-none focus:shadow-outline
        "
                @change="handleChange"
            >
                <option
                    v-for="option in ranges"
                    :key="option.value"
                    :selected="selectedRangeKey == option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>

        <p class="flex items-center text-4xl mb-4">
            {{ formattedValue }}
            <span v-if="suffix" class="ml-2 text-sm font-bold text-80">{{
                    formattedSuffix
                }}</span>
        </p>

        <div>
            <p class="flex items-center text-80 font-bold">
                <svg
                    v-if="increaseOrDecreaseLabel == 'Decrease'"
                    class="text-danger stroke-current mr-2"
                    fill="none"
                    height="24"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    width="24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                    />
                </svg>
                <svg
                    v-if="increaseOrDecreaseLabel == 'Increase'"
                    class="text-success stroke-current mr-2"
                    fill="none"
                    height="24"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    width="24"
                >
                    <path
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                    />
                </svg>

                <span v-if="increaseOrDecrease != 0">
          <span v-if="growthPercentage !== 0">
            {{ growthPercentage }}%
            {{ __(increaseOrDecreaseLabel) }}
          </span>

          <span v-else> {{ __('No Increase') }} </span>
        </span>

                <span v-else>
          <span v-if="previous == '0' && value != '0'">
            {{ __('No Prior Data') }}
          </span>

          <span v-if="value == '0' && previous != '0' && !zeroResult">
            {{ __('No Current Data') }}
          </span>

          <span v-if="value == '0' && previous == '0' && !zeroResult">
            {{ __('No Data') }}
          </span>
        </span>
            </p>
        </div>
    </loading-card>
</template>

<script>
import {SingularOrPlural} from 'laravel-nova';

export default {
    name: 'BaseValueMetric',
    props: {
        loading: {default: true},
        title: {},
        helpText: {},
        helpWidth: {},
        maxWidth: {},
        previous: {},
        value: {},
        prefix: '',
        suffix: '',
        suffixInflection: {
            default: true,
        },
        selectedRangeKey: [String, Number],
        ranges: {type: Array, default: () => []},
        format: {
            type: String,
            default: '(0[.]00a)',
        },
        zeroResult: {
            default: false,
        },
    },

    methods: {
        handleChange(event)
        {
            this.$emit('selected', event.target.value);
        },
    },

    computed: {
        growthPercentage()
        {
            return Math.abs(this.increaseOrDecrease);
        },

        increaseOrDecrease()
        {
            if (this.previous == 0 || this.previous == null || this.value == 0) {
                return 0;
            }

            return (((this.value - this.previous) / this.previous) * 100).toFixed(2);
        },

        increaseOrDecreaseLabel()
        {
            switch (Math.sign(this.increaseOrDecrease)) {
                case 1:
                    return 'Increase';
                case 0:
                    return 'Constant';
                case -1:
                    return 'Decrease';
            }
        },

        sign()
        {
            switch (Math.sign(this.increaseOrDecrease)) {
                case 1:
                    return '+';
                case 0:
                    return '';
                case -1:
                    return '-';
            }
        },

        isNullValue()
        {
            return this.value == null;
        },

        formattedValue()
        {
            if (!this.isNullValue) {
                return (
                    this.prefix + Nova.formatNumber(String(this.value), this.format)
                );
            }

            return '';
        },

        formattedSuffix()
        {
            if (this.suffixInflection === false) {
                return this.suffix;
            }

            return SingularOrPlural(this.value, this.suffix);
        },
    },
}
</script>
