<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $directoryDestinationAdmin = 'resources/dashboard/assets/img/administrator/';
    $directoryDestinationReserarcher = 'resources/dashboard/assets/img/researcher/';
    if($_SESSION['log_type'] == 'A'){
        $directoryDestination = $directoryDestinationAdmin;
    }else{
        $directoryDestination = $directoryDestinationReserarcher;
    } 
    $file = $_FILES['profile_photo'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = hash('md5',uniqid()) . '.' . $extension;
    $pathComplete = $directoryDestination . $filename;

    // Creates the directory if it does not exist
    if (!is_dir($directoryDestination)) {
        mkdir($directoryDestination, 0755, true);
    }

    // Allowed file types
    $typesallowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    // Checks if the file is a valid image
    if (in_array($_FILES['profile_photo']['type'], $typesallowed)) {
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $pathComplete)) {
            
            $_SESSION['photo'] = $filename;
            header('Location: '.$_ENV['BASE_URL'].'dashboard/administrator/update-foto/' . $_POST['fk_login_log_id']);
        } else {
            echo "Error sending photo.";
        }
    } else {
        echo "File format not allowed. Please upload a JPEG, PNG, or GIF image.";
    }
} else {
    echo "No files sent.";
}
?>