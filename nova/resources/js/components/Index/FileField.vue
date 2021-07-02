<template>
    <p>
        <img
            v-if="imageUrl"
            :class="{ 'rounded-full': field.rounded, rounded: !field.rounded }"
            :src="imageUrl"
            class="align-bottom w-8 h-8"
            style="object-fit: cover"
        />
        <span v-else>&mdash;</span>
    </p>
</template>

<script>
export default {
    props: ['viaResource', 'viaResourceId', 'resourceName', 'field'],
    computed: {
        imageUrl()
        {
            if (this.field.previewUrl && !this.field.thumbnailUrl) {
                return this.field.previewUrl;
            }

            return this.field.thumbnailUrl;
        },
    },
};
</script>
