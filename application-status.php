<?php include 'includes/header.php'; ?>

<main class="flex-grow">
  <!-- Hero Section -->
  <section class="bg-gov-blue text-white py-10">
    <div class="gov-container">
      <div class="text-center max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">Application Status</h1>
        <p class="text-lg">
          Track the status of your festival permit application
        </p>
      </div>
    </div>
  </section>
  
  <!-- Status Tracker Section -->
  <section class="py-12">
    <div class="gov-container">
      <div class="max-w-3xl mx-auto">
        <?php include 'components/status-tracker.php'; ?>
      </div>
    </div>
  </section>
  
  <!-- Information Section -->
  <section class="py-12 bg-white">
    <div class="gov-container">
      <div class="max-w-3xl mx-auto">
        <h2 class="text-2xl font-bold text-gov-darkblue mb-6">Understanding Your Application Status</h2>
        
        <div class="space-y-6">
          <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
            <div class="flex items-center mb-2">
              <div class="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                <span class="text-yellow-600 font-bold">1</span>
              </div>
              <h3 class="text-lg font-bold text-gov-darkblue">Pending</h3>
            </div>
            <p class="text-gray-600 ml-11">
              Your application has been successfully submitted and is in queue for initial review. No action is required from your end at this stage.
            </p>
          </div>
          
          <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
            <div class="flex items-center mb-2">
              <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                <span class="text-blue-600 font-bold">2</span>
              </div>
              <h3 class="text-lg font-bold text-gov-darkblue">Under Review</h3>
            </div>
            <p class="text-gray-600 ml-11">
              Your application is currently being reviewed by relevant authorities. This includes document verification, police approval, and municipal clearance.
            </p>
          </div>
          
          <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
            <div class="flex items-center mb-2">
              <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                <span class="text-green-600 font-bold">3</span>
              </div>
              <h3 class="text-lg font-bold text-gov-darkblue">Approved</h3>
            </div>
            <p class="text-gray-600 ml-11">
              Congratulations! Your application has been approved. You will receive your permit via email, and you can also download it from your account.
            </p>
          </div>
          
          <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
            <div class="flex items-center mb-2">
              <div class="h-8 w-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                <span class="text-red-600 font-bold">4</span>
              </div>
              <h3 class="text-lg font-bold text-gov-darkblue">Rejected</h3>
            </div>
            <p class="text-gray-600 ml-11">
              Your application has been rejected due to specific reasons which will be communicated to you. You may address these issues and reapply.
            </p>
          </div>
        </div>
        
        <div class="mt-8 p-4 bg-blue-50 border border-blue-100 rounded-sm">
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">Need Help?</h3>
          <p class="text-gray-700 mb-4">
            If you have questions about your application status or need assistance, please contact our support team.
          </p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm font-medium text-gray-600">Email Support:</p>
              <p class="text-sm">support@festivalpermits.gov.in</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Phone Support:</p>
              <p class="text-sm">+91-11-XXXXXXXX (10 AM - 5 PM)</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>