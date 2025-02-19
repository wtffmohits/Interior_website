<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
   exit;
};

if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);
   // Get selected colors
   $colors = isset($_POST['colors']) ? $_POST['colors'] : [];

   $image_01 = $_FILES['image_01']['name'];
   $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/'.$image_01;

   $image_02 = $_FILES['image_02']['name'];
   $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
   $image_size_02 = $_FILES['image_02']['size'];
   $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
   $image_folder_02 = '../uploaded_img/'.$image_02;

   $image_03 = $_FILES['image_03']['name'];
   $image_03 = filter_var($image_03, FILTER_SANITIZE_STRING);
   $image_size_03 = $_FILES['image_03']['size'];
   $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
   $image_folder_03 = '../uploaded_img/'.$image_03;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exist!';
   } else {
      try {
         // Begin transaction
         $conn->beginTransaction();
         
         // Insert product with colors (store colors as JSON)
         $insert_products = $conn->prepare("INSERT INTO `products`(name, details, price, image_01, image_02, image_03, colors) VALUES(?,?,?,?,?,?,?)");
         $insert_products->execute([$name, $details, $price, $image_01, $image_02, $image_03, json_encode($colors)]);
         
         // Get the last inserted product ID
         $id = $conn->lastInsertId();
         
         if($id === null){
             throw new Exception('Failed to get product ID after insertion');
         }

         // Handle image uploads
         if($image_size_01 > 2000000 || $image_size_02 > 2000000 || $image_size_03 > 2000000){
             throw new Exception('image size is too large!');
         }

         // Move uploaded files
         move_uploaded_file($image_tmp_name_01, $image_folder_01);
         move_uploaded_file($image_tmp_name_02, $image_folder_02);
         move_uploaded_file($image_tmp_name_03, $image_folder_03);

         // Commit transaction
         $conn->commit();
         $message[] = 'new product added!';

      } catch (Exception $e) {
         // Rollback transaction on error
         $conn->rollBack();
         $message[] = $e->getMessage();
      }
   }  

};

if(isset($_GET['delete'])){
   try {
       // Start transaction
       $conn->beginTransaction();
       
       $delete_id = $_GET['delete'];
       
       // First check if product exists and get image info
       $check_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
       $check_product->execute([$delete_id]);
       $product = $check_product->fetch(PDO::FETCH_ASSOC);
       
       if(!$product) {
           throw new Exception('Product not found!');
       }
       
       // Delete images if they exist
       $upload_path = '../uploaded_img/';
       
       // Function to safely delete file
       function safeUnlink($upload_path, $filename) {
           if(!empty($filename) && file_exists($upload_path . $filename)) {
               unlink($upload_path . $filename);
           }
       }
       
       // Delete all product images
       safeUnlink($upload_path, $product['image_01']);
       safeUnlink($upload_path, $product['image_02']);
       safeUnlink($upload_path, $product['image_03']);
       
       // Delete related records first (to maintain referential integrity)
       // Delete from cart
       $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
       $delete_cart->execute([$delete_id]);
       
       // Delete from wishlist
       $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
       $delete_wishlist->execute([$delete_id]);
       
       // Finally delete the product
       $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
       $delete_product->execute([$delete_id]);
       
       // Commit transaction
       $conn->commit();
       
       // Set success message
       $_SESSION['message'] = 'Product deleted successfully!';
       
   } catch(Exception $e) {
       // Rollback transaction on error
       $conn->rollBack();
       $_SESSION['error'] = 'Error deleting product: ' . $e->getMessage();
   }
   
   // Redirect regardless of outcome
   header('location:products.php');
   exit();
}

// Display messages if they exist
if(isset($_SESSION['message'])) {
   echo '<div class="success-message">'.$_SESSION['message'].'</div>';
   unset($_SESSION['message']);
}
if(isset($_SESSION['error'])) {
   echo '<div class="error-message">'.$_SESSION['error'].'</div>';
   unset($_SESSION['error']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Products</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
 
   <style>
   /* Add some CSS to make the color selection more user-friendly */
   select[multiple] {
       min-height: 120px;
   }

   select option {
       padding: 5px;
       margin: 2px;
       border-radius: 3px;
   }

   select option:checked {
       background-color: #007bff;
       color: white;
   }

   /* ------------delete message------------- */
   .success-message {
       background-color: #d4edda;
       color: #155724;
       padding: 1rem;
       margin: 1rem 0;
       border-radius: 4px;
       border: 1px solid #c3e6cb;
   }

   .error-message {
       background-color: #f8d7da;
       color: #721c24;
       padding: 1rem;
       margin: 1rem 0;
       border-radius: 4px;
       border: 1px solid hsl(354, 70.10%, 86.90%);
   }
   .box .price span {
       color: #e74c3c;
       font-weight: bold;
   }

   .box .color {
       font-size: 1.2rem;
       color: #666;
       margin-bottom: 0.5rem;
   }
   </style>

   <script>
   // Enhance the multiple select functionality for colors
   document.addEventListener('DOMContentLoaded', function() {
       const colorSelect = document.querySelector('select[name="colors[]"]');
       if(colorSelect) {
           // Allow clicking anywhere on the option to select it
           colorSelect.addEventListener('mousedown', function(e) {
               e.preventDefault();
               const option = e.target.closest('option');
               if(option) {
                   option.selected = !option.selected;
               }
           });
       }
   });
   </script>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="add-products">

   <h1 class="heading">Add Product</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>Product Name (required)</span>
            <input type="text" class="box" required maxlength="100" placeholder="enter product name" name="name">
         </div>
         <div class="inputBox">
            <span>Product Price (required)</span>
            <input type="number" min="0" class="box" required max="9999999999" placeholder="enter product price" onkeypress="if(this.value.length == 10) return false;" name="price">
         </div>
         <!-- Color selection input -->
         <div class="inputBox">
            <span>Product Colors (required)</span>
            <select name="colors[]" class="box" multiple required>
                <?php
                // Fetch colors from the database
                $select_colors = $conn->prepare("SELECT * FROM tbl_color ORDER BY color_name ASC");
                $select_colors->execute();
                while($color = $select_colors->fetch(PDO::FETCH_ASSOC)){
                    echo "<option value='".$color['color_id']."'>";
                    echo $color['color_name'];
                    if(!empty($color['color_code'])) {
                        echo " <span style='background-color: ".$color['color_code'].";'></span>";
                    }
                    echo "</option>";
                }
                ?>
            </select>
         </div>
      </div>
      <div class="flex">
         <div class="inputBox">
            <span>Image 01 (required)</span>
            <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
         </div>
         <div class="inputBox">
            <span>Image 02 (required)</span>
            <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
         </div>
         <div class="inputBox">
            <span>Image 03 (required)</span>
            <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
         </div>
         <div class="inputBox">
            <span>Product description (required)</span>
            <textarea name="details" placeholder="enter product details" class="box" required maxlength="500" cols="30" rows="10"></textarea>
         </div>
      </div>
      
      <input type="submit" value="add product" class="btn" name="add_product">
   </form>

</section>

<section class="show-products">

   <h1 class="heading">Products Added.</h1>

   <div class="box-container">
   <?php
      // Prepare a mapping of color IDs to color details for display in product list
      $colorsMap = [];
      $select_all_colors = $conn->prepare("SELECT * FROM tbl_color");
      $select_all_colors->execute();
      while($color = $select_all_colors->fetch(PDO::FETCH_ASSOC)){
          $colorsMap[$color['color_id']] = $color;
      }

      $select_products = $conn->prepare("SELECT * FROM `products`");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <div class="box">
      <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
      <div class="price">Nrs.<span><?= htmlspecialchars($fetch_products['price']); ?></span>/-</div>
      <div class="color">Color:- <span>
         <?php 
         // Decode the JSON stored color IDs and map them to color names
         $selectedColors = json_decode($fetch_products['colors'], true);
         $colorNames = [];
         if(is_array($selectedColors)){
             foreach($selectedColors as $colorId){
                 if(isset($colorsMap[$colorId])){
                     $colorNames[] = $colorsMap[$colorId]['color_name'];
                 }
             }
         }
         echo implode(', ', $colorNames);
         ?>
      </span></div>
      <div class="details"><span><?= htmlspecialchars($fetch_products['details']); ?></span></div>
      <div class="flex-btn">
         <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
         <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">no products added yet!</p>';
      }
   ?>
   </div>

</section>

<script src="../js/admin_script.js"></script>
   
</body>
</html>