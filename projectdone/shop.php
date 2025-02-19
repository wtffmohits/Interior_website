<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shop</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
    <!--font-awesome.min.css-->
    <link rel="stylesheet" href="css/css/font-awesome.min.css">

<!--linear icon css-->
<link rel="stylesheet" href="css/css/linearicons.css">

<!--animate.css-->
<link rel="stylesheet" href="css/css/animate.css">

<!--owl.carousel.css-->
<link rel="stylesheet" href="css/css/owl.carousel.min.css">
<link rel="stylesheet" href="css/css/owl.theme.default.min.css">

<!--bootstrap.min.css-->
<link rel="stylesheet" href="css/css/bootstrap.min.css">

<!-- bootsnav -->
<link rel="stylesheet" href="css/css/bootsnav.css" >	

<!--style.css-->
<link rel="stylesheet" href="css/css/style.css">

<!--responsive.css-->
<link rel="stylesheet" href="css/css/responsive.css">


</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products">

   <h1 class="heading">Latest Products.</h1>

   <div class="box-container">

   <?php
     $select_products = $conn->prepare("SELECT * FROM `products`"); 
     $select_products->execute();
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
      <div class="name"><?= $fetch_product['name']; ?></div>
      <div class="flex">
         <div class="price"><span>Nrs.</span><?= $fetch_product['price']; ?><span>/-</span></div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">no products found!</p>';
   }
   ?>

   </div>

</section>



<section id="newsletter" class="newsletter">
        <div class="container">
            <div class="hm-footer-details">
                <div class="row">
                    <!-- Quick Links -->
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="hm-footer-widget">
                            <div class="hm-foot-menu">
                                <ul>
                                    <li><a href="#">About Us</a></li>
                                    <li><a href="#">Contact Us</a></li>
                                    <li><a href="#">News</a></li>
                                    <li><a href="#">Store</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Collections -->
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="hm-footer-widget">
                            <div class="hm-foot-title">
                                <h4>Collections</h4>
                            </div>
                            <div class="hm-foot-menu">
                                <ul>
                                    <li><a href="#">Wooden Chair</a></li>
                                    <li><a href="#">Royal Cloth Sofa</a></li>
                                    <li><a href="#">Accent Chair</a></li>
                                    <li><a href="#">Bed</a></li>
                                    <li><a href="#">Hanging Lamp</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- My Accounts -->
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="hm-footer-widget">
                            <div class="hm-foot-title">
                                <h4>My Accounts</h4>
                            </div>
                            <div class="hm-foot-menu">
                                <ul>
                                    <li><a href="#">My Account</a></li>
                                    <li><a href="#">Wishlist</a></li>
                                    <li><a href="#">Community</a></li>
                                    <li><a href="#">Order History</a></li>
                                    <li><a href="#">My Cart</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Newsletter -->
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="hm-footer-widget">
                            <div class="hm-foot-title">
                                <h4>Newsletter</h4>
                            </div>
                            <div class="hm-foot-para">
                                <p>Subscribe to get latest news, update and information.</p>
                            </div>
                            <div class="hm-foot-email">
                                <div class="foot-email-box">
                                    <input type="text" class="form-control" placeholder="Enter Email Here....">
                                </div>
                                <div class="foot-email-subscribe">
                                    <span><i class="fa fa-location-arrow"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>