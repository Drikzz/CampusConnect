<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-medium">{{ title }}</h3>
        <p class="text-sm text-muted-foreground">{{ description }}</p>
      </div>
      
      <!-- Filter Controls -->
      <div class="flex flex-wrap gap-2">
        <Select v-if="showDateFilter" v-model="selectedDateRange" @update:model-value="applyFilters">
          <SelectTrigger class="w-40">
            <SelectValue placeholder="Date range" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="7days">Last 7 days</SelectItem>
            <SelectItem value="30days">Last 30 days</SelectItem>
            <SelectItem value="90days">Last 90 days</SelectItem>
            <SelectItem value="year">Last year</SelectItem>
            <SelectItem value="all">All time</SelectItem>
          </SelectContent>
        </Select>

        <Select v-if="showCategoryFilter && filterCategories.length > 0" v-model="selectedCategory" @update:model-value="applyFilters">
          <SelectTrigger class="w-40">
            <SelectValue placeholder="Category" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Categories</SelectItem>
            <SelectItem v-for="category in filterCategories" :key="category.value" :value="category.value">
              {{ category.label }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Select v-if="showStatusFilter && filterStatuses.length > 0" v-model="selectedStatus" @update:model-value="applyFilters">
          <SelectTrigger class="w-40">
            <SelectValue placeholder="Status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Statuses</SelectItem>
            <SelectItem v-for="status in filterStatuses" :key="status.value" :value="status.value">
              {{ status.label }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <!-- Chart Container -->
    <Card class="p-0">
      <CardContent class="pt-6">
        <div v-if="chartError" class="flex items-center justify-center p-8 text-red-500 text-center">
          <p>{{ chartError }}</p>
        </div>
        <div v-else-if="!hasData" class="flex items-center justify-center h-40 text-gray-500">
          <p>No data available for the selected period</p>
        </div>
        <canvas v-else ref="chartCanvas" :height="height"></canvas>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed, onBeforeUnmount, markRaw, nextTick } from 'vue'
import { Card, CardContent } from '@/Components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import Chart from 'chart.js/auto'

const props = defineProps({
  title: { type: String, default: 'Chart' },
  description: { type: String, default: '' },
  chartData: { type: Object, required: true },
  chartType: { 
    type: String, 
    default: 'line',
    validator: (value) => ['line', 'bar', 'pie', 'doughnut'].includes(value)
  },
  chartOptions: { type: Object, default: () => ({}) },
  height: { type: Number, default: 300 },
  showDateFilter: { type: Boolean, default: true },
  showCategoryFilter: { type: Boolean, default: false },
  showStatusFilter: { type: Boolean, default: false },
  filterCategories: { type: Array, default: () => [] },
  filterStatuses: { type: Array, default: () => [] },
  rawData: { type: Array, default: () => [] }
})

const emit = defineEmits(['filter-change'])

// State variables
const chartCanvas = ref(null)
const chartInstance = ref(null)
const selectedDateRange = ref('30days')
const selectedCategory = ref('all')
const selectedStatus = ref('all')
const chartError = ref(null)
let updateTimeout = null

// Theme-based chart colors using CSS variables with improved pastel fallbacks
const chartColors = [
  { variable: '--chart-1', background: 0.15, fallbackHue: 205, fallbackSat: '65%', fallbackLight: '60%' }, // Soft blue
  { variable: '--chart-2', background: 0.15, fallbackHue: 150, fallbackSat: '60%', fallbackLight: '65%' }, // Soft teal
  { variable: '--chart-3', background: 0.15, fallbackHue: 45, fallbackSat: '65%', fallbackLight: '65%' },  // Soft gold
  { variable: '--chart-4', background: 0.15, fallbackHue: 280, fallbackSat: '50%', fallbackLight: '70%' }, // Soft purple
  { variable: '--chart-5', background: 0.15, fallbackHue: 340, fallbackSat: '60%', fallbackLight: '70%' }, // Soft pink
  { variable: '--primary', background: 0.15, fallbackHue: 0, fallbackSat: '70%', fallbackLight: '60%' }    // Primary
]

/**
 * More efficient data cloning that handles Chart.js specific needs
 */
const prepareChartData = (source) => {
  try {
    if (!source || typeof source !== 'object') {
      return { labels: [], datasets: [] }
    }
    
    // Handle case where source might be in an unexpected format
    if (!source.labels && !source.datasets) {
      console.warn('Chart data is missing expected structure, trying to adapt...')
      
      // Try to adapt if source seems to be a direct dataset
      if (Array.isArray(source.data)) {
        return {
          labels: Array.isArray(source.labels) ? [...source.labels] : [],
          datasets: [{
            label: source.label || 'Data',
            data: [...source.data],
            // Set default colors
            borderColor: `hsl(${chartColors[0].fallbackHue}, 70%, 50%)`,
            backgroundColor: `hsla(${chartColors[0].fallbackHue}, 70%, 50%, ${chartColors[0].background})`
          }]
        }
      }
      
      return { labels: [], datasets: [] }
    }
    
    // Create basic structure with a simple array copy for labels
    const result = { 
      labels: Array.isArray(source.labels) ? [...source.labels] : [],
      datasets: []
    }
    
    // Process datasets with more efficient approach
    if (Array.isArray(source.datasets)) {
      result.datasets = source.datasets.map((dataset, index) => {
        // Create a new plain object with spread to avoid reactivity issues
        const newDataset = { ...dataset }
        
        // Simple array copy for data array
        newDataset.data = Array.isArray(dataset.data) ? [...dataset.data] : []
        
        // Apply consistent color scheme if not defined
        if (!newDataset.backgroundColor || !newDataset.borderColor) {
          const colorIndex = index % chartColors.length
          const colorVar = chartColors[colorIndex]
          
          // Generate fallback color in HSL format
          const hue = colorVar.fallbackHue
          const saturation = '70%'
          const lightness = '50%'

          if (!newDataset.backgroundColor) {
            // Different opacity for different chart types
            const opacity = props.chartType === 'line' 
              ? colorVar.background 
              : props.chartType === 'bar' 
                ? 0.7 
                : 0.8
                
            // Try to use CSS variables, fallback to HSL
            newDataset.backgroundColor = `hsla(var(${colorVar.variable}, ${hue} ${saturation} ${lightness}), ${opacity})`
          }
          
          if (!newDataset.borderColor) {
            newDataset.borderColor = `hsl(var(${colorVar.variable}, ${hue} ${saturation} ${lightness}))`
          }
        }
        
        return newDataset
      })
    }
    
    return result
  } catch (error) {
    console.error('Error preparing chart data:', error)
    chartError.value = "Failed to prepare chart data"
    return { labels: [], datasets: [] }
  }
}

/**
 * Get date range based on selected filter
 */
const getDateRange = () => {
  const now = new Date()
  let startDate = new Date()
  
  switch (selectedDateRange.value) {
    case '7days': startDate.setDate(now.getDate() - 7); break
    case '30days': startDate.setDate(now.getDate() - 30); break
    case '90days': startDate.setDate(now.getDate() - 90); break
    case 'year': startDate.setFullYear(now.getFullYear() - 1); break
    case 'all': default: startDate = new Date(0) // Beginning of time
  }
  
  return { startDate, endDate: now }
}

/**
 * Format number with locale-specific formatting
 */
const formatNumber = (num) => {
  if (typeof num !== 'number') return '0'
  return new Intl.NumberFormat().format(num)
}

/**
 * Apply filters and emit event to parent
 */
const applyFilters = () => {
  chartError.value = null // Reset error state
  
  let filters = {
    dateRange: selectedDateRange.value,
    category: selectedCategory.value,
    status: selectedStatus.value,
    startDate: getDateRange().startDate,
    endDate: getDateRange().endDate,
  }
  
  // Add a flag to indicate if we need server-side filtering for complex cases
  if (
    (selectedStatus.value === 'unverified' && 
     !props.chartData?.datasets?.some(ds => ds.label === 'Unverified Users')) || 
    (selectedCategory.value !== 'all' && !props.rawData)
  ) {
    filters.useServerData = true
  }
  
  emit('filter-change', filters)
}

/**
 * Validate chart data structure before use
 */
const validateChartData = (data) => {
  // Basic structure check with detailed logging
  if (!data) {
    console.warn('Chart data is null or undefined')
    return false
  }
  
  // Check for labels (be more permissive)
  if (!data.labels) {
    console.warn('Chart data is missing labels array - will create empty labels')
    data.labels = []
  } else if (!Array.isArray(data.labels)) {
    console.warn('Chart labels is not an array, converting:', data.labels)
    data.labels = [data.labels]
  } 
  
  // Check for datasets (be more permissive)
  if (!data.datasets) {
    console.warn('Chart data is missing datasets array - attempting recovery')
    // Try to recover by creating dataset from data if possible
    if (Array.isArray(data.data)) {
      console.log('Found data array at root level, creating dataset')
      data.datasets = [{
        label: 'Data',
        data: data.data,
        backgroundColor: `hsla(${chartColors[0].fallbackHue}, 70%, 50%, ${chartColors[0].background})`,
        borderColor: `hsl(${chartColors[0].fallbackHue}, 70%, 50%)`
      }]
    } else {
      console.error('No datasets or recoverable data found')
      return false
    }
  } else if (!Array.isArray(data.datasets)) {
    console.warn('Chart datasets is not an array, converting:', data.datasets)
    data.datasets = [data.datasets]
  } else if (data.datasets.length === 0) {
    console.warn('Chart datasets array is empty')
    return false
  }
  
  // For pie/doughnut charts
  if (['pie', 'doughnut'].includes(props.chartType)) {
    const hasData = data.datasets[0]?.data
    if (!hasData) {
      console.warn('Pie/doughnut chart missing data array in first dataset')
      return false
    }
    
    if (!Array.isArray(data.datasets[0].data)) {
      console.warn('Pie/doughnut data is not an array, converting')
      data.datasets[0].data = [data.datasets[0].data]
    }
    
    return data.datasets[0].data.length > 0
  } 
  
  // For line/bar charts, ensure each dataset has a data array
  let validDatasets = 0
  data.datasets.forEach((dataset, index) => {
    if (!dataset.data) {
      console.warn(`Dataset at index ${index} is missing a data array`)
      dataset.data = []
    } else if (!Array.isArray(dataset.data)) {
      console.warn(`Dataset at index ${index} data is not an array, converting:`, dataset.data)
      dataset.data = [dataset.data]
    }
    
    if (dataset.data.length > 0) {
      validDatasets++
    }
  })
  
  return validDatasets > 0
}

/**
 * Get default options based on chart type
 */
const getDefaultOptions = () => {
  const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: {
        position: 'top',
        align: 'start',
        labels: {
          boxWidth: 16,
          boxHeight: 16,
          useBorderRadius: true,
          borderRadius: 4,
          color: 'hsl(var(--foreground, 220 10% 40%))',
          font: {
            size: 12,
          },
          padding: 16,
        }
      },
      tooltip: {
        backgroundColor: 'hsl(var(--background, 0 0% 100%))',
        titleColor: 'hsl(var(--foreground, 220 10% 40%))',
        bodyColor: 'hsl(var(--foreground, 220 10% 40%))',
        bodyFont: {
          size: 12,
        },
        titleFont: {
          size: 12,
          weight: 'normal',
        },
        padding: 12,
        boxPadding: 6,
        borderColor: 'hsla(var(--border, 220 13% 91%), 0.5)',
        borderWidth: 1,
        cornerRadius: 6,
        displayColors: true,
        boxWidth: 12,
        boxHeight: 12,
        usePointStyle: true,
        callbacks: {
          label: (context) => {
            let label = context.dataset.label || ''
            if (label) label += ': '
            
            // Safely extract value based on chart type
            const value = (props.chartType === 'line' || props.chartType === 'bar')
              ? context.parsed?.y ?? context.raw ?? 0
              : context.parsed ?? context.raw ?? 0
              
            // Format value based on content
            if (label.includes('Sales') || label.includes('Revenue') || label.includes('Amount')) {
              return label + new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value)
            } 
            
            return label + formatNumber(value)
          }
        }
      }
    }
  }
  
  // Add scales for line and bar charts
  if (['line', 'bar'].includes(props.chartType)) {
    baseOptions.scales = {
      x: {
        display: true,
        border: {
          display: false,
        },
        grid: {
          display: false,
        },
        ticks: {
          color: 'hsl(var(--muted-foreground, 220 5% 45%))',
          padding: 8,
          font: {
            size: 11,
          },
        },
        title: {
          display: true,
          color: 'hsl(var(--muted-foreground, 220 5% 45%))',
          font: {
            size: 12,
            weight: 'normal',
          },
        }
      },
      y: {
        display: true,
        beginAtZero: true,
        border: {
          display: false,
        },
        grid: {
          color: 'hsla(var(--muted, 220 14% 96%), 0.08)', // Even lighter grid lines
        },
        ticks: {
          precision: 0,
          font: { size: 11 },
          color: 'hsl(var(--muted-foreground, 220 5% 45%))',
          padding: 8,
        },
        title: {
          display: true,
          color: 'hsl(var(--muted-foreground, 220 5% 45%))',
          font: {
            size: 12,
            weight: 'normal',
          },
        }
      }
    }
  }
  
  // Add specific options for line charts to make them cleaner
  if (props.chartType === 'line') {
    baseOptions.elements = {
      line: {
        tension: 0.3, // Slightly smoother lines
        borderWidth: 2,
        borderJoinStyle: 'round', // Round line joins
        capBezierPoints: true,
      },
      point: {
        radius: 0, // Hide points by default
        hoverRadius: 5, // Show on hover
        hitRadius: 8, // Larger hit area for interaction
        borderWidth: 2,
      }
    }
  }
  
  // Add specific options for bar charts
  if (props.chartType === 'bar') {
    baseOptions.elements = {
      bar: {
        borderWidth: 1,
        borderRadius: 4,
        borderSkipped: false, // Don't skip border radius on any side
      }
    }
    // Add proper spacing between bars
    baseOptions.barPercentage = 0.85;
    baseOptions.categoryPercentage = 0.8;
  }
  
  return baseOptions
}

/**
 * Create a new chart instance with better error handling
 */
const createChart = () => {
  try {
    // Clean up any existing chart
    destroyChart()

    if (!chartCanvas.value) {
      chartError.value = "Chart canvas not found"
      console.error("Chart canvas element is not available")
      return
    }
    
    // Add check for actual DOM element readiness
    const canvasElement = chartCanvas.value
    if (!canvasElement.getContext) {
      console.error("Canvas element does not have a context - DOM may not be fully initialized")
      chartError.value = "Canvas rendering context not available"
      return
    }
    
    // Prepare and validate chart data
    const data = prepareChartData(props.chartData)
    if (!validateChartData(data)) {
      chartError.value = "Invalid chart data structure"
      console.error("Chart data validation failed", data)
      return
    }

    // Apply consistent color scheme to datasets
    data.datasets.forEach((dataset, index) => {
      const colorIndex = index % chartColors.length
      const colorVar = chartColors[colorIndex]
      const hue = colorVar.fallbackHue
      const saturation = colorVar.fallbackSat || '65%'
      const lightness = colorVar.fallbackLight || '60%'
      
      // Set backgroundColor if not already defined
      if (!dataset.backgroundColor) {
        // Different opacity values for different chart types
        const opacity = ['pie', 'doughnut'].includes(props.chartType) ? 0.7 : 
                        props.chartType === 'bar' ? 0.5 : 0.15
                        
        dataset.backgroundColor = `hsla(${hue}, ${saturation}, ${lightness}, ${opacity})`
        
        // Try using CSS variables if available in the environment
        try {
          const style = window.getComputedStyle(document.documentElement)
          const cssVarValue = style.getPropertyValue(`var(${colorVar.variable})`)
          if (cssVarValue) {
            dataset.backgroundColor = `hsla(var(${colorVar.variable}), ${opacity})`
          }
        } catch (e) {
          // Fallback already set
        }
      }
      
      // Set borderColor if not already defined
      if (!dataset.borderColor) {
        dataset.borderColor = `hsl(${hue}, ${saturation}, ${lightness})`
        
        // Try using CSS variables if available
        try {
          const style = window.getComputedStyle(document.documentElement)
          const cssVarValue = style.getPropertyValue(`var(${colorVar.variable})`)
          if (cssVarValue) {
            dataset.borderColor = `hsl(var(${colorVar.variable}))`
          }
        } catch (e) {
          // Fallback already set
        }
      }
      
      // Add rounded corners for bar charts
      if (props.chartType === 'bar' && !dataset.hasOwnProperty('borderRadius')) {
        dataset.borderRadius = 4
      }
      
      // For pie/doughnut, add borderColor and hoverOffset if not set
      if (['pie', 'doughnut'].includes(props.chartType)) {
        if (!dataset.hasOwnProperty('borderWidth')) {
          dataset.borderWidth = 2
        }
        if (!dataset.hasOwnProperty('hoverOffset')) {
          dataset.hoverOffset = 10
        }
        if (!dataset.hasOwnProperty('borderColor')) {
          dataset.borderColor = 'rgb(255, 255, 255, 0.75)' // Lighter borders for pie slices
        }
      }
      
      // Specifically for line charts, set pointRadius to 0 to hide points
      if (props.chartType === 'line') {
        dataset.pointRadius = 0;
        dataset.pointHoverRadius = 5;
        dataset.pointHitRadius = 10;
        dataset.pointBorderWidth = 2;
        dataset.tension = 0.4; // Slightly more curve for softer appearance
        dataset.borderWidth = dataset.borderWidth || 2.5;
      }
    })

    // Merge options and create chart
    const options = { ...getDefaultOptions(), ...props.chartOptions }
    
    // Ensure the canvas context is available
    const ctx = canvasElement.getContext('2d')
    if (!ctx) {
      console.error("Failed to get 2D context from canvas")
      chartError.value = "Canvas rendering context not available"
      return
    }
    
    // Create chart with try/catch for specific Chart.js errors
    try {
      chartInstance.value = markRaw(new Chart(ctx, {
        type: props.chartType,
        data: markRaw(data),
        options: markRaw(options)
      }))
    } catch (error) {
      console.error('Chart.js initialization error:', error)
      chartError.value = `Chart.js error: ${error.message || 'Unknown error'}`
    }
  } catch (error) {
    console.error('Error creating chart:', error)
    chartError.value = "Failed to create chart: " + (error.message || 'Unknown error')
  }
}

/**
 * Update existing chart with new data
 */
const updateChart = () => {
  try {
    if (!chartInstance.value) {
      createChart()
      return
    }

    // Prepare and validate chart data
    const data = prepareChartData(props.chartData)
    if (!validateChartData(data)) {
      chartError.value = "Invalid chart data structure"
      return
    }
    
    // Update chart labels
    chartInstance.value.data.labels = data.labels
    
    // Efficiently update datasets
    data.datasets.forEach((newDataset, i) => {
      if (chartInstance.value.data.datasets[i]) {
        // Update existing dataset properties
        Object.assign(chartInstance.value.data.datasets[i], newDataset)
      } else {
        // Add new dataset
        chartInstance.value.data.datasets[i] = newDataset
      }
    })
    
    // Remove extra datasets if needed
    if (chartInstance.value.data.datasets.length > data.datasets.length) {
      chartInstance.value.data.datasets.length = data.datasets.length
    }
    
    // Update the chart with animation disabled for faster updates
    chartInstance.value.update('none')
  } catch (error) {
    console.error('Error updating chart:', error)
    chartError.value = "Failed to update chart: " + (error.message || 'Unknown error')
  }
}

/**
 * Safely destroy the chart instance
 */
const destroyChart = () => {
  if (chartInstance.value) {
    try {
      chartInstance.value.destroy()
    } catch (e) {
      console.error('Error destroying chart:', e)
    }
    chartInstance.value = null
  }
}

// Check if chart has valid data to display with better detection
const hasData = computed(() => {
  if (!props.chartData) return false
  
  // Try to detect data even if not in the expected structure
  if (Array.isArray(props.chartData.data) && props.chartData.data.length > 0) {
    return props.chartData.data.some(val => val !== null && val !== undefined)
  }
  
  if (!props.chartData.datasets || !Array.isArray(props.chartData.datasets)) {
    return false
  }
  
  // Check if any dataset has non-empty data
  return props.chartData.datasets.some(dataset => {
    if (!dataset?.data) return false
    if (!Array.isArray(dataset.data)) return dataset.data !== 0 && dataset.data !== null && dataset.data !== undefined
    return dataset.data.length > 0 && dataset.data.some(val => val !== null && val !== undefined)
  })
})

// Debounced chart update when data changes
watch(() => props.chartData, (newData, oldData) => {
  chartError.value = null
  
  // Prevent unnecessary updates by comparing the data
  const hasChanged = JSON.stringify(newData) !== JSON.stringify(oldData)
  if (!hasChanged) return
  
  clearTimeout(updateTimeout)
  updateTimeout = setTimeout(updateChart, 100)
}, { deep: true })

// Monitor filter changes
watch([selectedDateRange, selectedCategory, selectedStatus], applyFilters)

// Initialize chart on mount with improved DOM readiness detection
onMounted(() => {
  // Give the DOM more time to render, then initialize chart
  setTimeout(() => {
    let retryCount = 0
    const maxRetries = 5
    
    function tryInitChart() {
      if (chartCanvas.value && document.body.contains(chartCanvas.value)) {
        createChart()
        applyFilters()
      } else if (retryCount < maxRetries) {
        retryCount++
        setTimeout(tryInitChart, 100)
      } else {
        console.error('Failed to initialize chart after multiple attempts')
        chartError.value = 'Failed to initialize chart component'
      }
    }
    
    tryInitChart()
  }, 200)
})

// Clean up resources on unmount
onBeforeUnmount(() => {
  clearTimeout(updateTimeout)
  destroyChart()
})
</script>
