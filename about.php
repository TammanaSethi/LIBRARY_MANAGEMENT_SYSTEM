<?php
include "header.php";
?>

<div class="row" id="bannerpage1">
                <div class="col-lg-6 ps-5 ms-5">
                    <h1 style="color:white">About Us</h1>
                </div>
              </div>
              
              <div class="container">
          <div class="row rounded p-5 mt-5" id="tail1">
            <div class="col-lg-6 " >
              <h1 id="m1">Welcome to Library</h1>
              
<div data-aos="flip-left">
              <p id="m2">A library is a space dedicated to the collection, preservation, and sharing of knowledge, offering a wide range of resources, from books and journals to digital content and multimedia. It serves as a center for learning, research, and intellectual growth, welcoming individuals of all ages and backgrounds. Libraries play an essential role in fostering literacy, supporting education, and providing equal access to information for everyone, regardless of their socioeconomic status.</p>
</div>
            </div>   
            
            <div class="col-lg-6 ">
            <div data-aos="zoom-out-up">
              <img src="images/library5.jpg" width="80%" height="350px;"/>
</div>
            </div>
           </div>
           </div>


           <div class="row bg-dark justify-content-space-evenly" id="red1">
            <div class="col-lg-3">
              
            <div class="circular-progress" data-inner-circle-color="lightgrey" data-percentage="98" data-progress-color="#F06292" data-bg-color="black">
  <div class="inner-circle"></div>
  <p class="percentage">98%</p>
</div>
                <h1 id="heading1">Hardwork</h1>
                </div>

                <div class="col-lg-3">
                <div class="circular-progress" data-inner-circle-color="lightgrey" data-percentage="90" data-progress-color="#F06292" data-bg-color="black">
  <div class="inner-circle"></div>
  <p class="percentage">90%</p>
</div>
                    <h1 id="heading1">Pure love</h1>
                    </div>

                    <div class="col-lg-3">
                    <div class="circular-progress" data-inner-circle-color="lightgrey" data-percentage="100" data-progress-color="#F06292" data-bg-color="black">
  <div class="inner-circle"></div>
  <p class="percentage">100%</p>
</div>
                        <h1 id="heading1">Smart-idea</h1>
                        </div>

                        <div class="col-lg-3">
                        <div class="circular-progress" data-inner-circle-color="lightgrey" data-percentage="95" data-progress-color="#F06292" data-bg-color="black">
  <div class="inner-circle"></div>
  <p class="percentage">95%</p>
</div>
                            <h1 id="heading1">Good Decision</h1>
          
            </div>
         </div>

         <div class="row" id="red1">
            <div class="col-lg-6 p-5">
            
            <div data-aos="fade-up">
                <h3>"We love to help all the children that have problems in the world. After 15 years we have many goals achieved."</h3>
</div>
            </div>

            <div class="col-lg-6 p-5 ">
            <div data-aos="fade-down">
                <h3>"We love to help all the children that have problems in the world. After 15 years we have many goals achieved."</h3>
</div>
            </div>
         </div>

         <div class="row " id="row1">
            <div class="col-lg-6">
            <div data-aos="zoom-out-down">
               <h1 style="color: white; ">Help us so we can help others</h1>
</div>
            </div>
            
         </div>

         <script>
   const circularProgress = document.querySelectorAll(".circular-progress");

Array.from(circularProgress).forEach((progressBar) => {
  const progressValue = progressBar.querySelector(".percentage");
  const innerCircle = progressBar.querySelector(".inner-circle");
  let startValue = 0,
    endValue = Number(progressBar.getAttribute("data-percentage")),
    speed = 50,
    progressColor = progressBar.getAttribute("data-progress-color");

  const progress = setInterval(() => {
    startValue++;
    progressValue.textContent = `${startValue}%`;
    progressValue.style.color = `${progressColor}`;

    innerCircle.style.backgroundColor = `${progressBar.getAttribute(
      "data-inner-circle-color"
    )}`;

    progressBar.style.background = `conic-gradient(${progressColor} ${
      startValue * 3.6
    }deg,${progressBar.getAttribute("data-bg-color")} 0deg)`;
    if (startValue === endValue) {
      clearInterval(progress);
    }
  }, speed);
});  
</script>      








         


          
         <?php
include "footer.php";
?>
        