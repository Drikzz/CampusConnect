<template>
  <div class="contact-page">
    <!-- Header Section -->
    <div class="header">
      <h1>Contact Us</h1>
      <p>Have questions, feedback, or need help with Campus Connect? Reach out—we’d love to hear from you!</p>
    </div>

    <!-- Contact Details Section -->
    <div class="section contact-details">
      <!-- Email -->
      <div class="contact-item">
        <h2>Email</h2>
        <p>
          <a href="mailto:campusconnectyourmarketplace@gmail.com" class="text-link">
            campusconnectyourmarketplace@gmail.com
          </a>
        </p>
      </div>

      <!-- Campus Location -->
      <div class="contact-item">
        <h2>Campus Location</h2>
        <p>Western Mindanao State University (WMSU)</p>
        <p>Normal Road, Baliwasan, Zamboanga City, Philippines</p>
      </div>

      <!-- Social Media -->
      <div class="contact-item">
        <h2>Social Media</h2>
        <ul class="social-links">
          <li>
            <a href="https://facebook.com/CampusConnect" target="_blank" class="social-link">
              <i class="fab fa-facebook-square"></i> Facebook: CampusConnect
            </a>
          </li>
          <li>
            <a href="https://instagram.com/campus_connect05" target="_blank" class="social-link">
              <i class="fab fa-instagram"></i> Instagram: @campus_connect05
            </a>
          </li>
          <li>
            <a href="https://tiktok.com/@campusconnect_05" target="_blank" class="social-link">
              <i class="fab fa-tiktok"></i> TikTok: @campusconnect_05
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Contact Form Section -->
    <div class="section contact-form">
      <div class="form-container">
        <h2>Send Us a Message</h2>
        <form @submit.prevent="submitForm" class="form">
          <input v-model="form.name" type="text" placeholder="Your Name" class="input-field" />
          <input v-model="form.email" type="email" placeholder="Your Email" class="input-field" />
          <input v-model="form.subject" type="text" placeholder="Subject" class="input-field" />
          <textarea v-model="form.message" placeholder="Your Message" rows="5" class="textarea-field"></textarea>
          <div class="button-container">
            <button type="submit" class="submit-button">Send Message</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ContactPage",
  data() {
    return {
      form: {
        name: "",
        email: "",
        subject: "",
        message: "",
      },
    };
  },
  methods: {
    submitForm() {
      // Handle form submission logic
      console.log("Form submitted:", this.form);
      alert("Thank you for reaching out! We'll get back to you soon.");
      // Reset the form
      this.form = {
        name: "",
        email: "",
        subject: "",
        message: "",
      };
    },
  },
};
</script>

<style scoped>
.contact-page {
  padding: 20px;
  font-family: Arial, sans-serif;
  color: #333;
  line-height: 1.6;
  max-width: 800px; /* Center the content */
  margin: 0 auto; /* Center horizontally */
}

.header {
  text-align: center;
  margin-bottom: 40px; /* Added space below the header */
}

.header h1 {
  font-size: 2.5rem;
  color: #2c3e50;
}

.header p {
  font-size: 1.2rem;
  color: #7f8c8d;
  margin-top: 10px; /* Added space between title and description */
}

.section {
  margin-bottom: 40px; /* Added space between sections */
}

.section h2 {
  font-size: 1.8rem;
  color: #34495e;
  margin-bottom: 15px;
  border-bottom: 2px solid #e54646; /* Updated line color */
  display: inline-block;
  padding-bottom: 5px;
  text-align: center; /* Center the heading */
}

.contact-item {
  margin-bottom: 30px; /* Added space between contact items */
  text-align: center; /* Centered the content */
}

.contact-item p {
  margin: 5px 0;
}

.social-links {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: center; /* Center the social links */
}

.social-links li {
  margin-bottom: 10px;
  font-size: 1rem;
  color: #333;
}

.social-link {
  display: inline-flex; /* Center the icon and text inline */
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: #333;
  font-size: 1rem;
}

.social-link i {
  font-size: 1.5rem; /* Adjust icon size */
}

.social-link:hover {
  text-decoration: underline;
}

.form-container {
  background-color: #f9f9f9; /* Added a light background for the form */
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Added a subtle shadow */
  max-width: 600px; /* Adjusted size of the form container */
  margin: 0 auto; /* Centered the form container */
  text-align: center; /* Center the form content */
}

.contact-form h2 {
  font-size: 1.8rem;
  color: #34495e;
  margin-bottom: 20px;
  border-bottom: 2px solid #e54646;
  display: inline-block;
  padding-bottom: 5px;
}

.input-field,
.textarea-field {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 1rem;
  margin-bottom: 15px;
}

.input-field:focus,
.textarea-field:focus {
  outline: none;
  border-color: #e54646;
}

.button-container {
  text-align: center; /* Center the submit button */
}

.submit-button {
  background-color: #e54646;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
}

.submit-button:hover {
  background-color: #c0392b;
}
</style>