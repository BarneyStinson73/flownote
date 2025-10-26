<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/styles.css">
    <title>Login</title>
</head>
<body>
    <section class="vh-100">
    <div class="h-custom">
      <div class="row d-flex align-items-center">
        <div class="col-md-9 col-lg-6 col-xl-5">
          <img src="./assets/img/login_img.jpg"
            class="img-fluid vh-80" alt="Sample image">
        </div>
        <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
            <?php
            include 'config.php';

            session_start();

            if ($_SERVER['REQUEST_METHOD'] === 'POST' and isset($_POST['login'])) {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                // Validate and authenticate user
                if (empty($email) || empty($password)) {
                    echo '<p class="text-danger">Please fill in all fields.</p>';
                } else {
                    // Check credentials (this is just a placeholder, implement your own logic)
                    $stmt = $conn->prepare("SELECT id,name,email,password,is_verified FROM users WHERE email = ?");
                    $stmt->bind_param("s", $email);   // "s" means string
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();

                    if ($user && password_verify($password, $user['password']) && $user['is_verified'] == 1) {
                        $_SESSION['user'] = $email;
                        $_SESSION['username'] = $user['name'];
                        $_SESSION['user_id'] = $user['id'];
                        header('Location: dashboard.php');
                        exit;
                    } elseif ($user && password_verify($password, $user['password']) && $user['is_verified'] == 0) {
                        echo '<p class="text-danger">Please verify your email address.</p>';
                    } else {
                        echo '<p class="text-danger">Invalid email or password.</p>';
                    }
                }
            }
            ?>
          <form method="post" action="#login">
            <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
              <p class="lead fw-normal mb-0 me-3">Sign in to <a href="./index.php"
                  class="link-danger text-decoration-none">FlowNote</a></p>
                <!-- <a href="./dashboard.php">
                <img src ="./assets/img/sample_logo.png"  style="z-index: -1; width: 50px; height: auto; top: 0; left: 0;" alt="Wave Image"> -->
              </p>
            </div>
            <br>

            <!-- Email input -->
            <div data-mdb-input-init class="form-outline mb-4">
              <input type="email" id="form3Example3" class="form-control form-control-lg"
                placeholder="Enter a valid email address" name="email" />
              <label class="form-label" for="form3Example3">Email address</label>
            </div>

            <!-- Password input -->
            <div data-mdb-input-init class="form-outline mb-3">
              <input type="password" id="form3Example4" class="form-control form-control-lg"
                placeholder="Enter password" name="password" />
              <label class="form-label" for="form3Example4">Password</label>
            </div>

            <!-- <div class="d-flex justify-content-between align-items-center">
              
              <div class="form-check mb-0">
                <input class="form-check-input me-2" type="checkbox" value="" id="form2Example3" />
                <label class="form-check-label" for="form2Example3">
                  Remember me
                </label>
              </div>
            </div> -->

            <div class="text-center text-lg-start mt-4 pt-2">
              <button  type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                style="padding-left: 2.5rem; padding-right: 2.5rem;" name="login">Login</button>
              <p class="small fw-bold mt-2 pt-1 mb-0">Don't have an account? <a href="./index.php"
                  class="link-danger">Register from Homepage</a></p>
            </div>

          </form>
        </div>
      </div>
    </div>
</section>
</body>
</html>