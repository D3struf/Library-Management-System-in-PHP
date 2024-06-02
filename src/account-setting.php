<?php
    session_start();
    include('../config/db.php');

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: sign-in.php'); // Redirect to login page if not logged in
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Fetch user data
    $query = "SELECT `account`.`EmailAddress`, `account`.`Password`, `account`.`AccountType`, `librarymember`.`FirstName`, `librarymember`.`LastName`, `librarymember`.`MembershipType`, `librarymember`.`ProfileImage`  
    FROM `account` 
    JOIN `librarymember` ON `account`.`AccountID` = `librarymember`.`AccountID` 
    WHERE `account`.`EmailAddress` = '$user_id'";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
    } else {
        echo "User not found.";
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Ease</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo.png">

    <!-- js -->
    <script src="../assets/script/script.js"></script>

    <!-- css -->
    <style>
        .prevent-select {
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10 and IE 11 */
            user-select: none; /* Standard syntax */
        }
    </style>

    <!-- Tailwind config -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ChartJS -->
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Flowbite -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: '#18191a',
                        card: '#242526',
                        hover: '#3a3b3c',
                        shadow: '#111111',
                        primary_blue: '#1a31cd',
                        primary_text: '#e4e6eb',
                        secondary_text: '#b0b3b8',

                        lightgray: '#1e1e1e',
                        darkgray: '#1C1C1C',
                        lightgreen: '#4F653A',
                        darkgreen: '#2B3720'
                    }
                }
            }
        }
    </script>
</head>
<body class=" bg-background prevent-select">
    <header class=" bg-card shadow-lg shadow-shadow">
        <div class="flex items-center w-full px-6 py-2 justify-between ">
            <div class=" flex flex-row max-sm:flex-row-reverse m-0 max-sm:w-full max-sm:justify-end">
                <div class=" flex justify-center w-full">
                    <img src="../assets/img/logo.png" alt="tailwind-logo" class="h-10 w-10">
                </div>
                <div class=" flex items-center max-sm:flex-col-reverse max-sm:items-start">
                    <ul id="navigation" class=" flex flex-row gap-6 px-8 text-gray-400 font-medium max-sm:hidden max-sm:flex-col max-sm:px-4 max-sm:absolute max-sm:top-14 max-sm:bg-slate-800 max-sm:w-full max-sm:left-0 max-sm:gap-1 max-sm:pb-3 max-sm:rounded-b-lg">
                        <a class="py-2 px-3 rounded-md hover:bg-hover hover:text-primary_text" href="<?php if ($user_id == "admin") {echo './Admin/index.php';} else {echo './Client/dashboard.php';}?>"><li>Dashboard</li></a>
                        <a class="py-2 px-3 rounded-md hover:bg-hover hover:text-primary_text" href="<?php if ($user_id == "admin") {echo './Admin/book.php';} else {echo './Client/client.php';}?>"><li>Books</li></a>
                        <?php 
                            if ($user_id == "admin") {
                                echo '<a class="py-2 px-3 rounded-md hover:bg-hover hover:text-primary_text" href="./Admin/member.php"><li>Members</li></a><a class="py-2 px-3 rounded-md hover:bg-hover hover:text-primary_text" href="./Admin/checkout.php"><li>Checkout</li></a>';
                            }
                        ?>
                    </ul>
                    <div class=" max-sm:text-gray-400 max-sm:transition-all ">
                        <button onclick="activate()" class=" max-sm:p-2 max-sm:rounded-md max-sm:hover:bg-slate-700 max-sm:hover:text-gray-100 max-sm:cursor-pointer max-sm:active:ring-offset-1 max-sm:active:ring-1 max-sm:active:ring-gray-200">
                            <svg id="cross" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <svg id="burger" class="h-6 w-6 hidden max-sm:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Account Settings & Sign Out -->
            <div class=" flex flex-row items-center gap-4 text-gray-400 max-sm:right-0 group ">
                <h4 class=" font-medium text-gray-400 group-hover:text-gray-100 group-hover:cursor:pointer"><?php if ($user['Password'] == 'admin') {echo 'Admin';} else {echo htmlspecialchars($user['FirstName']); echo ' '; echo htmlspecialchars($user['LastName']);}?></h4>
                <button id="toggle" onclick="buttonToggle()" type="button" class="h-10 w-10 rounded-full cursor-pointer active:ring-offset-1 active:ring-1 active:ring-gray-200">
                    <?php if ($user['ProfileImage']): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($user['ProfileImage']); ?>" alt="Profile Image" class="h-8 w-8 mx-1 rounded-full">
                    <?php else: ?>
                        <svg class="text-gray-300 h-8 w-8 mx-1" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0021.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 003.065 7.097A9.716 9.716 0 0012 21.75a9.716 9.716 0 006.685-2.653zm-12.54-1.285A7.486 7.486 0 0112 15a7.486 7.486 0 015.855 2.812A8.224 8.224 0 0112 20.25a8.224 8.224 0 01-5.855-2.438zM15.75 9a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" clip-rule="evenodd" />
                        </svg>
                    <?php endif; ?>
                </button>
                <div id="settings" class="absolute bg-card rounded-md py-1 border border-shadow top-12 right-9 text-primary_text font-normal text-base leading-6 shadow-md hidden z-50">
                    <ul class="flex flex-col">
                        <a href="account-setting.php" class="relative inline-flex items-center py-2 pl-4 pr-20 hover:bg-hover">
                            <svg class="w-3 h-3 me-3"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path fill="#ffffff" d="M0 416c0 17.7 14.3 32 32 32l54.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-246.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 384c-17.7 0-32 14.3-32 32zm128 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zM320 256a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm32-80c-32.8 0-61 19.7-73.3 48L32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l246.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48l54.7 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-54.7 0c-12.3-28.3-40.5-48-73.3-48zM192 128a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm73.3-64C253 35.7 224.8 16 192 16s-61 19.7-73.3 48L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l86.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 128c17.7 0 32-14.3 32-32s-14.3-32-32-32L265.3 64z"/>
                            </svg>
                            <li>Account Settings</li></a>
                        <a href="about.php" class="relative inline-flex items-center py-2 pl-4 pr-20 hover:bg-hover">
                            <svg class="w-3 h-3 me-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0,0,256,256" width="48px" height="48px">
                                <g fill="#ffffff" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.33333,5.33333)"><path d="M24,4c-11.02771,0 -20,8.97229 -20,20c0,3.27532 0.86271,6.33485 2.26172,9.06445l-2.16797,7.76367c-0.50495,1.8034 1.27818,3.58449 3.08203,3.08008l7.76758,-2.16797c2.72769,1.39712 5.7836,2.25977 9.05664,2.25977c11.02771,0 20,-8.97229 20,-20c0,-11.02771 -8.97229,-20 -20,-20zM24,7c9.40629,0 17,7.59371 17,17c0,9.40629 -7.59371,17 -17,17c-3.00297,0 -5.80774,-0.78172 -8.25586,-2.14648c-0.34566,-0.19287 -0.75354,-0.24131 -1.13477,-0.13477l-7.38672,2.0625l2.0625,-7.38281c0.10655,-0.38122 0.05811,-0.7891 -0.13477,-1.13477c-1.36674,-2.4502 -2.15039,-5.25915 -2.15039,-8.26367c0,-9.40629 7.59371,-17 17,-17zM23.97656,12.97852c-0.82766,0.01293 -1.48843,0.69381 -1.47656,1.52148v12c-0.00765,0.54095 0.27656,1.04412 0.74381,1.31683c0.46725,0.27271 1.04514,0.27271 1.51238,0c0.46725,-0.27271 0.75146,-0.77588 0.74381,-1.31683v-12c0.00582,-0.40562 -0.15288,-0.7963 -0.43991,-1.08296c-0.28703,-0.28666 -0.67792,-0.44486 -1.08353,-0.43852zM24,31c-1.10457,0 -2,0.89543 -2,2c0,1.10457 0.89543,2 2,2c1.10457,0 2,-0.89543 2,-2c0,-1.10457 -0.89543,-2 -2,-2z"></path></g></g>
                            </svg>
                            <li class="">About</li>
                        </a>
                        <a href="sign-in.php" class="relative inline-flex items-center py-2 pl-4 pr-20 hover:bg-hover">
                            <svg class="w-4 h-4 me-2.5" data-name="Design Convert" id="Design_Convert" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" >
                                <defs><style>.cls-1{fill:#ffffff;}</style></defs><title/><path class="cls-1" d="M55,28H34a1,1,0,0,1,0-2H55a1,1,0,0,1,0,2Z"/><path class="cls-1" d="M28,57a1,1,0,0,1-.45-.11L8.66,47.45A3,3,0,0,1,7,44.76V10a3,3,0,0,1,3-3h9a1,1,0,0,1,0,2H11.34l17.09,8.1A1,1,0,0,1,29,18V56a1,1,0,0,1-.47.85A1,1,0,0,1,28,57ZM9,10.11V44.76a1,1,0,0,0,.55.9L27,54.38V18.63Z"/><path class="cls-1" d="M47,37a1,1,0,0,1-.71-.29,1,1,0,0,1,0-1.42L54.59,27l-8.3-8.29a1,1,0,0,1,1.42-1.42l9,9a1,1,0,0,1,0,1.42l-9,9A1,1,0,0,1,47,37Z"/><path class="cls-1" d="M37,47H28a1,1,0,0,1,0-2h9a1,1,0,0,0,1-1V36a1,1,0,0,1,2,0v8A3,3,0,0,1,37,47Z"/><path class="cls-1" d="M39,19a1,1,0,0,1-1-1V10a1,1,0,0,0-1-1H15a1,1,0,0,1,0-2H37a3,3,0,0,1,3,3v8A1,1,0,0,1,39,19Z"/>
                            </svg>
                            <li>Sign out</li>
                        </a>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <?php include('../config/db.php'); ?>
    
    <section id="personal-information" class=" flex flex-row justify-center gap-10">
        <article class=" mt-10 ">
            <h2 class=" leading-6 text-xl font-bold text-primary_text">Personal Information</h2>
            <h3 class=" leading-4 text-normal font-normal text-secondary_text mt-2">Use your main email account where you can receive mail.</h3>
        </article>
        <article class=" mt-10 w-[700px] py-5 px-10 bg-card shadow-lg shadow-shadow rounded-lg">
            <form action="./Utils/personal-update.php" method="post" enctype="multipart/form-data">
                <div class=" flex flex-col">
                    <label for="photo" class=" leading-4 text-normal font-medium text-primary_text mt-2">Upload Avatar</label>
                    <div class=" my-3 flex items-center">
                        <?php if ($user['ProfileImage']): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['ProfileImage']); ?>" alt="Profile Image" class=" h-24 w-24 mx-1 rounded-full">
                        <?php else: ?>
                            <svg class="text-gray-300 h-24 w-24 mx-1" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0021.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 003.065 7.097A9.716 9.716 0 0012 21.75a9.716 9.716 0 006.685-2.653zm-12.54-1.285A7.486 7.486 0 0112 15a7.486 7.486 0 015.855 2.812A8.224 8.224 0 0112 20.25a8.224 8.224 0 01-5.855-2.438zM15.75 9a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" clip-rule="evenodd" />
                            </svg>
                        <?php endif; ?>

                        <div>
                            <label for="file-upload"></label>
                            <input type="file" name="file-upload" id="file-upload" class=" ms-3 block w-full text-sm border rounded-lg cursor-pointer text-gray-400 focus:outline-none bg-gray-700 border-gray-600 placeholder-gray-400" <?php if ($user['Password'] == 'admin') echo 'disabled'; ?>>
                            <p class=" text-secondary_text mt-2 ml-3 font-normal">PNG, JPG, JPEG, or GIF (MAX. 16 Mb)</p>
                        </div>
                    </div>
                </div>
                <div class=" flex max-sm:flex-col">
                    <div class=" flex flex-col flex-1 pr-5 max-sm:pr-0 max-sm:mb-5">
                        <label for="first-name" class=" leading-4 text-normal font-medium text-primary_text mt-2">First Name</label>
                        <input type="text" name="first-name" value="<?php echo htmlspecialchars($user['FirstName']); ?>" class=" mt-3 border border-gray-300 rounded-md h-10 shadow-sm ring-inset focus-within:ring-2 focus-within:ring-indigo-600 focus-within:border-indigo-600" <?php if ($user['FirstName'] == 'admin') echo 'disabled'; ?>>
                    </div>
                    <div class=" flex flex-col flex-1">
                        <label for="last-name" class=" leading-4 text-normal font-medium text-primary_text mt-2">Last Name</label>
                        <input type="text" name="last-name" value="<?php echo htmlspecialchars($user['LastName']); ?>" class=" mt-3 border border-gray-300 rounded-md h-10 shadow-sm ring-inset focus-within:ring-2 focus-within:ring-indigo-600 focus-within:border-indigo-600" <?php if ($user['LastName'] == 'admin') echo 'disabled'; ?>>
                    </div>
                </div>
                <div class=" flex flex-col mt-5 max-sm:pr-0">
                    <label for="email" class=" leading-4 text-normal font-medium text-primary_text mt-2">Email address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['EmailAddress']); ?>" class=" mt-3 border border-gray-300 rounded-md h-10 shadow-sm ring-inset focus-within:ring-2 focus-within:ring-indigo-600 focus-within:border-indigo-600" <?php if ($user['EmailAddress'] == 'admin') echo 'disabled'; ?>>
                </div>
                <div class=" flex flex-col mt-5 max-sm:pr-40">
                    <label for="membership-type" class=" leading-4 text-normal font-medium text-primary_text mt-2">Membership Type</label>
                    <select name="membership-type" id="membership-type" class=" border border-gray-300 rounded-md h-10 shadow-sm mt-3 px-3 max-sm:bg-slate-50 max-sm:pt-1" disabled>
                        <option value="" selected disabled>Select Membership Type</option>
                        <option value="student" <?php if ($user['MembershipType'] == 'student') echo 'selected'; ?>>Student</option>
                        <option value="basic" <?php if ($user['MembershipType'] == 'basic') echo 'selected'; ?>>Basic</option>
                        <option value="premium" <?php if ($user['MembershipType'] == 'premium') echo 'selected'; ?>>Premium</option>
                    </select>
                </div>
                <button type="submit" name="save-personal" class=" rounded-md mt-8 py-2 px-4 bg-green-600 hover:bg-green-400"  <?php if ($user['FirstName'] == 'admin') echo 'disabled'; ?>>Save</button>
            </form>
        </article>
    </section>
    <section id="security" class=" flex flex-row justify-center gap-10">
        <article class=" mt-10 pr-14">
            <h2 class=" leading-6 text-xl font-bold text-primary_text">Security</h2>
            <h3 class=" leading-4 text-normal font-normal text-secondary_text mt-2">Use a strong password for more secure account.</h3>
        </article>
        <article class=" mt-10 w-[700px] py-5 px-10 bg-card shadow-lg shadow-shadow rounded-lg">
            <form action="./Utils/password-validation.php" method="post">
                <div class=" flex flex-col mt-5 max-sm:pr-0">
                    <label for="password" class=" leading-4 text-normal font-medium text-primary_text mt-2">Old Password</label>
                    <input type="password" name="old-password" id="old-password" class=" mt-3 border border-gray-300 rounded-md h-8 shadow-sm ring-inset focus-within:ring-2 focus-within:ring-indigo-600 focus-within:border-indigo-600" <?php if ($user['Password'] == 'admin') echo 'disabled'; ?>>
                </div>
                <div class=" flex flex-col mt-5 max-sm:pr-0">
                    <label for="password" class=" leading-4 text-normal font-medium text-primary_text mt-2">New Password</label>
                    <input type="password" name="new-password" id="new-password" class=" mt-3 border border-gray-300 rounded-md h-8 shadow-sm ring-inset focus-within:ring-2 focus-within:ring-indigo-600 focus-within:border-indigo-600" <?php if ($user['Password'] == 'admin') echo 'disabled'; ?>>
                </div>
                <div class=" flex flex-col mt-5 max-sm:pr-0">
                    <label for="password" class=" leading-4 text-normal font-medium text-primary_text mt-2">Confirm New Password</label>
                    <input type="password" name="confirm-new-password" id="confirm-new-password" class=" mt-3 border border-gray-300 rounded-md h-8 shadow-sm ring-inset focus-within:ring-2 focus-within:ring-indigo-600 focus-within:border-indigo-600" <?php if ($user['Password'] == 'admin') echo 'disabled'; ?>>
                </div>
                <button type="submit" name="save-password" id="save-password" class=" rounded-md mt-8 py-2 px-4 bg-green-600 hover:bg-green-400" <?php if ($user['FirstName'] == 'admin') echo 'disabled'; ?>>Save</button>
            </form>
        </article>
    </section>

    <!-- Password Validation AND Update Validation -->
    <?php
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            echo '<div id="toast-danger" class="absolute right-5 top-24 flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800" role="alert">';
            echo '<div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">';
            echo '<svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">';
            echo '<path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>';
            echo '</svg>';
            echo '<span class="sr-only">Error icon</span>';
            echo '</div>';
            echo '<div class="ms-3 text-sm font-normal">' . $error . '</div>';
            echo '<button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-danger" aria-label="Close">';
            echo '<span class="sr-only">Close</span>';
            echo '<svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">';
            echo '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>';
            echo '</svg>';
            echo '</button>';
            echo '</div>';
        } elseif (isset($_GET['success'])) {
            $success = $_GET['success'];
            
            echo '<div id="toast-success" class="absolute right-5 top-24 flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800" role="alert">';
            echo '<div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">';
            echo '<svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">';
            echo '<path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>';
            echo '</svg>';
            echo '<span class="sr-only">Check icon</span>';
            echo '</div>';
            echo '<div class="ms-3 text-sm font-normal">' . $success . '</div>';
            echo '<button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-success" aria-label="Close">';
            echo '<span class="sr-only">Close</span>';
            echo '<svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">';
            echo '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>';
            echo '</svg>';
            echo '</button>';
            echo '</div>';
        }
    ?>
    
    <footer class=" bottom-0 mt-10 w-full text-center mx-auto backdrop-blur-md py-3"> 
        <p class=" text-normal font-semibold text-secondary_text">© 2024 John Paul Monter</p>
    </footer>
</body>
</html>