<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $name = $_POST['name'];
   $email = $_POST['email'];
   $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

   // Set default role for new signups
   $role = 'Pending'; // or 'Staff', depending on your workflow

   if (empty($name) || empty($email) || empty($_POST['password'])) {
       echo "All fields are required!";
   } else {
       $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("ssss", $name, $email, $password, $role);
       if ($stmt->execute()) {
           echo "Signup successful! Your account is pending approval from the owner.";
           header("Refresh: 3; url=login.php");
       } else {
           echo "Error: " . $stmt->error;
       }
       $stmt->close();
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Signup - Laundry Shop Management</title>
   <link rel="stylesheet" href="styles.css">
</head>
<body>
   <div class="container">
   <h1>W.I.Y Laundry Shop</h1>    
   <h2>Sign Up</h2>
       <form method="POST" action="signup.php">
           <label for="name">Name:</label>
           <input type="text" id="name" name="name" required><br>
          
           <label for="email">Email:</label>
           <input type="email" id="email" name="email" required><br>
          
           <label for="password">Password:</label>
           <input type="password" id="password" name="password" required><br>
          
           <button type="submit">Sign Up</button>
       </form>

       <p>Already have an account?</p>
       <a href="login.php" class="signup-btn">Login</a>
   </div>
</body>
</html>
