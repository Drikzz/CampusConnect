<template>
  <AdminChart
    title="User Growth"
    description="Track user registration trends over time"
    :chart-data="chartData"
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
  }
})

const emit = defineEmits(['filter-change'])

// Chart data structure
const chartData = ref({
  labels: [],
  datasets: []
})

// Update chartData whenever userData changes
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
    const borderWidths = [3, 2.5, 2];
    
    // Update all dataset colors to be softer
    processedData.datasets = processedData.datasets.map((dataset, index) => {
      // Define friendly colors based on dataset type
      let borderColor, backgroundColor;
      
      if (dataset.label === 'Total Users') {
        borderColor = 'hsl(210, 70%, 60%)'; // Softer blue
        backgroundColor = 'hsla(210, 70%, 60%, 0.12)';
      } else if (dataset.label === 'Verified Users') {
        borderColor = 'hsl(150, 65%, 60%)'; // Softer green
        backgroundColor = 'hsla(150, 65%, 60%, 0.12)';
      } else if (dataset.label === 'Unverified Users') {
        // Updated from gold to a more vibrant orange that's easier on the eyes
        borderColor = 'hsl(25, 90%, 65%)'; 
        backgroundColor = 'hsla(25, 90%, 65%, 0.12)';
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
        borderWidth: borderWidths[index % borderWidths.length],
        borderDash: dashPatterns[index % dashPatterns.length],
        pointRadius: 3,
        pointBorderWidth: 2,
        pointBackgroundColor: borderColor,
        pointBorderColor: 'white',
        fill: true,
        tension: 0.4,
        // Add small vertical offset to prevent exact overlapping
        data: dataset.data.map(value => value === null ? null : value + (index * 0.01)),
      };
    });
    
    chartData.value = processedData;
  }
}, { immediate: true, deep: true })

// Shadcn-styled chart options
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
        text: 'Number of Users',
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
      borderWidth: 2.5,
      borderJoinStyle: 'round',
    },
    point: {
      radius: 3, // Show small points to help distinguish overlapping lines
      hoverRadius: 6,
      hitRadius: 10,
      borderWidth: 2,
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
          
          // Remove the small offset before displaying the value
          let value = context.parsed.y;
          if (value !== null && value !== undefined) {
            // Round to remove the artificial offset we added
            value = Math.floor(value * 100) / 100;
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
