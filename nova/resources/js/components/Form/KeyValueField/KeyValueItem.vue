<template>
    <div v-if="isNotObject" class="flex items-center key-value-item">
        <div class="flex flex-grow border-b border-50 key-value-fields">
            <div
                :class="{ 'bg-30': readOnlyKeys || !isEditable }"
                class="w-48 cursor-text"
            >
        <textarea
            ref="keyField"
            v-model="item.key"
            :class="{
            'bg-white': !isEditable || readOnlyKeys,
            'hover:bg-20 focus:bg-white': isEditable && !readOnlyKeys,
          }"
            :disabled="!isEditable || readOnlyKeys"
            :dusk="`key-value-key-${index}`"
            class="
            font-mono
            text-sm
            resize-none
            block
            min-h-input
            w-full
            form-control form-input form-input-row
            py-4
            text-90
          "
            style="background-clip: border-box"
            type="text"
            @focus="handleKeyFieldFocus"
        />
            </div>

            <div class="flex-grow border-l border-50" @click="handleValueFieldFocus">
        <textarea
            ref="valueField"
            v-model="item.value"
            :class="{
            'bg-white': !isEditable,
            'hover:bg-20 focus:bg-white': isEditable,
          }"
            :disabled="!isEditable"
            :dusk="`key-value-value-${index}`"
            class="
            font-mono
            text-sm
            block
            min-h-input
            w-full
            form-control form-input form-input-row
            py-4
            text-90
          "
            type="text"
            @focus="handleValueFieldFocus"
        />
            </div>
        </div>

        <div
            v-if="isEditable && canDeleteRow"
            class="flex justify-center h-11 w-11 absolute"
            style="right: -50px"
        >
            <button
                :dusk="`remove-key-value-${index}`"
                class="
          flex
          appearance-none
          cursor-pointer
          text-70
          hover:text-primary
          active:outline-none active:shadow-outline
          focus:outline-none focus:shadow-outline
        "
                tabindex="-1"
                title="Delete"
                type="button"
                @click="$emit('remove-row', item.id)"
            >
                <icon/>
            </button>
        </div>
    </div>
</template>

<script>
import autosize from 'autosize';

export default {
    props: {
        index: Number,
        item: Object,
        disabled: {
            type: Boolean,
            default: false,
        },
        readOnly: {
            type: Boolean,
            default: false,
        },
        readOnlyKeys: {
            type: Boolean,
            default: false,
        },
        canDeleteRow: {
            type: Boolean,
            default: true,
        },
    },

    mounted()
    {
        autosize(this.$refs.keyField);
        autosize(this.$refs.valueField);
    },

    methods: {
        handleKeyFieldFocus()
        {
            this.$refs.keyField.select();
        },

        handleValueFieldFocus()
        {
            this.$refs.valueField.select();
        },
    },

    computed: {
        isNotObject()
        {
            return !(this.item.value instanceof Object);
        },
        isEditable()
        {
            return !this.readOnly && !this.disabled;
        },
    },
};
</script>
