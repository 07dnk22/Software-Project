<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatiable" content="IE=edge">
  <meta name="viewpoint" content="width=device-width, initial-scale=1.0">
  <title>Program Prototype Login Page</title>
  <link rel="stylesheet" href="style_Login.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

  <div class="container" id="Register" style="display: none;">
      <h1 class="form-title">Register</h1>
      <form method="post" action="register.php">
        <div class="input-box">
           <i class="fas fa-user"></i>
           <input type="text" name="firstName" id="firstName" placeholder="First Name" required>
        </div>
        <div class="input-box">
            <i class="fas fa-user"></i>
            <input type="text" name="lastName" id="lastName" placeholder="Last Name" required>
        </div>
        <div class="input-box">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" id="email" placeholder="Email" required>
        </div>
        <div class="input-box">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" id="password" placeholder="Password" required>
        </div>
       <input type="submit" class="btn" value="Sign Up" name="signUp">
      </form>
      <div class="login-link">
        <p>Already Have Account?</p> <button id="LoginBtn">Sign In</button>
      </div>
  </div>

  <div class="container" id="Login">
    <h1>Login</h1>
      <form method="post" action="register.php">
        <div class="input-box">
          <input type="email" name="email" placeholder="Email" required>
          <i class="bx bx-user"></i>
        </div>
        <div class="input-box">
          <input type="password" name="password" placeholder="Password" required>
          <i class="bx bx-lock-alt"></i>
        </div>

        <input type="submit" class="btn" value="Login" name="Login">
      </form>

      <div class="register-link">
        <p>Don't have an account?</p><button id="RegisterBtn">Register</button>
      </div>
  </div>

  <script src="script.js"></script>

</body>

</html>