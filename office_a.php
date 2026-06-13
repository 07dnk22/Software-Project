<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Get all taken seats in Office A
$takenSeats = [];
$takenQuery = "SELECT seat_number FROM seats WHERE office = 'A'";
$takenResult = $conn->query($takenQuery);
while ($row = $takenResult->fetch_assoc()) {
    $takenSeats[] = intval($row['seat_number']);
}

// Get current user's seat (if any)
$userSeat = null;
$userOffice = null;
$userQuery = $conn->prepare("SELECT office, seat_number FROM seats WHERE email = ?");
$userQuery->bind_param("s", $email);
$userQuery->execute();
$userResult = $userQuery->get_result();
if ($userRow = $userResult->fetch_assoc()) {
    $userSeat = intval($userRow['seat_number']);
    $userOffice = $userRow['office'];
}
$userQuery->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Office A</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, Helvetica, sans-serif;
    }

    body {
      min-height: 100vh;
      background-color: #f0f2f5;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .back-btn {
      position: absolute;
      top: 20px;
      left: 20px;
      padding: 10px 22px;
      background-color: #ffffff;
      color: #333;
      text-decoration: none;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .back-btn:hover {
      background-color: #333;
      color: #ffffff;
      transform: translateX(-3px);
    }

    h1 {
      margin-top: 80px;
      font-size: 36px;
      color: #333;
    }

    .grid-container {
      display: grid;
      grid-template-columns: repeat(3, 80px);
      gap: 16px;
      margin-top: 40px;
    }

    .seat-btn {
      width: 80px;
      height: 80px;
      background-color: #ffffff;
      color: #333;
      border: 2px solid #ccc;
      border-radius: 12px;
      font-size: 22px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .seat-btn:hover:not(.taken):not(.selected) {
      background-color: #e8ecf8;
      border-color: #4a6cf7;
      transform: translateY(-3px);
    }

    .seat-btn.selected {
      background-color: #4a6cf7;
      color: #ffffff;
      border-color: #2f4ad0;
    }

    .seat-btn.taken {
      background-color: #e0e0e0;
      color: #999;
      border-color: #d0d0d0;
      cursor: not-allowed;
    }

    .save-btn {
      position: fixed;
      bottom: 30px;
      right: 30px;
      padding: 14px 40px;
      background-color: #ccc;
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: 18px;
      font-weight: bold;
      cursor: not-allowed;
      transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .save-btn.enabled {
      background-color: #4a6cf7;
      cursor: pointer;
    }

    .save-btn.enabled:hover {
      background-color: #2f4ad0;
      transform: translateY(-3px);
    }

    #message {
      margin-top: 20px;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      display: none;
    }

    #message.success {
      display: block;
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    #message.error {
      display: block;
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
  </style>
</head>

<body>
  <a href="office_selection.html" class="back-btn">&larr; Back</a>

  <h1>Office A</h1>

  <div id="message"></div>

  <div class="grid-container" id="seatGrid"></div>

  <button class="save-btn" id="saveBtn" disabled>Save</button>

  <script>
    const takenSeats = <?php echo json_encode($takenSeats); ?>;
    const userSeat = <?php echo json_encode($userSeat); ?>;
    const userOffice = <?php echo json_encode($userOffice); ?>;

    const grid = document.getElementById('seatGrid');
    const saveBtn = document.getElementById('saveBtn');
    const messageEl = document.getElementById('message');
    let selectedSeat = null;

    // Create 12 buttons (1-12) in a 3-column grid
    for (let i = 1; i <= 12; i++) {
      const btn = document.createElement('button');
      btn.className = 'seat-btn';
      btn.textContent = i;
      btn.dataset.seat = i;

      // Mark as taken if someone else has this seat
      if (takenSeats.includes(i) && !(userOffice === 'A' && userSeat === i)) {
        btn.classList.add('taken');
        btn.disabled = true;
      }

      // Pre-select if this is the user's current seat
      if (userOffice === 'A' && userSeat === i) {
        btn.classList.add('selected');
        selectedSeat = i;
        saveBtn.disabled = false;
        saveBtn.classList.add('enabled');
      }

      btn.addEventListener('click', function () {
        if (this.disabled) return;

        // Deselect previous
        if (selectedSeat !== null) {
          document.querySelector(`.seat-btn[data-seat="${selectedSeat}"]`).classList.remove('selected');
        }

        // If clicking the same button, deselect it
        if (selectedSeat === i) {
          selectedSeat = null;
          saveBtn.disabled = true;
          saveBtn.classList.remove('enabled');
          return;
        }

        // Select new button
        selectedSeat = i;
        this.classList.add('selected');
        saveBtn.disabled = false;
        saveBtn.classList.add('enabled');
      });

      grid.appendChild(btn);
    }

    saveBtn.addEventListener('click', function () {
      if (this.disabled || selectedSeat === null) return;

      messageEl.style.display = 'none';
      messageEl.className = '';
      this.disabled = true;
      this.classList.remove('enabled');
      this.textContent = 'Saving...';

      const formData = new FormData();
      formData.append('office', 'A');
      formData.append('seat_number', selectedSeat);

      fetch('save_seat.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          messageEl.className = 'success';
          messageEl.textContent = data.message;
          messageEl.style.display = 'block';

          // If user moved from another office, now all buttons in this office should be refreshed
          // Remove taken from previously selected if it was a new selection
          document.querySelectorAll('.seat-btn.selected').forEach(b => b.classList.remove('selected'));
          document.querySelector(`.seat-btn[data-seat="${selectedSeat}"]`).classList.add('selected');
          
          saveBtn.disabled = false;
          saveBtn.classList.add('enabled');
          saveBtn.textContent = 'Save';
        } else {
          messageEl.className = 'error';
          messageEl.textContent = data.message;
          messageEl.style.display = 'block';
          
          // Re-enable save button
          saveBtn.disabled = false;
          saveBtn.classList.add('enabled');
          saveBtn.textContent = 'Save';
        }
      })
      .catch(err => {
        messageEl.className = 'error';
        messageEl.textContent = 'An error occurred. Please try again.';
        messageEl.style.display = 'block';
        
        saveBtn.disabled = false;
        saveBtn.classList.add('enabled');
        saveBtn.textContent = 'Save';
      });
    });
  </script>
</body>

</html>