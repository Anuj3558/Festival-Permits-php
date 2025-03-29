<?php include 'includes/header.php'; ?>

<main class="flex-grow">
  <!-- Hero Section -->
  <section class="relative min-h-screen bg-gov-blue  text-white flex items-center py-12 md:py-20 overflow-hidden">
        <!-- Carousel Background -->
        <div id="carouselBackground" class="absolute inset-0 z-0 opacity-30">
            <div class="carousel-images h-[60vh] w-full">
                <img src="https://i.ytimg.com/vi/jE2BYzLGNuM/maxresdefault.jpg" alt="Festival Image 1" class="absolute top-0 left-0 w-full h-full object-cover transition-opacity duration-1000 opacity-0">
                <img src="https://i.ytimg.com/vi/nMdttRG0a7U/maxresdefault.jpg" alt="Festival Image 2" class="absolute top-0 left-0 w-full h-full object-cover transition-opacity duration-1000 opacity-0">
                <img src="https://i.ytimg.com/vi/Mt9yT9pJkc4/maxresdefault.jpg" alt="Festival Image 3" class="absolute top-0 left-0 w-full h-full object-cover transition-opacity duration-1000 opacity-0">
            </div>
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center max-w-3xl mx-auto stagger-animation">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Festival Permits Self-Service Portal</h1>
                <p class="text-lg md:text-xl mb-8">
                    Apply online for Ganpati and Durga Puja celebration permits with automated fee calculation and real-time status tracking.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="apply.php" class="bg-white text-gov-blue hover:bg-gray-100 transition-colors duration-300 font-medium py-3 px-6 rounded-sm text-center">
                        Apply for Permit
                    </a>
                    <a href="application-status.php" class="bg-transparent hover:bg-blue-700 transition-colors duration-300 border border-white text-white font-medium py-3 px-6 rounded-sm text-center">
                        Track Application
                    </a>
                </div>
            </div>
        </div>
    </section>
  
  <!-- Process Steps -->
  <section class="py-16 bg-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-gov-darkblue">How It Works</h2>
        <p class="text-gray-600 mt-2">Simple steps to apply for your festival permit</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm text-center">
          <div class="h-16 w-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl font-bold text-gov-blue">1</span>
          </div>
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">Fill Application</h3>
          <p class="text-gray-600">
            Complete the online form with details about pandal dimensions, duration, and sound system requirements.
          </p>
        </div>
        
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm text-center">
          <div class="h-16 w-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl font-bold text-gov-blue">2</span>
          </div>
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">Pay Fees</h3>
          <p class="text-gray-600">
            Review automatically calculated fees based on your requirements and complete the payment.
          </p>
        </div>
        
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm text-center">
          <div class="h-16 w-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl font-bold text-gov-blue">3</span>
          </div>
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">Receive Approval</h3>
          <p class="text-gray-600">
            Track your application status online and receive your permit via email once approved.
          </p>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Benefits Section -->
  <section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-gov-darkblue">Benefits</h2>
        <p class="text-gray-600 mt-2">Why use our online permit system</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex">
          <div class="mr-4">
            <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gov-darkblue mb-2">Faster Processing</h3>
            <p class="text-gray-600">
              Get your approvals faster with our streamlined online process compared to traditional methods.
            </p>
          </div>
        </div>
        
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex">
          <div class="mr-4">
            <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gov-darkblue mb-2">24/7 Availability</h3>
            <p class="text-gray-600">
              Apply anytime, anywhere without waiting in lines or visiting multiple offices.
            </p>
          </div>
        </div>
        
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex">
          <div class="mr-4">
            <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gov-darkblue mb-2">Transparent Fees</h3>
            <p class="text-gray-600">
              See exactly how fees are calculated based on your specific requirements with no hidden charges.
            </p>
          </div>
        </div>
        
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex">
          <div class="mr-4">
            <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gov-darkblue mb-2">Real-time Updates</h3>
            <p class="text-gray-600">
              Receive SMS and email notifications about your application status at every step.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- FAQ Section -->
  <section class="py-16 bg-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-gov-darkblue">Frequently Asked Questions</h2>
        <p class="text-gray-600 mt-2">Find answers to common questions about festival permits</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">How long does the approval process take?</h3>
          <p class="text-gray-600">
            The approval process typically takes 3-5 working days, depending on the complexity of your application and the current volume of requests.
          </p>
        </div>
        
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">What documents do I need to submit?</h3>
          <p class="text-gray-600">
            You need to provide proof of identity, location details, and a basic design layout of your pandal. Additional documents may be required based on your specific requirements.
          </p>
        </div>
        
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">Can I modify my application after submission?</h3>
          <p class="text-gray-600">
            Minor modifications can be made by contacting our support team with your application ID. Major changes may require a new application.
          </p>
        </div>
        
        <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
          <h3 class="text-lg font-bold text-gov-darkblue mb-2">What are the sound system restrictions?</h3>
          <p class="text-gray-600">
            Sound systems must comply with local noise regulations, typically limited to 75dB during daytime (6 AM - 10 PM) and 45dB during nighttime. Additional restrictions apply in residential areas.
          </p>
        </div>
      </div>
      
      <div class="text-center mt-8">
        <a href="guidelines.php" class="inline-flex items-center text-gov-blue hover:text-gov-darkblue font-medium">
          View more FAQs
          <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      </div>
    </div>
  </section>
  
  <!-- CTA Section -->
  <section class="py-16 bg-gov-blue text-white">
    <div class="container mx-auto px-4">
      <div class="text-center max-w-3xl mx-auto">
        <h2 class="text-2xl md:text-3xl font-bold mb-4">Ready to Apply for Your Festival Permit?</h2>
        <p class="text-lg mb-8">
          Complete your application in minutes and get faster approvals through our streamlined process.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <a href="apply.php" class="bg-white text-gov-blue hover:bg-gray-100 transition-colors duration-300 font-medium py-3 px-6 rounded-sm text-center">
            Apply Now
          </a>
          <a href="guidelines.php" class="bg-transparent hover:bg-blue-700 transition-colors duration-300 border border-white text-white font-medium py-3 px-6 rounded-sm text-center">
            Read Guidelines
          </a>
        </div>
      </div>
    </div>
  </section>
  <section class="py-16 bg-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-gov-darkblue">PCMC Leadership</h2>
        <p class="text-gray-600 mt-2">Meet the Leaders of Pimpri-Chinchwad Municipal Corporation</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 justify-center">
        <!-- Mayor Section -->
        <div class="bg-white p-8 border border-gray-200 rounded-sm shadow-sm">
          <div class="flex flex-col items-center">
            <div class="mb-6 flex-shrink-0">
              <img src="/api/placeholder/250/300" alt="Mayor of PCMC" class="w-64 h-80 object-cover rounded-sm">
            </div>
            <div class="text-center">
              <h3 class="text-xl font-bold text-gov-darkblue mb-2">Mr. Vikram Gaikwad</h3>
              <div class="text-gray-600 space-y-2">
                <p><strong>Designation:</strong> Mayor of Pimpri-Chinchwad</p>
                <p><strong>Political Party:</strong> Shivsena</p>
                <p><strong>Office Address:</strong> PCMC Head Office, Pimpri, Pune 411018</p>
                <p><strong>Contact:</strong> mayor@pcmcindia.gov.in</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Commissioner Section -->
        <div class="bg-white p-8 border border-gray-200 rounded-sm shadow-sm">
          <div class="flex flex-col items-center">
            <div class="mb-6 flex-shrink-0">
              <img src="/api/placeholder/250/300" alt="Commissioner of PCMC" class="w-64 h-80 object-cover rounded-sm">
            </div>
            <div class="text-center">
              <h3 class="text-xl font-bold text-gov-darkblue mb-2">Mr. Shekhar Singh (I.A.S.)</h3>
              <div class="text-gray-600 space-y-2">
                <p><strong>Education:</strong> B.Tech(Civil Engg), M.Tech(Structural Engg)</p>
                <p><strong>Designation:</strong> Municipal Commissioner</p>
                <p><strong>Office Address:</strong> 4th floor, PCMC main building, Mumbai-Pune highway, Pimpri, Pune 411018</p>
                <p><strong>Official Email:</strong> commissioner@pcmcindia.gov.in</p>
                <p><strong>Personal Email:</strong> s.singh@pcmcindia.gov.in</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const carouselImages = document.querySelectorAll('#carouselBackground .carousel-images img');
    let currentImageIndex = 0;

    function changeBackgroundImage() {
        // Hide all images
        carouselImages.forEach(img => img.style.opacity = '0');
        
        // Show current image
        carouselImages[currentImageIndex].style.opacity = '1';
        
        // Increment index or reset
        currentImageIndex = (currentImageIndex + 1) % carouselImages.length;
    }

    // Initial display
    carouselImages[0].style.opacity = '1';

    // Change image every 5 seconds
    setInterval(changeBackgroundImage, 5000);
});
</script>
<?php include 'includes/footer.php'; ?>