<script setup>
import { useForm } from '@inertiajs/vue3';
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { router } from '@inertiajs/vue3';

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const resendEmail = () => {
    router.post(route('verification.send'));
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <Card class="w-[32rem]">
            <CardContent class="p-8">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Verify Your Email Address</h2>
                    
                    <p class="text-gray-600 mb-6">
                        Thanks for signing up! Before getting started, could you verify your email address by clicking on 
                        the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                    </p>

                    <div v-if="$page.props.flash.message" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                        {{ $page.props.flash.message }}
                    </div>

                    <form @submit.prevent="submit">
                        <Button 
                            type="submit" 
                            class="w-full bg-primary-color"
                            :disabled="form.processing"
                        >
                            Resend Verification Email
                        </Button>
                    </form>

                    <div class="mt-5">
                        <button
                            @click="resendEmail"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Resend Verification Email
                        </button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
