<template>
  <div class="blog-page">
    <div class="header">
      <h1>{{ blogDetails.title }}</h1>
      <p><strong>Posted on:</strong> {{ blogDetails.postedOn }}</p>
      <p><strong>By:</strong> {{ blogDetails.author }}</p>
    </div>

    <p class="introduction" v-html="blogDetails.introduction"></p>

    <div class="sections">
      <div v-for="(section, index) in blogDetails.sections" :key="index" class="section">
        <h2>{{ section.subtitle }}</h2>
        <p v-if="section.content" v-html="section.content"></p>
        <ul v-if="section.list">
          <li v-for="(item, idx) in section.list" :key="idx" v-html="item"></li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const blogDetails = {
  title: "Make the Most of Campus Connect: A Complete Guide for WMSU Students",
  postedOn: "April 22, 2025",
  author: "Campus Connect Team",
  introduction: `
    Being a student at WMSU means juggling academics, organization fees, projects, and personal expenses — and it adds up fast. 
    That’s exactly why we built Campus Connect: to help you save money, earn extra cash, and connect with fellow students safely.
    Whether you're looking to buy pre-loved school items or sell your unused stuff, this guide is your go-to!
  `,
  sections: [
    {
      subtitle: "Why Buying Second-Hand Is a Smart Student Move",
      content: `
        Let’s be honest: not everything you need for school has to be brand new.
        Here’s why second-hand just makes sense:
      `,
      list: [
        "You Save Money - Why buy a ₱2,000 textbook when a gently used one is just ₱700?",
        "It’s Sustainable - Reusing items helps the environment by reducing waste and carbon emissions.",
        "You're Supporting Your Campus Community - Every purchase helps a fellow student earn some cash — it's student-to-student support!",
        "It’s Safe and Exclusive - All users are verified with @wmsu.edu.ph email addresses for a secure community experience.",
      ],
    },
    {
      subtitle: "Want to Sell Fast? Here Are 6 Tips That Work",
      list: [
        "Set a Reasonable Price - Check what others are charging for similar items. Aim for a sweet spot — affordable but fair.",
        "Write a Clear, Honest Title - Example: “CIT School Blazer – Female, Size Medium (Used Once)”",
        "Describe Properly - Include the brand, color, size, condition, and any flaws. Honesty builds trust.",
        "Upload Clear Photos - Lighting and clarity matter! Show front, back, and close-up shots. Use plain backgrounds and natural light.",
        "Use Keywords - Write “PE Uniform for CHS” or “Basic Engineering Calculator” so your item pops up in searches.",
        "Reply Quickly - Buyers tend to choose active, responsive sellers. Check your Campus Connect messages often!",
      ],
    },
    {
      subtitle: "Quick Guide: How to Take Awesome Item Photos",
      list: [
        "Use Natural Lighting - Avoid flash. Shoot near a window for best results.",
        "Keep Backgrounds Clean - A simple wall or desk works best.",
        "Clean the Item - Make it look fresh! Wipe off dust or stains.",
        "Take Multiple Angles - Front, back, sides, and a zoomed-in detail shot.",
        "Don’t Over-Edit - Keep it real and accurate.",
      ],
    },
    {
      subtitle: "Stay Safe While Buying or Selling",
      content: `
        We work hard to keep Campus Connect safe, but here’s how you can help protect yourself:
      `,
      list: [
        "Only Deal Within Campus Connect - No need to move to outside apps like Messenger.",
        "Never Share Personal Info - No one from Campus Connect will ever ask for your password or login.",
        "Avoid Unrealistic Offers - If a deal feels too good to be true, it probably is.",
        "Report Suspicious Activity - If something seems off, email us right away at campusconnectyourmarketplace@gmail.com",
      ],
    },
    {
      subtitle: "Ready to Get Started?",
      list: [
        "Need a book? Check the marketplace.",
        "Want to sell your old bag? Upload it today.",
        `<span style="color: red; font-weight: bold;">Just want to window shop? Go for it — you might find hidden gems!</span>`,
      ],
      content: `
        Campus Connect is here to make student life easier, cheaper, and safer — all in one place.
      `,
    },
  ],
};
</script>

<style scoped>
.blog-page {
  padding: 20px;
  font-family: Arial, sans-serif;
  color: #333;
  line-height: 1.6;
  max-width: 800px;
  margin: 0 auto;
}

.header {
  text-align: center;
  margin-bottom: 30px;
}

.header h1 {
  font-size: 2.5rem;
  color: #2c3e50;
}

.header p {
  font-size: 1rem;
  color: #7f8c8d;
}

.introduction {
  font-size: 1.2rem;
  margin-bottom: 40px;
}

.sections {
  margin-top: 20px;
}

.section h2 {
  font-size: 1.8rem;
  color: #34495e;
  margin-bottom: 15px;
  border-bottom: 2px solid #e54646;
  display: inline-block;
  padding-bottom: 5px;
}

.section ul {
  list-style-type: disc; /* Add bullet points */
  padding-left: 20px; /* Indent bullets */
  margin: 10px 0;
}

.section ul li {
  margin-bottom: 10px;
}
</style>