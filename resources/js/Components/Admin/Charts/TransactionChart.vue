<template>
  <AdminChart
    title="Transactions Analysis"
    description="Monitor transaction trends and volume"
    :chart-data="chartData"
    chart-type="line"
    :chart-options="chartOptions"
    :show-date-filter="true"
    :show-category-filter="true"
    :filter-categories="transactionCategories"
    @filter-change="handleFilterChange"
  />
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import AdminChart from '@/Components/Admin/AdminChart.vue'
import axios from 'axios'

const props = defineProps({
  transactionData: {
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

// Update chartData whenever transactionData changes
watch(() => props.transactionData, (newData) => {
  if (newData && newData.labels && newData.datasets) {
    // Apply a pleasant color palette to the datasets
    let processedData = { 
      labels: newData.labels,
      datasets: newData.datasets.map((dataset, index) => {
        // Define specific colors based on dataset type
        let borderColor, backgroundColor;
        
        if (dataset.label === 'Orders') {
          borderColor = 'hsl(210, 75%, 60%)'; // Bright blue
          backgroundColor = 'hsla(210, 75%, 60%, 0.15)';
        } else if (dataset.label === 'Trades') {
          borderColor = 'hsl(150, 70%, 60%)'; // Bright green
          backgroundColor = 'hsla(150, 70%, 60%, 0.15)';
        } else if (dataset.label === 'Wallet Transactions') {
          borderColor = 'hsl(25, 90%, 65%)'; // Bright orange (changed from gold)
          backgroundColor = 'hsla(25, 90%, 65%, 0.15)';
        } else {
          // Enhanced default fallback colors with brighter, more vibrant options
          const hues = [210, 150, 25, 320, 180]; // Blue, Green, Orange, Purple, Teal
          const colorIndex = index % hues.length;
          borderColor = `hsl(${hues[colorIndex]}, 75%, 60%)`;
          backgroundColor = `hsla(${hues[colorIndex]}, 75%, 60%, 0.15)`;
        }
        
        // Add dataset style variations to prevent overlapping issues
        const dashPatterns = [undefined, [6, 3], [2, 2], [10, 5, 2, 5]];
        const borderWidths = [3, 2.5, 2, 3.5];
        
        return {
          ...dataset,
          borderColor,
          backgroundColor,
          borderWidth: borderWidths[index % borderWidths.length],
          pointRadius: 3, // Show small points to distinguish overlapping lines
          pointHoverRadius: 6,
          pointHitRadius: 10,
          pointBorderWidth: 2,
          pointBackgroundColor: borderColor,
          pointBorderColor: 'white',
          tension: 0.4,
          fill: true,
          borderDash: dashPatterns[index % dashPatterns.length], // Apply different dash patterns
          // Add small vertical offset to prevent exact overlapping
          data: dataset.data.map(value => value === null ? null : value + (index * 0.01)),
        };
      })
    };
    chartData.value = processedData;
  }
}, { immediate: true, deep: true })

// Shadcn-styled chart options for line chart
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
    includeInvisible: true, // Show tooltip for hidden points too
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
        text: 'Transaction Count',
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
    line: {
      tension: 0.4, // Smoother curves for softer appearance
      borderWidth: 2.5, // Slightly thicker lines
      borderJoinStyle: 'round',
    },
    point: {
      radius: 3, // Show small points by default
      hoverRadius: 6, // Show points on hover
      hitRadius: 10, // Larger hit area for better UX
    }
  },
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
      intersect: false,
      mode: 'index',
      callbacks: {
        label: (context) => {
          let label = context.dataset.label || '';
          if (label) label += ': ';
          
          // Remove the small offset before displaying the value
          let value = context.parsed.y;
          if (value !== null && value !== undefined) {
            // Remove the artificial offset we added to prevent overlaps
            const datasetIndex = context.datasetIndex;
            value = Math.floor(value * 100) / 100; // Round to remove the small offset
          }
          return label + value;
        }
      }
    }
  }
}

const transactionCategories = [
  { value: 'all', label: 'All Transactions' },
  { value: 'orders', label: 'Orders' },
  { value: 'trades', label: 'Trades' },
  { value: 'wallet', label: 'Wallet' }
]

// Handle filter changes from the chart component
async function handleFilterChange(filters) {
  // Emit the filter change up to parent component
  emit('filter-change', filters)
  
  // Optional direct API call for server-side filtering
  if (filters.useServerData) {
    try {
      const response = await axios.get('/api/admin/charts/transactions', {
        params: filters
      })
      if (response.data && response.data.labels && response.data.datasets) {
        chartData.value = response.data
      }
    } catch (error) {
      console.error('Failed to fetch filtered transaction data:', error)
    }
  }
}
</script>
