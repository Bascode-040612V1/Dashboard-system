<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = '';
$nameError = $studentNumberError = $rfidError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $rfid = $_POST['rfid'];

// Validate RFID length (must be exactly 10 characters)
if (strlen($rfid) !== 10) {
    $rfidError = "RFID must be exactly 10 characters.";
}

    $checkRfidSql = "SELECT * FROM rfid_scans WHERE rfid_number = '$rfid'";
    $checkRfidResult = $conn->query($checkRfidSql);


    if ($checkRfidResult->num_rows > 0) {
        $rfidError = "The RFID number already exists. Please choose a different RFID number.";
    }

    if (empty($rfidError)) {

      

      $sql = "INSERT INTO rfid_scans (rfid_number) VALUES ('$rfid')";


        if ($conn->query($sql) === TRUE) {
            $successMessage = "Student registered successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Student</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: url('images/room.jpg') no-repeat center center fixed;
            background-size: cover;
            padding: 50px;
            display: flex;
            justify-content: center;
            position: relative;
        }

        .top-bar {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .top-bar img {
            width: 20px;
            height: 20px;
        }

        .home-btn {
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

        .home-btn:hover {
            background: #ecf0f1;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            width: 400px;
            margin: 0 auto;
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .input-field {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

      

        .image-upload {
            margin-top: 10px;
            text-align: center;
        }

        .image-upload label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #333;
        }

        .profile-image-preview {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            margin-bottom: 10px;
            object-fit: cover;
            border: 2px solid #ccc;
        }

        .error-message {
            color: red;
            font-size: 12px;
            margin-top: -10px;
            margin-bottom: 10px;
        }

        .success-message {
            text-align: center;
            margin-top: 20px;
            color: green;
        }

        #loadingOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        #loadingOverlay img {
            width: 150px;
            height: auto;
        }
        .success-box {
    background-color: #2ecc71; /* green */
    color: white;
    text-align: center;
    padding: 12px;
    margin-top: 15px;
    border-radius: 8px;
    font-weight: bold;
    opacity: 1;
    transition: opacity 1s ease-out;
}

    </style>
</head>
<body>

<!-- Top Bar with Home Button -->
<div class="top-bar">
    <a href="admin.php" class="home-btn" id="returnBtn">
        <img src="images/return.png" alt="Home Icon">
        Return
    </a>
</div>

<!-- Loading GIF Overlay -->
<div id="loadingOverlay">
    <div style="text-align: center; color: white; font-size: 20px;">
        <img id="loadingGif" src="images/load.gif" alt="Loading..." 
             style="display:block; margin:0 auto; width:150px; height:auto;">
        <p id="loadingText" style="margin-top: 15px;">
            Processing... On Your App please Refresh The RFID Number to finish Registration
        </p>
    </div>
</div>



<!-- Registration Form -->
<div class="container">
    <h2>Register Student</h2>
    <form id="registrationForm" action="register.php" method="POST">
        <p class="instruction"> Please scan the student's RFID to complete the registration.</p>

      <input 
    type="text" 
    name="rfid" 
    id="rfidInput" 
    class="input-field" 
    placeholder="Scan RFID" 
    value="<?php echo isset($rfid) ? htmlspecialchars($rfid) : ''; ?>" 
    required 
    autofocus
>

    <?php if (!empty($rfidError)): ?>
    <div class="error-message"><?php echo $rfidError; ?></div>
<?php endif; ?>
 
    </form>

<div id="successMessageBox" class="success-box" style="display:none;">
    ✅ Student successfully registered!
</div>

    <?php if (!empty($successMessage)): ?>
<script>
window.onload = function() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    const rfid = "<?php echo $rfid; ?>";
    const rfidInput = document.getElementById('rfidInput');

    loadingOverlay.style.display = 'flex';

    let attempts = 0;
    const maxAttempts = 30; // stop after ~30s

    function checkStudent() {
        fetch("check_student.php?rfid=" + rfid)
            .then(res => res.json())
            .then(data => {
                if (data.status === "found") {
    // Change text to success
   // Hide overlay
loadingOverlay.style.display = 'none';

// Show success message box
const successBox = document.getElementById('successMessageBox');
successBox.style.display = 'block';
successBox.innerText = "✅ Student successfully registered!";

// Fade out after 3 seconds
setTimeout(() => {
    successBox.style.opacity = '0';
    setTimeout(() => {
        successBox.style.display = 'none';
        successBox.style.opacity = '1'; // reset for next time
    }, 1000); // wait for fade-out animation to finish
}, 3000);

// Reset input for next scan
rfidInput.value = '';
rfidInput.focus();

}
 else {
                    attempts++;
                    if (attempts < maxAttempts) {
                        setTimeout(checkStudent, 2000);
                    } else {
                        loadingOverlay.style.display = 'none';
                        alert("Timeout: Student record not found. Please try again.");
                       rfidInput.value = '';   // Clear the input box
                       rfidInput.focus();      // 👈 Put the cursor back inside
                    }
                }
            })
            .catch(err => {
                console.error(err);
                setTimeout(checkStudent, 2000);
            });
    }

    checkStudent();
};

</script>
<?php endif; ?>



<script>
   


document.getElementById('rfidInput').addEventListener('input', function() {
    if (this.value.length === 10) {
        document.getElementById('registrationForm').submit();
    }
});




</script>

</body>
</html>
