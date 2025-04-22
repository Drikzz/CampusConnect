<template>
  <div class="terms-page">
    <!-- Header Section -->
    <div class="header">
      <h1>Campus Connect – Terms and Conditions</h1>
      <p>Effective Date: April 2025</p>
    </div>

    <!-- Introduction Section -->
    <div class="section introduction">
      <p>
        These Terms and Conditions (“Terms”) govern your use of the Campus Connect platform, services, and applications. By using Campus Connect, you confirm that you have read, understood, and agreed to be bound by these Terms. If you do not agree, please do not use the platform.
      </p>
    </div>

    <!-- Platform Overview Section -->
    <div class="section overview">
      <h2>Platform Overview</h2>
      <p>
        Campus Connect is a digital marketplace exclusively for students, faculty, and staff of Western Mindanao State University (WMSU). It allows verified users to buy, sell, and trade school-related and personal items in a secure, trusted community.
      </p>
    </div>

    <!-- User Eligibility Section -->
    <div class="section user-eligibility">
      <h2>User Eligibility</h2>
      <p>To access Campus Connect, users must:</p>
      <ul>
        <li>Be a current student, faculty member, or employee of WMSU.</li>
        <li>Complete verification using a <strong>valid WMSU email address</strong>.</li>
        <li>Maintain only one user account.</li>
      </ul>
      <p>Campus Connect may refuse access or revoke accounts if eligibility is not met.</p>
    </div>

    <!-- Account Responsibilities Section -->
    <div class="section account-responsibilities">
      <h2>Account Responsibilities</h2>
      <p>Users must:</p>
      <ul>
        <li>Provide true and accurate information during registration.</li>
        <li>Keep login credentials secure.</li>
        <li>Take responsibility for all actions under their account.</li>
        <li>Update personal information if there are any changes.</li>
      </ul>
    </div>

    <!-- Listing and Selling Items Section -->
    <div class="section listing-selling">
      <h2>Listing and Selling Items</h2>
      <p>Sellers are responsible for:</p>
      <ul>
        <li>Providing accurate item descriptions, pricing, and condition.</li>
        <li>Confirming a sale after a buyer checks out.</li>
        <li>Fulfilling transactions in a timely and honest manner.</li>
      </ul>
      <p>Sellers are <strong>prohibited</strong> from listing:</p>
      <ul>
        <li>Stolen or counterfeit goods</li>
        <li>Items that violate laws or WMSU policies</li>
        <li>Misleading or false listings</li>
      </ul>
    </div>

    <!-- Buying Items Section -->
    <div class="section buying-items">
      <h2>Buying Items</h2>
      <p>Buyers must:</p>
      <ul>
        <li>Carefully review item details before checkout.</li>
        <li>Communicate respectfully with sellers.</li>
        <li>Avoid false claims or abusive behavior.</li>
      </ul>
    </div>

    <!-- Wallet and Cash-Outs Section -->
    <div class="section wallet-cashouts">
      <h2>Wallet and Cash-Outs</h2>
      <ul>
        <li>All earnings are credited to the seller’s <strong>Campus Connect Wallet</strong>.</li>
        <li>After commission deductions, users may <strong>request a cashout</strong>.</li>
        <li>Processing time for cashouts may vary depending on internal policies and payment partner availability.</li>
      </ul>
    </div>

    <!-- Prohibited Behavior Section -->
    <div class="section prohibited-behavior">
      <h2>Prohibited Behavior</h2>
      <p>Users must NOT:</p>
      <ul>
        <li>Post or sell illegal, counterfeit, or stolen items</li>
        <li>Misrepresent products or provide false information</li>
        <li>Abuse or harass other users</li>
        <li>Attempt to manipulate or scam the system</li>
      </ul>
      <p>Any violation may result in immediate suspension or termination of your account.</p>
    </div>

    <!-- Suspension and Termination Section -->
    <div class="section suspension-termination">
      <h2>Suspension and Termination</h2>
      <p>
        Campus Connect may suspend or terminate any account without notice for:
      </p>
      <ul>
        <li>Violations of these Terms</li>
        <li>Fraudulent activity</li>
        <li>Misuse of the platform</li>
      </ul>
      <p>
        In the case of account termination, any remaining balance in the user's wallet may be withheld, especially in cases involving violations or disputes.
      </p>
    </div>

    <!-- Limitation of Liability Section -->
    <div class="section limitation-liability">
      <h2>Limitation of Liability</h2>
      <p>
        Campus Connect is a marketplace facilitator. We are not directly involved in transactions between buyers and sellers. Therefore:
      </p>
      <ul>
        <li>We are not responsible for the condition, delivery, or accuracy of items sold.</li>
        <li>We may assist in resolving disputes but are not liable for user actions.</li>
      </ul>
    </div>

    <!-- Contact Us Section -->
    <div class="section contact-us">
      <h2>Contact Us</h2>
      <p>If you have any questions, feedback, or issues, contact our support team:</p>
      <p>📧 <strong>campusconnectyourmarketplace@gmail.com</strong></p>
      <p>📍 Campus Connect – WMSU Main Campus, Zamboanga City, Philippines</p>
    </div>
  </div>
</template>

<script>
export default {
  name: "TermsPage",
};
</script>

<style scoped>
.terms-page {
  padding: 20px;
  font-family: Arial, sans-serif;
  color: #333;
  line-height: 1.6;
  max-width: 800px; /* Center the content */
  margin: 0 auto; /* Center horizontally */
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
  border-bottom: 2px solid #e54646; /* Updated line color */
  display: inline-block;
  padding-bottom: 5px;
}

.section p,
.section ul {
  margin: 10px 0;
}

ul {
  list-style-type: disc; /* Add bullet points */
  padding-left: 20px; /* Indent bullets */
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  border: 2px solid #e54646; /* Updated border color */
}

table th,
table td {
  border: 1px solid #e54646; /* Updated border color */
  padding: 12px;
  text-align: left;
}

table th {
  background-color: #fce4e4; /* Light red background for headers */
  font-weight: bold;
  color: #e54646; /* Header text color */
  text-transform: uppercase;
}

table tr:nth-child(even) td {
  background-color: #f9f9f9; /* Alternating row color */
}

table tr:hover td {
  background-color: #ffecec; /* Highlight row on hover */
}
</style>