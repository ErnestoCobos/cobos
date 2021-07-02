<template functional>
    <div :class="{ 'flex-col': props.stacked }" class="flex border-b border-40">
        <slot/>
    </div>
</template>
<script>
export default {
    props: {
        stacked: {type: Boolean, default: false},
    },
};
</script>
