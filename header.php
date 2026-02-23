
<?php
include "config.php";
?>
<html>
    <head>
        <title>library management system</title>
        
          <link rel="icon" type="image/x-icon" href="images/logo.png">

        <link rel="stylesheet" href="css/bootstrap.min.css"/>
        <link rel="stylesheet" href="css/bootstrap-grid.min.css"/>
        <link rel="stylesheet" href="stylesheet.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />






        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

        <srcipt src="js/bootstrap.bundle.min.js"></srcipt>
          <srcipt src="js/bootstrap.min.js'"></srcipt>

          <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
          <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
          <script>
          
        
        
$(document).ready(function() {

$('.counter').each(function () {
$(this).prop('Counter',0).animate({
Counter: $(this).text()
}, {
duration: 4000,
easing: 'swing',
step: function (now) {
    $(this).text(Math.ceil(now));
}
});
});

});  




  AOS.init();
</script>

        
    </head>
    <body>
        <div class="container-fluid">
           <div class="row top p-3 flex-wrap justify-content-center justify-content-lg-between align-items-lg-center" >
            
            <div class="col-lg-3 top1 ">
              <p>MAIL:abc@gmail.com</p>
            </div>
            <div class="col-lg-6 top2 ">
              <p>PHONE:79867-69023</p>
            </div>
            
           </div>
           </div>
           
       
        
            
          <div class="row bg-white text-dark">
            <div class="col-lg-12 p-3">
            <div data-aos="flip-up">
           
            <nav class="navbar navbar-expand-lg navbar-dark">
              <div class="container-fluid">
                <img src="images/logo.png"  height="35%" width="56px"/>&nbsp;&nbsp;
              <h1 style="color:#F06292">Library Management System</h1>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNavAltMarkup">
                  <div class="navbar-nav">
                    <a class="nav-link active" aria-current="page" href="index.php" style="color: #F06292; font-weight:500;">Home</a>
                    <a class="nav-link " href="about.php" style="color:black; font-weight:500;">About us</a>
                   
                   
                    
                    <a class="nav-link " href="contact.php" style="color:black; font-weight:500;"  >Contact</a>
                    <a class="nav-link " href="register.php" style="color:black; font-weight:500;">Register</a>
                    

                   
                  </div>
                </div>
              </div>
            </nav>
</div>
           </div>
          </div>