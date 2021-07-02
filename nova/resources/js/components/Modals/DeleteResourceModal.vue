<template>
    <modal @modal-close="handleClose">
        <form
            slot-scope="props"
            class="bg-white rounded-lg shadow-lg overflow-hidden"
            style="width: 460px"
            @submit.prevent="handleConfirm"
        >
            <slot :mode="mode" :uppercaseMode="uppercaseMode">
                <div class="p-8">
                    <heading :level="2" class="mb-6">{{
                            __(uppercaseMode + ' Resource')
                        }}
                    </heading>
                    <p class="text-80 leading-normal">
                        {{
                            __(
                                'Are you sure you want to ' + mode + ' the selected resources?',
                            )
                        }}
                    </p>
                </div>
            </slot>

            <div class="bg-30 px-6 py-3 flex">
                <div class="ml-auto">
                    <button
                        class="btn text-80 font-normal h-9 px-3 mr-3 btn-link"
                        data-testid="cancel-button"
                        dusk="cancel-delete-button"
                        type="button"
                        @click.prevent="handleClose"
                    >
                        {{ __('Cancel') }}
                    </button>

                    <loading-button
                        id="confirm-delete-button"
                        ref="confirmButton"
                        :disabled="working"
                        :processing="working"
                        class="btn btn-default btn-danger"
                        data-testid="confirm-button"
                        type="submit"
                    >
                        {{ __(uppercaseMode) }}
                    </loading-button>
                </div>
            </div>
        </form>
    </modal>
</template>

<script>
export default {
    props: {
        mode: {
            type: String,
            default: 'delete',
            validator: function(value) {
                return ['force delete', 'delete', 'detach'].indexOf(value) !== -1;
            },
        },
    },

    data: () => ({
        working: false,
    }),

    methods: {
        handleClose()
        {
            this.$emit('close');
            this.working = false;
        },

        handleConfirm()
        {
            this.$emit('confirm');
            this.working = true;
        },
    },

    /**
     * Mount the component.
     */
    mounted()
    {
        this.$refs.confirmButton.focus();
    },

    computed: {
        uppercaseMode()
        {
            return _.startCase(this.mode);
        },
    },
};
</script>
