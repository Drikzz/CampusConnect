<template>
  <div class="relative">
    <img 
      v-if="src && !imageError" 
      :src="formattedSrc" 
      :alt="name || 'User'" 
      @error="handleImageError"
      class="h-full w-full object-cover"
      :class="avatarClasses"
    />
    <div v-else class="flex items-center justify-center h-full w-full bg-gray-200 dark:bg-gray-700" :class="avatarClasses">
      <img 
        :src="`/storage/imgs/image-placeholder.jpg`"
        alt="Default avatar"
        class="h-full w-full object-cover"
        :class="avatarClasses"
        @error="useTextFallback = true"
        v-if="!useTextFallback"
      />
      <span v-else class="text-gray-600 dark:text-gray-300 font-medium">
        {{ getInitials(name) }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  src: {
    type: String,
    default: ''
  },
  name: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (val) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(val)
  },
  rounded: {
    type: String,
    default: 'full',
    validator: (val) => ['none', 'sm', 'md', 'lg', 'full'].includes(val)
  },
  bordered: {
    type: Boolean,
    default: false
  },
  borderColor: {
    type: String,
    default: 'border-primary'
  }
});

const imageError = ref(false);
const useTextFallback = ref(false);

const handleImageError = () => {
  imageError.value = true;
};

// Get initials from name
const getInitials = (name) => {
  if (!name) return '?';
  return name
    .split(' ')
    .map(part => part.charAt(0).toUpperCase())
    .slice(0, 2)
    .join('');
};

// Ensure the src is properly formatted
const formattedSrc = computed(() => {
  if (!props.src) return '';
  
  // If already a full URL, return as is
  if (props.src.startsWith('http://') || props.src.startsWith('https://')) {
    return props.src;
  }
  
  // If it starts with /storage, it's correct
  if (props.src.startsWith('/storage/')) {
    return props.src;
  }
  
  // If it starts with storage/ (without slash), add the slash
  if (props.src.startsWith('storage/')) {
    return '/' + props.src;
  }
  
  // Otherwise, assume it should be in storage
  return `/storage/${props.src}`;
});

// Size classes
const sizeClasses = computed(() => {
  const sizes = {
    xs: 'h-6 w-6 text-xs',
    sm: 'h-8 w-8 text-sm',
    md: 'h-10 w-10 text-base',
    lg: 'h-12 w-12 text-lg',
    xl: 'h-16 w-16 text-xl'
  };
  return sizes[props.size] || sizes.md;
});

const roundedClasses = computed(() => {
  return 'rounded-full';
});

const avatarClasses = computed(() => {
  const classes = [sizeClasses.value, roundedClasses.value];
  
  if (props.bordered) {
    classes.push('border-2', props.borderColor);
  }
  
  return classes;
});

// Reset the error state when src changes
watch(() => props.src, () => {
  imageError.value = false;
  useTextFallback.value = false;
}, { immediate: true });
</script>