<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
   <meta name="description" content="" />
   <meta name="author" content="" />
   <title>FlowNote - Your Everyday Note Sharing Platform</title>
   <!-- Favicon-->
   <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
   <!-- Bootstrap Icons-->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
   <!-- Google fonts-->
   <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet" />
   <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic" rel="stylesheet" type="text/css" />
   <!-- SimpleLightbox plugin CSS-->
   <link href="https://cdnjs.cloudflare.com/ajax/libs/SimpleLightbox/2.1.0/simpleLightbox.min.css" rel="stylesheet" />
   <!-- Core theme CSS (includes Bootstrap)-->
   <link href="css/styles.css" rel="stylesheet" />

   <style>
      /* Reserve space for fixed-top navbar */
      body {
         padding-top: 70px; 
      }
   </style>
</head>

<body id="page-top">
   <!-- Navigation-->
   <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3" id="mainNav">
      <div class="container">
         <a class="navbar-brand" href="./dashboard.php">
            <img src="assets/img/sample_logo.png" width="auto" height="80" alt="FlowNote Logo" />
         </a>
         <button class="navbar-toggler navbar-toggler-right" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
         <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto my-2 my-lg-0">
               <li class="nav-item">
                  <div class="dropdown">
                     <button class="btn my-custom-button dropdown-toggle text-light" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false">
                        Explore
                     </button>
                     <ul class="dropdown-menu custom-dropdown-menu" aria-labelledby="dropdownMenu2">
                        <li>
                           <form action="my_notes.php" method="get" style="margin:0;">
                              <button class="dropdown-item custom-dropdown-item" type="submit">My Notes</button>
                           </form>
                        </li>
                        <li>
                           <form action="shared_notes.php" method="get" style="margin:0;">
                              <button class="dropdown-item custom-dropdown-item" type="submit">Shared Notes</button>
                           </form>
                        </li>
                        <li>
                           <form action="upload.php" method="get" style="margin:0;">
                              <button class="dropdown-item custom-dropdown-item" type="submit">Upload</button>
                           </form>
                        </li>
                     </ul>
                  </div>
               </li>

               <li class="nav-item">
                  <div class="dropdown signout">
                     <button class="btn my-custom-button dropdown-toggle text-light" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php
                        include 'config.php';
                        if (!isset($_SESSION['user'])) {
                           header("Location: 401.html");
                           exit();
                        } else {
                           echo $_SESSION['username'];
                        }
                        ?>
                     </button>
                     <ul class="dropdown-menu custom-dropdown-menu" aria-labelledby="userDropdown">
                        <li>
                           <form method="post" style="margin:0;">
                              <button class="dropdown-item custom-dropdown-item" type="submit" name="signout">Sign Out</button>
                           </form>
                        </li>
                     </ul>
                  </div>
               </li>
            </ul>
         </div>
      </div>
   </nav>

   <!-- Bootstrap 5 JS Bundle - MUST be loaded for dropdowns to work -->
   <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> -->
   <!-- My custom scripts -->
   <!-- <script src="js/scripts.js"></script> -->
</body>
</html>