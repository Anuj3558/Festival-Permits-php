<?php 
$pageTitle = "Contact Us";
include 'includes/header.php';
require_once 'config/database.php';

// Form processing
$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => '',
    'queryType' => 'general'
];
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $formData = [
        'name' => htmlspecialchars(trim($_POST['name'] ?? '')),
        'email' => htmlspecialchars(trim($_POST['email'] ?? '')),
        'phone' => htmlspecialchars(trim($_POST['phone'] ?? '')),
        'subject' => htmlspecialchars(trim($_POST['subject'] ?? '')),
        'message' => htmlspecialchars(trim($_POST['message'] ?? '')),
        'queryType' => htmlspecialchars(trim($_POST['queryType'] ?? 'general'))
    ];

    // Validate inputs
    if (empty($formData['name'])) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($formData['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email is invalid';
    }
    
    if (!empty($formData['phone']) && !preg_match('/^\d{10}$/', $formData['phone'])) {
        $errors['phone'] = 'Phone number must be 10 digits';
    }
    
    if (empty($formData['subject'])) {
        $errors['subject'] = 'Subject is required';
    }
    
    if (empty($formData['message'])) {
        $errors['message'] = 'Message is required';
    }
    // If no errors, process form and insert into database
    if (empty($errors)) {
        try {
            $db = (new Database())->connect();
            $stmt = $db->prepare("INSERT INTO contact_submissions (name, email, phone, subject, message, query_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $formData['name'],
                $formData['email'],
                $formData['phone'],
                $formData['subject'],
                $formData['message'],
                $formData['queryType']
            ]);

            $successMessage = "Thank you for your message! We'll get back to you soon.";
            
            // Reset form on success
            $formData = [
                'name' => '',
                'email' => '',
                'phone' => '',
                'subject' => '',
                'message' => '',
                'queryType' => 'general'
            ];
        } catch (PDOException $e) {
            error_log("Database Error in contact.php: " . $e->getMessage());
            $errors['database'] = "An error occurred while submitting your message. Please try again later.";
        }
    }
}

// Rest of the existing HTML code
?>


<main class="flex-grow">
  <!-- Hero Section -->
  <section class="bg-gov-blue text-white py-10">
    <div class="container mx-auto px-4">
      <div class="text-center max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">Contact Us</h1>
        <p class="text-lg">
          We're here to help with your festival permit inquiries
        </p>
      </div>
    </div>
  </section>
  
  <!-- Contact Info Section -->
  <section class="py-12">
    <div class="container mx-auto px-4">
      <?php if ($successMessage): ?>
        <div class="bg-green-50 border border-green-200 rounded-sm p-4 mb-8">
          <div class="flex">
            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-green-800"><?php echo $successMessage; ?></p>
          </div>
        </div>
      <?php endif; ?>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm text-center">
          <div class="flex justify-center mb-4">
            <svg class="h-10 w-10 text-gov-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">Call Us</h3>
          <p class="text-gray-700 mb-2">Mon-Fri: 10 AM to 5 PM</p>
          <p class="text-gray-900 font-medium">+91-11-XXXXXXXX</p>
        </div>
        
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm text-center">
          <div class="flex justify-center mb-4">
            <svg class="h-10 w-10 text-gov-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">Email Us</h3>
          <p class="text-gray-700 mb-2">We'll respond within 24 hours</p>
          <p class="text-gray-900 font-medium">support@festivalpermits.gov.in</p>
        </div>
        
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm text-center">
          <div class="flex justify-center mb-4">
            <svg class="h-10 w-10 text-gov-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">Visit Us</h3>
          <p class="text-gray-700 mb-2">Ministry of Culture & Tourism</p>
          <p class="text-gray-900 font-medium">New Delhi, India - 110001</p>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
          <h2 class="text-2xl font-bold text-gov-darkblue mb-6">Send us a Message</h2>
          
          <form method="POST" class="space-y-4">
            <div>
              <label for="queryType" class="gov-label">Query Type</label>
              <select
                id="queryType"
                name="queryType"
                class="gov-input w-full h-10 p-2 "
              >
                <option value="general" <?php echo $formData['queryType'] === 'general' ? 'selected' : ''; ?>>General Inquiry</option>
                <option value="application" <?php echo $formData['queryType'] === 'application' ? 'selected' : ''; ?>>Application Status</option>
                <option value="technical" <?php echo $formData['queryType'] === 'technical' ? 'selected' : ''; ?>>Technical Issue</option>
                <option value="payment" <?php echo $formData['queryType'] === 'payment' ? 'selected' : ''; ?>>Payment Related</option>
                <option value="complaint" <?php echo $formData['queryType'] === 'complaint' ? 'selected' : ''; ?>>Complaint</option>
              </select>
            </div>
            
            <div>
              <label for="name" class="gov-label">Full Name</label>
              <input
                type="text"
                id="name"
                name="name"
                value="<?php echo htmlspecialchars($formData['name']); ?>"
                placeholder="Enter your name"
                class="gov-input w-full h-10 p-2  <?php echo isset($errors['name']) ? 'border-red-500' : ''; ?>"
              />
              <?php if (isset($errors['name'])): ?>
                <p class="text-red-500 text-xs mt-1"><?php echo $errors['name']; ?></p>
              <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="email" class="gov-label">Email Address</label>
                <input
                  type="email"
                  id="email"
                  name="email"
                  value="<?php echo htmlspecialchars($formData['email']); ?>"
                  placeholder="Enter your email"
                  class="gov-input  w-full h-10 p-2  <?php echo isset($errors['email']) ? 'border-red-500' : ''; ?>"
                />
                <?php if (isset($errors['email'])): ?>
                  <p class="text-red-500 text-xs mt-1"><?php echo $errors['email']; ?></p>
                <?php endif; ?>
              </div>
              
              <div>
                <label for="phone" class="gov-label">Phone Number (Optional)</label>
                <input
                  type="text"
                  id="phone"
                  name="phone"
                  value="<?php echo htmlspecialchars($formData['phone']); ?>"
                  placeholder="Enter 10-digit number"
                  class="gov-input  w-full h-10 p-2  <?php echo isset($errors['phone']) ? 'border-red-500' : ''; ?>"
                />
                <?php if (isset($errors['phone'])): ?>
                  <p class="text-red-500 text-xs mt-1"><?php echo $errors['phone']; ?></p>
                <?php endif; ?>
              </div>
            </div>
            
            <div>
              <label for="subject" class="gov-label">Subject</label>
              <input
                type="text"
                id="subject"
                name="subject"
                value="<?php echo htmlspecialchars($formData['subject']); ?>"
                placeholder="Enter subject"
                class="gov-input  w-full h-10 p-2  <?php echo isset($errors['subject']) ? 'border-red-500' : ''; ?>"
              />
              <?php if (isset($errors['subject'])): ?>
                <p class="text-red-500 text-xs mt-1"><?php echo $errors['subject']; ?></p>
              <?php endif; ?>
            </div>
            
            <div>
              <label for="message" class="gov-label ">Message</label>
              <textarea
                id="message"
                name="message"
                rows="5"
                placeholder="Enter your message"
                class="gov-input p-2 w-full h-40 p-2  <?php echo isset($errors['message']) ? 'border-red-500' : ''; ?>"
              ><?php echo htmlspecialchars($formData['message']); ?></textarea>
              <?php if (isset($errors['message'])): ?>
                <p class="text-red-500 text-xs mt-1 "><?php echo $errors['message']; ?></p>
              <?php endif; ?>
            </div>
            
            <button
              type="submit"
              class="gov-btn w-full border bg-gov-darkblue text-white h-16 hover:bg-gov-darkblue hover:text-white"
            >
              Send Message
            </button>
          </form>
        </div>
        
        <div>
          <h2 class="text-2xl font-bold text-gov-darkblue mb-6">Frequently Asked Questions</h2>
          
          <div class="space-y-4">
            <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
              <div class="flex">
                <svg class="h-5 w-5 text-gov-blue mr-2 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-bold text-gov-darkblue mb-2">How long does it take to process an application?</h3>
              </div>
              <p class="text-gray-700 ml-7">
                Standard applications are processed within 3-5 working days. Applications for larger events may take 7-10 working days.
              </p>
            </div>
            
            <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
              <div class="flex">
                <svg class="h-5 w-5 text-gov-blue mr-2 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-bold text-gov-darkblue mb-2">How do I check my application status?</h3>
              </div>
              <p class="text-gray-700 ml-7">
                You can check your application status using your application ID and registered mobile number on the Application Status page.
              </p>
            </div>
            
            <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
              <div class="flex">
                <svg class="h-5 w-5 text-gov-blue mr-2 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-bold text-gov-darkblue mb-2">What if my application is rejected?</h3>
              </div>
              <p class="text-gray-700 ml-7">
                If your application is rejected, you will receive a notification with the reasons for rejection. You can address those issues and reapply.
              </p>
            </div>
            
            <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
              <div class="flex">
                <svg class="h-5 w-5 text-red-500 mr-2 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="text-lg font-bold text-gov-darkblue mb-2">Important Notice</h3>
              </div>
              <p class="text-gray-700 ml-7">
                All permit applications for the upcoming festival season must be submitted at least 15 days before the event date. Late applications may not be processed in time.
              </p>
            </div>
            
            <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
              <div class="flex">
                <svg class="h-5 w-5 text-gov-blue mr-2 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="text-lg font-bold text-gov-darkblue mb-2">Contact Our Regional Offices</h3>
              </div>
              <div class="ml-7 space-y-3 mt-3">
                <div>
                  <p class="font-medium text-gray-800">Mumbai Office</p>
                  <p class="text-sm text-gray-600">+91-22-XXXXXXXX</p>
                </div>
                
                <div>
                  <p class="font-medium text-gray-800">Kolkata Office</p>
                  <p class="text-sm text-gray-600">+91-33-XXXXXXXX</p>
                </div>
                
                <div>
                  <p class="font-medium text-gray-800">Pune Office</p>
                  <p class="text-sm text-gray-600">+91-20-XXXXXXXX</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>