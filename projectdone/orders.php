<?php
include 'components/connect.php';
include 'utils/ColorConverter.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';

// Color utility class
class ColorConverter {
    private static $colorMap = [
        '#ff0000' => 'Red',
        '#00ff00' => 'Green',
        '#0000ff' => 'Blue',
        '#ffffff' => 'White',
        '#000000' => 'Black',
        '#ffff00' => 'Yellow',
        '#800080' => 'Purple',
        '#ffa500' => 'Orange',
        // Add more colors as needed
    ];

    public static function getColorName($colorCode) {
        return self::$colorMap[strtolower($colorCode)] ?? $colorCode;
    }

    public static function getMultipleColorNames($colorCodes) {
        if (empty($colorCodes)) return '';
        $codes = explode(',', $colorCodes);
        return implode(', ', array_map([self::class, 'getColorName'], array_map('trim', $codes)));
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'components/user_header.php'; ?>

    <section class="orders">
        <h1 class="heading">Placed Orders</h1>
        <div class="box-container">
        <?php
        if($user_id == '') {
            echo '<p class="empty">Please login to see your orders</p>';
        } else {
            $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
            $select_orders->execute([$user_id]);
            
            if($select_orders->rowCount() > 0) {
                while($order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="box">
                        <p>Placed on : <span><?= htmlspecialchars($order['placed_on']) ?></span></p>
                        <p>Name : <span><?= htmlspecialchars($order['name']) ?></span></p>
                        <p>Email : <span><?= htmlspecialchars($order['email']) ?></span></p>
                        <p>Phone Number : <span><?= htmlspecialchars($order['number']) ?></span></p>
                        <p>Address : <span><?= htmlspecialchars($order['address']) ?></span></p>
                        <p>Payment Method : <span><?= htmlspecialchars($order['method']) ?></span></p>
                        <p>Color : <span><?= ColorConverter::getMultipleColorNames($order['colors']) ?></span></p>
                        <p>Your orders : <span><?= htmlspecialchars($order['total_products']) ?></span></p>
                        <p>Total price : <span>Nrs.<?= htmlspecialchars($order['total_price']) ?>/-</span></p>
                        <p>Payment status : 
                            <span style="color:<?= $order['payment_status'] == 'pending' ? 'red' : 'green' ?>">
                                <?= htmlspecialchars($order['payment_status']) ?>
                            </span>
                        </p>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="empty">No orders placed yet!</p>';
            }
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

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="css/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/css/linearicons.css">
    <link rel="stylesheet" href="css/css/animate.css">
    <link rel="stylesheet" href="css/css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/css/bootsnav.css">
    <link rel="stylesheet" href="css/css/style.css">
    <link rel="stylesheet" href="css/css/responsive.css">

    <script src="js/script.js"></script>
</body>
</html>