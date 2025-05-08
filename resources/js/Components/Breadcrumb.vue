<script setup>
const props = defineProps({
  items: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <nav class="text-sm text-gray-500 mb-4 flex items-center" aria-label="Breadcrumb">
    <ol class="list-none p-0 inline-flex items-center">
      <li v-for="(item, idx) in items" :key="idx" class="flex items-center">
        <template v-if="item.href">
          <a :href="item.href" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center">
            <i v-if="item.icon" :class="item.icon + ' mr-1'" />{{ item.label }}
          </a>
        </template>
        <template v-else>
          <span class="text-gray-700 font-semibold flex items-center">
            <i v-if="item.icon" :class="item.icon + ' mr-1'" />{{ item.label }}
          </span>
        </template>
        <span v-if="idx < items.length - 1" class="mx-2 text-gray-400">&gt;</span>
      </li>
    </ol>
  </nav>
</template> 