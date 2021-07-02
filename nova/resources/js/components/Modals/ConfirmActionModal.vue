<template>
    <modal
        :classWhitelist="[
      'flatpickr-current-month',
      'flatpickr-next-month',
      'flatpickr-prev-month',
      'flatpickr-weekday',
      'flatpickr-weekdays',
      'flatpickr-calendar',
    ]"
        data-testid="confirm-action-modal"
        role="dialog"
        tabindex="-1"
        @modal-close="handleClose"
    >
        <form
            :class="{
        'w-action-fields': action.fields.length > 0,
        'w-action': action.fields.length == 0,
      }"
            autocomplete="off"
            class="bg-white rounded-lg shadow-lg overflow-hidden"
            @keydown="handleKeydown"
            @submit.prevent.stop="handleConfirm"
        >
            <div>
                <heading :level="2" class="border-b border-40 py-8 px-8">{{
                        action.name
                    }}
                </heading>

                <p v-if="action.fields.length == 0" class="text-80 px-8 my-8">
                    {{ action.confirmText }}
                </p>

                <div v-else>
                    <!-- Validation Errors -->
                    <validation-errors :errors="errors"/>

                    <!-- Action Fields -->
                    <div
                        v-for="field in action.fields"
                        :key="field.attribute"
                        class="action"
                    >
                        <component
                            :is="'form-' + field.component"
                            :errors="errors"
                            :field="field"
                            :resource-name="resourceName"
                            :show-help-text="field.helpText != null"
                        />
                    </div>
                </div>
            </div>

            <div class="bg-30 px-6 py-3 flex">
                <div class="flex items-center ml-auto">
                    <button
                        class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6"
                        dusk="cancel-action-button"
                        type="button"
                        @click.prevent="handleClose"
                    >
                        {{ action.cancelButtonText }}
                    </button>

                    <loading-button
                        ref="runButton"
                        :class="action.class"
                        :disabled="working"
                        :processing="working"
                        class="btn btn-default"
                        dusk="confirm-action-button"
                        type="submit"
                    >
                        {{ action.confirmButtonText }}
                    </loading-button>
                </div>
            </div>
        </form>
    </modal>
</template>

<script>
export default {
    props: {
        working: Boolean,
        resourceName: {type: String, required: true},
        action: {type: Object, required: true},
        selectedResources: {type: [Array, String], required: true},
        errors: {type: Object, required: true},
    },

    /**
     * Mount the component.
     */
    mounted()
    {
        // If the modal has inputs, let's highlight the first one, otherwise
        // let's highlight the submit button
        if (document.querySelectorAll('.modal input').length) {
            document.querySelectorAll('.modal input')[0].focus();
        } else {
            this.$refs.runButton.focus();
        }
    },

    methods: {
        /**
         * Stop propogation of input events unless it's for an escape or enter keypress
         */
        handleKeydown(e)
        {
            if (['Escape', 'Enter'].indexOf(e.key) !== -1) {
                return;
            }

            e.stopPropagation();
        },

        /**
         * Execute the selected action.
         */
        handleConfirm()
        {
            this.$emit('confirm');
        },

        /**
         * Close the modal.
         */
        handleClose()
        {
            this.$emit('close');
        },
    },
};
</script>
