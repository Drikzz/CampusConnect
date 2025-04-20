<template>
  <AdminChart
    title="Product Listings"
    description="Track product listings by type and status"
    :chart-data="chartData"
    chart-type="bar"
    :chart-options="chartOptions"
    :show-date-filter="true"
    :show-category-filter="true"
    :filter-categories="productTypes"
    @filter-change="handleFilterChange"
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
  }
})

const emit = defineEmits(['filter-change'])

// Chart data structure
const chartData = ref({
  labels: [],
  datasets: []
})

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
        
        // Make pattern variations for bars to distinguish overlap
        const patterns = [null, 'stripe', 'dot', 'crosshatch'];
        const pattern = patterns[index % patterns.length];

        return {
          ...dataset,
          backgroundColor,
          borderColor,
          borderWidth: 1,
          borderRadius: 6,
          // Add small offset for bar charts to enhance visibility when values are identical
          data: dataset.data.map(value => value === null ? null : value + (index * 0.01)),
          // For pattern fills (could be implemented with pattern.js if available)
          pattern: pattern,
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
        color: 'hsla(220, 14%, 96%, 0.08)', // Even lighter grid lines
        drawBorder: false,
      },
      ticks: {
        color: 'hsl(var(--muted-foreground))',
        padding: 8,
        font: {
          size: 11,
        },
        maxTicksLimit: 6, // Limit the number of ticks for cleaner appearance
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
        maxTicksLimit: 10, // Limit the number of ticks for cleaner appearance
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
      borderRadius: 6, // Slightly more rounded bars
      borderSkipped: false, // Don't skip border radius on any side
    }
  },
  barPercentage: 0.75, // Slightly thinner bars
  categoryPercentage: 0.8, // Control spacing between bar groups
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
      backgroundColor: 'rgba(255, 255, 255, 0.95)', // Always use white background
      titleColor: '#333', // Dark text for contrast
      bodyColor: '#333', // Dark text for contrast
      bodyFont: {
        size: 12,
      },
      titleFont: {
        size: 12,
        weight: 'normal',
      },
      padding: 12,
      boxPadding: 6,
      borderColor: 'rgba(0, 0, 0, 0.1)', // Light border
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
          
          // Remove small offset before displaying the value
          let value = context.parsed.y;
          if (value !== null && value !== undefined) {
            // Round to remove the artificial offset
            value = Math.floor(value * 100) / 100;
          }
          return label + value;
        }
      }
    }
  }
}

// Changed from product statuses to product types as requested
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
