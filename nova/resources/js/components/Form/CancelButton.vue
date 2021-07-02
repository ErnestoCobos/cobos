<template>
    <a
        class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6"
        tabindex="0"
        @click="$emit('click')"
    >
        {{ __('Cancel') }}
    </a>
</template>

<script>
export default {
    //
};
</script>
