<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:user_login.php');
    exit();
}

if (isset($_POST['delete'])) {
    $cart_id = $_POST['cart_id'];
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$cart_id]);
}

if (isset($_GET['delete_all'])) {
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_cart_item->execute([$user_id]);
    header('location:cart.php');
    exit();
}

if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $qty = $_POST['qty'];
    $qty = filter_var($qty, FILTER_SANITIZE_STRING);
    $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
    $update_qty->execute([$qty, $cart_id]);
    $message[] = 'cart quantity updated';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <style>
   /* cart page color display */
   .products.shopping-cart .color-name {
      margin: 10px 0;
      color: #333;
      font-size: 14px;
   }
   .color-badge {
      display: inline-block;
      padding: 3px 8px;
      margin: 0 5px 5px 0;
      border-radius: 4px;
      color: #fff;
      font-size: 12px;
   }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products shopping-cart">

   <h3 class="heading">Shopping cart</h3>

   <div class="box-container">

   <?php
      $grand_total = 0;
      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);
      if ($select_cart->rowCount() > 0) {
         while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
            // Fetch product details for better data accuracy
            $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
            $select_product->execute([$fetch_cart['pid']]);
            $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
            
            /* 
             * Determine which color data to display.
             * The cart may store a color value either as:
             *   - A JSON-encoded array of color IDs (from product table),
             *   - Or, simply a single color code (from the add-to-cart process).
             *
             * We attempt to decode JSON; if decoding fails, we treat the value as a single color.
             */
            if (isset($fetch_cart['colors']) && trim($fetch_cart['colors']) !== "") {
                $colors_decoded = json_decode($fetch_cart['colors'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($colors_decoded)) {
                    $colors = $colors_decoded;
                } else {
                    // Treat as a single color value if JSON decoding fails
                    $colors = [$fetch_cart['colors']];
                }
            } elseif (isset($fetch_product['colors']) && trim($fetch_product['colors']) !== "") {
                $colors_decoded = json_decode($fetch_product['colors'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($colors_decoded)) {
                    $colors = $colors_decoded;
                } else {
                    $colors = [$fetch_product['colors']];
                }
            } else {
                $colors = [];
            }
         ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="cart_id" value="<?= htmlspecialchars($fetch_cart['id']); ?>">
      <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_cart['pid']); ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_cart['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_cart['name']); ?></div>
      <!-- Display selected color name(s) -->
      <div class="color-name">
         Color:
         <?php 
         if (!empty($colors) && is_array($colors)):
            /* 
             * If the stored color value is a simple color code (non-numeric) then attempt to retrieve the color name from tbl_color.
             * Otherwise, assume they are color IDs and retrieve their color names accordingly.
             */
            if (count($colors) === 1 && !is_numeric($colors[0])) {
                // Query tbl_color using the color code
                $colorCode = $colors[0];
                $colorStmt = $conn->prepare("SELECT color_name, color_code FROM tbl_color WHERE color_code = ?");
                $colorStmt->execute([$colorCode]);
                if ($colorRow = $colorStmt->fetch(PDO::FETCH_ASSOC)) {
                    // Display the color name with its background color sample.
                    echo '<span class="color-badge" style="background-color:' . htmlspecialchars($colorRow['color_code']) . ';">' . htmlspecialchars($colorRow['color_name']) . '</span>';
                } else {
                    // If no record found, fallback to display the code
                    echo '<span class="color-badge" style="background-color:' . htmlspecialchars($colorCode) . ';">' . htmlspecialchars($colorCode) . '</span>';
                }
            } else {
                $placeholders = implode(',', array_fill(0, count($colors), '?'));
                $colorStmt = $conn->prepare("SELECT * FROM tbl_color WHERE color_id IN ($placeholders) ORDER BY color_name ASC");
                $colorStmt->execute($colors);
                $colorOutput = [];
                while ($color = $colorStmt->fetch(PDO::FETCH_ASSOC)) {
                    // Display the color name (instead of its code) with the sample background.
                    $colorOutput[] = '<span class="color-badge" style="background-color:' . htmlspecialchars($color['color_code']) . ';">' . htmlspecialchars($color['color_name']) . '</span>';
                }
                echo implode(' ', $colorOutput);
            }
         else:
            echo '<span>No colors available</span>';
         endif;
         ?>
      </div>
      <div class="flex">
         <div class="price">Nrs.<?= htmlspecialchars($fetch_cart['price']); ?>/-</div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="<?= htmlspecialchars($fetch_cart['quantity']); ?>">
         <button type="submit" class="fas fa-edit" name="update_qty"></button>
      </div>
      <div class="sub-total"> Sub Total : <span>Nrs.<?= $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?>/-</span> </div>
      <input type="submit" value="delete item" onclick="return confirm('delete this from cart?');" class="delete-btn" name="delete">
   </form>
   <?php
         $grand_total += $sub_total;
         }
      } else {
         echo '<p class="empty">your cart is empty</p>';
      }
   ?>
   </div>

   <div class="cart-total">
      <p>Grand Total : <span>Nrs.<?= $grand_total; ?>/-</span></p>
      <a href="shop.php" class="option-btn">Continue Shopping.</a>
      <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" onclick="return confirm('delete all from cart?');">Delete All Items ?</a>
      <a href="checkout.php" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>">Proceed to Checkout.</a>
   </div>

</section>

<!--newsletter start-->
<section id="newsletter"  class="newsletter">
   <div class="container">
      <div class="hm-footer-details">
         <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
               <div class="hm-footer-widget">
                  <div class="hm-foot-title">
                  </div>
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
            <div class="col-md-3 col-sm-6 col-xs-12">
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
            <div class="col-md-3 col-sm-6 col-xs-12">
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
            <div class="col-md-3 col-sm-6 col-xs-12">
               <div class="hm-footer-widget">
                  <div class="hm-foot-title">
                     <h4>newsletter</h4>
                  </div>
                  <div class="hm-foot-para">
                     <p>
                        Subscribe to get latest news, update and information.
                     </p>
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
<!--newsletter end-->

<?php include 'components/footer.php'; ?>

<!-- Additional CSS and JS files -->
<link rel="stylesheet" href="css/css/font-awesome.min.css">
<link rel="stylesheet" href="css/css/linearicons.css">
<link rel="stylesheet" href="css/css/animate.css">
<link rel="stylesheet" href="css/css/owl.carousel.min.css">
<link rel="stylesheet" href="css/css/owl.theme.default.min.css">
<link rel="stylesheet" href="css/css/bootstrap.min.css">
<link rel="stylesheet" href="css/css/bootsnav.css">
<link rel="stylesheet" href="css/css/style.css">
<link rel="stylesheet" href="css/css/responsive.css">

<script src="js/script.js"></script>

</body>
</html>
?>