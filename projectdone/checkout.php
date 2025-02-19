<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:user_login.php');
   exit;
}

if (isset($_POST['order'])) {

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $address = 'flat no. ' . $_POST['flat'] . ', ' . $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['state'] . ', ' . $_POST['country'] . ' - ' . $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if ($check_cart->rowCount() > 0) {
      // Prepare to collect all colors values from cart items using the correct field 'colors'
      $all_colors = [];

      // Build the order summary text from cart items
      $cart_items = [];
      while ($fetch_cart = $check_cart->fetch(PDO::FETCH_ASSOC)) {
         // Append each product's info including colors into summary
         $cart_items[] = $fetch_cart['name'] . ' (Color: ' . $fetch_cart['colors'] . ' - ' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')';
         // Collect the colors for storing separately
         $all_colors[] = $fetch_cart['colors'];
      }
      $total_products = implode(' - ', $cart_items);
      // Create a comma-separated string for all colors
      $all_colors_str = implode(', ', $all_colors);

      // Inserting order with the summary of products and colors details stored separately.
      // IMPORTANT: Ensure the orders table has a column named "colors"
      $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, colors, total_price) VALUES (?,?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $all_colors_str, $total_price]);

      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);

      $message[] = 'order placed successfully!';
   } else {
      $message[] = 'your cart is empty';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">
   <form action="" method="POST">
      <h3>Your Orders</h3>
      <div class="display-orders">
         <?php
         $grand_total = 0;
         $cart_items = [];
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if ($select_cart->rowCount() > 0) {
            while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
               // Including the colors value in the order summary (using the correct field name "colors")
               $cart_items[] = $fetch_cart['name'] . ' (Color: ' . $fetch_cart['colors'] . ' - ' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ') - ';
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
            }
            $total_products = implode($cart_items);
         } else {
            echo '<p class="empty">your cart is empty!</p>';
         }
         ?>
         <input type="hidden" name="total_products" value="<?= isset($total_products) ? $total_products : ''; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
         <div class="grand-total">Grand Total : <span>Nrs.<?= $grand_total; ?>/-</span></div>
      </div>

      <h3>place your orders</h3>
      <div class="flex">
         <div class="inputBox">
            <span>Tapaiko subh nam :</span>
            <input type="text" name="name" placeholder="enter your name" class="box" maxlength="20" required>
         </div>
         <div class="inputBox">
            <span>Your Number :</span>
            <input type="number" name="number" placeholder="enter your number" class="box" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;" required>
         </div>
         <div class="inputBox">
            <span>Your Email :</span>
            <input type="email" name="email" placeholder="enter your email" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>kasari halnuhunx paisa? :</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Cash On Delivery</option>
               <option value="credit card">Credit Card</option>
               <option value="paytm">eSewa</option>
               <option value="paypal">Khalti</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address line 01 :</span>
            <input type="text" name="flat" placeholder="e.g. Flat number" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Address line 02 :</span>
            <input type="text" name="street" placeholder="Street name" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>City :</span>
            <input type="text" name="city" placeholder="Kathmandu" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Province:</span>
            <input type="text" name="state" placeholder="Bagmati" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Country :</span>
            <input type="text" name="country" placeholder="Nepal" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>ZIP CODE :</span>
            <input type="number" min="0" name="pin_code" placeholder="e.g. 56400" max="999999" onkeypress="if(this.value.length == 6) return false;" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" value="place order">
   </form>
</section>

<!--newsletter strat -->
<section id="newsletter" class="newsletter">
   <div class="container">
      <div class="hm-footer-details">
         <div class="row">
            <div class=" col-md-3 col-sm-6 col-xs-12">
               <div class="hm-footer-widget">
                  <div class="hm-foot-title"></div>
                  <div class="hm-foot-menu">
                     <ul>
                        <li><a href="#">about us</a></li>
                        <li><a href="#">contact us</a></li>
                        <li><a href="#">news</a></li>
                        <li><a href="#">store</a></li>
                     </ul>
                  </div>
               </div>
            </div>
            <div class=" col-md-3 col-sm-6 col-xs-12">
               <div class="hm-footer-widget">
                  <div class="hm-foot-title">
                     <h4>collections</h4>
                  </div>
                  <div class="hm-foot-menu">
                     <ul>
                        <li><a href="#">wooden chair</a></li>
                        <li><a href="#">royal cloth sofa</a></li>
                        <li><a href="#">accent chair</a></li>
                        <li><a href="#">bed</a></li>
                        <li><a href="#">hanging lamp</a></li>
                     </ul>
                  </div>
               </div>
            </div>
            <div class=" col-md-3 col-sm-6 col-xs-12">
               <div class="hm-footer-widget">
                  <div class="hm-foot-title">
                     <h4>my accounts</h4>
                  </div>
                  <div class="hm-foot-menu">
                     <ul>
                        <li><a href="#">my account</a></li>
                        <li><a href="#">wishlist</a></li>
                        <li><a href="#">Community</a></li>
                        <li><a href="#">order history</a></li>
                        <li><a href="#">my cart</a></li>
                     </ul>
                  </div>
               </div>
            </div>
            <div class=" col-md-3 col-sm-6  col-xs-12">
               <div class="hm-footer-widget">
                  <div class="hm-foot-title">
                     <h4>newsletter</h4>
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
<!--newsletter end -->

<?php include 'components/footer.php'; ?>

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
<link rel="stylesheet" href="css/css/bootsnav.css">   

<!--style.css-->
<link rel="stylesheet" href="css/css/style.css">

<!--responsive.css-->
<link rel="stylesheet" href="css/css/responsive.css">

<script src="js/script.js"></script>
</body>
</html>
?>