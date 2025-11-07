<?php
session_start();
include 'db.php';

// Get room number from URL parameter
$selectedRoom = isset($_GET['room']) ? $_GET['room'] : '';

// Check if room exists and is available
if ($selectedRoom) {
    $room_check = $conn->prepare("SELECT * FROM rooms WHERE room_number = ? AND status = 'Available'");
    $room_check->bind_param("s", $selectedRoom);
    $room_check->execute();
    $room_result = $room_check->get_result();
    
    if ($room_result->num_rows == 0) {
        header("Location: rooms.php");
        exit();
    }
    
    // Get room details
    $room_info = $conn->prepare("SELECT * FROM rooms WHERE room_number = ?");
    $room_info->bind_param("s", $selectedRoom);
    $room_info->execute();
    $room_data = $room_info->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reserve Room | eBMS</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Reset and base styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: #333;
      background-color: #f8f9fa;
    }
    
    /* Main content area */
    .main-content {
      padding-top: 80px;
      min-height: calc(100vh - 160px);
      padding-bottom: 60px;
      transition: margin-left 0.3s ease;
    }
    
    /* Form container */
    .form-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 30px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    /* Form grid layout */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 25px;
    }
    
    .form-group.full-width {
      grid-column: 1 / -1;
    }
    
    /* Form elements styling */
    .form-group {
      display: flex;
      flex-direction: column;
    }
    
    .form-group label {
      margin-bottom: 8px;
      font-weight: 600;
      color: #495057;
    }
    
    .form-group label.required::after {
      content: " *";
      color: #dc3545;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
      padding: 12px 15px;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      font-size: 15px;
      transition: all 0.3s ease;
      background-color: #fff;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #4263eb;
      box-shadow: 0 0 0 3px rgba(66, 99, 235, 0.1);
    }
    
    .form-group textarea {
      resize: vertical;
      min-height: 100px;
    }
    
    .error {
      color: #dc3545;
      font-size: 13px;
      margin-top: 5px;
      min-height: 18px;
    }
    
    /* Section headers */
    .section-header {
      color: #4263eb;
      font-size: 18px;
      margin: 25px 0 15px 0;
      padding-bottom: 10px;
      border-bottom: 2px solid #4263eb;
      font-weight: 600;
    }
    
    /* Selected room banner */
    .selected-room-banner {
      background: linear-gradient(135deg, #4263eb 0%, #3b5bdb 100%);
      color: white;
      padding: 20px;
      margin-bottom: 25px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(66, 99, 235, 0.2);
      text-align: center;
    }
    
    .selected-room-banner h3 {
      margin: 0 0 8px 0;
      font-size: 20px;
      font-weight: 600;
    }
    
    .selected-room-banner p {
      margin: 0;
      font-size: 16px;
      opacity: 0.9;
    }
    
    /* Button styles */
    .btn-container {
      text-align: center;
      margin: 25px 0;
    }
    
    .btn {
      padding: 14px 32px;
      font-size: 16px;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    
    .btn-primary {
      background-color: #4263eb;
      color: white;
    }
    
    .btn-primary:hover {
      background-color: #3b5bdb;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(66, 99, 235, 0.3);
    }
    
    .btn-secondary {
      background-color: #6c757d;
      color: white;
    }
    
    .btn-secondary:hover {
      background-color: #5a6268;
      transform: translateY(-2px);
    }
    
    .btn-tertiary {
      background-color: #28a745;
      color: white;
    }
    
    .btn-tertiary:hover {
      background-color: #218838;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
    }
    
    /* Contract Modal Styles */
    .contract-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.7);
      z-index: 1000;
      overflow-y: auto;
      backdrop-filter: blur(5px);
    }
    
    .contract-content {
      position: relative;
      background-color: white;
      margin: 40px auto;
      padding: 40px;
      width: 85%;
      max-width: 950px;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      animation: modalSlideIn 0.3s ease-out;
    }
    
    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .close-contract {
      position: absolute;
      top: 20px;
      right: 20px;
      font-size: 28px;
      cursor: pointer;
      color: #6c757d;
      background: none;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }
    
    .close-contract:hover {
      color: #495057;
      background-color: #f8f9fa;
    }
    
    .contract-header {
      text-align: center;
      margin-bottom: 35px;
      padding-bottom: 25px;
      border-bottom: 3px solid #4263eb;
    }
    
    .contract-header h2 {
      color: #4263eb;
      margin-bottom: 10px;
      font-size: 28px;
    }
    
    .contract-body {
      margin-bottom: 35px;
      line-height: 1.7;
      font-size: 15px;
    }
    
    .contract-section {
      margin-bottom: 30px;
    }
    
    .contract-section h3 {
      color: #4263eb;
      margin-bottom: 15px;
      padding-bottom: 8px;
      border-bottom: 1px solid #e9ecef;
      font-size: 20px;
    }
    
    .contract-clause {
      margin-bottom: 18px;
      text-align: justify;
    }
    
    .contract-clause-number {
      font-weight: 700;
      color: #4263eb;
      margin-right: 5px;
    }
    
    .contract-clause ul {
      margin: 10px 0;
      padding-left: 25px;
    }
    
    .contract-clause li {
      margin-bottom: 8px;
    }
    
    .signature-section {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      margin-top: 50px;
    }
    
    .signature-area {
      text-align: center;
      padding: 25px;
      border-top: 2px solid #dee2e6;
    }
    
    .signature-line {
      margin-top: 70px;
      border-top: 1px solid #495057;
      width: 80%;
      margin-left: auto;
      margin-right: auto;
    }
    
    .terms-agreement {
      margin: 35px 0;
      padding: 20px;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-radius: 8px;
      border-left: 4px solid #4263eb;
    }
    
    /* Page header */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #e9ecef;
    }
    
    .page-header h2 {
      color: #343a40;
      font-size: 28px;
      font-weight: 700;
    }
    
    /* Room confirmation section */
    .room-confirmation {
      background: #f8f9fa;
      border: 2px solid #e9ecef;
      border-radius: 10px;
      padding: 20px;
      margin: 20px 0;
      text-align: center;
    }
    
    .room-confirmation h4 {
      color: #4263eb;
      margin-bottom: 10px;
      font-size: 18px;
    }
    
    .room-confirmation p {
      font-size: 16px;
      font-weight: 600;
      color: #495057;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
      .form-container {
        margin: 20px;
        padding: 25px;
      }
      
      .contract-content {
        width: 90%;
        padding: 30px;
      }
    }
    
    @media (max-width: 768px) {
      .main-content {
        padding-top: 70px;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .form-container {
        margin: 15px;
        padding: 20px;
      }
      
      .contract-content {
        width: 95%;
        margin: 20px auto;
        padding: 25px;
      }
      
      .signature-section {
        grid-template-columns: 1fr;
        gap: 30px;
      }
      
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .btn-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
      }
      
      .btn {
        width: 100%;
        margin: 0;
      }
    }
    
    @media (max-width: 480px) {
      .main-content {
        padding-top: 60px;
      }
      
      .form-container {
        margin: 10px;
        padding: 15px;
      }
      
      .contract-content {
        padding: 20px;
      }
      
      .contract-header h2 {
        font-size: 24px;
      }
      
      .page-header h2 {
        font-size: 24px;
      }
      
      .selected-room-banner {
        padding: 15px;
      }
      
      .selected-room-banner h3 {
        font-size: 18px;
      }
    }
    
    /* Print styles for contract */
    @media print {
      body * {
        visibility: hidden;
      }
      .contract-content, .contract-content * {
        visibility: visible;
      }
      .contract-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 20px;
        box-shadow: none;
      }
      .btn-container, .close-contract {
        display: none;
      }
    }
  </style>
</head>
<body>
  <?php include 'index-header.php'; ?>
  <?php include 'index-sidebar.php'; ?>

  <div class="main-content" id="mainContent">
    <div class="form-container">
      <?php if ($selectedRoom): ?>
      <div class="selected-room-banner">
        <h3>Your Chosen Room</h3>
        <p>Room <?php echo $room_data['room_number']; ?> - ₱<?php echo $room_data['monthly_rent']; ?>/month</p>
      </div>
      <?php endif; ?>
      
      <div class="page-header">
        <h2>Reserve Room</h2>
        <button type="button" class="btn btn-tertiary" onclick="viewContract()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14,2 14,8 20,8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10,9 9,9 8,9"></polyline>
          </svg>
          View Contract
        </button>
      </div>
      
      <!-- Room Confirmation Section -->
      <div class="room-confirmation">
        <h4>Room Confirmation</h4>
        <p>You are reserving: <strong>Room <?php echo $room_data['room_number']; ?></strong></p>
        <p>Monthly Rate: <strong>₱<?php echo $room_data['monthly_rent']; ?></strong></p>
      </div>
      
      <form id="reservationForm" action="process-reserve.php" method="POST" onsubmit="return validateForm('reservationForm')">
        <input type="hidden" name="room" value="<?php echo $room_data['room_number']; ?>">
        
        <div class="form-grid">
          <!-- Personal Information -->
          <div class="section-header full-width">Personal Information</div>
          
          <div class="form-group">
            <label class="required">First Name</label>
            <input type="text" name="fname" required placeholder="Enter your first name">
            <div class="error" id="fnameError"></div>
          </div>
          
          <div class="form-group">
            <label>Middle Name</label>
            <input type="text" name="mname" placeholder="Enter your middle name">
          </div>

          <div class="form-group">
            <label class="required">Last Name</label>
            <input type="text" name="lname" required placeholder="Enter your last name">
            <div class="error" id="lnameError"></div>
          </div>
          
          <div class="form-group">
            <label class="required">Email</label>
            <input type="email" name="email" required placeholder="your.email@example.com">
            <div class="error" id="emailError"></div>
          </div>

          <div class="form-group">
            <label class="required">Contact No.</label>
            <input type="text" name="contact" required pattern="[0-9+\-\s()]{10,}" placeholder="+63 912 345 6789">
            <div class="error" id="contactError"></div>
          </div>

          <div class="form-group">
            <label class="required">Age</label>
            <input type="number" name="age" min="18" max="120" required placeholder="18">
            <div class="error" id="ageError"></div>
          </div>
          
          <div class="form-group full-width">
            <label class="required">Address</label>
            <textarea name="address" rows="3" required placeholder="Enter your complete address"></textarea>
            <div class="error" id="addressError"></div>
          </div>

          <!-- Guardian Information -->
          <div class="section-header full-width">Guardian Information</div>

          <div class="form-group">
            <label class="required">Guardian Full Name</label>
            <input type="text" name="guardian_fullname" required placeholder="Enter guardian's full name">
            <div class="error" id="guardian_fullnameError"></div>
          </div>
          
          <div class="form-group">
            <label class="required">Relationship</label>
            <select name="guardian_relationship" required>
              <option value="">-- Select Relationship --</option>
              <option value="Parent">Parent</option>
              <option value="Sibling">Sibling</option>
              <option value="Spouse">Spouse</option>
              <option value="Relative">Relative</option>
              <option value="Friend">Friend</option>
              <option value="Other">Other</option>
            </select>
            <div class="error" id="guardian_relationshipError"></div>
          </div>

          <div class="form-group">
            <label class="required">Guardian Contact No.</label>
            <input type="text" name="guardian_contact" required pattern="[0-9+\-\s()]{10,}" placeholder="+63 912 345 6789">
            <div class="error" id="guardian_contactError"></div>
          </div>

          <div class="form-group">
            <label>Guardian Email (Optional)</label>
            <input type="email" name="guardian_email" placeholder="guardian.email@example.com">
            <div class="error" id="guardian_emailError"></div>
          </div>

          <div class="form-group full-width" style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="padding: 14px 40px; font-size: 16px;">
              Reserve Room
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Contract Modal -->
  <div id="contractModal" class="contract-modal">
    <div class="contract-content">
      <button class="close-contract" onclick="closeContract()">&times;</button>
      
      <div class="contract-header">
        <h2>BOARDING HOUSE RESERVATION CONTRACT</h2>
        <p>This agreement is made and entered into on <span id="currentDate"></span></p>
      </div>
      
      <div class="contract-body">
        <div class="contract-section">
          <h3>PARTIES</h3>
          <div class="contract-clause">
            This Reservation Contract (hereinafter referred to as the "Contract") is made and entered into by and between:
          </div>
          <div class="contract-clause">
            <strong>THE MANAGEMENT</strong> (hereinafter referred to as the "Lessor"), represented by its authorized representative, 
            and the person whose details are provided below (hereinafter referred to as the "Boarder").
          </div>
        </div>
        
        <div class="contract-section">
          <h3>RESERVATION DETAILS</h3>
          <div class="contract-clause">
            <strong>Selected Room:</strong><br>
            <span id="contractRoomInfo" style="font-weight: bold; color: #4263eb; font-size: 16px;"></span>
          </div>
        </div>
        
        <div class="contract-section">
          <h3>TERMS AND CONDITIONS</h3>
          
          <div class="contract-clause">
            <span class="contract-clause-number">1. ADVANCE PAYMENT:</span>
            The Boarder agrees to pay an advance payment equivalent to one (1) month's rent amounting to 
            <span id="advancePaymentAmount" style="font-weight: bold;"></span> upon signing this contract. This advance payment will be applied 
            to the first month of occupancy.
          </div>
          
          <div class="contract-clause">
            <span class="contract-clause-number">2. SECURITY DEPOSIT:</span>
            The Boarder agrees to pay a security deposit equivalent to one (1) month's rent amounting to 
            <span id="depositAmount" style="font-weight: bold;"></span> upon signing this contract. This deposit shall be held by the Lessor 
            as security for the faithful performance by the Boarder of all the terms and conditions of this Contract.
          </div>
          
          <div class="contract-clause">
            <span class="contract-clause-number">3. REFUND OF DEPOSIT:</span>
            The security deposit, less any deductions for damages, outstanding utilities, or other charges, 
            shall be refunded to the Boarder within thirty (30) days after the termination of this Contract 
            and the Boarder's vacating of the premises, provided that:
            <ul>
              <li>The room is returned in the same condition as when occupied, normal wear and tear excepted</li>
              <li>There are no outstanding utility bills or other charges</li>
              <li>All keys and access cards are returned</li>
              <li>The Boarder has provided proper notice of termination as per this Contract</li>
            </ul>
          </div>
          
          <div class="contract-clause">
            <span class="contract-clause-number">4. DAMAGES AND REPAIRS:</span>
            The Boarder shall be responsible for any damages to the room, furniture, fixtures, and appliances 
            beyond normal wear and tear. Any destruction or damage to property inside the room shall be shouldered 
            by the Boarder. The cost of repairs or replacement will be deducted from the security deposit. 
            If the cost exceeds the security deposit, the Boarder agrees to pay the difference within fifteen (15) days 
            of receiving notification from the Lessor.
          </div>
          
          <div class="contract-clause">
            <span class="contract-clause-number">5. RESERVATION PERIOD:</span>
            This reservation is valid for seven (7) days from the date of this Contract. If the Boarder fails to 
            occupy the room within this period without prior written notice to the Lessor, the reservation shall 
            be forfeited and the advance payment shall be retained by the Lessor as liquidated damages.
          </div>
          
          <div class="contract-clause">
            <span class="contract-clause-number">6. CANCELLATION:</span>
            In case of cancellation by the Boarder:
            <ul>
              <li>If cancellation is made within three (3) days of signing this Contract, 50% of the advance payment will be refunded</li>
              <li>If cancellation is made after three (3) days, the entire advance payment will be forfeited</li>
            </ul>
          </div>
          
          <div class="contract-clause">
            <span class="contract-clause-number">7. HOUSE RULES:</span>
            The Boarder agrees to abide by all house rules and regulations of the boarding house, which include 
            but are not limited to: quiet hours, visitor policies, cleanliness standards, and proper use of common areas.
          </div>
          
          <div class="contract-clause">
            <span class="contract-clause-number">8. TERMINATION:</span>
            This Contract may be terminated by either party with thirty (30) days written notice to the other party. 
            In case of violation of any terms of this Contract or house rules, the Lessor reserves the right to 
            terminate this Contract immediately.
          </div>
        </div>
        
        <div class="terms-agreement">
          <p><strong>ACKNOWLEDGEMENT:</strong> I have read and understood all the terms and conditions of this Reservation Contract and agree to be bound by them.</p>
        </div>
        
        <div class="signature-section">
          <div class="signature-area">
            <p><strong>BOARDER</strong></p>
            <div class="signature-line"></div>
            <p>Signature over Printed Name</p>
            <p>Date: ___________________</p>
          </div>
          
          <div class="signature-area">
            <p><strong>LESSOR/AUTHORIZED REPRESENTATIVE</strong></p>
            <div class="signature-line"></div>
            <p>Signature over Printed Name</p>
            <p>Date: ___________________</p>
          </div>
        </div>
      </div>
      
      <div class="btn-container">
        <button class="btn btn-primary" onclick="printContract()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
          </svg>
          Print Contract
        </button>
        <button class="btn btn-secondary" onclick="closeContract()">Close</button>
      </div>
    </div>
  </div>

  <?php include 'index-footer.php'; ?>
  <script src="script.js"></script>
  <script>
    // Set current date
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
    
    // Update contract details with room information
    function updateContractDetails() {
      const roomNumber = "<?php echo $room_data['room_number']; ?>";
      const monthlyRent = "<?php echo $room_data['monthly_rent']; ?>";
      
      if (roomNumber) {
        document.getElementById('contractRoomInfo').textContent = 
          `Room ${roomNumber} - ₱${monthlyRent}/month`;
        document.getElementById('advancePaymentAmount').textContent = `₱${monthlyRent}`;
        document.getElementById('depositAmount').textContent = `₱${monthlyRent}`;
      } else {
        document.getElementById('contractRoomInfo').textContent = 'No room selected';
        document.getElementById('advancePaymentAmount').textContent = '₱0.00';
        document.getElementById('depositAmount').textContent = '₱0.00';
      }
    }
    
    // Initialize contract details
    document.addEventListener('DOMContentLoaded', function() {
      updateContractDetails();
    });
    
    // View contract modal
    function viewContract() {
      updateContractDetails();
      document.getElementById('contractModal').style.display = 'block';
      document.body.style.overflow = 'hidden';
    }
    
    // Close contract modal
    function closeContract() {
      document.getElementById('contractModal').style.display = 'none';
      document.body.style.overflow = 'auto';
    }
    
    // Print contract
    function printContract() {
      window.print();
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('contractModal');
      if (event.target === modal) {
        closeContract();
      }
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeContract();
      }
    });
    
    // Additional JavaScript to ensure content is properly visible
    document.addEventListener('DOMContentLoaded', function() {
      // Scroll to top to ensure form is visible
      window.scrollTo(0, 0);
      
      // Adjust padding on resize
      function adjustMainContentPadding() {
        const header = document.querySelector('header');
        if (header) {
          const headerHeight = header.offsetHeight;
          document.querySelector('.main-content').style.paddingTop = (headerHeight + 20) + 'px';
        }
      }
      
      // Initial adjustment
      adjustMainContentPadding();
      
      // Adjust on window resize
      window.addEventListener('resize', adjustMainContentPadding);
    });
  </script>
</body>
</html>