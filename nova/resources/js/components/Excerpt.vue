<template>
    <div v-if="shouldShow && hasContent">
        <div
            :class="{ 'whitespace-pre-wrap': plainText }"
            class="markdown leading-normal"
            v-html="content"
        />
    </div>
    <div v-else-if="hasContent">
        <div
            v-if="expanded"
            :class="{ 'whitespace-pre-wrap': plainText }"
            class="markdown leading-normal"
            v-html="content"
        />

        <a
            v-if="!shouldShow"
            :class="{ 'mt-6': expanded }"
            aria-role="button"
            class="cursor-pointer dim inline-block text-primary font-bold"
            @click="toggle"
        >
            {{ showHideLabel }}
        </a>
    </div>
    <div v-else>&mdash;</div>
</template>

<script>
export default {
    props: {
        plainText: {
            type: Boolean,
            default: false,
        },
        shouldShow: {
            type: Boolean,
            default: false,
        },
        content: {
            type: String,
        },
    },

    data: () => ({expanded: false}),

    methods: {
        toggle()
        {
            this.expanded = !this.expanded;
        },
    },

    computed: {
        hasContent()
        {
            return this.content !== '' && this.content !== null;
        },

        showHideLabel()
        {
            return !this.expanded ? this.__('Show Content') : this.__('Hide Content');
        },
    },
};
</script>
