<template>
    <modal
        :classWhitelist="[
      'flatpickr-current-month',
      'flatpickr-next-month',
      'flatpickr-prev-month',
      'flatpickr-weekday',
      'flatpickr-weekdays',
      'flatpickr-calendar',
      'form-file-input',
    ]"
        dusk="new-relation-modal"
        role="dialog"
        tabindex="-1"
        @modal-close="handleClose"
    >
        <div
            class="bg-40 rounded-lg shadow-lg overflow-hidden p-8"
            style="width: 800px"
        >
            <Create
                :resource-name="resourceName"
                mode="modal"
                resource-id=""
                via-relationship=""
                via-resource=""
                via-resource-id=""
                @refresh="handleRefresh"
                @cancelled-create="handleCancelledCreate"
            />
        </div>
    </modal>
</template>

<script>
import Create from '@/views/Create';

export default {
    components: {Create},

    props: {
        resourceName: {},
        resourceId: {},
        viaResource: {},
        viaResourceId: {},
        viaRelationship: {},
    },

    methods: {
        handleRefresh(data)
        {
            // alert('wew refreshing')
            this.$emit('set-resource', data);
        },

        handleCancelledCreate()
        {
            return this.$emit('cancelled-create');
        },

        /**
         * Close the modal.
         */
        handleClose()
        {
            this.$emit('cancelled-create');
        },
    },
};
</script>
