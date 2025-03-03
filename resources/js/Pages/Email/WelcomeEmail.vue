<script setup>
defineProps({
  user: {
    type: Object,
    required: true
  }
});
</script>

<template>
  <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <div style="background-color: #4F46E5; padding: 20px; text-align: center;">
      <h1 style="color: white; margin: 0;">Welcome to Campus Connect!</h1>
    </div>
    
    <div style="padding: 20px; background-color: #f8f8f8;">
      <h2>Hello {{ user.first_name }}!</h2>
      
      <p>Thank you for joining Campus Connect. We're excited to have you as part of our community!</p>
      
      <p>Please verify your email address by clicking the button below:</p>
      
      <div style="text-align: center; margin: 30px 0;">
        <a :href="user.verification_url"
           style="background-color: #4F46E5; color: white; padding: 12px 24px; 
                  text-decoration: none; border-radius: 5px; display: inline-block;">
          Verify Email Address
        </a>
      </div>
      
      <p>If you did not create an account, no further action is required.</p>
      
      <p>Best regards,<br>The Campus Connect Team</p>
    </div>
  </div>
</template>
