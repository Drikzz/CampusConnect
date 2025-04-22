<template>
  <div class="about-page">
    <!-- Header Section -->
    <div class="header">
      <h1>About Campus Connect</h1>
      <p>Your trusted marketplace for the WMSU community.</p>
    </div>

    <!-- Company Section -->
    <div class="section company">
      <h2>Company</h2>
      <div class="content">
        <p>
          Campus Connect is a digital marketplace developed by UniLink exclusively for students, faculty, and staff of Western Mindanao State University (WMSU). The platform enables users to buy, sell, and trade school-related items such as textbooks, uniforms, lab equipment, and other academic essentials.
        </p>
        <p>
          By providing a trusted and accessible environment, Campus Connect helps users save money, reduce waste, and conveniently connect with others within the WMSU community.
        </p>
      </div>
    </div>

    <!-- Mission & Vision Section -->
    <div class="section mission-vision">
      <h2>Mission & Vision</h2>
      <div class="content">
        <div class="mission">
          <h3>Mission</h3>
          <p>
            To bridge the gap within university communities by providing a seamless platform for buying and selling a wide range of items, empowering users to save money, reduce waste, and build a supportive campus environment.
          </p>
        </div>
        <div class="vision">
          <h3>Vision</h3>
          <p>
            Revolutionize the university community's access to fundamental resources, moving the game forward towards sustainable and affordable shopping, while instilling a culture of sustainability, mutual support, and collaboration among students, faculty, and staff.
          </p>
        </div>
      </div>
    </div>

    <!-- Who We Serve Section -->
    <div class="section who-we-serve">
      <h2>Who We Serve</h2>
      <div class="content">
        <p>
          Campus Connect is exclusively available to the students, faculty, and staff of Western Mindanao State University (WMSU). All users are verified through their school ID or university email to ensure a safe and trustworthy trading environment.
        </p>
      </div>
    </div>

    <!-- Future Plans Section -->
    <div class="section future-plans">
      <h2>Future Plans</h2>
      <div class="content">
        <p>
          After successful implementation at WMSU, <strong>Campus Connect aims to expand</strong> to other universities in the Philippines. The goal is to build an inter-campus network where students from various institutions can engage in safe, sustainable, and meaningful exchanges of educational resources.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AboutPage",
};
</script>

<style scoped>
.about-page {
  padding: 20px;
  font-family: Arial, sans-serif;
  color: #333;
  line-height: 1.6;
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
  font-size: 1.2rem;
  color: #7f8c8d;
}

.section {
  margin-bottom: 40px;
}

.section h2 {
  font-size: 1.8rem;
  color: #34495e;
  margin-bottom: 15px;
  border-bottom: 2px solid #e54646;
  display: inline-block;
  padding-bottom: 5px;
}

.section .content {
  text-align: center;
}

.section p {
  margin: 10px 0;
}

.mission,
.vision {
  margin-bottom: 20px;
}

.future-plans strong {
  color: #e74c3c;
}

@media (min-width: 768px) {
  .about-page {
    max-width: 800px;
    margin: 0 auto;
  }

  .section .content {
    text-align: left;
  }
}
</style>