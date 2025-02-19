<?php
// Make sure to include your database connection before using any statements
// For example: include 'db_connect.php';

if (isset($_POST['add_to_wishlist'])) {
    if ($user_id == '') {
        header('location:user_login.php');
        exit;
    } else {
        // Retrieve and sanitize inputs
        $pid   = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
        $name  = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
        $image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);

        // Retrieve colors from POST (expected as an array), if any
        $colors = isset($_POST['colors']) ? $_POST['colors'] : [];
        // Uncomment the next line to log debug info (ensure error_log is enabled)
        // error_log("Wishlist POST Colors: " . print_r($colors, true));

        // Check if product already exists in wishlist
        $check_wishlist_stmt = $conn->prepare("SELECT id FROM wishlist WHERE name = ? AND user_id = ?");
        $check_wishlist_stmt->execute([$name, $user_id]);

        // Check if product already exists in cart
        $check_cart_stmt = $conn->prepare("SELECT id FROM cart WHERE name = ? AND user_id = ?");
        $check_cart_stmt->execute([$name, $user_id]);

        if ($check_wishlist_stmt->rowCount() > 0) {
            $message[] = 'already added to wishlist!';
        } elseif ($check_cart_stmt->rowCount() > 0) {
            $message[] = 'already added to cart!';
        } else {
            // Insert the new wishlist item, encoding the colors array as JSON
            $insert_wishlist_stmt = $conn->prepare("INSERT INTO wishlist (user_id, pid, name, price, image, colors) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_wishlist_stmt->execute([$user_id, $pid, $name, $price, $image, json_encode($colors)]);
            $message[] = 'added to wishlist!';
        }
    }
}

if (isset($_POST['add_to_cart'])) {
    try {
        // Check if user is logged in
        if ($user_id == '') {
            header('location:user_login.php');
            exit();
        }

        // Sanitize input data
        $pid   = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
        $name  = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        // Using FILTER_SANITIZE_NUMBER_FLOAT with FILTER_FLAG_ALLOW_FRACTION in case price contains decimals
        $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);
        $qty   = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);

        // Retrieve selected color value from the form input (expects a single value from a select menu with name="color")
        // If your form is using a multiple select (named as "colors[]"), adjust accordingly.
        $selectedColor = isset($_POST['color']) ? filter_var($_POST['color'], FILTER_SANITIZE_STRING) : '';

        // Debug: Log the selected color to help diagnose if it's sent correctly
        error_log("Cart POST Selected Color: " . $selectedColor);

        // Validate required fields
        if (empty($pid) || empty($name) || empty($price) || empty($image)) {
            throw new Exception('Missing required data');
        }

        // Check if item already exists in cart
        $checkCartStmt = $conn->prepare("SELECT id FROM cart WHERE name = ? AND user_id = ?");
        $checkCartStmt->execute([$name, $user_id]);

        if ($checkCartStmt->rowCount() > 0) {
            throw new Exception('Item already added to cart!');
        }

        // Remove from wishlist if exists
        $checkWishlistStmt = $conn->prepare("SELECT id FROM wishlist WHERE name = ? AND user_id = ?");
        $checkWishlistStmt->execute([$name, $user_id]);

        if ($checkWishlistStmt->rowCount() > 0) {
            $deleteWishlistStmt = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
            $deleteWishlistStmt->execute([$name, $user_id]);
        }

        // Prepare cart insertion query.
        // Note: The 'colors' column will store the chosen color as a simple string.
        $insertCartStmt = $conn->prepare("
            INSERT INTO cart 
            (user_id, pid, name, price, quantity, image, colors) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        // Use the selected color directly. If no color is selected, it will be stored as an empty string.
        $colorValue = !empty($selectedColor) ? $selectedColor : '';

        // Debug: Log the color value that will be stored
        error_log("Color value to be stored in cart: " . $colorValue);

        if (!$insertCartStmt->execute([$user_id, $pid, $name, $price, $qty, $image, $colorValue])) {
            throw new Exception('Failed to add item to cart');
        }

        // Return success response if used via AJAX, otherwise you may set a message variable.
        $message[] = 'Item added to cart successfully!';
        echo json_encode(['status' => 'success', 'message' => $message]);
    } catch (Exception $e) {
        // Return error response
        $message[] = $e->getMessage();
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
}
?>