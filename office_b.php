<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Get all taken seats in Office B
$takenSeats = [];
$takenQuery = "SELECT seat_number FROM seats WHERE office = 'B'";
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
  <title>Office B</title>
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

    .tables-container {
      display: flex;
      gap: 60px;
      margin-top: 40px;
    }

    .table-group {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .table-label {
      font-size: 20px;
      font-weight: bold;
      color: #555;
      margin-bottom: 16px;
    }

    .grid-container {
      display: grid;
      grid-template-columns: repeat(3, 80px);
      gap: 16px;
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

  <h1>Office B</h1>

  <div id="message"></div>

  <div class="tables-container">
    <div class="table-group">
      <div class="table-label">Seats 1–12</div>
      <div class="grid-container" id="leftGrid"></div>
    </div>
    <div class="table-group">
      <div class="table-label">Seats 13–24</div>
      <div class="grid-container" id="rightGrid"></div>
    </div>
  </div>

  <button class="save-btn" id="saveBtn" disabled>Save</button>

  <script>
    const takenSeats = <?php echo json_encode($takenSeats); ?>;
    const userSeat = <?php echo json_encode($userSeat); ?>;
    const userOffice = <?php echo json_encode($userOffice); ?>;

    const saveBtn = document.getElementById('saveBtn');
    const messageEl = document.getElementById('message');
    let selectedSeat = null;

    function createSeatButton(seatNumber) {
      const btn = document.createElement('button');
      btn.className = 'seat-btn';
      btn.textContent = seatNumber;
      btn.dataset.seat = seatNumber;

      // Mark as taken if someone else has this seat
      if (takenSeats.includes(seatNumber) && !(userOffice === 'B' && userSeat === seatNumber)) {
        btn.classList.add('taken');
        btn.disabled = true;
      }

      // Pre-select if this is the user's current seat
      if (userOffice === 'B' && userSeat === seatNumber) {
        btn.classList.add('selected');
        selectedSeat = seatNumber;
        saveBtn.disabled = false;
        saveBtn.classList.add('enabled');
      }

      btn.addEventListener('click', function () {
        if (this.disabled) return;

        // Deselect previous
        if (selectedSeat !== null) {
          const prev = document.querySelector(`.seat-btn[data-seat="${selectedSeat}"]`);
          if (prev) prev.classList.remove('selected');
        }

        // If clicking the same button, deselect it
        if (selectedSeat === seatNumber) {
          selectedSeat = null;
          saveBtn.disabled = true;
          saveBtn.classList.remove('enabled');
          return;
        }

        // Select new button
        selectedSeat = seatNumber;
        this.classList.add('selected');
        saveBtn.disabled = false;
        saveBtn.classList.add('enabled');
      });

      return btn;
    }

    const leftGrid = document.getElementById('leftGrid');
    const rightGrid = document.getElementById('rightGrid');

    // Create buttons 1-12 in the left grid
    for (let i = 1; i <= 12; i++) {
      leftGrid.appendChild(createSeatButton(i));
    }

    // Create buttons 13-24 in the right grid
    for (let i = 13; i <= 24; i++) {
      rightGrid.appendChild(createSeatButton(i));
    }

    saveBtn.addEventListener('click', function () {
      if (this.disabled || selectedSeat === null) return;

      messageEl.style.display = 'none';
      messageEl.className = '';
      this.disabled = true;
      this.classList.remove('enabled');
      this.textContent = 'Saving...';

      const formData = new FormData();
      formData.append('office', 'B');
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

          document.querySelectorAll('.seat-btn.selected').forEach(b => b.classList.remove('selected'));
          document.querySelector(`.seat-btn[data-seat="${selectedSeat}"]`).classList.add('selected');
          
          saveBtn.disabled = false;
          saveBtn.classList.add('enabled');
          saveBtn.textContent = 'Save';
        } else {
          messageEl.className = 'error';
          messageEl.textContent = data.message;
          messageEl.style.display = 'block';
          
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