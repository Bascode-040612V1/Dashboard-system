<?php
$attendance_records = [];
$absentees = [];
$error_message = '';
$success_message = '';
$selected_date = '';
$today = date('Y-m-d');
$formatted_date_header = '';

$conn = new mysqli("localhost", "root", "", "rfid_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch available saved dates
$date_query = "SELECT DISTINCT saved_date FROM saved_attendance ORDER BY saved_date DESC";
$date_result = $conn->query($date_query);
$dates = [];
while ($row = $date_result->fetch_assoc()) {
    $dates[] = $row['saved_date'];
}

// Save today's attendance
if (isset($_POST['save_attendance'])) {
    $fetch_query = "SELECT a.*, s.name, s.student_number, s.image
                    FROM attendance a
                    JOIN students s ON a.student_id = s.id
                    WHERE DATE(a.time_in) = '$today'";
    $fetch_result = $conn->query($fetch_query);
    
    while ($row = $fetch_result->fetch_assoc()) {
        $student_id = $row['student_id'];
        $name = $conn->real_escape_string($row['name']);
        $student_number = $conn->real_escape_string($row['student_number']);
        $image = $conn->real_escape_string($row['image']);
        $saved_time_in = $row['time_in'];
        $saved_time_out = $row['time_out'];
        
        $insert = "INSERT INTO saved_attendance (student_id, name, student_number, image, saved_time_in, saved_time_out, saved_date)
                   VALUES ('$student_id', '$name', '$student_number', '$image', '$saved_time_in', '$saved_time_out', '$today')";
        $conn->query($insert);
    }

    $conn->query("DELETE FROM attendance WHERE DATE(time_in) = '$today'");
    $success_message = "Today's attendance saved and cleared successfully!";
}

// Get attendance records and absentees
if (isset($_POST['selected_date']) && $_POST['selected_date'] != '') {
    $selected_date = $_POST['selected_date'];
    $formatted_date_header = date('F d, Y', strtotime($selected_date)); // Format for the header

    // Fetch saved attendance
    $sql = "SELECT * FROM saved_attendance WHERE saved_date = '$selected_date' ORDER BY saved_time_in DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $attendance_records[] = $row;
    }

    // Fetch absentees for saved date
    $absent_query = "
        SELECT * FROM students 
        WHERE id NOT IN (
            SELECT student_id FROM saved_attendance WHERE saved_date = '$selected_date'
        )
    ";
} else {
    // Fetch today's attendance
    $sql = "SELECT a.*, s.name, s.student_number, s.image
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            WHERE DATE(a.time_in) = '$today'
            ORDER BY a.time_in DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $attendance_records[] = $row;
    }

    // Fetch absentees for today
    $absent_query = "
        SELECT * FROM students 
        WHERE id NOT IN (
            SELECT student_id FROM attendance WHERE DATE(time_in) = '$today'
        )
    ";
    $formatted_date_header = date('F d, Y', strtotime($today)); // Default header text for today
}

$absent_result = $conn->query($absent_query);
while ($row = $absent_result->fetch_assoc()) {
    $absentees[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Attendance Records</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: url('images/room.jpg') no-repeat center center fixed;
      background-size: cover;
      padding: 20px;
    }

    .header {
      background: rgba(52, 152, 219, 0.95);
      color: white;
      padding: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 10px;
      margin-bottom: 30px;
      position: relative;
    }

    .header h1 {
      font-size: 28px;
      margin: 0;
    }

    .return-btn {
      position: absolute;
      left: 20px;
      top: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
      background: white;
      color: #3498db;
      padding: 8px 12px;
      border-radius: 20px;
      font-weight: bold;
      text-decoration: none;
      transition: background 0.3s;
    }

    .return-btn img {
      width: 20px;
      height: 20px;
    }

    .return-btn:hover {
      background: #ecf0f1;
    }

    .attendance-container {
      background: rgba(255, 255, 255, 0.85);
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .select-date-form {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      align-items: center;
    }

    .select-date-form select {
      padding: 10px;
      font-size: 16px;
      border-radius: 5px;
      width: 200px;
    }

    .attendance-table, .absentees-table {
      width: 100%;
      border-collapse: collapse;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th, td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #3498db;
      color: white;
    }

    .notification {
      margin-top: 15px;
      color: green;
      font-weight: bold;
    }

    .save-button-container {
      text-align: right;
      margin-top: 20px;
    }

    button {
      padding: 10px 15px;
      font-size: 16px;
      cursor: pointer;
      border-radius: 5px;
      background-color: #3498db;
      color: white;
      border: none;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #2980b9;
    }

    .absentees-table td {
      color: #e74c3c;
    }

    .absentees-header {
      font-size: 20px;
      font-weight: bold;
      color: #e74c3c;
      margin-top: 20px;
    }

    /* Hide absent students table if no attendance records exist for selected date */
    .absentees-table-container {
      display: <?php echo empty($attendance_records) ? 'none' : 'block'; ?>;
    }
  </style>
</head>
<body>

<div class="header">
  <a href="admin.php" class="return-btn">
    <img src="images/return.png" alt="Return Icon">
    Return
  </a>
  <h1>Attendance</h1>
</div>

<div class="attendance-container">
  <div class="select-date-form">
    <label for="attendance_date">Select Attendance Date:</label>
    <form method="POST">
      <select name="selected_date" id="attendance_date" onchange="this.form.submit()">
        <option value="">-- Select a Date --</option>
        <?php foreach ($dates as $date): ?>
          <option value="<?php echo $date; ?>" <?php echo ($selected_date == $date) ? 'selected' : ''; ?>>
            <?php echo date('F d, Y', strtotime($date)); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <!-- Attendance Table -->
  <h2 id="attendance-header">Attendance for <?php echo $formatted_date_header; ?></h2>

  <?php if (!empty($attendance_records)): ?>
    <table class="attendance-table">
      <thead>
        <tr>
          <th>Picture</th>
          <th>Name</th>
          <th>Student Number</th>
          <th>Time In</th>
          <th>Time Out</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($attendance_records as $row): ?>
          <tr>
            <td><img src="<?php echo $row['image'] ?? 'assets/default-profile.png'; ?>" width="50" height="50" style="border-radius: 50%;"></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['student_number']; ?></td>
            <td><?php echo date('H:i:s', strtotime($row['saved_time_in'] ?? $row['time_in'])); ?></td>
            <td><?php echo date('H:i:s', strtotime($row['saved_time_out'] ?? $row['time_out'])) ?: 'Still in'; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No attendance records for the selected date.</p>
  <?php endif; ?>

  <?php if (empty($selected_date)): ?>
    <div class="save-button-container">
      <form method="POST" onsubmit="return confirm('Are you sure you want to save and clear today\'s attendance?');">
        <button type="submit" name="save_attendance">Save Attendance</button>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($success_message): ?>
    <div class="notification"><?php echo $success_message; ?></div>
  <?php elseif ($error_message): ?>
    <div class="notification"><?php echo $error_message; ?></div>
  <?php endif; ?>

  <!-- Absent Students -->
  <div class="absentees-table-container">
    <?php if (!empty($absentees)): ?>
      <div class="absentees-header">Absent Students for <?php echo $formatted_date_header; ?></div>
      <table class="absentees-table">
        <thead>
          <tr>
            <th>Picture</th>
            <th>Name</th>
            <th>Student Number</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($absentees as $row): ?>
            <tr>
              <td><img src="<?php echo $row['image'] ?? 'assets/default-profile.png'; ?>" width="50" height="50" style="border-radius: 50%;"></td>
              <td><?php echo $row['name']; ?></td>
              <td><?php echo $row['student_number']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No absentee records for the selected date.</p>
    <?php endif; ?>
  </div>
</div>

<script>
  document.getElementById('attendance_date').addEventListener('change', function() {
    var selectedDate = this.value;
    var formattedDate = new Date(selectedDate);
    var dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
    var formattedDateString = formattedDate.toLocaleDateString(undefined, dateOptions);
    document.getElementById('attendance-header').innerHTML = 'Attendance for ' + formattedDateString;
  });
</script>

</body>
</html>
