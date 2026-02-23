<?php
include "header.php";
?>

<div class="row" id="bannerpage">
                <div class="col-lg-6 ps-5 ms-5">
                    <h1 style="color:white">Contact</h1>
                </div>
              </div>
              <div class="container">
              <div class="row p-5 ">
                <div class="col-lg-5 tail2 justify-content-end">
                <div data-aos="flip-up">
                    <h2>Get in touch with us</h2>
                    <p>
                      If you have any questions, need assistance, or would like to learn more about our services, please don't hesitate to get in touch with us. You can reach us by filling out the contact form on our website, calling us at [7986769023] or emailing us at [donation@gmail.com]. Additionally, follow us on our social media channels for the latest updates and news.</p>
                      <br/>
                      <p> <i class="fa-solid fa-phone-volume" style="color:  #F06292;"></i>&nbsp;&nbsp;79867-69023</p>
                
                      <p><i class="fa-sharp fa-solid fa-envelope"style="color:  #F06292" ></i>&nbsp;&nbsp;donation@gmail.com</p>
                      <p><i class="fa-solid fa-location-dot"  style="color: #F06292"></i>&nbsp;&nbsp;Gidderbaha</p>
                        </br>
</div>
                </div>
                <div class="col-lg-7 tail1 pt-5">


<?php
if(isset($_POST['btncontact'])) 
{
  $name=mysqli_real_escape_string($con,$_POST['name']);
  $email=mysqli_real_escape_string($con,$_POST['email']);
  $message=mysqli_real_escape_string($con,$_POST['message']);
  $query="insert into contact(name,email,message,curdate) values('$name','$email','$message',now())";
  if(mysqli_query($con,$query))
  {
    echo "<script>alert('Your message is submitted')</script>";
  }
  else{
    echo mysqli_error($con);
  }
}     
?>          
                 <form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">

                    <input type="text" placeholder="Name"  class="form-control" name="name"/>
                    <br/>
                    
                    <input type="email" placeholder="Email"  class="form-control" name="email"/>
                    <br/>
                    <textarea class="form-control" placeholder="Message" name="message" rows="12" cols="6"></textarea>
                    <br/>
                  
                 
                 <button class="btn10" type="submit" name="btncontact">Contact us</button>
                </form>
                </div>
             

              </div>
              </div>

              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d27585.119269701743!2d74.63737938340195!3d30.204554039940486!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39173ced44f68beb%3A0xcab7967a8d3dcef1!2sGidderbaha%2C%20Punjab%20152101!5e0!3m2!1sen!2sin!4v1718698128984!5m2!1sen!2sin" width="100%" height="550" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

              <?php
include "footer.php";
?>