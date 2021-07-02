<template>
    <div :class="`text-${field.textAlign}`">
        <template v-if="hasValue">
            <div class="leading-normal my-2">
                <component
                    :is="`index-${line.component}`"
                    v-for="line in field.lines"
                    :key="line.value"
                    :field="line"
                    :resourceName="resourceName"
                    class="whitespace-no-wrap"
                />
            </div>
        </template>
        <p v-else>&mdash;</p>
    </div>
</template>

<script>
export default {
    props: ['resourceName', 'field'],

    computed: {
        /**
         * Determine if the field has a value other than null.
         */
        hasValue()
        {
            return this.field.lines;
        },
    },
};
</script>
