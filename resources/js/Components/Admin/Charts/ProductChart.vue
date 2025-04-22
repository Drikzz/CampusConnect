<template>
  <AdminChart
    title="Product Listings"
    description="Track product listings by type and status"
    :chart-data="processedChartData"
    chart-type="bar"
    :chart-options="chartOptions"
    :show-date-filter="true"
    :show-category-filter="true"
    :filter-categories="productTypes"
    @filter-change="handleFilterChange"
    :y-formatter="yFormatter"
  />
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import AdminChart from '@/Components/Admin/AdminChart.vue'
import axios from 'axios'

const props = defineProps({
  productData: {
    type: Object,
    required: true,
    default: () => ({
      labels: [],
      datasets: []
    })
  },
  // New props to support ShadCN-style flat data structure
  data: {
    type: Array,
    default: () => []
  },
  index: {
    type: String,
    default: 'name'
  },
  categories: {
    type: Array,
    default: () => ['sale', 'trade']
  },
  yFormatter: {
    type: Function,
    default: (value) => value?.toString() || ''
  }
})

const emit = defineEmits(['filter-change'])

// Chart data structure
const chartData = ref({
  labels: [],
  datasets: []
})

// Process either flat data or Chart.js formatted data
const processedChartData = computed(() => {
  // If the ShadCN-style flat data array is provided, use it
  if (props.data && props.data.length > 0) {
    const labels = props.data.map(item => item[props.index])
    
    // Create datasets from categories
    const datasets = props.categories.map((category, index) => {
      // Define friendly colors based on dataset type
      let borderColor, backgroundColor;
      
      if (category.toLowerCase().includes('sale')) {
        borderColor = 'hsl(210, 75%, 60%)'; // Bright blue
        backgroundColor = 'hsla(210, 75%, 60%, 0.7)';
      } else if (category.toLowerCase().includes('trade')) {
        borderColor = 'hsl(150, 70%, 60%)'; // Bright green
        backgroundColor = 'hsla(150, 70%, 60%, 0.7)';
      } else {
        // Enhanced default fallback colors
        const hues = [210, 150, 25, 320, 180]; // Blue, Green, Orange, Purple, Teal
        const colorIndex = index % hues.length;
        borderColor = `hsl(${hues[colorIndex]}, 75%, 60%)`;
        backgroundColor = `hsla(${hues[colorIndex]}, 75%, 60%, 0.7)`;
      }

      return {
        label: category.charAt(0).toUpperCase() + category.slice(1),
        data: props.data.map(item => item[category] || 0),
        borderColor,
        backgroundColor,
        borderWidth: 1,
        borderRadius: 6,
        // No data offset needed with this format as it's already separated by category
      };
    });

    return { labels, datasets };
  }
  
  // Otherwise, fall back to the existing chart data
  return chartData.value;
});

// Update chartData whenever productData changes
watch(() => props.productData, (newData) => {
  if (newData && newData.labels && newData.datasets) {
    // Apply pleasing color palette to the datasets
    let processedData = { 
      labels: newData.labels,
      datasets: newData.datasets.map((dataset, index) => {
        // Define specific colors based on dataset type
        let backgroundColor, borderColor;
        
        if (dataset.label.toLowerCase().includes('sale')) {
          backgroundColor = 'hsla(210, 75%, 60%, 0.7)'; // Brighter blue with higher opacity
          borderColor = 'hsl(210, 75%, 60%)';
        } else if (dataset.label.toLowerCase().includes('trade')) {
          backgroundColor = 'hsla(150, 70%, 60%, 0.7)'; // Brighter green with higher opacity
          borderColor = 'hsl(150, 70%, 60%)';
        } else {
          // Enhanced default fallback for other datasets with brighter colors
          const hues = [210, 150, 25, 320, 180]; // Blue, Green, Orange, Purple, Teal
          const colorIndex = index % hues.length;
          backgroundColor = `hsla(${hues[colorIndex]}, 75%, 60%, 0.7)`;
          borderColor = `hsl(${hues[colorIndex]}, 75%, 60%)`;
        }
        
        return {
          ...dataset,
          backgroundColor,
          borderColor,
          borderWidth: 1,
          borderRadius: 6,
        };
      })
    };
    chartData.value = processedData;
  }
}, { immediate: true, deep: true })

// Shadcn-styled chart options for bar chart
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
    includeInvisible: true,
  },
  scales: {
    y: {
      beginAtZero: true,
      border: {
        display: false,
      },
      grid: {
        color: 'hsla(var(--muted), 0.08)', // Theme-based grid lines
        display: true,
        drawBorder: false,
      },
      ticks: {
        color: 'hsl(var(--muted-foreground))',
        padding: 8,
        font: {
          size: 11,
        },
        maxTicksLimit: 6,
        callback: function(value) {
          // Use the formatter if provided through props
          return props.yFormatter ? props.yFormatter(value) : Math.round(value);
        }
      },
      title: {
        display: true,
        text: 'Number of Products',
        color: 'hsl(var(--muted-foreground))',
        font: {
          size: 12,
          weight: 'normal',
        },
        padding: {
          bottom: 4,
        },
      }
    },
    x: {
      border: {
        display: false,
      },
      grid: {
        display: false,
        drawBorder: false,
      },
      ticks: {
        color: 'hsl(var(--muted-foreground))',
        padding: 8,
        font: {
          size: 11,
        },
        maxRotation: 0, // Keep labels horizontal
        autoSkip: true, // Skip labels that would overlap
      },
      title: {
        display: true,
        text: 'Date',
        color: 'hsl(var(--muted-foreground))',
        font: {
          size: 12,
          weight: 'normal',
        },
        padding: {
          top: 4,
        },
      }
    }
  },
  elements: {
    bar: {
      borderWidth: 1,
      borderRadius: 6,
      borderSkipped: false,
    }
  },
  barPercentage: 0.75,
  categoryPercentage: 0.8,
  plugins: {
    legend: {
      position: 'top',
      align: 'start',
      labels: {
        boxWidth: 16,
        boxHeight: 16,
        useBorderRadius: true,
        borderRadius: 4,
        color: 'hsl(var(--foreground))',
        font: {
          size: 12,
        },
        padding: 16,
      }
    },
    tooltip: {
      backgroundColor: 'rgba(255, 255, 255, 0.95)',
      titleColor: '#333',
      bodyColor: '#333',
      bodyFont: {
        size: 12,
      },
      titleFont: {
        size: 12,
        weight: 'normal',
      },
      padding: 12,
      boxPadding: 6,
      borderColor: 'rgba(0, 0, 0, 0.1)',
      borderWidth: 1,
      cornerRadius: 6,
      displayColors: true,
      boxWidth: 12,
      boxHeight: 12,
      usePointStyle: true,
      callbacks: {
        label: (context) => {
          let label = context.dataset.label || '';
          if (label) label += ': ';
          
          let value = context.parsed.y;
          if (value !== null && value !== undefined) {
            // Use the formatter if provided through props
            value = props.yFormatter ? props.yFormatter(value) : Math.round(value);
          }
          return label + value;
        }
      }
    }
  }
}

// Product types for filtering
const productTypes = [
  { value: 'all', label: 'All Products' },
  { value: 'sale', label: 'For Sale Products' },
  { value: 'trade', label: 'For Trade Products' }
]

// Handle filter changes from the chart component
async function handleFilterChange(filters) {
  // Emit the filter change up to parent component
  emit('filter-change', filters)
  
  // Optional direct API call for server-side filtering
  if (filters.useServerData) {
    try {
      const response = await axios.get('/api/admin/charts/products', {
        params: filters
      })
      if (response.data && response.data.labels && response.data.datasets) {
        chartData.value = response.data
      }
    } catch (error) {
      console.error('Failed to fetch filtered product data:', error)
    }
  }
}
</script>
