<template>
  <AdminChart
    title="User Growth"
    description="Track user registration trends over time"
    :chart-data="processedChartData"
    chart-type="line"
    :chart-options="chartOptions"
    :show-date-filter="true"
    :show-status-filter="true"
    :filter-statuses="userStatuses"
    @filter-change="handleFilterChange"
  />
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import AdminChart from '@/Components/Admin/AdminChart.vue'
import axios from 'axios'

const props = defineProps({
  userData: {
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
    default: () => ['Total Users', 'Verified Users', 'Unverified Users']
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

// Transform flat data to Chart.js format if flat data is provided
const processedChartData = computed(() => {
  // If the ShadCN-style flat data array is provided, use it
  if (props.data && props.data.length > 0) {
    const labels = props.data.map(item => item[props.index])
    
    // Create datasets from categories
    const datasets = props.categories.map((category, index) => {
      // Define friendly colors based on dataset type
      let borderColor, backgroundColor;
      
      if (category === 'Total Users') {
        borderColor = 'hsl(210, 70%, 60%)'; // Softer blue
        backgroundColor = 'hsla(210, 70%, 60%, 0.7)';
      } else if (category === 'Verified Users') {
        borderColor = 'hsl(150, 65%, 60%)'; // Softer green
        backgroundColor = 'hsla(150, 65%, 60%, 0.7)'; 
      } else if (category === 'Unverified Users') {
        borderColor = 'hsl(25, 90%, 65%)'; // Vibrant orange
        backgroundColor = 'hsla(25, 90%, 65%, 0.7)';
      } else {
        // Default fallback colors
        const hues = [210, 150, 25, 320, 180]; // Blue, Green, Orange, Purple, Teal
        borderColor = `hsl(${hues[index % hues.length]}, 70%, 65%)`;
        backgroundColor = `hsla(${hues[index % hues.length]}, 70%, 65%, 0.12)`;
      }

      // Apply dash patterns for different lines
      const dashPatterns = [undefined, [6, 3], [2, 2]];
      
      return {
        label: category,
        data: props.data.map(item => item[category] || 0), // Keep original values
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

// Watch for changes in the userData prop (original format)
watch(() => props.userData, (newData) => {
  if (newData && newData.labels && newData.datasets) {
    // Check if we need to add unverified users dataset
    let hasUnverifiedDataset = newData.datasets.some(
      ds => ds.label === 'Unverified Users' || ds.label.toLowerCase().includes('unverified')
    );
    
    let processedData = { ...newData };
    
    // If no unverified users dataset exists but we have total and verified, calculate unverified
    if (!hasUnverifiedDataset) {
      const totalUsersDataset = newData.datasets.find(ds => ds.label === 'Total Users');
      const verifiedUsersDataset = newData.datasets.find(ds => ds.label === 'Verified Users');
      
      if (totalUsersDataset && verifiedUsersDataset) {
        // Calculate unverified as: total - verified
        const unverifiedData = totalUsersDataset.data.map((total, idx) => 
          total - (verifiedUsersDataset.data[idx] || 0)
        );
        
        // Add unverified users dataset with softer colors
        processedData.datasets.push({
          label: 'Unverified Users',
          data: unverifiedData,
          borderColor: 'hsl(45, 85%, 65%)', // Softer gold color
          backgroundColor: 'hsla(45, 85%, 65%, 0.15)',
          borderWidth: 2,
          fill: true,
          tension: 0.4,
        });
      }
    }
    
    // Apply different dash patterns and widths for better visibility
    const dashPatterns = [undefined, [6, 3], [2, 2]];
    // Vary line widths to differentiate datasets
    const lineWidths = [3, 2.5, 2, 1.5];
    
    // Update all dataset colors to be softer
    processedData.datasets = processedData.datasets.map((dataset, index) => {
      // Define friendly colors based on dataset type
      let borderColor, backgroundColor;
      
      if (dataset.label === 'Total Users') {
        borderColor = 'hsl(210, 70%, 60%)'; // Softer blue
        backgroundColor = 'hsla(210, 70%, 60%, 0.7)'; // Increased opacity to match product chart
      } else if (dataset.label === 'Verified Users') {
        borderColor = 'hsl(150, 65%, 60%)'; // Softer green
        backgroundColor = 'hsla(150, 65%, 60%, 0.7)'; // Increased opacity to match product chart
      } else if (dataset.label === 'Unverified Users') {
        // Updated from gold to a more vibrant orange that's easier on the eyes
        borderColor = 'hsl(25, 90%, 65%)'; 
        backgroundColor = 'hsla(25, 90%, 65%, 0.7)'; // Increased opacity to match product chart
      } else {
        // Updated default fallback options with brighter colors
        const colorIndex = index % 5;
        const hues = [210, 150, 25, 320, 180]; // Blue, Green, Orange, Purple, Teal
        borderColor = `hsl(${hues[colorIndex]}, 70%, 65%)`;
        backgroundColor = `hsla(${hues[colorIndex]}, 70%, 65%, 0.12)`;
      }
      
      return {
        ...dataset,
        borderColor,
        backgroundColor,
        borderWidth: lineWidths[index % lineWidths.length], // Use varying line widths for differentiation
        borderDash: dashPatterns[index % dashPatterns.length],
        pointRadius: 0, // Changed from 4 to 0 to remove circles
        pointHoverRadius: 6,
        pointHitRadius: 10,
        pointBorderWidth: 1.5,
        pointBackgroundColor: borderColor,
        pointBorderColor: 'white',
        fill: false,
        tension: 0.2,
        // Keep original data without offsets or rounding
        data: dataset.data,
      };
    });
    
    chartData.value = processedData;
  }
}, { immediate: true, deep: true })

// Update chart options with formatter
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
    includeInvisible: true, // Ensure tooltip shows all datasets
  },
  scales: {
    y: {
      beginAtZero: true,
      border: {
        display: false,
      },
      grid: {
        color: 'hsla(var(--muted), 0.08)', // Theme-based grid lines
        drawBorder: false,
      },
      ticks: {
        color: 'hsl(var(--muted-foreground))',
        padding: 8,
        font: {
          size: window.innerWidth < 640 ? 9 : 11, // Smaller font on mobile
        },
        maxTicksLimit: window.innerWidth < 640 ? 4 : 6, // Fewer ticks on mobile
        callback: function(value) {
          // Use the formatter if provided, otherwise default to rounding
          return props.yFormatter ? props.yFormatter(value, this.index) : Math.round(value);
        }
      },
      title: {
        display: true,
        text: 'Number of Users',
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
      backgroundColor: 'rgba(255, 255, 255, 0.95)', // Always use white background for visibility
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
          
          // Show actual value without modification
          let value = context.parsed.y;
          if (value !== null && value !== undefined) {
            // Use the yFormatter if provided, otherwise show raw value
            value = props.yFormatter ? props.yFormatter(value) : value;
          }
          return label + value;
        }
      }
    }
  }
}

// Enhanced user status filters with more descriptive labels
const userStatuses = [
  { value: 'all', label: 'All Users' },
  { value: 'verified', label: 'Verified Users Only' },
  { value: 'unverified', label: 'Unverified Users Only' }
]

// Enhanced filter change handler that makes API call if client-side filtering isn't adequate
async function handleFilterChange(filters) {
  // First emit the filter change up to parent component
  emit('filter-change', filters)
  
  // Optionally make a direct API call for more accurate server-side filtered data
  if (filters.useServerData) {
    try {
      const response = await axios.get('/api/admin/charts/users', {
        params: filters
      })
      if (response.data && response.data.labels && response.data.datasets) {
        chartData.value = response.data
      }
    } catch (error) {
      console.error('Failed to fetch filtered user data:', error)
    }
  }
}
</script>
