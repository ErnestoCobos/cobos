<template>
    <div>
        <slot>
            <heading :class="panel.helpText ? 'mb-2' : 'mb-3'" :level="1">{{
                    panel.name
                }}
            </heading>

            <p
                v-if="panel.helpText"
                class="text-80 text-sm font-semibold italic mb-3"
                v-html="panel.helpText"
            ></p>
        </slot>

        <card class="mb-6 py-3 px-6">
            <component
                :is="resolveComponentName(field)"
                v-for="(field, index) in fields"
                :key="index"
                :class="{
          'remove-bottom-border': index == panel.fields.length - 1,
        }"
                :field="field"
                :resource="resource"
                :resource-id="resourceId"
                :resource-name="resourceName"
                @actionExecuted="actionExecuted"
            />

            <div
                v-if="shouldShowShowAllFieldsButton"
                class="
          bg-20
          -mt-px
          -mx-6
          -mb-6
          border-t border-40
          p-3
          text-center
          rounded-b
          text-center
        "
            >
                <button
                    class="block w-full dim text-sm text-80 font-bold"
                    @click="showAllFields"
                >
                    {{ __('Show All Fields') }}
                </button>
            </div>
        </card>
    </div>
</template>

<script>
import {BehavesAsPanel} from 'laravel-nova';

export default {
    mixins: [BehavesAsPanel],

    methods: {
        /**
         * Resolve the component name.
         */
        resolveComponentName(field)
        {
            return field.prefixComponent
                ? 'detail-' + field.component
                : field.component;
        },

        /**
         * Show all of the Panel's fields.
         */
        showAllFields()
        {
            return (this.panel.limit = 0);
        },
    },

    computed: {
        /**
         * Limits the visible fields.
         */
        fields()
        {
            if (this.panel.limit > 0) {
                return this.panel.fields.slice(0, this.panel.limit);
            }

            return this.panel.fields;
        },

        /**
         * Determines if should display the 'Show all fields' button.
         */
        shouldShowShowAllFieldsButton()
        {
            return this.panel.limit > 0;
        },
    },
};
</script>
