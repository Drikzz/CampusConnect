<template>
  <AdminChart
    title="Transactions Analysis"
    description="Monitor transaction trends and volume"
    :chart-data="processedChartData"
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
  },
  // New props to support ShadCN-style flat data structure
  data: {
    type: Array,
    default: () => []
  },
  index: {
    type: String,
    default: 'date'
  },
  categories: {
    type: Array,
    default: () => ['Orders', 'Trades', 'Wallet Transactions']
  },
  yFormatter: {
    type: Function,
    default: (value) => value?.toString() || ''
  }
})

const emit = defineEmits(['filter-change'])

// Add a tracker for previous filters to prevent loops
const previousFilters = ref(null)
// Add flag to track if update is coming from parent
const isReceivingParentUpdate = ref(false)

// Chart data structure
const chartData = ref({
  labels: [],
  datasets: []
})

// Transform flat data to Chart.js format if flat data is provided
const processedChartData = computed(() => {
  // If the ShadCN-style flat data array is provided, use it
  if (props.data && props.data.length > 0) {
    const labels = props.data.map(item => item[props.index])
    
    // Create datasets from categories
    const datasets = props.categories.map((category, index) => {
      // Define specific colors based on dataset type
      let borderColor, backgroundColor;
      
      if (category === 'Orders') {
        borderColor = 'hsl(210, 75%, 60%)'; // Bright blue
        backgroundColor = 'hsla(210, 75%, 60%, 0.7)';
      } else if (category === 'Trades') {
        borderColor = 'hsl(150, 70%, 60%)'; // Bright green
        backgroundColor = 'hsla(150, 70%, 60%, 0.7)';
      } else if (category === 'Wallet Transactions') {
        borderColor = 'hsl(25, 90%, 65%)'; // Bright orange
        backgroundColor = 'hsla(25, 90%, 65%, 0.7)';
      } else {
        // Enhanced default fallback colors
        const hues = [210, 150, 25, 320, 180]; // Blue, Green, Orange, Purple, Teal
        borderColor = `hsl(${hues[index % hues.length]}, 75%, 60%)`;
        backgroundColor = `hsla(${hues[index % hues.length]}, 75%, 60%, 0.7)`;
      }
      
      // Apply dash patterns for different lines
      const dashPatterns = [undefined, [6, 3], [2, 2], [10, 5, 2, 5]];
      
      return {
        label: category,
        data: props.data.map(item => item[category] || 0), // Remove Math.round() to show actual values
        borderColor,
        backgroundColor,
        borderWidth: 2,
        borderDash: dashPatterns[index % dashPatterns.length],
        pointRadius: 0,
        pointHoverRadius: 6,
        pointHitRadius: 10,
        pointBorderWidth: 1.5,
        pointBackgroundColor: borderColor,
        pointBorderColor: 'white',
        fill: false,
        tension: 0.2,
      };
    });

    return { labels, datasets };
  }
  
  // Otherwise, fall back to the existing chart data
  return chartData.value;
});

// Update chartData whenever transactionData changes
watch(() => props.transactionData, (newData) => {
  if (newData && newData.labels && newData.datasets) {
    // Set flag to indicate this is a parent-driven update
    isReceivingParentUpdate.value = true;
    
    // Apply a pleasant color palette to the datasets
    let processedData = { 
      labels: newData.labels,
      datasets: newData.datasets.map((dataset, index) => {
        // Define specific colors based on dataset type
        let borderColor, backgroundColor;
        
        if (dataset.label === 'Orders') {
          borderColor = 'hsl(210, 75%, 60%)'; // Bright blue
          backgroundColor = 'hsla(210, 75%, 60%, 0.7)'; // Increased opacity to match product chart
        } else if (dataset.label === 'Trades') {
          borderColor = 'hsl(150, 70%, 60%)'; // Bright green
          backgroundColor = 'hsla(150, 70%, 60%, 0.7)'; // Increased opacity to match product chart
        } else if (dataset.label === 'Wallet Transactions') {
          borderColor = 'hsl(25, 90%, 65%)'; // Bright orange (changed from gold)
          backgroundColor = 'hsla(25, 90%, 65%, 0.7)'; // Increased opacity to match product chart
        } else {
          // Enhanced default fallback colors with brighter, more vibrant options
          const hues = [210, 150, 25, 320, 180]; // Blue, Green, Orange, Purple, Teal
          const colorIndex = index % hues.length;
          borderColor = `hsl(${hues[colorIndex]}, 75%, 60%)`;
          backgroundColor = `hsla(${hues[colorIndex]}, 75%, 60%, 0.7)`;
        }
        
        // Add dataset style variations to prevent overlapping issues
        const dashPatterns = [undefined, [6, 3], [2, 2], [10, 5, 2, 5]];
        
        // Assign different point styles to each dataset for better differentiation
        const pointStyles = ['circle', 'rect', 'triangle', 'star'];
        const pointStyle = pointStyles[index % pointStyles.length];
        
        // Enhanced visual differentiation through line width instead of vertical offsets
        const lineWidths = [2.5, 2, 1.5, 3];
        
        return {
          ...dataset,
          borderColor,
          backgroundColor,
          borderWidth: lineWidths[index % lineWidths.length], // Use different line widths instead of offsets
          pointRadius: 0,
          pointHoverRadius: 6,
          pointHitRadius: 10,
          pointBorderWidth: 1.5,
          pointBackgroundColor: borderColor,
          pointBorderColor: 'white',
          pointStyle: pointStyle,
          tension: 0.2,
          fill: false,
          borderDash: dashPatterns[index % dashPatterns.length],
          // Keep original data without offsets or rounding
          data: dataset.data,
        };
      })
    };
    chartData.value = processedData;

    // Reset the flag after a short delay to allow reactivity to settle
    setTimeout(() => {
      isReceivingParentUpdate.value = false;
    }, 100);
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
        color: 'hsla(var(--muted), 0.08)', // Theme-based grid lines like UserChart
        display: true, // Explicitly ensure grid lines are shown
        drawBorder: false,
        z: 0, // Ensure grid lines are drawn behind the data
      },
      ticks: {
        color: 'hsl(var(--muted-foreground))',
        padding: 8,
        font: {
          size: window.innerWidth < 640 ? 9 : 11, // Smaller font on mobile
        },
        maxTicksLimit: window.innerWidth < 640 ? 4 : 6, // Fewer ticks on mobile
        stepSize: 1, // Force integer steps
        // Add callback to ensure whole numbers only
        callback: function(value) {
          // Force whole numbers only
          return Math.floor(value);
        }
      },
      title: {
        display: true,
        text: 'Transaction Count',
        color: 'hsl(var(--muted-foreground))',
        font: {
          size: window.innerWidth < 640 ? 10 : 12,
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
          size: window.innerWidth < 640 ? 9 : 11,
        },
        maxRotation: 0, // Keep labels horizontal
        autoSkip: true, // Skip labels that would overlap
        maxTicksLimit: window.innerWidth < 640 ? 5 : 10, // Fewer labels on small screens
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
      tension: 0.2,
      borderWidth: 2, // Standardized line width
      borderJoinStyle: 'round',
    },
    point: {
      radius: 0, // Changed from 4 to 0 to remove circles
      hoverRadius: 6,
      hitRadius: 10,
      borderWidth: 1.5,
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
      callbacks: {
        title: (items) => {
          return items[0].label;
        },
        label: (context) => {
          let label = context.dataset.label || '';
          if (label) label += ': ';
          
          // Display exact value from database without any manipulation
          let value = context.parsed.y;
          if (value !== null && value !== undefined) {
            // Use formatter if provided, otherwise display raw value
            value = props.yFormatter ? props.yFormatter(value) : value;
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
  // Guard: skip if we're currently processing a parent update
  if (isReceivingParentUpdate.value) return;
  
  // Guard: check if these are actually new filter values
  if (previousFilters.value && 
      previousFilters.value.dateRange === filters.dateRange &&
      previousFilters.value.category === filters.category) {
    return // Skip duplicate filter changes
  }
  
  // Store current filters
  previousFilters.value = { ...filters }
  
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
