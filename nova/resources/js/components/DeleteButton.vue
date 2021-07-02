<template>
    <button
        class="
      cursor-pointer
      dim
      btn btn-link
      text-primary
      inline-flex
      items-center
    "
        tabindex="0"
        type="button"
        @keydown.enter.prevent="$emit('click')"
        @click.prevent="$emit('click')"
    >
        <icon height="16" type="delete" view-box="0 0 20 20" width="16"/>
        <slot/>
    </button>
</template>

<script>
export default {};
</script>
