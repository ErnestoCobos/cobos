<template>
    <div class="flex items-center">
    <span v-if="field.loadingWords.includes(field.value)" class="mr-3 text-60">
      <loader width="30"/>
    </span>

        <span :class="{ 'text-danger': field.failedWords.includes(field.value) }">
      {{ field.value }}
    </span>
    </div>
</template>

<script>
export default {
    props: ['resourceName', 'field'],
};
</script>
