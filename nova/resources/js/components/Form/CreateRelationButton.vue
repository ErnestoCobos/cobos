<template>
    <button
        class="
      rounded
      dim
      font-bold
      text-sm text-primary
      inline-flex
      items-center
      focus:outline-none
      active:outline-none
      focus:shadow-outline
      active:shadow-outline
      pl-1
      pr-2
    "
        type="button"
        @click="$emit('click')"
    >
        <icon height="24" type="add" view-box="0 0 24 24" width="24"/>
        <span>{{ __('Create') }}</span>
    </button>
</template>

<script>
export default {
    //
};
</script>
