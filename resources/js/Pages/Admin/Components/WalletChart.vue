<template>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Revenue Stats Card -->
    <Card className="col-span-2">
      <CardHeader>
        <CardTitle>Revenue Overview</CardTitle>
        <CardDescription>Platform revenue from fees and charges</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="flex items-center space-x-4">
          <ChartBarIcon class="h-12 w-12 text-primary-color" />
          <div>
            <p class="text-3xl font-bold">₱{{ formatNumber(data.revenue) }}</p>
            <p class="text-sm text-muted-foreground">Total Revenue</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Top Sellers Stats -->
    <Card>
      <CardHeader>
        <CardTitle>Top Sellers</CardTitle>
        <CardDescription>By wallet balance</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="space-y-4">
          <div v-for="seller in data.top_sellers" :key="seller.name" class="flex items-center justify-between">
            <div class="space-y-1">
              <div class="flex items-center space-x-2">
                <UserCircleIcon class="h-5 w-5 text-muted-foreground" />
                <p class="text-sm font-medium leading-none">{{ seller.name }}</p>
              </div>
            </div>
            <div class="text-sm text-muted-foreground">₱{{ formatNumber(seller.balance) }}</div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Revenue Stats Cards -->
    <Card>
      <CardHeader>
        <div class="flex items-center space-x-2">
          <CurrencyDollarIcon class="h-5 w-5 text-primary-color" />
          <CardTitle>Monthly Revenue</CardTitle>
        </div>
        <CardDescription>Total fees collected</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">₱{{ formatNumber(data.revenue) }}</div>
      </CardContent>
    </Card>

    <!-- Refunds Stats Card -->
    <Card>
      <CardHeader>
        <div class="flex items-center space-x-2">
          <ArrowUturnLeftIcon class="h-5 w-5 text-red-500" />
          <CardTitle>Total Refunds</CardTitle>
        </div>
        <CardDescription>This month</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold text-red-500">₱{{ formatNumber(data.refunds) }}</div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card"
import { ChartBarIcon, UserCircleIcon, CurrencyDollarIcon, ArrowUturnLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)
</script>
