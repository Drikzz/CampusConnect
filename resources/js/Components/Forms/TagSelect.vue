<template>
  <div class="w-full">
    <Label class="block mb-2">
      Product Tags 
      <span class="text-sm text-gray-500">(Select up to 5)</span>
    </Label>
    
    <div class="relative">
      <Command class="w-full rounded-lg border bg-white">
        <!-- Selected Tags Display -->
        <div class="flex flex-wrap gap-2 p-2">
          <div v-for="tag in modelValue" :key="tag.id" 
               class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm">
            {{ tag.name }}
            <button type="button" @click="removeTag(tag)" 
                    class="rounded-full p-0.5 hover:bg-primary/20">
              <X class="h-3 w-3" />
            </button>
          </div>
        </div>

        <!-- Tag Selector -->
        <div v-if="modelValue.length < 5" class="border-t">
          <CommandInput 
            placeholder="Search tags..." 
            class="border-none focus:ring-0"
          />
          <CommandList class="max-h-[200px] overflow-auto p-2">
            <CommandEmpty>No tags found</CommandEmpty>
            <CommandGroup>
              <CommandItem
                v-for="tag in availableTags.filter(t => !isTagSelected(t))"
                :key="tag.id"
                :value="tag.name"
                @select="() => addTag(tag)"
                class="cursor-pointer rounded-md px-2 py-1.5 hover:bg-primary/10"
              >
                {{ tag.name }}
              </CommandItem>
            </CommandGroup>
          </CommandList>
        </div>
      </Command>
    </div>

    <div v-if="modelValue.length >= 5" class="mt-1.5 text-sm text-yellow-600">
      Maximum number of tags reached (5)
    </div>
    
    <div v-if="error" class="mt-1.5 text-sm text-red-500">
      {{ error }}
    </div>

    <!-- Update the hidden input to pass tag IDs directly -->
    <input 
      type="hidden" 
      :value="JSON.stringify(modelValue.map(tag => tag.id))" 
      name="tags" 
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Label } from '@/Components/ui/label';
import { X } from 'lucide-vue-next';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/Components/ui/command';

const props = defineProps({
  modelValue: {
    type: Array,
    required: true,
    default: () => []
  },
  availableTags: {
    type: Array,
    required: true
  },
  error: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const isTagSelected = (tag) => {
  return props.modelValue.some(t => t.id === tag.id);
};

const addTag = (tag) => {
  if (props.modelValue.length < 5 && !isTagSelected(tag)) {
    emit('update:modelValue', [...props.modelValue, {
      id: tag.id,
      name: tag.name,
      slug: tag.slug || ''
    }]);
  }
};

const removeTag = (tag) => {
  emit('update:modelValue', props.modelValue.filter(t => t.id !== tag.id));
};
</script>
